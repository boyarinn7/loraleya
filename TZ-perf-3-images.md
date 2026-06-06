# ТЗ-перф-3 — Картинки цветовой страницы: LCP + отзывчивые изображения

**Проект:** LoraLeya (loraleya.ru)
**Файл:** `wp-content/themes/<тема>/taxonomy-pa_fabric_color.php`
**Исполнитель:** Claude Code (VS Code)
**Цель:** убрать высокий LCP (4,1 с) и перевес картинок (находка PageSpeed «Улучшите загрузку изображений» — 1 618 КиБ) на странице цвета.

**Две причины проблемы (обе чиним):**
1. LCP-картинка (`hero-servirovka`, большое фото вверху) стоит с `loading="lazy"` → браузер грузит её не первой. Это завышает LCP.
2. Все картинки выводятся через `loraleya_color_photo()` = полноразмерный оригинал, без `srcset` → мобильный качает огромные файлы.

**Решение:** один новый хелпер, который отдаёт картинку через `wp_get_attachment_image()` (это автоматически добавляет `srcset` + варианты под размер экрана). LCP-картинку делаем `eager` + `fetchpriority="high"`.

---

## 0. Git-чекпойнт (до правок)

```bash
git add -A && git commit -m "checkpoint before TZ-perf-3 (color page images)" --allow-empty
git tag pre-tz-perf-3
```

---

## 1. Шаг 1 — добавить хелпер

**Найти** (конец функции `loraleya_color_photo`, перед `loraleya_color_video`):
```php
    return '';
}

function loraleya_color_video($prefix) {
```

**Заменить на** (вставляем новый хелпер между ними):
```php
    return '';
}

/**
 * Отдаёт отзывчивый <img> (srcset под размер экрана) по prefix+type.
 * $attr — массив атрибутов (alt, loading, fetchpriority, sizes…). Пусто → '' .
 */
function loraleya_color_img($prefix, $type, $size = 'medium_large', $attr = []) {
    $search_title = $prefix . '-' . $type;
    $att = get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'title'=>$search_title]);
    if (empty($att)) {
        $att = get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'s'=>$search_title]);
    }
    if (empty($att)) return '';
    return wp_get_attachment_image((int) $att[0]->ID, $size, false, $attr);
}

function loraleya_color_video($prefix) {
```
> Примечание: `wp_get_attachment_image()` по умолчанию сам ставит `loading="lazy"` и `decoding="async"`. Для LCP-картинки мы это переопределяем на `eager`.

## 2. Шаг 2 — LCP-картинка (hero-servirovka): eager + приоритет

**Найти:**
```php
            <div class="chc-main">
                <?php if ($hero_main) : ?>
                    <img src="<?php echo esc_url($hero_main); ?>" alt="Сервировка <?php echo esc_attr($color['name']); ?>" loading="lazy">
                <?php else : ?>
                    <span class="chc-ph">Фото · сервировка в этом цвете</span>
                <?php endif; ?>
            </div>
```

**Заменить на:**
```php
            <div class="chc-main">
                <?php
                $hero_main_img = loraleya_color_img($photo_prefix, 'hero-servirovka', 'large', [
                    'alt'           => 'Сервировка ' . esc_attr($color['name']),
                    'loading'       => 'eager',
                    'fetchpriority' => 'high',
                    'sizes'         => '(max-width: 700px) 100vw, 600px',
                ]);
                if ($hero_main_img) : echo $hero_main_img; else : ?>
                    <span class="chc-ph">Фото · сервировка в этом цвете</span>
                <?php endif; ?>
            </div>
```

## 3. Шаг 3 — боковые фото героя (detail + kuvert): отзывчивые, lazy

**Найти:**
```php
                <div class="chc-detail">
                    <?php if ($hero_detail) : ?>
                        <img src="<?php echo esc_url($hero_detail); ?>" alt="Детали · салфетка <?php echo esc_attr($color['name']); ?>" loading="lazy">
                    <?php else : ?>
                        <span class="chc-ph">Детали · салфетка</span>
                    <?php endif; ?>
                </div>
                <div class="chc-kuvert">
                    <?php if ($hero_kuvert) : ?>
                        <img src="<?php echo esc_url($hero_kuvert); ?>" alt="Куверт <?php echo esc_attr($color['name']); ?>" loading="lazy">
                    <?php else : ?>
                        <span class="chc-ph">Куверт · веер</span>
                    <?php endif; ?>
                </div>
```

**Заменить на:**
```php
                <div class="chc-detail">
                    <?php
                    $hero_detail_img = loraleya_color_img($photo_prefix, 'hero-detail', 'medium_large', [
                        'alt'   => 'Детали · салфетка ' . esc_attr($color['name']),
                        'sizes' => '(max-width: 700px) 45vw, 200px',
                    ]);
                    if ($hero_detail_img) : echo $hero_detail_img; else : ?>
                        <span class="chc-ph">Детали · салфетка</span>
                    <?php endif; ?>
                </div>
                <div class="chc-kuvert">
                    <?php
                    $hero_kuvert_img = loraleya_color_img($photo_prefix, 'kuvert', 'medium_large', [
                        'alt'   => 'Куверт ' . esc_attr($color['name']),
                        'sizes' => '(max-width: 700px) 45vw, 200px',
                    ]);
                    if ($hero_kuvert_img) : echo $hero_kuvert_img; else : ?>
                        <span class="chc-ph">Куверт · веер</span>
                    <?php endif; ?>
                </div>
```

## 4. Шаг 4 — убрать ставшие лишними строки

После шагов 2–3 переменные `$hero_main / $hero_detail / $hero_kuvert` больше нигде не используются. **Найти и удалить** (они только плодят лишние запросы):
```php
            $hero_main   = loraleya_color_photo($upload_url, $photo_prefix, 'hero-servirovka');
            $hero_kuvert = loraleya_color_photo($upload_url, $photo_prefix, 'kuvert');
            $hero_detail = loraleya_color_photo($upload_url, $photo_prefix, 'hero-detail');
```
(Если агент сомневается — оставить можно, вреда нет, только лишние 3 запроса.)

## 5. Шаг 5 — остальные картинки (macro, наборы, поштучно): отзывчивые, lazy

Общее правило для всех оставшихся `<img src="<?php echo esc_url($X); ?>" … loading="lazy">`, где `$X` берётся из `loraleya_color_photo(...)`: заменить на `loraleya_color_img()` с тем же `alt`, размером `medium_large`, оставив lazy (по умолчанию). Конкретно:

**5a. MACRO-strip** — найти:
```php
        <?php if ($macro_url) : ?>
            <div class="macro-item"><img src="<?php echo esc_url($macro_url); ?>" alt="<?php echo esc_attr($macro_labels[$i]); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover"></div>
        <?php else : ?>
```
заменить на:
```php
        <?php
        $macro_img = loraleya_color_img($photo_prefix, $m, 'medium', [
            'alt'   => esc_attr($macro_labels[$i]),
            'sizes' => '(max-width: 700px) 33vw, 200px',
            'style' => 'width:100%;height:100%;object-fit:cover',
        ]);
        if ($macro_img) : ?>
            <div class="macro-item"><?php echo $macro_img; ?></div>
        <?php else : ?>
```
(строку `$macro_url = loraleya_color_photo(...)` над блоком можно удалить.)

**5b. Наборы (4 карточки `.set`)** — в каждой есть пара:
```php
            <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'ТИП'); ?>
            <?php if ($nabor_photo) : ?>
                <div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="АЛТ" loading="lazy"></div>
            <?php endif; ?>
```
Заменить в каждой на:
```php
            <?php $nabor_img = loraleya_color_img($photo_prefix, 'ТИП', 'medium_large', ['alt' => 'АЛТ', 'sizes' => '(max-width: 700px) 90vw, 280px']); ?>
            <?php if ($nabor_img) : ?>
                <div class="set-img"><?php echo $nabor_img; ?></div>
            <?php endif; ?>
```
ТИП и АЛТ сохранить как были (4 шт.): `nabor-4-140` / `nabor-4-175` / `nabor-6-240` / `nabor-6-300` с соответствующими alt.

**5c. Поштучно (цикл `foreach ($products as $p)`)** — найти:
```php
            <?php if ($photo_url) : ?>
                <div class="prod-img"><img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($p['name']); ?>" loading="lazy"></div>
            <?php else : ?>
```
заменить на:
```php
            <?php $prod_img = loraleya_color_img($photo_prefix, $p['photo'], 'medium_large', ['alt' => esc_attr($p['name']), 'sizes' => '(max-width: 700px) 45vw, 260px']); ?>
            <?php if ($prod_img) : ?>
                <div class="prod-img"><?php echo $prod_img; ?></div>
            <?php else : ?>
```
(строку `$photo_url = loraleya_color_photo(...)` над блоком можно удалить.)

---

## 6. Что НЕ делаем

- НЕ трогаем `loraleya_color_photo()` и `loraleya_color_video()` — они ещё могут использоваться (видео-постер, og:image). Только добавляем рядом новый хелпер.
- НЕ трогаем `<video>` (строка с `preload="metadata"`).
- НЕ трогаем главную, наборы-логику, цены, item-map, swatch — только вывод `<img>`.
- НЕ меняем CSS.

---

## 7. Проверка (acceptance) — ВАЖНО

1. Открыть цветовую (Блек золото) в **инкогнито**. Все фото на месте, не битые.
2. DevTools → **Сеть** → фильтр «Изображения», перезагрузить на узкой (мобильной) ширине окна. Файлы фото должны быть **маленькие (сотни КБ суммарно, не мегабайты)** — браузер берёт уменьшенные варианты.
3. Кликнуть на главное фото героя в Сети → у него должен быть **`srcset` с несколькими размерами**, а `loading="eager"` и `fetchpriority="high"`.
4. **⚠ Если у картинок `srcset` пустой / только один размер** — значит WordPress не сгенерировал уменьшенные копии для этих фото (их импортировали скриптом). Тогда вес не упадёт. Решение: поставить плагин **«Regenerate Thumbnails»**, запустить один раз («Regenerate All»), затем перепроверить п.2–3. (Это разовое действие, делает Борис в админке.)
5. PageSpeed (mobile) по цветовой: находка «Улучшите загрузку изображений» (1 618 КиБ) — резко уменьшилась/ушла; **LCP с 4,1 с → ближе к 2,5 с**; оценка → 90+.
6. После деплоя — «Удалить весь кэш».

---

## 8. Откат

```bash
git reset --hard pre-tz-perf-3
```

---

## Примечание координатору (Борис)
Главную (LCP 4,4) этим ТЗ не трогаем — там hero это градиент, тяжёлой картинки нет, выигрыш мал. Если после цветовой захотим добить и её — отдельная мелкая задача (preload фонового изображения первой карточки). Пока не приоритет.
