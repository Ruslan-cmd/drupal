'use strict';

(function ($) {
  Drupal.behaviors.objectFitImages = {
    attach: function attach(context, settings) {
      objectFitImages();
    }
  };
})(jQuery);

(function ($, Drupal) {
  Drupal.behaviors.main = {
    attach: function attach(context) {
      var items = document.querySelectorAll('.multiple-carousel-item');

      items.forEach(function (el) {
        var minPerSlide = 4;
        var next = el.nextElementSibling;
        for (var i = 1; i < minPerSlide; i++) {
          if (!next) {
            next = items[0];
          }
          var cloneChild = next.cloneNode(true);
          el.appendChild(cloneChild.children[0]);
          next = next.nextElementSibling;
        }
      });

      $('.sort__name-select').select2({
        minimumResultsForSearch: -1
      });

      $('.sort__price-select').select2({
        minimumResultsForSearch: -1
      });
    }
  };
})(jQuery, Drupal);