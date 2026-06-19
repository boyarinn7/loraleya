# ТЗ Code-Клоду — Палитра: фото по цвету из вариаций товара (а не общая фактура)

*Доработка предыдущей правки палитры. Проблема: подменяли главное фото общей фактурой цвета (`{цвет}-macro-faktura`), одинаковой для всех товаров → на скатерти показывается снимок ткани, а не скатерть. Решение: брать фото ВАРИАЦИИ текущего товара по выбранному цвету. Если у цвета нет своего фото — фото не подменять.*

**Файлы:** `functions.php`, `assets/js/product-swatches.js`
**Гейты:** ноль CLS; НЕ трогать логику корзины/валидацию/вариации.

## 0. Git-чекпойнт
```bash
git add -A && git commit -m "checkpoint before swatch per-product photo" --allow-empty
git tag pre-swatch-prodphoto
```

## 1. `functions.php` — отдать в JS карту «цвет → фото вариации текущего товара»

В функции, где подключается `loraleya-product-swatches` и локализуется `LoraleyaSwatches`.
**Найти:**
```php
    wp_localize_script('loraleya-product-swatches', 'LoraleyaSwatches', ['colors' => $map]);
});
```
**Заменить на:**
```php
    wp_localize_script('loraleya-product-swatches', 'LoraleyaSwatches', ['colors' => $map]);

    // Фото товара по цвету (специфично для ТЕКУЩЕГО товара): цвет → фото вариации
    if (function_exists('is_product') && is_product()) {
        $prod = wc_get_product(get_queried_object_id());
        if ($prod && $prod->is_type('variable')) {
            $color_imgs = [];
            foreach ($prod->get_children() as $vid) {
                $thumb_id = get_post_thumbnail_id($vid); // только собственное фото вариации
                if (!$thumb_id) continue;
                $variation = wc_get_product($vid);
                if (!$variation) continue;
                $attrs = $variation->get_variation_attributes();
                $color = isset($attrs['attribute_pa_fabric_color']) ? $attrs['attribute_pa_fabric_color'] : '';
                if ($color === '' || isset($color_imgs[$color])) continue;
                $u = wp_get_attachment_image_url($thumb_id, 'woocommerce_single');
                if ($u) $color_imgs[$color] = $u;
            }
            wp_localize_script('loraleya-product-swatches', 'LoraleyaProductColors', ['images' => $color_imgs]);
        }
    }
});
```
> Берём только вариации с СОБСТВЕННЫМ фото (`get_post_thumbnail_id`), по одному фото на цвет. Размер `woocommerce_single` = родной размер главной галереи.

## 2. `assets/js/product-swatches.js` — менять фото на фото вариации товара

**Найти** (функцию из прошлой правки):
```js
    // Сменить главное фото галереи на картинку выбранного цвета (сразу, без полной вариации)
    function setGalleryImage(slug) {
        var url = (data[slug] || {}).image;
        if (!url) return;
        var $img = $('.woocommerce-product-gallery__wrapper .wp-post-image').first();
        if (!$img.length) $img = $('.woocommerce-product-gallery img').first();
        if (!$img.length) return;
        $img.attr('src', url).attr('srcset', '').removeAttr('data-src').removeAttr('data-large_image');
    }
```
**Заменить на:**
```js
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
```
> Маленькие свотчи в палитре по-прежнему используют общую фактуру (`LoraleyaSwatches`) — это правильно, их не трогаем. Меняется только источник ГЛАВНОГО фото.

## 3. (Необязательно) убрать ставшее ненужным
В `functions.php` строка из прошлой правки `$img = ... loraleya_color_swatch_url($c[0], 'large');` и `'image' => $img` в `$map` больше не используются галереей. Можно оставить (безвредно) или убрать для чистоты — на твоё усмотрение.

## 4. Что НЕ делаем
- Логику корзины/валидацию/запись цвета в `<select>` — не трогаем.
- Свотчи палитры (их фон) — не трогаем.

## 5. Приёмка / диагностика
1. Карточка `/product/skatert/`: клик по цвету → главное фото меняется на **фото скатерти в этом цвете** (не на фактуру/салфетку).
2. Если фото НЕ меняется (остаётся штатное) — значит у вариаций этого цвета **нет своего фото**. Это не баг кода, а отсутствие данных: нужно загрузить фото в вариации (или сообщить правило именования фото товар-цвет — смаплю иначе).
3. Проверить на разных товарах (скатерть, салфетка, набор): где у вариаций есть фото — меняется; где нет — остаётся штатное, без чужой картинки.
4. Выбор цвета + размер → корзина с верным цветом (как раньше).

## 6. Откат
```bash
git reset --hard pre-swatch-prodphoto
```

---
*Вне адаптива. После — F (контрольный проход), PageSpeed mobile, снятие noindex.*
