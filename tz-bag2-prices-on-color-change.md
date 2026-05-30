# ТЗ — Баг 2: цены на странице сценария не меняются при смене цвета

**Версия:** 1.0
**Дата:** 30 мая 2026
**Спринт:** 2 (правки по тестам Куренковой)
**Адресат:** Claude Code agent в VS Code
**Файлы:** `functions.php`, `single-scenario.php`, `assets/js/constructor.js`
**Тип:** правка PHP + JS, средняя сложность
**Связано с:** Багом 1 (цены в БД, закрыто скриптом `fix-prices.php`)

---

## ⚠️ Перед стартом

```bash
git add -A && git commit -m "snapshot before sprint-2-bug2-fix" && git tag pre-sprint-2-bug2
```

---

## Корень бага

На странице сценария (`/scenarios/{slug}/`) при смене цвета в дропдауне конструктора **цены поштучных товаров не обновляются**. Куренкова: «при смене цвета с обычной ткани на меланж цены не меняются». Проверено в коде:

1. **`single-scenario.php` строка 113**:
```php
$item_prices = loraleya_get_item_prices($default_color);
```
Цены берутся ОДИН РАЗ для дефолтного цвета (бирюза/мрамор). Вставляются в HTML через PHP-хелперы `$ip_fmt()` и `$ip_price()` — это статичная отрисовка.

2. **`functions.php` строка 78**:
```php
wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES', loraleya_get_item_prices('biryuza'));
```
В JS прокидывается набор цен **только для бирюзы** (то есть мраморный тариф).

3. **`constructor.js`** при смене цвета НЕ дергает новые цены — он не знает про существование меланжевого тарифа.

Итог: цена в карточке остаётся бирюзовая (мраморная) при любом выбранном цвете.

---

## Решение

Использовать существующий паттерн темы: рядом с `LORALEYA_ITEM_MAP_BY_COLOR` (functions.php стр.73-77 — карта вариаций по всем 17 цветам) сделать аналогичную **карту цен по всем 17 цветам**. JS при смене цвета берёт цены из нужной ветки карты по slug. Никаких AJAX, никаких эвристик «мрамор vs меланж» в клиенте — карта статичная, готовая, для каждого цвета свой набор цен (мрамор у мраморных, меланж у меланжевых и блек-золота, потому что это уже учтено в `fix-prices.php` в БД).

---

## Правка 1 — `functions.php`, локализация карты цен по всем цветам

**Файл:** `wp-content/themes/loraleya/functions.php`
**Место:** строки 73-78 (контекст: после построения `$map_by_color`, перед локализацией `LORALEYA_ITEM_PRICES`)

**Найди блок:**
```php
        $map_by_color = [];
        foreach ($all_color_slugs as $cs) {
            $map_by_color[$cs] = loraleya_build_item_map($cs);
        }
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_MAP_BY_COLOR', $map_by_color);
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES', loraleya_get_item_prices('biryuza'));
```

**Замени на:**
```php
        $map_by_color = [];
        $prices_by_color = [];
        foreach ($all_color_slugs as $cs) {
            $map_by_color[$cs] = loraleya_build_item_map($cs);
            $prices_by_color[$cs] = loraleya_get_item_prices($cs);
        }
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_MAP_BY_COLOR', $map_by_color);
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES_BY_COLOR', $prices_by_color);
        // Совместимость: бирюза как дефолт (старый код, ещё не перешедший на BY_COLOR)
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES', loraleya_get_item_prices('biryuza'));
```

**Что делает:** строит вторую карту `prices_by_color` (по всем 17 цветам, каждый ключ — slug, значение — массив цен от `loraleya_get_item_prices`), и локализует её под именем `LORALEYA_ITEM_PRICES_BY_COLOR`. Старая `LORALEYA_ITEM_PRICES` остаётся как fallback (страница цвета и другие места могут на неё опираться — пока не ломаем).

---

## Правка 2 — `assets/js/constructor.js`, обновление цен при смене цвета

**Файл:** `wp-content/themes/loraleya/assets/js/constructor.js`

Это правка JS. Раз я не вижу полного содержимого файла, агент должен:

1. **Открыть `constructor.js`** и найти место, где обрабатывается **смена цвета** (скорее всего обработчик клика по свотчу цвета или change-event дропдауна цвета). Признаки этого места:
   - вызов `setColor()`, `selectColor()`, или event listener на элементе с классом вроде `.color-swatch`, `.color-option`, `[data-color]`
   - где-то рядом обновляется заголовок «Выбран цвет: ...» и подсвечивается активный свотч

2. **В этот же обработчик** добавить вызов функции обновления цен. Структура:

```js
// Вспомогательная функция (добавить в верх файла или рядом с другими утилитами)
function updateItemPricesForColor(colorSlug) {
    // Карта цен прилетает из PHP через wp_localize_script
    if (typeof LORALEYA_ITEM_PRICES_BY_COLOR === 'undefined') return;
    var prices = LORALEYA_ITEM_PRICES_BY_COLOR[colorSlug];
    if (!prices) return;

    // Обновить все карточки .ir с data-item
    document.querySelectorAll('.ir[data-item]').forEach(function(el) {
        var key = el.getAttribute('data-item');
        if (!prices[key]) return;
        var price = parseInt(prices[key].price, 10);
        if (!price) return;

        // Обновляем data-price (используется при добавлении в корзину)
        el.setAttribute('data-price', price);

        // Обновляем видимый текст цены
        var priceEl = el.querySelector('.ir-price');
        if (priceEl) {
            // Сохраняем суффикс (например " / шт"), если был
            var oldText = priceEl.textContent;
            var suffixMatch = oldText.match(/\s*\/\s*\S+\s*$/);
            var suffix = suffixMatch ? suffixMatch[0] : ' / шт';
            priceEl.textContent = price.toLocaleString('ru-RU') + ' ₽' + suffix;
        }
    });
}
```

3. **В существующем обработчике смены цвета** добавить вызов:
```js
updateItemPricesForColor(newColorSlug);
```
где `newColorSlug` — slug нового выбранного цвета (та же переменная, по которой агент уже обновляет другие элементы UI при смене цвета).

**Если в `constructor.js` уже есть функция обновления чего-либо при смене цвета** — добавь вызов `updateItemPricesForColor(colorSlug)` в ту же функцию, не дублируй обработчик. Цель: одно событие «цвет изменился» → каскад обновлений (включая цены).

---

## Правка 3 (побочная) — баг slug индивидуального заказа в functions.php

**Файл:** `functions.php` строка 90.

В Спринте 2 мы переименовывали slug `custom-order` → `individualnyy-zakaz` через `tz-sprint-2-bugfix-B.md`. По логике B4 эта строка должна быть исправлена, но в загруженной версии файла она всё ещё с `custom-order`:

**Найди:**
```php
    if (is_page('custom-order')) {
        wp_enqueue_script('loraleya-custom-order', get_template_directory_uri() . '/assets/js/custom-order.js', [], '1.0.0', true);
    }
```

**Замени `is_page('custom-order')` → `is_page('individualnyy-zakaz')`:**
```php
    if (is_page('individualnyy-zakaz')) {
        wp_enqueue_script('loraleya-custom-order', get_template_directory_uri() . '/assets/js/custom-order.js', [], '1.0.0', true);
    }
```

⚠️ Имя ассета `loraleya-custom-order` и путь к файлу `custom-order.js` **НЕ меняем** — это внутреннее имя, slug там не влияет.

**Зачем эта правка здесь:** иначе на странице индивидуального заказа не подгрузится JS конструктора (`custom-order.js`), и конфигуратор не работает. Возможно агент пропустил эту строку в багфиксе-B, или версия в локали отличается. Включаю в это ТЗ, чтобы закрыть точно.

---

## Чек-лист проверки результата (для Бориса)

После применения правок:

1. **Открыть `/scenarios/romanticheskij-uzhin/`** (или любой сценарий)
2. **В блоке «Соберите свой комплект»** убедиться, что выбран цвет, скажем, **бирюза** (мраморный) → запомнить цену дорожки 175 (должна быть 1100 ₽)
3. **Кликнуть на цвет `melanzh-zoloto`** (меланжевый) → цена дорожки 175 должна стать **1250 ₽**
4. Переключить на **`blek-zoloto`** → тоже **1250 ₽** (как меланж)
5. Переключить на **`platina`** (мрамор) → **1100 ₽**
6. **Открыть `/individualnyy-zakaz/`** → конфигуратор работает (выбор формы стола, размер, цвет, переключатели «что шьём»). Если конфигуратор не реагирует на клики — значит JS `custom-order.js` не загрузился, это правка 3 не применилась.
7. **Открыть `/color/bezhevyj/`** → цены поштучных товаров в блоке «Поштучно» должны остаться корректными (мраморными). Эта страница работает по другому пути, не должна сломаться.

---

## Откат

Если что-то сломалось:
```bash
git reset --hard pre-sprint-2-bug2
```

---

## Что НЕ делаем

1. **Не убираем** `LORALEYA_ITEM_PRICES` (старый — оставляем для совместимости)
2. **Не трогаем** `loraleya_get_item_prices()` — она читает из БД, цены там корректные после Бага 1
3. **Не трогаем** шаблоны цветовой страницы (`taxonomy-pa_fabric_color.php`) — там другой механизм
4. **Не делаем AJAX** — карта статичная, прокидывается через `wp_localize_script`
5. **Не меняем** имя ассета JS `custom-order.js` (правка 3)

---

## Финал

```bash
git add wp-content/themes/loraleya/functions.php \
        wp-content/themes/loraleya/assets/js/constructor.js
git commit -m "sprint-2-bug2: prices update on color change in scenario constructor; fix individualnyy-zakaz JS enqueue"
```

Сообщи Борису: подтверждение по чек-листу + скрин сценария с переключением цвета (бирюза → меланж-золото) и видимой сменой цены.

---

**Конец ТЗ. Баг 1 закрыт скриптом `fix-prices.php` (БД), Баг 2 закрывает это ТЗ (фронт).**
