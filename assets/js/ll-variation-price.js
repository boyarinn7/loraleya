(function ($) {
  'use strict';
  $(function () {
    var $form = $('form.variations_form');
    if (!$form.length) return;

    function priceEl() { return $form.find('.woocommerce-variation-price'); }

    function hidePrice() { priceEl().css('visibility', 'hidden'); }
    function showPrice() { priceEl().css('visibility', 'visible'); }

    $form.on('show_variation', function () { showPrice(); });
    $form.on('hide_variation', function () { hidePrice(); });
    $form.on('reset_data',    function () { hidePrice(); });

    $form.on('change', '.variations select', function () {
      var vid = $form.find('input.variation_id').val();
      if (!vid || vid === '0') { hidePrice(); } else { showPrice(); }
    });

    hidePrice();
  });
})(jQuery);
