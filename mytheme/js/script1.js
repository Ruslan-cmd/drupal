Drupal.behaviors.myblock1 = {
  attach: function (context, settings) {
    // Behavior вызывается несколько раз на странице, не забывайте использовать функцию .once().
    jQuery( "#tabs" ).tabs();
    jQuery('a[href="#tabs-2"]').click();
  }
};
