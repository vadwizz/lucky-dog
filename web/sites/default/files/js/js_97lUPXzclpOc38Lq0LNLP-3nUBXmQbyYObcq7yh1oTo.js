/**
 * @file
 * Provides base widget behaviours.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Handles "facets_filter" event and triggers "facets_filtering".
   *
   * The facets module will listend and trigger defined events on elements with
   * class: "js-facets-widget".
   *
   * Events are doing following:
   * "facets_filter" - widget should trigger this event. The facets module will
   *   handle it accordingly in case of AJAX and Non-AJAX views.
   * "facets_filtering" - The facets module will trigger this event before
   *   filter is executed.
   *
   * This is an example how to trigger "facets_filter" event for your widget:
   *   $(once('my-custom-widget-on-change', '.my-custom-widget.js-facets-widget'))
   *     .on('change', function () {
   *       // In this example $(this).val() will provide needed URL.
   *       $(this).trigger('facets_filter', [ $(this).val(), false ]);
   *     });
   *
   * The facets module will trigger "facets_filtering" before filter is
   * executed. Widgets can listen on "facets_filtering" event and react before
   * filter is executed. Most common use case is to disable widget. When you
   * disable widget, a user will not be able to trigger new "facets_filter"
   * event before initial filter request is finished.
   *
   * This is an example how to handle "facets_filtering":
   *   $(once('my-custom-widget-on-facets-filtering', '.my-custom-widget.js-facets-widget'))
   *     .on('facets_filtering.my_widget_module', function () {
   *       // Let's say, that widget can be simply disabled (fe. select).
   *       $(this).prop('disabled', true);
   *     });
   *
   * You should namespace events for your module widgets. With namespaced events
   * you have better control on your handlers and if it's needed, you can easier
   * register/deregister them.
   */
  Drupal.behaviors.facetsFilter = {
    attach: function (context) {
      $(once('js-facet-filter', '.js-facets-widget', context))
        .on('facets_filter.facets', function (event, url) {
          $('.js-facets-widget').trigger('facets_filtering');

          window.location = url;
        });
    }
  };

})(jQuery, Drupal, once);
;
/**
 * @file
 * Transforms links into checkboxes.
 */

(function ($, Drupal, once) {

  'use strict';

  Drupal.facets = Drupal.facets || {};
  Drupal.behaviors.facetsCheckboxWidget = {
    attach: function (context) {
      Drupal.facets.makeCheckboxes(context);
    }
  };

  window.onbeforeunload = function(e) {
    if (Drupal.facets) {
      var $checkboxWidgets = $('.js-facets-checkbox-links, .js-facets-links');
      if ($checkboxWidgets.length > 0) {
        $checkboxWidgets.each(function (index, widget) {
          var $widget = $(widget);
          var $widgetLinks = $widget.find('.facet-item > a');
          $widgetLinks.each(Drupal.facets.updateCheckbox);
        });
      }
    }
  };

  /**
   * Turns all facet links into checkboxes.
   */
  Drupal.facets.makeCheckboxes = function (context) {
    // Find all checkbox facet links and give them a checkbox.
    var $checkboxWidgets = $(once('facets-checkbox-transform', '.js-facets-checkbox-links, .js-facets-links', context));

    if ($checkboxWidgets.length > 0) {
      $checkboxWidgets.each(function (index, widget) {
        var $widget = $(widget);
        var $widgetLinks = $widget.find('.facet-item > a');

        // Add correct CSS selector for the widget. The Facets JS API will
        // register handlers on that element.
        $widget.addClass('js-facets-widget');

        // Transform links to checkboxes.
        $widgetLinks.each(Drupal.facets.makeCheckbox);

        // We have to trigger attaching of behaviours, so that Facets JS API can
        // register handlers on checkbox widgets.
        Drupal.attachBehaviors(this.parentNode, Drupal.settings);
      });

    }

    // Set indeterminate value on parents having an active trail.
    $('.facet-item--expanded.facet-item--active-trail > input').prop('indeterminate', true);
  };

  /**
   * Replace a link with a checked checkbox.
   */
  Drupal.facets.makeCheckbox = function () {
    var $link = $(this);
    var active = $link.hasClass('is-active');
    var description = $link.html();
    var href = $link.attr('href');
    var id = $link.data('drupal-facet-item-id');
    var type = $link.data('drupal-facet-widget-element-class');

    var checkbox = $('<input type="checkbox">')
      .attr('id', id)
      .attr('name', $(this).closest('.js-facets-widget').data('drupal-facet-filter-key') + '[]')
      .addClass(type)
      .val($link.data('drupal-facet-filter-value'))
      .data($link.data())
      .data('facetsredir', href);

    var single_selection_group = $(this).data('drupal-facet-single-selection-group');
    if (single_selection_group) {
      checkbox.addClass(single_selection_group);
    }

    if (type === 'facets-link') {
      checkbox.hide();
    }

    var label = $('<label for="' + id + '">' + description + '</label>');

    checkbox.on('change.facets', function (e) {
      if ($link.data('drupal-facet-ajax') == 0) {
        e.preventDefault();

        var $widget = $(this).closest('.js-facets-widget');

        Drupal.facets.disableFacet($widget);
        $widget.trigger('facets_filter', [href]);
      }
      else {
        var current = $(this);
        if (current.is(':checked') && current.hasClass(single_selection_group)) {
          var $widget = current.closest('.js-facets-widget');
          $widget.find('input.' + single_selection_group + ':not(#' + current.attr('id') + ')').prop('checked', false);
        }
      }
    });

    if (active) {
      checkbox.prop('checked', true);
      label.addClass('is-active');
      label.find('.js-facet-deactivate').remove();
    }

    $link.before(checkbox).before(label).hide();

  };

  /**
   * Update checkbox active state.
   */
  Drupal.facets.updateCheckbox = function () {
    var $link = $(this);
    var active = $link.hasClass('is-active');

    if (!active) {
      $link.parent().find('input.facets-checkbox').prop('checked', false);
    }
  };

  /**
   * Disable all facet checkboxes in the facet and apply a 'disabled' class.
   *
   * @param {object} $facet
   *   jQuery object of the facet.
   */
  Drupal.facets.disableFacet = function ($facet) {
    $facet.addClass('facets-disabled');
    $('input.facets-checkbox, input.facets-link', $facet).click(Drupal.facets.preventDefault);
    $('input.facets-checkbox, input.facets-link', $facet).attr('disabled', true);
  };

  /**
   * Event listener for easy prevention of event propagation.
   *
   * @param {object} e
   *   Event.
   */
  Drupal.facets.preventDefault = function (e) {
    e.preventDefault();
  };

})(jQuery, Drupal, once);
;
