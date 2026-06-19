# ТЗ Code-Клоду — Карточка товара: палитра меняет фото сразу + подчёркивание цены

*Две правки карточки. Палитра — основное; цена `ins` — мелочь, заодно.*

**Файлы:** `functions.php`, `assets/js/product-swatches.js`, `style.css`
**Гейты:** ноль CLS; НЕ трогать логику добавления в корзину/валидацию параметров/вариации.

## Контекст (что выяснено по коду — чтобы было понятно «зачем»)
- Палитра `.ll-sw` (в `product-swatches.js`) по клику делает `$sel.val(slug).trigger('change')` — выставляет цвет в `<select>` цвета (**цвет уже пишется в «память»** ✓) и зовёт WooCommerce. Но WC меняет фото только когда собрана ПОЛНАЯ вариация (цвет + размер). Нет размера → фото не меняется.
- Нужно: фото меняется **сразу** по клику на цвет. Данные есть — карта `LoraleyaSwatches.colors`. Но в ней URL картинки в размере `thumbnail` (мелкий). Добавим большую версию и будем подменять фото ею.
- Когда выберут и размер — WC сам подставит настоящее фото вариации (наш превью уступит). Валидацию «введите параметры» при добавлении в корзину НЕ трогаем — она остаётся.

## 0. Git-чекпойнт
```bash
git add -A && git commit -m "checkpoint before swatch-instant-photo + price-ins" --allow-empty
git tag pre-swatch-photo
```

---
# ЧАСТЬ 1. Палитра меняет фото сразу

## 1.1 `functions.php` — отдать в карту большую картинку цвета

**1.1a. Функцию `loraleya_color_swatch_url` сделать с размером. Найти:**
```php
function loraleya_color_swatch_url($slug) {
    static $cache = [];
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }
```
**Заменить на:**
```php
function loraleya_color_swatch_url($slug, $size = 'thumbnail') {
    static $cache = [];
    $ck = $slug . '|' . $size;
    if (isset($cache[$ck])) {
        return $cache[$ck];
    }
```

**1.1b. Возврат — на тот же ключ кэша и размер. Найти:**
```php
    if (!empty($attachment)) {
        $url = wp_get_attachment_image_url($attachment[0]->ID, 'thumbnail');
        $cache[$slug] = $url ?: '';
        return $cache[$slug];
    }

    $cache[$slug] = '';
    return '';
}
```
**Заменить на:**
```php
    if (!empty($attachment)) {
        $url = wp_get_attachment_image_url($attachment[0]->ID, $size);
        $cache[$ck] = $url ?: '';
        return $cache[$ck];
    }

    $cache[$ck] = '';
    return '';
}
```

**1.1c. В карту добавить большую картинку. Найти:**
```php
        $url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0]) : '';
        $map[$c[0]] = ['name' => $c[1], 'url' => $url];
```
**Заменить на:**
```php
        $url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0]) : '';
        $img = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0], 'large') : '';
        $map[$c[0]] = ['name' => $c[1], 'url' => $url, 'image' => $img];
```

## 1.2 `assets/js/product-swatches.js` — менять фото по клику

В первом jQuery-блоке (палитра). **Найти:**
```js
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
```
**Заменить на:**
```js
    function activate(slug) {
        $wrap.find('.ll-sw').removeClass('on');
        $wrap.find('.ll-sw[data-slug="' + slug + '"]').addClass('on');
        $label.text((data[slug] || {}).name || '');
    }

    // Сменить главное фото галереи на картинку выбранного цвета (сразу, без полной вариации)
    function setGalleryImage(slug) {
        var url = (data[slug] || {}).image;
        if (!url) return;
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

    if ($sel.val()) activate($sel.val());
    $(document.body).on('reset_data', function () {
        $wrap.find('.ll-sw').removeClass('on');
        $label.text('');
    });
```
- На загрузке фото НЕ подменяем (показываем штатное главное фото товара) — превью включается только после клика по цвету (`userPicked`).
- Когда выберут размер и соберётся полная вариация — WC по событию `found_variation` подставит настоящее фото вариации, наш превью уступит. Это правильно.

---
# ЧАСТЬ 2. Подчёркивание акционной цены

## 2.1 `style.css` — добавить В КОНЕЦ
```css
/* Убрать дефолтное подчёркивание у акционной цены (тег ins) */
.single-product .summary .price ins {
    text-decoration: none;
    background: transparent;
    color: var(--gold-light, #d4bc7c);
}
```

---
## 3. Что НЕ делаем
- Логику добавления в корзину и валидацию «введите параметры» — не трогаем.
- Запись цвета в `<select>` — уже работает, не трогаем.
- Зачёркивание старой цены (`del`) — оставляем, так и надо.

## 4. Приёмка
1. Карточка товара (например, `/product/skatert/`): **клик по цвету сразу меняет главное фото** на этот цвет — даже если размер ещё не выбран.
2. После смены цвета выбрать размер → собирается вариация → фото становится настоящим фото вариации (если у вариации есть своё фото); цена обновляется.
3. Добавление в корзину без размера → по-прежнему предупреждение «введите параметры» (не сломали).
4. Цвет «запомнен»: выбрал цвет → выбрал размер → «В КОРЗИНУ» → в корзине верный цвет.
5. Акционная цена у кнопки — **без подчёркивания**, старая цена зачёркнута.
6. Проверить, что селектор фото подходит под реальную галерею (стандартная `.woocommerce-product-gallery` — шаблон не переопределён, должно совпасть).

## 5. Откат
```bash
git reset --hard pre-swatch-photo
```

---
*Это вне адаптива (карточка/корзина). После — возвращаемся к адаптиву: остаётся F (контрольный проход цвет/About/блог), затем PageSpeed mobile и снятие noindex.*
*Известное мелкое ограничение: ссылка-зум на фото (если есть) до выбора полной вариации может открывать исходное фото, а не превью цвета — это не влияет на выбор/заказ; при желании поправим отдельно.*
