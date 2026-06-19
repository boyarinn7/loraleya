# ТЗ Code-Клоду — Корзина: вернуть кликабельную картинку товара (ссылка на карточку)

*Регресс (не адаптив): в модалке корзины картинка товара перестала вести на карточку — на всех ширинах. Причина: серверные данные позиции не содержат URL товара, а в `renderCart` `<img>` не обёрнут в ссылку. Восстанавливаем.*

**Файлы:** `functions.php`, `assets/js/main.js`, `style.css`
**Гейты:** ноль CLS; не трогать логику корзины/количества/итогов.

## 0. Git-чекпойнт
```bash
git add -A && git commit -m "checkpoint before cart-img-link restore" --allow-empty
git tag pre-cart-imglink
```

## 1. `functions.php` — отдать URL товара в данных позиции
В функции `loraleya_ajax_get_cart` (массив позиции, ~стр. 497).
**Найти:**
```php
            'product_id'       => $cart_item['product_id'],
            'variation_id'     => $cart_item['variation_id'],
```
**Заменить на:**
```php
            'product_id'       => $cart_item['product_id'],
            'variation_id'     => $cart_item['variation_id'],
            'permalink'        => get_permalink($cart_item['product_id']),
```
(`get_permalink` по `product_id` — это страница карточки товара.)

## 2. `assets/js/main.js` — обернуть картинку в ссылку (в `renderCart`)

**2a. Найти:**
```js
            var imgSrc = item.image || '';
            var variationText = '';
```
**Заменить на:**
```js
            var imgSrc = item.image || '';
            var permalink = item.permalink || '';
            var variationText = '';
```

**2b. Найти:**
```js
            if (imgSrc) {
                html += '  <img class="ll-cart-item__img" src="' + escapeHtml(imgSrc) + '" alt="' + escapeHtml(item.name) + '">';
            } else {
```
**Заменить на:**
```js
            if (imgSrc) {
                var imgTag = '<img class="ll-cart-item__img" src="' + escapeHtml(imgSrc) + '" alt="' + escapeHtml(item.name) + '">';
                if (permalink) {
                    html += '  <a class="ll-cart-item__imglink" href="' + escapeHtml(permalink) + '">' + imgTag + '</a>';
                } else {
                    html += '  ' + imgTag;
                }
            } else {
```

## 3. `style.css` — чтобы обёртка не сдвинула раскладку. Добавить В КОНЕЦ
```css
/* Картинка товара в корзине — кликабельная ссылка на карточку */
.ll-cart-item__imglink { display: block; line-height: 0; }
```
(`.ll-cart-item` — грид с колонкой 56px под картинку; `display:block; line-height:0` сохраняет размер 56×56 без лишних отступов.)

## 4. Что НЕ делаем
- Не трогаем логику количества/итогов/очистки корзины, серверные обработчики add/update/clear.
- Имя товара ссылкой пока НЕ делаем (просьба была про картинку). Если захочется — добавим отдельно, легко.

## 5. Приёмка
1. Открыть корзину (любой размер экрана), в позиции **кликнуть по картинке** → переход на карточку этого товара.
2. Раскладка позиции не сдвинулась (картинка 56×56, как была).
3. Кнопки количества/удаление/итого в корзине работают как прежде.
4. Проверить и на десктопе, и на мобиле (регресс был на всех ширинах).

## 6. Откат
```bash
git reset --hard pre-cart-imglink
```

---
*Это правка вне адаптива. После неё возвращаемся к адаптиву: осталась компонента **F** (контрольный проход: цвет / About / блог), затем PageSpeed mobile и снятие noindex.*
