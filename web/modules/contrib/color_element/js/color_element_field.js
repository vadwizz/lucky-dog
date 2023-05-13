(function ($, Drupal) {

    'use strict';

    /**
     * Enables box widget on color elements.
     *
     * @type {Drupal~behavior}
     *
     * @prop {Drupal~behaviorAttach} attach
     *   Attaches a box widget to a color input element.
     */
    Drupal.behaviors.color_element = {
        attach: function (context, settings) {

            var $context = $(context);

            $('.color-element-swatch .inner').once('colorElement').each(
                function (index, value) {
                    $(this).css({ "background-color": $(this).parent().data('swatch-color') });
                    $(this).click(
                        function () {
                            $(this).parent().addClass('selected').siblings().removeClass('selected');
                            var selected_colour = $(this).parent().data('swatch-color');
                            $(this).parent().parent().parent().find('input').val(selected_colour);
                        }
                    );
                }
            );

            $context.find('.color-element').once('colorElement').each(
                function (index, element) {
                    var $element = $(element);
                    var $input = $element.prev().find('input');
                    $input.hide();
                }
            );

        },
    };

})(jQuery, Drupal);
