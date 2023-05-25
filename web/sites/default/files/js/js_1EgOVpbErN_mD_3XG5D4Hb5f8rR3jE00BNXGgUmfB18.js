/*!
 * jQuery Once v2.2.3 - http://github.com/robloach/jquery-once
 * @license MIT, GPL-2.0
 *   http://opensource.org/licenses/MIT
 *   http://opensource.org/licenses/GPL-2.0
 */
(function(e){"use strict";if(typeof exports==="object"&&typeof exports.nodeName!=="string"){e(require("jquery"))}else if(typeof define==="function"&&define.amd){define(["jquery"],e)}else{e(jQuery)}})(function(t){"use strict";var r=function(e){e=e||"once";if(typeof e!=="string"){throw new TypeError("The jQuery Once id parameter must be a string")}return e};t.fn.once=function(e){var n="jquery-once-"+r(e);return this.filter(function(){return t(this).data(n)!==true}).data(n,true)};t.fn.removeOnce=function(e){return this.findOnce(e).removeData("jquery-once-"+r(e))};t.fn.findOnce=function(e){var n="jquery-once-"+r(e);return this.filter(function(){return t(this).data(n)===true})}});

;
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
;
