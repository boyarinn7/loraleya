# ТЗ Code-Клоду — LCP страницы цвета (Ken Burns грузит оригиналы)

**Файл:** `taxonomy-pa_fabric_color.php` (один файл, точечно)
**Зачем:** PageSpeed mobile `/color/biryuza/` = 70, LCP 8.9 с, экономия по картинкам ~1880 КиБ. Шаблон один на все 17 цветов → фикс чинит все. Остальные страницы сайта 92–100, их не трогаем.

## Диагноз (по коду, проверено)

Hero / макро-полоса / наборы / товары — через `loraleya_color_img()` → `wp_get_attachment_image()` с именованными размерами: srcset + width/height + авто-lazy. **Это корректно, не трогать.** Hero-картинка помечена `fetchpriority=high` + `loading=eager` — правильный LCP-кандидат.

Провал — блок **Ken Burns** в секции VIDEO (ветка «видео нет»). Кадры собираются через `loraleya_color_photo()`, которая возвращает `wp_get_attachment_url()` = **оригинал без ресайза** (многомегабайтный, без srcset). Кадр 0 ещё и `eager`. Эти оригиналы = ~1880 КиБ и конкуренция за канал с настоящей hero-картинкой → LCP 8.9 с.

## Правка

### 1. Сбор кадров — хранить ТИП, а не URL оригинала (строки ~420–432)

**Было:**
```php
$kb_types  = ['hero-servirovka', 'macro-pereliv', 'hero-detail', 'macro-faktura', 'salfetka-tsvetok'];
$kb_max    = 3;
$kb_frames = [];
if (!$color_video) {
    foreach ($kb_types as $kb_t) {
        $kb_u = loraleya_color_photo($upload_url, $photo_prefix, $kb_t);
        if ($kb_u) {
            $kb_frames[] = $kb_u;
            if (count($kb_frames) >= $kb_max) break;
        }
    }
}
$kb_count = count($kb_frames);
```

**Стало:**
```php
$kb_types  = ['hero-servirovka', 'macro-pereliv', 'hero-detail', 'macro-faktura', 'salfetka-tsvetok'];
$kb_max    = 3;
$kb_frames = []; // теперь храним ТИП кадра, а не URL оригинала
if (!$color_video) {
    foreach ($kb_types as $kb_t) {
        if (loraleya_color_photo($upload_url, $photo_prefix, $kb_t)) { // только проверка наличия
            $kb_frames[] = $kb_t;
            if (count($kb_frames) >= $kb_max) break;
        }
    }
}
$kb_count = count($kb_frames);
```

### 2. Рендер кадров — через `loraleya_color_img()` размером `large`, все lazy (строки ~449–453)

**Было:**
```php
<?php foreach ($kb_frames as $kb_i => $kb_src) : ?>
    <div class="kb-frame">
        <img src="<?php echo esc_url($kb_src); ?>" alt="" <?php echo $kb_i === 0 ? '' : 'loading="lazy"'; ?>>
    </div>
<?php endforeach; ?>
```

**Стало:**
```php
<?php foreach ($kb_frames as $kb_type) :
    $kb_img = loraleya_color_img($photo_prefix, $kb_type, 'large', [
        'alt'     => '',
        'loading' => 'lazy',
        'sizes'   => '(max-width: 700px) 100vw, 900px',
    ]);
?>
    <div class="kb-frame"><?php echo $kb_img; ?></div>
<?php endforeach; ?>
```

## Что это даёт
1. Кадры Ken Burns теперь = производная `large` (srcset + width/height) вместо оригинала → минус ~1880 КиБ.
2. Все кадры `loading=lazy`. KB сидит ниже hero; LCP — это hero (уже `fetchpriority=high`). Кадр 0 больше не `eager` → не отбирает канал у hero-картинки.
3. `hero-servirovka` в KB переиспользует тот же `large`, что и hero → из кэша, почти бесплатно.

## НЕ трогать
- Блок HERO (строки ~371–410) — уже верный.
- Макро-полосу, наборы, товары — уже верные.
- Функцию `loraleya_color_photo()` — остаётся как проверка наличия (возвращает URL или ''), это ок.
- Инлайн-стили и floating color switcher — к LCP не относятся.

## Деплой и проверка
- Атомарный коммит, `git push`. Борис деплоит через WP File Manager, чистит кэш («Удалить весь кэш»).
- Перетест PageSpeed mobile: `https://loraleya.ru/color/biryuza/` + 1–2 других цвета (`/color/bezhevyj/`, `/color/grafit/`).

## Критерий приёмки
- `/color/biryuza/` mobile: LCP ≤ 2.5 с, Производительность ≥ 90.
- Визуально Ken Burns-секция не сломалась (кадры на месте, анимация идёт).

## Если LCP всё ещё > 2.5 с после этого (вторичное, отдельным шагом)
Останется render-blocking: в `<head>` блокируют `fonts.css` + главный `style.css` (скрипты уже в футере, Google Fonts убраны). Тогда Борис присылает Спринт-Клоду размер/содержимое `style.css` — оценим critical CSS / `media` для несрочного. Пока не делаем — сначала картинки.
