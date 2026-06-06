# ТЗ-перф-2 — Self-host шрифтов + filemtime-версионирование

**Проект:** LoraLeya (loraleya.ru)
**Файл темы:** `wp-content/themes/<тема>/functions.php` + новые файлы в `assets/`
**Исполнитель:** Claude Code (VS Code)
**Цель:** убрать render-blocking от внешних Google Fonts (`fonts.googleapis.com` + `fonts.gstatic.com`), переведя шрифты на self-host. Заодно перевести версии CSS/JS на `filemtime()` для авто-сброса кэша.

---

## 0. Git-чекпойнт (обязательно ДО правок)

```bash
git add -A && git commit -m "checkpoint before TZ-perf-2 (self-host fonts)" --allow-empty
git tag pre-tz-perf-2
```
Откат в конце документа.

---

## 1. Что делаем (по шагам)

### Шаг 1. Скачать woff2 нужных начертаний

Нужны ровно те начертания, что сейчас в enqueue:

- **Cormorant Garamond** — normal `300, 400, 500, 600` + italic `300, 400`
- **Raleway** — normal `300, 400, 500, 600`
- **Подмножества (subsets):** `cyrillic, latin` (сайт русский — кириллица обязательна). Можно добавить `cyrillic-ext, latin-ext`, но без них тоже норм.
- **Формат:** только `woff2`.

Самый надёжный путь — **google-webfonts-helper** (gwfh.mranftl.com): выбрать семейство → отметить нужные веса/стили и subsets `cyrillic`+`latin` → скачать woff2 + взять готовый `@font-face` CSS как основу.

Альтернатива (если gwfh недоступен) — забрать CSS2 с браузерным User-Agent и вытащить woff2-ссылки:
```bash
curl -s -A "Mozilla/5.0 (Windows NT 10.0; Win64; x64)" \
"https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Raleway:wght@300;400;500;600&display=swap" \
| grep -oE "https://fonts.gstatic.com[^)]+\.woff2"
```
(Каждый блок `@font-face` в ответе помечен комментарием subset — берём блоки `cyrillic` и `latin`.)

Положить файлы в: **`assets/fonts/`** (создать папку). Имена — человекочитаемые, напр.:
`cormorant-garamond-400-cyrillic.woff2`, `raleway-300-latin.woff2` и т.д.

### Шаг 2. Создать `assets/css/fonts.css`

Создать файл с `@font-face` для всех скачанных начертаний. Требования:
- `font-display: swap;` в каждом блоке.
- `font-family` строго **`'Cormorant Garamond'`** и **`'Raleway'`** — ровно как в `style.css` (там используются через `var(--serif)` / `var(--sans)`, менять имена НЕЛЬЗЯ).
- Правильные `font-weight` (300/400/500/600) и `font-style` (normal/italic).
- `unicode-range` для каждого subset (берётся из gwfh / из ответа CSS2) — тогда браузер качает только нужный диапазон.

Шаблон одного блока:
```css
@font-face {
  font-family: 'Cormorant Garamond';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url('../fonts/cormorant-garamond-400-cyrillic.woff2') format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
```
(и аналогично — latin-блок того же веса с latin unicode-range, и т.д. для всех начертаний)

### Шаг 3. Переключить enqueue с Google на локальный файл

В `functions.php`, функция `loraleya_scripts()`.

**Найти (якорь):**
```php
    // Google Fonts
    wp_enqueue_style(
        'loraleya-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Raleway:wght@300;400;500;600&display=swap',
        [],
        null
    );

    // Main stylesheet
    wp_enqueue_style('loraleya-style', get_stylesheet_uri(), ['loraleya-fonts'], '1.0.0');
```

**Заменить на:**
```php
    // Self-hosted fonts (бывш. Google Fonts — убраны для устранения render-blocking)
    $fonts_css = get_template_directory() . '/assets/css/fonts.css';
    wp_enqueue_style(
        'loraleya-fonts',
        get_template_directory_uri() . '/assets/css/fonts.css',
        [],
        file_exists($fonts_css) ? filemtime($fonts_css) : '1.0.0'
    );

    // Main stylesheet
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'loraleya-style',
        get_stylesheet_uri(),
        ['loraleya-fonts'],
        file_exists($style_path) ? filemtime($style_path) : '1.0.0'
    );
```

### Шаг 4. filemtime для JS (тот же блок)

**Найти:**
```php
    // Main script
    wp_enqueue_script('loraleya-main', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);

    // Constructor script
    wp_enqueue_script('loraleya-constructor', get_template_directory_uri() . '/assets/js/constructor.js', [], '1.0', true);
```

**Заменить на:**
```php
    // Main script
    $main_js = get_template_directory() . '/assets/js/main.js';
    wp_enqueue_script('loraleya-main', get_template_directory_uri() . '/assets/js/main.js', [], file_exists($main_js) ? filemtime($main_js) : '1.0.0', true);

    // Constructor script
    $constructor_js = get_template_directory() . '/assets/js/constructor.js';
    wp_enqueue_script('loraleya-constructor', get_template_directory_uri() . '/assets/js/constructor.js', [], file_exists($constructor_js) ? filemtime($constructor_js) : '1.0', true);
```

---

## 2. Что НЕ делаем (границы задачи)

- НЕ трогаем `wc-blocks.css`, `front.min.css` (куки-плагин) — это отдельная уборка (P4).
- НЕ инлайним и НЕ дефферим `style.css` — отдельный шаг, риск FOUC, не в этом ТЗ.
- НЕ меняем имена `font-family`, переменные `--serif/--sans`, ничего в самом `style.css`.
- НЕ трогаем `wp_localize_script`, item-map, custom-order enqueue и enqueue на стр.~1314 (product-swatches) — кроме версий, их не касаемся.
- НЕ удаляем `dns-prefetch` — он безвреден, после ухода Google-шрифтов просто перестанет использоваться.

---

## 3. Проверка (acceptance)

1. Открыть сайт в **инкогнито**, любую страницу. Шрифты (заголовки Cormorant, текст Raleway) отрисованы корректно, **кириллица и курсив** на месте (проверить заголовок цветовой страницы — там есть курсивный подзаголовок).
2. DevTools → **Сеть** → перезагрузить: **НЕТ запросов к `fonts.googleapis.com` и `fonts.gstatic.com`**. Есть `fonts.css` и `*.woff2` с своего домена.
3. `.woff2` отдаются с `Cache-Control: max-age=31536000` (наш `.htaccess` уже это делает для woff2).
4. PageSpeed (mobile) по цветовой: находка **«Запросы, блокирующие отрисовку»** уменьшилась (ушёл внешний шрифтовой запрос). FCP не вырос.
5. После деплоя — в админке **«Удалить весь кэш»** (WP Super Cache), иначе аноним увидит старую версию.

---

## 4. Откат

```bash
git reset --hard pre-tz-perf-2
```
Либо вручную: вернуть enqueue `loraleya-fonts` на Google-URL, удалить `assets/css/fonts.css` и `assets/fonts/`.

---

## Примечание для координатора (Борис)
После успешного деплоя CSS/JS теперь версионируются по `filemtime` → можно безопасно поднять кэш `text/css` и `application/javascript` в `.htaccess` с `1 month` до `1 year` (отдельная мелкая правка). Делать только после подтверждения, что self-host шрифтов прошёл чисто.
