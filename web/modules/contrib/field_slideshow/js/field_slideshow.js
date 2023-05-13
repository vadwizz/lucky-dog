/**
 * @file
 * Attaches behaviors for field slideshow.
 */

(function ($, Drupal) {
  Drupal.behaviors.field_slideshow = {
    attach: function (context, settings) {
      for (var i in settings.field_slideshow) {
        if (settings.field_slideshow.hasOwnProperty(i)) {
          var slideshowSettings = settings.field_slideshow[i];

          // Setup default options.
          slideshowSettings.slides = '> div';
          slideshowSettings.pager = '.cycle-pager-' + i;
          slideshowSettings.pagerTemplate = '';
          slideshowSettings.next = '.cycle-controls-next-' + i;
          slideshowSettings.prev = '.cycle-controls-prev-' + i;
          slideshowSettings.log = false;

          $('#' + i)
            .once('field-slideshow')
            .cycle(slideshowSettings);
        }
      }
    }
  };
})(jQuery, Drupal);
