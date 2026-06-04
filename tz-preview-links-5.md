# ТЗ-5: Превью товаров на главной → ссылки на карточки

**Версия:** 1.0
**Дата:** 04.06.2026
**Тип:** правка одного блока в `front-page.php` + лёгкий CSS
**Файлы:** `front-page.php`, `style.css`
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клод

> Задача от Куренковой: в блоке «Что входит в сервировку» на главной четыре превью (Дорожки, Скатерти, Салфетки, Куверты) сейчас статичны — сделать каждое ссылкой на карточку соответствующего товара.

---

## Решение

Каждый `.product-preview` (сейчас `<div>`) превращаем в `<a>` с URL карточки. Ссылку строим через `get_permalink(ID)` по ID товаров из `loraleya_build_item_map` — Дорожка **39**, Скатерть **44**, Салфетка **48**, Куверт **49**. По ID надёжнее, чем хардкодить slug: URL останется верным, даже если slug товара поменяется. Если товар вдруг не опубликован — ссылка не подставляется (превью остаётся неактивным, не ведёт в 404).

PHP-фрагмент проверен php-parser (OK). Перед коммитом — `php -l front-page.php`.

---

## ⚠️ ПЕРЕД СТАРТОМ

```
git add -A && git commit -m "checkpoint: перед ссылками превью товаров (ТЗ-5)"
git tag pre-pp-links
```
Свериться, что блок `<!-- PRODUCTS PREVIEW -->` в `front-page.php` соответствует приведённому НАЙТИ (он завязан на `loraleya_get_color_photo_url`). Если структура иная — сообщить, не применять вслепую.

---

## Правка — `front-page.php`, блок `<!-- PRODUCTS PREVIEW -->`

### Шаг 1. В PHP-преамбуле блока (после строк с `$pp_*_url = ...`, перед `?>`) добавить карту ссылок:
```php
$pp_links = [
    'runner'     => 39,  // Дорожка
    'tablecloth' => 44,  // Скатерть
    'napkin'     => 48,  // Салфетка
    'kuvert'     => 49,  // Куверт
];
$pp_href = [];
foreach ($pp_links as $k => $pid) {
    $pp_href[$k] = ($pid && get_post_status($pid) === 'publish') ? get_permalink($pid) : '';
}
```

### Шаг 2. Каждый из четырёх `.product-preview` сменить с `<div>` на `<a>`.

**Дорожки** — НАЙТИ:
```php
            <div class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_runner_url ? 'style="background-image:url(' . esc_url($pp_runner_url) . ')"' : ''; ?>></div>
```
ЗАМЕНИТЬ начало на:
```php
            <a href="<?php echo esc_url($pp_href['runner']); ?>" class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_runner_url ? 'style="background-image:url(' . esc_url($pp_runner_url) . ')"' : ''; ?>></div>
```
и закрывающий этого блока `</div>` (тот, что закрывает `.product-preview`) сменить на `</a>`.

**Скатерти** — то же: открывающий `<div class="product-preview">` → `<a href="<?php echo esc_url($pp_href['tablecloth']); ?>" class="product-preview">`, закрывающий `</div>` → `</a>`.

**Салфетки** — открывающий → `<a href="<?php echo esc_url($pp_href['napkin']); ?>" class="product-preview">`, закрывающий → `</a>`.

**Куверты** — открывающий → `<a href="<?php echo esc_url($pp_href['kuvert']); ?>" class="product-preview">`, закрывающий → `</a>`.

> Внутреннее содержимое (`__photo`, `__text`, `__label`, `__size`, `__price`) не меняется. Меняется только внешний тег обёртки `div` → `a` и добавляется `href`.

---

## CSS — `style.css` (в конец)

Убрать подчёркивание у ссылки-превью и добавить лёгкий hover (приподнять, как карточки блога), чтобы было понятно, что это кликабельно.

```css

/* ===== Превью товаров на главной — кликабельность (ТЗ-5) ===== */
a.product-preview{text-decoration:none; color:inherit; display:block; transition:transform .4s ease, box-shadow .4s ease}
a.product-preview:hover{transform:translateY(-6px); box-shadow:0 20px 40px -22px rgba(0,0,0,.7)}
a.product-preview:hover .product-preview__label{color:var(--gold-light,#d4bc7c)}
.product-preview__label{transition:color .35s}
```

---

## Чек-лист

- [ ] `php -l front-page.php` чист.
- [ ] На главной в блоке «Что входит в сервировку» все четыре превью кликабельны.
- [ ] Клик «Дорожки» → карточка дорожки; «Скатерти» → скатерть; «Салфетки» → салфетка; «Куверты» → куверт. Ни одного 404.
- [ ] При наведении превью слегка приподнимается, подпись золотится — видно, что это ссылка.
- [ ] Вёрстка блока не съехала (четыре в ряд, как было); мобильный — как раньше.
- [ ] Консоль без ошибок.

---

## Что НЕ делаем

- НЕ трогаем фото-хелпер и сами изображения превью.
- НЕ меняем тексты/цены в превью.
- НЕ трогаем другие секции главной.

---

## Финал
```
git add -A
git commit -m "feat(home): превью товаров ведут на карточки (ТЗ-5)"
git push
```
Сообщить Борису: все ли четыре превью ведут на верные карточки.

## Откат
```
git reset --hard pre-pp-links
```
