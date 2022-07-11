Drupal.behaviors.myblock = {
  attach: function (context, settings) {
    // Behavior вызывается несколько раз на странице, не забывайте использовать функцию .once().
    jQuery( "#tabs" ).tabs();
  }
};
