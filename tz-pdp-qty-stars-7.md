# ТЗ-7: Карточка товара — количество −/+ и звёзды отзыва

**Версия:** 1.0
**Дата:** 05.06.2026
**Тип:** дополнение `assets/js/product-swatches.js` + CSS-блок в `style.css`
**Файлы:** `assets/js/product-swatches.js`, `style.css`
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клд

> Две правки по стилю: нативный счётчик количества → кнопки −/+; текстовый рейтинг отзыва → 5 золотых звёзд. Обе — JS-надстройки поверх нативных полей (значения уходят в те же `input`/`select`, отправка форм не ломается). Причина обеих — отключённые дефолтные стили WooCommerce.

---

## ⚠️ ПЕРЕД СТАРТОМ
```
git add -A && git commit -m "checkpoint: перед qty/stars на карточке (ТЗ-7)"
git tag pre-pdp-qty-stars
```

---

## ФАЗА 1 — дописать в конец `assets/js/product-swatches.js`

Добавить В КОНЕЦ файла (после существующего кода свотчей; это два независимых `jQuery(...)`-блока, существующий код не трогаем). JS уже грузится на странице товара (ТЗ-3), отдельный enqueue не нужен.

```javascript

/* ===== Доточка карточки v2 (ТЗ-7): количество −/+ и звёзды отзыва ===== */

// Количество: кнопки − / + вместо нативных стрелок
jQuery(function ($) {
    $('.single-product .quantity').each(function () {
        var $q = $(this);
        if ($q.hasClass('ll-qty')) return;
        var $input = $q.find('input.qty');
        if (!$input.length) return;
        $q.addClass('ll-qty');
        $('<button type="button" class="ll-qty-btn ll-qty-minus" aria-label="Меньше">\u2212</button>').prependTo($q);
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
        $stars.append('<span class="ll-star" data-v="' + i + '">\u2606</span>');
    }
    $select.before($stars);

    var current = parseInt($select.val(), 10) || 0;
    function paint(n) {
        $stars.find('.ll-star').each(function () {
            var v = $(this).data('v');
            $(this).text(v <= n ? '\u2605' : '\u2606').toggleClass('on', v <= n);
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
```

(`\u2212` — минус, `\u2606` — пустая звезда ☆, `\u2605` — заполненная ★. Синтаксис проверен `node --check` — OK.)

---

## ФАЗА 2 — CSS в конец `style.css`

```css

/* ===== Карточка: количество −/+ и звёзды отзыва (ТЗ-7) ===== */

/* Количество */
.single-product .ll-qty{display:inline-flex; align-items:stretch; border:1px solid rgba(197,165,90,.3); width:auto}
.single-product .ll-qty input.qty{
    border:none !important; background:var(--bg2,#1a1917) !important; color:var(--cream,#e8e0d0) !important;
    width:56px; height:48px; text-align:center; -moz-appearance:textfield; margin:0; border-radius:0;
}
.single-product .ll-qty input.qty::-webkit-outer-spin-button,
.single-product .ll-qty input.qty::-webkit-inner-spin-button{-webkit-appearance:none; margin:0}
.single-product .ll-qty-btn{
    width:44px; height:48px; background:none; border:none; color:var(--gold,#c5a55a);
    font-family:var(--serif); font-size:1.3rem; line-height:1; cursor:pointer; transition:background .2s;
}
.single-product .ll-qty-btn:hover{background:rgba(197,165,90,.1)}

/* Звёзды отзыва */
.single-product .comment-form-rating label{display:block; margin-bottom:.3rem}
.single-product .ll-stars{display:inline-flex; gap:.25rem; font-size:1.6rem; line-height:1; cursor:pointer; margin:.2rem 0 1.2rem}
.single-product .ll-star{color:var(--gold,#c5a55a); transition:transform .12s}
.single-product .ll-star:hover{transform:scale(1.12)}
```

> Пустая звезда ☆ золотого цвета = контур золотом; заполненная ★ золотого цвета = золотая заливка — ровно как просили.

---

## Чек-лист

- [ ] Количество: вместо нативных стрелок — кнопки − и + по бокам поля; «−» не уходит ниже 1, «+» не превышает доступный остаток; значение меняется, «в корзину» кладёт верное количество.
- [ ] Нативные стрелки спиннера не видны (ни в Chrome, ни в Firefox).
- [ ] Отзывы: вместо строки «1 из 5 звёзд…» — 5 пустых золотых звёзд; наведение подсвечивает до текущей, клик фиксирует заливку; при отправке отзыва оценка сохраняется (проверить, что рейтинг записался).
- [ ] Мобильный: счётчик и звёзды не разъезжаются.
- [ ] Регресс: свотчи, выбор вариации, «в корзину» работают как раньше.

---

## Что НЕ делаем
- НЕ трогаем код свотчей (только дописываем независимые блоки в конец файла).
- НЕ трогаем ссылки статей / баг сценариев / производительность — отдельные волны.

---

## Финал
```
git add -A
git commit -m "polish(woo): количество −/+ и звёзды отзыва на карточке (ТЗ-7)"
git push
```

## Откат
```
git reset --hard pre-pdp-qty-stars
```
