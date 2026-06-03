# ТЗ-3: Премиум-карточка товара (свотчи + luxury-вёрстка)

**Версия:** 1.0
**Дата:** 03.06.2026
**Тип:** новый JS, новый CSS-блок, правка `functions.php`. **Без переписывания шаблонов WooCommerce.**
**Файлы:** `functions.php`, `assets/js/product-swatches.js` (новый), `style.css`
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клод
**Основа:** утверждённый прототип `proto-product/index.html`.

---

## Подход и почему так

Карточку товара **не переписываем шаблоном**. Вместо этого — luxury-вёрстка через CSS поверх нативной разметки WooCommerce + свотчи как JS-надстройка над стандартным селектом цвета.

Причина — минимум риска. Вся логика вариаций уже работает нативно: выбор цвета меняет цену, фото (у вариаций есть свои изображения), наличие и кладёт в корзину правильную вариацию через `wc-add-to-cart-variation.js`. Переписывать это вручную — значит дублировать и ломать проверенное. Мы только: (1) раскладываем карточку в две колонки и красим под luxury через CSS, (2) прячем дропдаун цвета и рисуем поверх свотчи-кружки, которые при клике дёргают тот же нативный селект. WooCommerce делает всё остальное сам.

**Архитектура товаров** (из `loraleya_build_item_map`): Куверт (49) и Салфетка (48) — только цвет; Дорожка (39), Скатерть (44), Набор (50) — цвет × размер. Свотчи вешаем на атрибут `pa_fabric_color`; размерный селект (где есть) остаётся стилизованным дропдауном.

Фрагменты JS и PHP ниже проверены (node --check / php-parser — OK). Перед коммитом — `php -l` на `functions.php`.

---

## ⚠️ ПЕРЕД СТАРТОМ — обязательная сверка

```
git add -A && git commit -m "checkpoint: перед премиум-карточкой товара (ТЗ-3)"
git tag pre-pdp
```

**Сверка реальной разметки** (критично — свотчи цепляются за конкретный селект). Открой карточку куверта в браузере, посмотри исходник формы вариаций и подтверди:
- селект цвета имеет `name="attribute_pa_fabric_color"` (стандарт WooCommerce);
- галерея — это `.woocommerce-product-gallery`, сводка — `.summary.entry-summary`, форма — `form.variations_form`.

Если имена/классы отличаются от стандартных — НЕ применяй JS/CSS вслепую, сообщи реальные селекторы, поправлю. Всё ниже написано под стандартную разметку WooCommerce.

---

## ФАЗА 1 — `functions.php`: подключить свотчи

Добавить в конец файла. Грузит скрипт только на странице товара и передаёт в него карту 17 цветов (slug → название + URL фактуры через существующую `loraleya_color_swatch_url`).

```php
// === Свотчи на карточке товара (ТЗ-3) ===
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_product') || !is_product()) return;

    wp_enqueue_script(
        'loraleya-product-swatches',
        get_stylesheet_directory_uri() . '/assets/js/product-swatches.js',
        ['jquery', 'wc-add-to-cart-variation'],
        '1.0',
        true
    );

    $colors = [
        ['fioletovyj','Фиолетовый'],['grafit','Графит'],['bronza','Бронза'],['sirenevyj','Сиреневый'],
        ['bezhevyj','Бежевый'],['belyj','Белый'],['biryuza','Бирюза'],['blek-zoloto','Блек золото'],
        ['goluboj','Голубой'],['zelenyj','Зелёный'],['melanzh-zoloto','Меланж золото'],
        ['melanzh-serebro','Меланж серебро'],['melanzh-seryj','Меланж серый'],['melanzh-chernyj','Меланж чёрный'],
        ['platina','Платина'],['serebro','Серебро'],['temno-biryuzovyj','Тёмно-бирюзовый'],
    ];
    $map = [];
    foreach ($colors as $c) {
        $url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0]) : '';
        $map[$c[0]] = ['name' => $c[1], 'url' => $url];
    }
    wp_localize_script('loraleya-product-swatches', 'LoraleyaSwatches', ['colors' => $map]);
});
```

---

## ФАЗА 2 — новый файл `assets/js/product-swatches.js`

Прячет дропдаун цвета, рисует кружки, синхронизирует с нативным селектом. Зависит от `wc-add-to-cart-variation` (нативная логика вариаций).

```javascript
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
```

---

## ФАЗА 3 — `style.css`: luxury-раскладка и свотчи

Добавить в конец. Раскладывает карточку в две колонки (галерея | сводка) и красит элементы под тему. Свотчи — те же, что на сценариях (кружок 2.5rem, активный с золотой обводкой и галочкой).

```css

/* ===== Премиум-карточка товара (ТЗ-3) ===== */
.single-product div.product{
    display:grid; grid-template-columns:1fr 1fr; gap:4rem;
    max-width:1200px; margin:2.5rem auto 4rem; padding:0 2.5rem; align-items:start;
}
.single-product div.product .woocommerce-product-gallery{position:sticky; top:6rem; margin:0; width:auto !important; float:none !important}
.single-product div.product .summary{margin:0}

/* типографика сводки */
.single-product .product_title{font-family:var(--serif); font-weight:500; font-size:clamp(2.2rem,4vw,3.2rem); line-height:1.05; color:var(--cream,#e8e0d0); margin-bottom:1rem}
.single-product .summary .price{font-family:var(--serif); font-size:1.8rem; color:var(--gold-light,#d4bc7c)}
.single-product .summary .price del{color:var(--gold-dim,#8a7a4a); font-size:1.2rem}
.single-product .woocommerce-product-details__short-description{color:var(--gold-dim,#8a7a4a); margin:1rem 0 1.5rem}

/* свотчи (как на сценариях) */
.ll-sw-name{font-family:var(--serif); font-size:1.2rem; color:var(--cream,#e8e0d0); margin:.5rem 0 1rem}
.ll-swatches{display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:2rem}
.ll-sw{width:2.5rem; height:2.5rem; border-radius:50%; cursor:pointer; border:1px solid rgba(197,165,90,.3); transition:all .3s; position:relative; background-size:130%!important; background-position:center!important}
.ll-sw:hover,.ll-sw.on{border-color:var(--gold,#c5a55a); transform:scale(1.12)}
.ll-sw.on::after{content:'✓'; position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.65rem; text-shadow:0 1px 3px rgba(0,0,0,.6)}

/* размерный селект (дорожка/скатерть/набор) — стилизуем дропдаун */
.single-product table.variations{margin-bottom:1.5rem; border-collapse:collapse}
.single-product table.variations th{font-size:.7rem; letter-spacing:.18em; text-transform:uppercase; color:var(--gold-dim,#8a7a4a); text-align:left; padding-right:1rem; vertical-align:middle}
.single-product table.variations select{background:var(--bg2,#1a1917); color:var(--cream,#e8e0d0); border:1px solid rgba(197,165,90,.3); padding:.6rem .8rem; font-family:var(--sans); border-radius:0}
.single-product .reset_variations{color:var(--gold-dim,#8a7a4a); font-size:.78rem}

/* количество + кнопка */
.single-product .quantity input{background:var(--bg2,#1a1917); color:var(--cream,#e8e0d0); border:1px solid rgba(197,165,90,.3); height:48px; width:64px; text-align:center}
.single-product .single_add_to_cart_button{
    background:var(--gold,#c5a55a)!important; color:var(--bg,#0e0e0c)!important;
    font-family:var(--sans); font-weight:600; letter-spacing:.18em; text-transform:uppercase;
    font-size:.8rem; border:none; border-radius:0; padding:1.1rem 2rem; transition:.3s;
}
.single-product .single_add_to_cart_button:hover{background:var(--gold-light,#d4bc7c)!important}
.single-product .product_meta{font-size:.78rem; color:var(--gold-dim,#8a7a4a); margin-top:1.5rem}
.single-product .product_meta .sku{color:var(--cream,#e8e0d0)}

/* табы описание/детали/отзывы */
.single-product .woocommerce-tabs{grid-column:1 / -1; margin-top:3rem; border-top:1px solid rgba(197,165,90,.12); padding-top:1.8rem}
.single-product .woocommerce-tabs ul.tabs{list-style:none; display:flex; gap:2rem; margin:0 0 1.4rem; padding:0}
.single-product .woocommerce-tabs ul.tabs li{font-size:.72rem; letter-spacing:.18em; text-transform:uppercase}
.single-product .woocommerce-tabs ul.tabs li a{color:var(--gold-dim,#8a7a4a); padding-bottom:.4rem}
.single-product .woocommerce-tabs ul.tabs li.active a{color:var(--gold,#c5a55a); border-bottom:1px solid var(--gold,#c5a55a)}
.single-product .woocommerce-tabs .panel{color:var(--cream,#e8e0d0); font-weight:300}

@media(max-width:900px){
    .single-product div.product{grid-template-columns:1fr; gap:2.5rem}
    .single-product div.product .woocommerce-product-gallery{position:static}
}
```

> Если после раскладки галерея и сводка встают не в те колонки — причина в том, что WooCommerce оборачивает их по-разному в разных версиях. Тогда проверить, что прямые потомки `div.product` — это `.woocommerce-product-gallery` и `.summary`, и при необходимости поправить селектор грида. (Это и проверяем на этапе сверки.)

---

## Чек-лист

- [ ] `php -l functions.php` чист; JS грузится только на странице товара (в консоли нет ошибок).
- [ ] **Куверт** (только цвет): под ценой ряд из 17 свотчей-кружков, дропдаун цвета скрыт. Клик по кружку — активный с галочкой, подпись цвета меняется, **фото меняется на фото вариации**, цена/артикул обновляются.
- [ ] «В корзину» с выбранным цветом кладёт **правильную вариацию** (проверить в корзине — цвет/артикул совпадают).
- [ ] **Дорожка** (цвет × размер): свотчи цвета + стилизованный селект размера; выбор обоих → корректная вариация, цена, кнопка активна.
- [ ] Раскладка: галерея слева (липкая), сводка справа, табы на всю ширину снизу.
- [ ] Без выбора (если есть пустой дефолт) кнопка корректно неактивна — нативное поведение WooCommerce не сломано.
- [ ] Мобильный: одна колонка, галерея сверху, свотчи переносятся.
- [ ] Регресс: корзина, выбор количества, переход к оформлению — всё работает как раньше.

---

## Что НЕ делаем (границы)

- НЕ переписываем шаблоны WooCommerce (`single-product.php` и т.п.) — папка `woocommerce/` остаётся пустой; всё через CSS + JS + enqueue.
- НЕ трогаем логику вариаций, цен, корзины — она нативная и работает.
- НЕ ставим главные фото товаров и фото вариаций — это контент (Куренкова). Свотчи показывают фактуру через `loraleya_color_swatch_url`; главное фото в каталоге — отдельная задача.
- НЕ трогаем блог, цветовые и сценарные страницы.

---

## Финал
```
git add -A
git commit -m "feat(woo): премиум-карточка товара — свотчи + luxury-вёрстка (ТЗ-3)"
git push
```
Сообщить Борису: работают ли свотчи (смена фото/цены/вариации), кладётся ли верная вариация в корзину, как ведут себя товары с размером (дорожка/скатерть/набор).

## Откат
```
git reset --hard pre-pdp
```
