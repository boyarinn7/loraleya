jQuery(function ($) {
    var $sel = $('select[name="attribute_pa_fabric_color"]');
    if (!$sel.length) return;

    var data = (window.LoraleyaSwatches || {}).colors || {};

    var $label = $('<div class="ll-sw-name"></div>');
    var $wrap  = $('<div class="ll-swatches"></div>');

    $sel.find('option').each(function () {
        var slug = $(this).val();
        if (!slug) return;
        var info = data[slug] || {};
        var $sw = $('<span class="ll-sw" data-slug="' + slug + '" title="' + (info.name || slug) + '"></span>');
        if (info.url) $sw.css('background-image', 'url(' + info.url + ')');
        $wrap.append($sw);
    });

    $sel.hide().after($wrap).after($label);

    function activate(slug) {
        $wrap.find('.ll-sw').removeClass('on');
        $wrap.find('.ll-sw[data-slug="' + slug + '"]').addClass('on');
        $label.text((data[slug] || {}).name || '');
    }

    $wrap.on('click', '.ll-sw', function () {
        var slug = $(this).data('slug');
        $sel.val(slug).trigger('change'); // WooCommerce сам обновит цену, фото, наличие, вариацию
        activate(slug);
    });

    if ($sel.val()) activate($sel.val());
    $(document.body).on('reset_data', function () {
        $wrap.find('.ll-sw').removeClass('on');
        $label.text('');
    });
});
