# ТЗ: Ken Burns как временная замена видео на цветовых страницах

**Версия:** 1.0
**Дата:** 01.06.2026
**Тип:** правка шаблона + CSS (фронтенд, без БД)
**Файлы:** `taxonomy-pa_fabric_color.php`, `style.css`
**Исполнитель:** Claude Code (правки в файлах + git push)
**Автор ТЗ:** Sprint-Клод

---

## Контекст и цель

На цветовых страницах есть видео-блок `.video-sec` (`taxonomy-pa_fabric_color.php`). Сейчас реальное видео залито только у одного цвета (серебро) — у остальных 16 цветов рендерится пустая серая заглушка `video-box--empty`. Это блокер прода.

**Задача:** где видео нет, но есть фото — показывать анимацию **Ken Burns** (медленный зум/панорама по 1–3 фото) вместо пустой заглушки. Эффект имитирует видео и подходит премиум-тону. Когда реальные видео будут готовы и залиты как `{префикс}-video`, они автоматически перехватят показ — правка не мешает будущей замене.


**Логика трёх веток в `.video-sec`:**
1. Есть `{префикс}-video` → играем `<video>` (как сейчас).
2. Видео нет, но нашлось ≥1 фото → Ken Burns по найденным кадрам.
3. Совсем пусто → текущая заглушка `video-box--empty` (как сейчас).

Блок самоадаптивный: сам подбирает доступные кадры через существующую `loraleya_color_photo()`, работает для всех 17 цветов, корректно деградирует.

---

## ⚠️ ПЕРЕД СТАРТОМ

1. Убедись, что рабочее дерево чистое, при необходимости закоммить текущее состояние.
2. Поставь точку отката:
   ```
   git add -A && git commit -m "checkpoint: перед Ken Burns video fallback"
   git tag pre-kenburns
   ```
3. **Проверка совместимости перед правкой 1:** открой `taxonomy-pa_fabric_color.php`, найди функцию `loraleya_color_photo(` и блок `<!-- 2. VIDEO -->` / `<section class="video-sec">`. Убедись, что они на месте и переменные `$photo_prefix`, `$upload_url`, `$color['name']` определены выше по файлу (по разведке — да: `$photo_prefix` ≈ стр. 256, `$upload_url` ≈ стр. 260). Если структура отличается от приведённого ниже `НАЙТИ` — НЕ применяй вслепую, сообщи Борису.

---

## Правка 1 — `taxonomy-pa_fabric_color.php` (блок `.video-sec`)

Заменить весь блок секции видео. **Сверяй по якорным комментариям `<!-- 2. VIDEO -->` и `<!-- 3. MACRO STRIP -->`** — заменяется всё между ними (не включая строку `<!-- 3. MACRO STRIP -->`).

### НАЙТИ
```php
<!-- 2. VIDEO -->
<section class="video-sec">
    <?php $color_video = loraleya_color_video($photo_prefix); ?>

    <?php if ($color_video) : ?>
        <div class="video-box video-box--playable">
            <video
                src="<?php echo esc_url($color_video['url']); ?>"
                preload="metadata"
                controls
                playsinline
                aria-label="Сервировка <?php echo esc_attr(mb_strtolower($color['name'])); ?> при разном освещении"
            ></video>
        </div>
    <?php else : ?>
        <div class="video-box video-box--empty">
            <div class="vlabel">Видео · сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
        </div>
    <?php endif; ?>
</section>
```

### ЗАМЕНИТЬ НА
```php
<!-- 2. VIDEO -->
<section class="video-sec">
    <?php
    $color_video = loraleya_color_video($photo_prefix);

    // Ken Burns fallback: если видео нет — собрать до 3 фото-кадров.
    // Приоритет — сервировка и перелив (показывают «при разном освещении»).
    // НАСТРАИВАЕТСЯ: порядок типов и максимум кадров.
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
    ?>

    <?php if ($color_video) : ?>
        <div class="video-box video-box--playable">
            <video
                src="<?php echo esc_url($color_video['url']); ?>"
                preload="metadata"
                controls
                playsinline
                aria-label="Сервировка <?php echo esc_attr(mb_strtolower($color['name'])); ?> при разном освещении"
            ></video>
        </div>
    <?php elseif ($kb_count > 0) : ?>
        <div class="video-box video-box--kenburns kb--<?php echo (int)$kb_count; ?>"
             role="img"
             aria-label="Сервировка <?php echo esc_attr(mb_strtolower($color['name'])); ?> при разном освещении">
            <?php foreach ($kb_frames as $kb_i => $kb_src) : ?>
                <div class="kb-frame">
                    <img src="<?php echo esc_url($kb_src); ?>" alt="" <?php echo $kb_i === 0 ? '' : 'loading="lazy"'; ?>>
                </div>
            <?php endforeach; ?>
            <div class="vlabel vlabel--overlay">Сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
        </div>
    <?php else : ?>
        <div class="video-box video-box--empty">
            <div class="vlabel">Видео · сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
        </div>
    <?php endif; ?>
</section>
```

**Синтаксис фрагмента проверен php-parser (OK). Прогони `php -l taxonomy-pa_fabric_color.php` в своей среде перед коммитом** — у Sprint-Клода нет PHP-бинарника в песочнице.

---

## Правка 2 — `style.css` (стили Ken Burns)

Вставить блок **сразу после правила `.video-box video { ... }`** (по разведке — заканчивается перед `.vlabel`).

### НАЙТИ
```css
.video-box video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
```

### ВСТАВИТЬ ПОСЛЕ (не заменяя найденное)
```css

/* ===== Ken Burns — фото вместо видео (временный фолбэк) ===== */
/* .video-box--kenburns наследует .video-box: aspect-ratio 16/7, position:relative, overflow:hidden */

.video-box--kenburns .kb-frame {
    position: absolute;
    inset: 0;
    opacity: 0;
    will-change: opacity;
}
.video-box--kenburns .kb-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform-origin: center;
    will-change: transform;
}

/* подпись-оверлей + затемнение снизу для читаемости */
.video-box--kenburns::after {
    content: "";
    position: absolute;
    left: 0; right: 0; bottom: 0;
    height: 5rem;
    background: linear-gradient(to top, rgba(14,14,12,.6), transparent);
    z-index: 1;
    pointer-events: none;
}
.vlabel--overlay {
    position: absolute;
    left: 1.25rem;
    bottom: 1rem;
    margin: 0;
    z-index: 2;
    pointer-events: none;
}

/* 1 кадр: только медленный зум, без смены */
.kb--1 .kb-frame { opacity: 1; }
.kb--1 .kb-frame img { animation: kbZoom 14s ease-in-out infinite alternate; }

/* 2 кадра: crossfade, по 8с на кадр, цикл 16с */
.kb--2 .kb-frame { animation: kbFade2 16s ease-in-out infinite; }
.kb--2 .kb-frame:nth-child(1) { animation-delay: 0s; }
.kb--2 .kb-frame:nth-child(2) { animation-delay: 8s; }
.kb--2 .kb-frame img { animation: kbZoom 8s ease-in-out infinite alternate; }

/* 3 кадра: crossfade, по 8с на кадр, цикл 24с */
.kb--3 .kb-frame { animation: kbFade3 24s ease-in-out infinite; }
.kb--3 .kb-frame:nth-child(1) { animation-delay: 0s; }
.kb--3 .kb-frame:nth-child(2) { animation-delay: 8s; }
.kb--3 .kb-frame:nth-child(3) { animation-delay: 16s; }
.kb--3 .kb-frame img { animation: kbZoom 8s ease-in-out infinite alternate; }

/* НАСТРАИВАЕТСЯ: деликатность зума и панорамы */
@keyframes kbZoom {
    from { transform: scale(1.0)  translate(0, 0); }
    to   { transform: scale(1.08) translate(-1.5%, -1%); }
}
@keyframes kbFade2 {
    0% { opacity: 0; } 6% { opacity: 1; } 44% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 0; }
}
@keyframes kbFade3 {
    0% { opacity: 0; } 4% { opacity: 1; } 29% { opacity: 1; } 33% { opacity: 0; } 100% { opacity: 0; }
}

/* a11y: при reduce-motion — статичный первый кадр, без движения */
@media (prefers-reduced-motion: reduce) {
    .video-box--kenburns .kb-frame { animation: none; opacity: 0; }
    .video-box--kenburns .kb-frame:first-child { opacity: 1; }
    .video-box--kenburns .kb-frame img { animation: none; transform: none; }
}
```

---

## Настраиваемые параметры (если Борис захочет иначе — менять тут)

| Что | Где | Текущее значение | Как изменить |
|---|---|---|---|
| Какие кадры берём и в каком порядке | Правка 1, `$kb_types` | сервировка → перелив → деталь → фактура → салфетка | переставить/убрать типы |
| Максимум кадров | Правка 1, `$kb_max` | 3 | поставить 1 или 2 |
| Скорость зума | CSS `@keyframes kbZoom` | scale 1.0→1.08 | усилить/ослабить scale и translate |
| Длительность показа кадра | CSS `.kb--2/.kb--3` | 8с на кадр | менять `animation-delay` и длительность синхронно |
| Подпись-оверлей | Правка 1, `.vlabel--overlay` | «Сервировка {цвет} при разном освещении» | убрать `<div>` целиком, если не нужна |

---

## Чек-лист проверки результата

- [ ] `php -l taxonomy-pa_fabric_color.php` — без ошибок.
- [ ] Цвет с реальным видео (**серебро**): по-прежнему `<video controls>`, Ken Burns НЕ появился.
- [ ] Цвет без видео, но с фото (любой из 16): вместо серой заглушки — анимация Ken Burns, фото плавно зумятся/сменяются.
- [ ] Цвет, у которого фото нужных типов нет: осталась старая заглушка `video-box--empty` (страница не сломана).
- [ ] Подпись-оверлей читается на светлых и тёмных кадрах (за счёт градиента снизу).
- [ ] Мобильная вёрстка: блок держит `aspect-ratio 16/7`, не растягивается.
- [ ] DevTools → эмуляция `prefers-reduced-motion: reduce`: анимация останавливается, виден статичный первый кадр.
- [ ] Консоль браузера — без ошибок; 404 по фото нет.

---

## Что НЕ делаем (границы)

- НЕ трогаем функции `loraleya_color_video()` и `loraleya_color_photo()` — переиспользуем как есть.
- НЕ трогаем ветку реального видео и ветку пустой заглушки — только добавляем среднюю.
- НЕ заливаем фото и НЕ работаем с БД/Media Library.
- НЕ меняем разметку других секций цветовой страницы (hero, macro-strip, наборы, FAQ).
- НЕ оптимизируем/апскейлим сами фото — апскейл проблемных кадров отдельной задачей, по факту.

---

## Финал

1. `git add -A`
2. `git commit -m "feat(color): Ken Burns фото-фолбэк вместо видео на цветовых страницах"`
3. `git push`
4. Сообщить Борису:
   - на скольких цветах Ken Burns реально появился (= у скольких нашлись фото нужных типов),
   - есть ли цвета, оставшиеся с пустой заглушкой (= нет ни одного из `$kb_types`),
   - визуальные замечания: где зум даёт «мыло» (кандидаты на апскейл).

## Откат
```
git reset --hard pre-kenburns
```
(или `git revert` коммита, если уже ушло в общую ветку.)
