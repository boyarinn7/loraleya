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

/* ===== Доточка карточки v2 (ТЗ-7): количество −/+ и звёзды отзыва ===== */

// Количество: кнопки − / + вместо нативных стрелок
jQuery(function ($) {
    $('.single-product .quantity').each(function () {
        var $q = $(this);
        if ($q.hasClass('ll-qty')) return;
        var $input = $q.find('input.qty');
        if (!$input.length) return;
        $q.addClass('ll-qty');
        $('<button type="button" class="ll-qty-btn ll-qty-minus" aria-label="Меньше">−</button>').prependTo($q);
        $('<button type="button" class="ll-qty-btn ll-qty-plus" aria-label="Больше">+</button>').appendTo($q);
        $q.on('click', '.ll-qty-minus', function () {
            var v = parseInt($input.val(), 10) || 1;
            var min = parseInt($input.attr('min'), 10) || 1;
            if (v > min) $input.val(v - 1).trigger('change');
        });
        $q.on('click', '.ll-qty-plus', function () {
            var v = parseInt($input.val(), 10) || 0;
            var max = parseInt($input.attr('max'), 10) || 0;
            if (!max || v < max) $input.val(v + 1).trigger('change');
        });
    });
});

// Звёзды отзыва: 5 золотых звёзд, клик заполняет
jQuery(function ($) {
    var $rating = $('.single-product .comment-form-rating');
    if (!$rating.length) return;
    var $select = $rating.find('select[name="rating"]');
    if (!$select.length) return;

    $rating.find('p.stars').remove();
    $select.hide();

    var $stars = $('<div class="ll-stars"></div>');
    for (var i = 1; i <= 5; i++) {
        $stars.append('<span class="ll-star" data-v="' + i + '">☆</span>');
    }
    $select.before($stars);

    var current = parseInt($select.val(), 10) || 0;
    function paint(n) {
        $stars.find('.ll-star').each(function () {
            var v = $(this).data('v');
            $(this).text(v <= n ? '★' : '☆').toggleClass('on', v <= n);
        });
    }
    $stars.on('mouseenter', '.ll-star', function () { paint($(this).data('v')); });
    $stars.on('mouseleave', function () { paint(current); });
    $stars.on('click', '.ll-star', function () {
        current = $(this).data('v');
        $select.val(current);
        paint(current);
    });
    paint(current);
});
