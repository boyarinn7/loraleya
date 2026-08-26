jQuery(function ($) {
    var $sel = $('select[name="attribute_pa_fabric_color"]');
    if (!$sel.length) return;
    var $form = $sel.closest('form.variations_form');

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

    // Фото товара по выбранному цвету: фото вариации ИМЕННО этого товара (не общая фактура)
    function colorPhoto(slug) {
        var pc = (window.LoraleyaProductColors || {}).images || {};
        return pc[slug] || '';
    }
    function setGalleryImage(slug) {
        var url = colorPhoto(slug);
        if (!url) return; // нет фото этого цвета у товара — НЕ подменяем (не показываем чужую картинку)
        var $img = $('.woocommerce-product-gallery__wrapper .wp-post-image').first();
        if (!$img.length) $img = $('.woocommerce-product-gallery img').first();
        if (!$img.length) return;
        $img.attr('src', url).attr('srcset', '').removeAttr('data-src').removeAttr('data-large_image');
    }

    var userPicked = false;

    $wrap.on('click', '.ll-sw', function () {
        var slug = $(this).data('slug');
        userPicked = true;
        $sel.val(slug).trigger('change'); // WC обновит цену/вариацию; цвет записан в «память»
        activate(slug);
        setGalleryImage(slug);             // фото меняем сразу, не дожидаясь размера
    });

    // Если WC не собрал полную вариацию (нет размера) — удержать превью выбранного цвета
    $(document.body).on('hide_variation', function () {
        if (!userPicked) return;
        var slug = $sel.val();
        if (slug) setGalleryImage(slug);
    });

    // WC подменяет gallery изображением вариации; после обновления возвращаем фото выбранного цвета
    $form.on('show_variation', function () {
        if (!userPicked) return;
        var slug = $sel.val();
        if (slug) setGalleryImage(slug);
    });

    if ($sel.val()) activate($sel.val());
    $(document.body).on('reset_data', function () {
        $wrap.find('.ll-sw').removeClass('on');
        $label.text('');
    });
});

/* ===== Доступный фирменный dropdown размеров поверх штатного WC select ===== */
(function ($) {
    'use strict';

    var selector = [
        'select[name="attribute_pa_razmer-skaterti"]',
        'select[name="attribute_pa_razmer-dorozhki"]',
        'select[name="attribute_pa_razmer-nabora"]'
    ].join(',');
    var counter = 0;

    function visibleLabel(select, option) {
        var label = $.trim(option.textContent || '');
        var prefix = {
            'attribute_pa_razmer-skaterti': '140 × ',
            'attribute_pa_razmer-dorozhki': '40 × '
        }[select.name];

        if (!option.value || !prefix) return label;

        var size = label.match(/\d+/);
        return size ? prefix + size[0] + ' см' : label;
    }

    function closeDropdown(root, returnFocus) {
        var trigger = root.querySelector('.ll-variation-select__trigger');
        var list = root.querySelector('.ll-variation-select__list');
        trigger.setAttribute('aria-expanded', 'false');
        list.hidden = true;
        root.classList.remove('is-open');
        if (returnFocus) trigger.focus();
    }

    function closeOtherDropdowns(current) {
        document.querySelectorAll('.ll-variation-select.is-open').forEach(function (root) {
            if (root !== current) closeDropdown(root, false);
        });
    }

    function enabledOptions(root) {
        return Array.prototype.filter.call(
            root.querySelectorAll('.ll-variation-select__option'),
            function (option) { return option.getAttribute('aria-disabled') !== 'true'; }
        );
    }

    function focusOption(root, direction) {
        var options = enabledOptions(root);
        if (!options.length) return;

        var selected = root.querySelector('.ll-variation-select__option[aria-selected="true"]');
        var index = options.indexOf(selected);
        if (direction === 'last') index = options.length - 1;
        else if (direction === 'first' || index < 0) index = 0;
        options[index].focus();
    }

    function openDropdown(root, direction) {
        closeOtherDropdowns(root);
        root.querySelector('.ll-variation-select__trigger').setAttribute('aria-expanded', 'true');
        root.querySelector('.ll-variation-select__list').hidden = false;
        root.classList.add('is-open');
        focusOption(root, direction || 'first');
    }

    function initSelect(select) {
        if (select.dataset.llVariationSelect === '1') return;
        select.dataset.llVariationSelect = '1';
        select.classList.add('ll-native-variation-select');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        counter += 1;
        var root = document.createElement('div');
        var trigger = document.createElement('button');
        var value = document.createElement('span');
        var arrow = document.createElement('span');
        var list = document.createElement('div');
        var labelNode = select.closest('tr') && select.closest('tr').querySelector('th label');
        var fieldLabel = labelNode ? $.trim(labelNode.textContent) : 'Размер';
        var listId = 'll-variation-select-list-' + counter;

        root.className = 'll-variation-select';
        trigger.type = 'button';
        trigger.className = 'll-variation-select__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', listId);
        value.className = 'll-variation-select__value';
        arrow.className = 'll-variation-select__arrow';
        arrow.setAttribute('aria-hidden', 'true');
        list.className = 'll-variation-select__list';
        list.id = listId;
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', fieldLabel);
        list.hidden = true;
        trigger.appendChild(value);
        trigger.appendChild(arrow);
        root.appendChild(trigger);
        root.appendChild(list);
        select.insertAdjacentElement('afterend', root);

        function sync() {
            list.innerHTML = '';
            Array.prototype.forEach.call(select.options, function (nativeOption, index) {
                var option = document.createElement('div');
                var label = visibleLabel(select, nativeOption);
                var selected = nativeOption.value === select.value;
                option.className = 'll-variation-select__option';
                option.id = listId + '-option-' + index;
                option.setAttribute('role', 'option');
                option.setAttribute('tabindex', '-1');
                option.setAttribute('data-value', nativeOption.value);
                option.setAttribute('aria-selected', selected ? 'true' : 'false');
                option.setAttribute('aria-disabled', nativeOption.disabled ? 'true' : 'false');
                option.textContent = label;
                list.appendChild(option);
                if (selected) value.textContent = label;
            });
            trigger.setAttribute('aria-label', fieldLabel + ': ' + value.textContent);
        }

        function choose(option) {
            if (!option || option.getAttribute('aria-disabled') === 'true') return;
            select.value = option.getAttribute('data-value');
            $(select).trigger('change');
            closeDropdown(root, true);
        }

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (root.classList.contains('is-open')) closeDropdown(root, false);
            else openDropdown(root, 'first');
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                openDropdown(root, event.key === 'ArrowUp' ? 'last' : 'first');
            } else if (event.key === 'Escape') {
                closeDropdown(root, false);
            }
        });

        list.addEventListener('click', function (event) {
            event.stopPropagation();
            choose(event.target.closest('.ll-variation-select__option'));
        });

        list.addEventListener('keydown', function (event) {
            var options = enabledOptions(root);
            var current = event.target.closest('.ll-variation-select__option');
            var index = options.indexOf(current);

            if (event.key === 'Escape') {
                event.preventDefault();
                closeDropdown(root, true);
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                choose(current);
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (!options.length) return;
                index += event.key === 'ArrowDown' ? 1 : -1;
                options[(index + options.length) % options.length].focus();
            } else if (event.key === 'Home' || event.key === 'End') {
                event.preventDefault();
                options[event.key === 'Home' ? 0 : options.length - 1].focus();
            } else if (event.key === 'Tab') {
                closeDropdown(root, false);
            }
        });

        $(select).on('change.llVariationSelect', sync);
        $(select).closest('form.variations_form').on(
            'woocommerce_update_variation_values.llVariationSelect reset_data.llVariationSelect',
            sync
        );
        sync();
    }

    function init(root) {
        $(root || document).find(selector).each(function () { initSelect(this); });
    }

    $(function () { init(document); });
    $(document).on('wc_variation_form.llVariationSelect', 'form.variations_form', function () {
        init(this);
    });
    document.addEventListener('click', function () { closeOtherDropdowns(null); });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeOtherDropdowns(null);
    });
})(jQuery);

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

    $rating.find('p.stars, .stars').remove();
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
