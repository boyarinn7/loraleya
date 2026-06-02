# ТЗ-1: Инфраструктура блога — бэкенд + страница статьи

**Версия:** 1.0
**Дата:** 02.06.2026
**Тип:** правки `functions.php` + новый `single.php` + правки `style.css` + одноразовый seed-скрипт
**Файлы:** `functions.php`, `single.php` (новый), `style.css`, `seed-blog-categories.php` (новый, временный)
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клод

> Это **ТЗ-1 из двух**. Покрывает всё, что нужно, чтобы статья блога жила и индексировалась: SEO-поля, обвязка, шаблон страницы, категории. После него можно заливать 3 статьи и они работают.
> **ТЗ-2** (отдельно, следом) — витрина `/blog/`, `category.php` и перелинковочные блоки (главная, цвета, сценарии, «похожие статьи»).

---

## Контекст и зафиксированные решения

- Сущность статей — нативный `post`. URL блога — `/blog/`. SEO-обвязка — **Вариант А** (расширяем существующую кастомную, не ждём Rank Math).
- Рубрики — нативные WP Categories. На старте создаём 3 наполняемые (`entsiklopediya`, `materialy-i-ukhod`, `prazdnichnaya-servirovka`). Расширение до 6 хабов — по решению SEO-Клода (см. Фаза 4).
- FAQ статьи хранится в post-meta `seo_faq` (JSON), выводится и как блок на странице, и как JSON-LD FAQPage.
- Контент 3 статей уже нормализован (frontmatter + чистый HTML), заливка — после этого ТЗ.

**Все PHP-фрагменты ниже проверены парсером (php-parser, OK). Перед коммитом прогони `php -l` на каждом изменённом/новом `.php` в своей среде** — у Sprint-Клода нет PHP-бинарника в песочнице.

---

## ⚠️ ПЕРЕД СТАРТОМ

```
git add -A && git commit -m "checkpoint: перед инфраструктурой блога (ТЗ-1)"
git tag pre-blog-infra
```
Сверь, что в `functions.php` присутствуют доноры (по ним копируем паттерн): мета-бокс сценария `add_meta_boxes_scenario` / `loraleya_scenario_seo_meta_box` / `save_post_scenario`; term-meta цветов `pa_fabric_color_add_form_fields` и далее; три SEO-функции `pre_get_document_title`, два `wp_head`. Если структура отличается — НЕ применяй вслепую, сообщи.

---

## ФАЗА 1 — `functions.php`

### Правка 1.1 — post-meta + мета-бокс SEO для статей

Добавить в конец файла (перед закрывающим `?>`, если он есть; иначе в конец). Это копия паттерна сценария с заменой `scenario` → `post` плюс регистрация полей в REST (чтобы будущий пайплайн мог писать их по API; ручное заполнение это не ломает).

```php
// === SEO-поля для статей блога (post) — ТЗ-1 ===

add_action('init', function() {
    foreach (['seo_title', 'seo_description', 'seo_faq'] as $key) {
        register_post_meta('post', $key, [
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
        ]);
    }
});

add_action('add_meta_boxes_post', function() {
    add_meta_box('post_seo_meta', 'SEO-поля статьи', 'loraleya_post_seo_meta_box', 'post', 'normal', 'high');
});

function loraleya_post_seo_meta_box($post) {
    wp_nonce_field('loraleya_post_seo_save', 'loraleya_post_seo_nonce');
    $seo_title       = get_post_meta($post->ID, 'seo_title', true);
    $seo_description = get_post_meta($post->ID, 'seo_description', true);
    $seo_faq         = get_post_meta($post->ID, 'seo_faq', true);
    ?>
    <p>
        <label for="seo_title"><strong>SEO Title:</strong></label><br>
        <input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width:100%">
        <small>50-65 символов</small>
    </p>
    <p>
        <label for="seo_description"><strong>SEO Description:</strong></label><br>
        <textarea name="seo_description" id="seo_description" rows="3" style="width:100%"><?php echo esc_textarea($seo_description); ?></textarea>
        <small>120-160 символов</small>
    </p>
    <p>
        <label for="seo_faq"><strong>SEO FAQ (JSON):</strong></label><br>
        <textarea name="seo_faq" id="seo_faq" rows="12" style="width:100%; font-family:monospace"><?php echo esc_textarea($seo_faq); ?></textarea>
        <small>JSON-массив объектов {question, answer}.</small>
    </p>
    <?php
}

add_action('save_post_post', function($post_id) {
    if (!isset($_POST['loraleya_post_seo_nonce']) ||
        !wp_verify_nonce($_POST['loraleya_post_seo_nonce'], 'loraleya_post_seo_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_faq'])) {
        update_post_meta($post_id, 'seo_faq', wp_unslash($_POST['seo_faq']));
    }
});
```

### Правка 1.2 — term-meta SEO для категорий блога

Добавить следом. Копия паттерна цветов с заменой `pa_fabric_color` → `category`. Поля: `seo_title`, `seo_description`, `seo_text` (FAQ для категорий не нужен).

```php
// === SEO-поля для категорий блога — ТЗ-1 ===

add_action('category_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label for="seo_title">SEO Title</label>
        <input type="text" name="seo_title" id="seo_title" value="">
        <p>50-65 символов.</p>
    </div>
    <div class="form-field">
        <label for="seo_description">SEO Description</label>
        <textarea name="seo_description" id="seo_description" rows="2"></textarea>
        <p>120-160 символов.</p>
    </div>
    <div class="form-field">
        <label for="seo_text">SEO Text (HTML)</label>
        <textarea name="seo_text" id="seo_text" rows="10"></textarea>
        <p>Описание хаба. Допустимы h2, h3, p, ul, li, a, strong.</p>
    </div>
    <?php
});

add_action('category_edit_form_fields', function($term) {
    $seo_title       = get_term_meta($term->term_id, 'seo_title', true);
    $seo_description = get_term_meta($term->term_id, 'seo_description', true);
    $seo_text        = get_term_meta($term->term_id, 'seo_text', true);
    ?>
    <tr class="form-field">
        <th><label for="seo_title">SEO Title</label></th>
        <td><input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>"></td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_description">SEO Description</label></th>
        <td><textarea name="seo_description" id="seo_description" rows="2" cols="50"><?php echo esc_textarea($seo_description); ?></textarea></td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_text">SEO Text (HTML)</label></th>
        <td><textarea name="seo_text" id="seo_text" rows="15" cols="50" class="large-text"><?php echo esc_textarea($seo_text); ?></textarea></td>
    </tr>
    <?php
});

$loraleya_cat_seo_save = function($term_id) {
    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_term_meta($term_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_text'])) {
        update_term_meta($term_id, 'seo_text', wp_kses_post($_POST['seo_text']));
    }
};
add_action('created_category', $loraleya_cat_seo_save);
add_action('edited_category', $loraleya_cat_seo_save);
```

### Правка 1.3 — расширить три SEO-функции на статьи и категории

В существующих функциях добавить ветки. **Не создавать новые функции — дописать в существующие.**

**1.3.a — `pre_get_document_title`.** Внутри колбэка, ПЕРЕД блоком `if (is_front_page()) {`, вставить:
```php
    if (is_singular('post')) {
        $custom = get_post_meta(get_the_ID(), 'seo_title', true);
        if (!empty($custom)) return $custom;
    }
    if (is_category()) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {
            $custom = get_term_meta($term->term_id, 'seo_title', true);
            if (!empty($custom)) return $custom;
        }
    }
```

**1.3.b — `wp_head` meta description** (приоритет 5). В цепочке `if / elseif`, ПЕРЕД `elseif (is_front_page())`, добавить два звена:
```php
    } elseif (is_singular('post')) {
        $description = get_post_meta(get_the_ID(), 'seo_description', true);
    } elseif (is_category()) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {
            $description = get_term_meta($term->term_id, 'seo_description', true);
        }
```
(встроить как новые `elseif` перед `is_front_page`, сохранив структуру.)

**1.3.c — `wp_head` JSON-LD FAQPage.** В цепочке после блока `elseif (is_singular('scenario'))`, добавить:
```php
    } elseif (is_singular('post')) {
        $faq_json = get_post_meta(get_the_ID(), 'seo_faq', true);
```
(категории FAQ не имеют — ветку `is_category` сюда НЕ добавляем. Генератор схемы ниже не трогаем — он переиспользуется.)

---

## ФАЗА 2 — новый файл `single.php`

Создать в корне темы. Hero (хлебные крошки + категория + H1 + дата) → тело через `the_content()` в `.article-body` → FAQ-блок из `seo_faq` (разметка идентична FAQ цветовых страниц, источник — post-meta).

```php
<?php
/**
 * Template: single.php — страница статьи блога
 */
get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<article <?php post_class('blog-single'); ?>>

    <section class="blog-hero">
        <div class="container">
            <?php $cats = get_the_category(); ?>
            <nav class="blog-hero__bc">
                <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a> /
                <a href="<?php echo esc_url(home_url('/blog/')); ?>">Блог</a>
                <?php if (!empty($cats)) : ?>
                    / <a href="<?php echo esc_url(get_category_link($cats[0]->term_id)); ?>"><?php echo esc_html($cats[0]->name); ?></a>
                <?php endif; ?>
            </nav>
            <?php if (!empty($cats)) : ?><div class="eyebrow"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
            <h1><?php the_title(); ?></h1>
            <div class="blog-hero__meta"><?php echo esc_html(get_the_date()); ?></div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="article-body"><?php the_content(); ?></div>
        </div>
    </section>

    <?php
    $faq_json = get_post_meta(get_the_ID(), 'seo_faq', true);
    $faq_data = !empty($faq_json) ? json_decode($faq_json, true) : [];
    if (is_array($faq_data) && !empty($faq_data)) : ?>
    <section class="color-faq">
        <div class="container">
            <div class="color-faq__inner">
                <div class="eyebrow">Частые вопросы</div>
                <div class="color-faq__list">
                    <?php foreach ($faq_data as $item) : ?>
                        <details class="color-faq__item">
                            <summary class="color-faq__question"><?php echo esc_html($item['question'] ?? ''); ?></summary>
                            <div class="color-faq__answer"><?php echo wp_kses_post($item['answer'] ?? ''); ?></div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

</article>
<?php endwhile; ?>
<?php get_footer(); ?>
```

> Примечание: `single.php` перехватывает ВСЕ нативные посты. У темы CPT-страницы (`single-scenario.php`, `taxonomy-*`) имеют свои шаблоны и не затрагиваются.

---

## ФАЗА 3 — `style.css` (добавки)

Вставить в конец файла. FAQ переиспользует существующие `.color-faq*` — их НЕ дублируем. Новое: hero статьи, тело `.article-body` (включая `<table>` и `<ol>`, которых нет в `.color-seo-text__inner`).

```css

/* ===== Страница статьи блога (ТЗ-1) ===== */
.blog-hero {
    padding: 6rem 0 2.5rem;
    border-bottom: 1px solid rgba(197,165,90,.12);
}
.blog-hero__bc {
    font-size: .7rem; letter-spacing: .15em; text-transform: uppercase;
    color: var(--gold-dim, #8a7a4a); margin-bottom: 1.5rem;
}
.blog-hero__bc a { color: var(--gold-dim, #8a7a4a); text-decoration: none; }
.blog-hero__bc a:hover { color: var(--gold, #c5a55a); }
.blog-hero h1 {
    font-family: var(--serif); font-size: clamp(2rem, 5vw, 3.25rem);
    line-height: 1.12; color: var(--cream, #e8e0d0); margin: .5rem 0 1rem;
}
.blog-hero__meta { font-size: .8rem; color: var(--gold-dim, #8a7a4a); }

.article-body {
    max-width: 760px; margin: 0 auto;
    font-family: var(--sans); color: var(--cream, #e8e0d0); line-height: 1.8;
}
.article-body h2 { font-family: var(--serif); font-size: 1.9rem; color: var(--cream, #e8e0d0); margin: 2.5rem 0 1rem; }
.article-body h3 { font-family: var(--serif); font-size: 1.35rem; color: var(--cream, #e8e0d0); margin: 2rem 0 .75rem; }
.article-body p { margin: 0 0 1.25rem; }
.article-body ul, .article-body ol { margin: 0 0 1.25rem 1.5rem; }
.article-body li { margin: 0 0 .5rem; }
.article-body a { color: var(--gold, #c5a55a); border-bottom: 1px solid rgba(197,165,90,.3); text-decoration: none; }
.article-body a:hover { border-color: var(--gold, #c5a55a); }
.article-body strong { color: var(--cream, #e8e0d0); font-weight: 600; }

/* таблица — статья «Хлопок или полиэстер» */
.article-body table { width: 100%; border-collapse: collapse; margin: 0 0 1.5rem; font-size: .95rem; }
.article-body th, .article-body td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid rgba(197,165,90,.15); }
.article-body thead th {
    font-family: var(--sans); font-weight: 600; color: var(--gold, #c5a55a);
    text-transform: uppercase; letter-spacing: .05em; font-size: .8rem;
}
.article-body tbody tr:hover { background: rgba(197,165,90,.04); }

@media (max-width: 600px) {
    .article-body table, .article-body thead, .article-body tbody,
    .article-body tr, .article-body th, .article-body td { display: block; }
    .article-body thead { display: none; }
    .article-body td { border: none; padding: .35rem 0; }
    .article-body tbody tr { border-bottom: 1px solid rgba(197,165,90,.15); padding: .75rem 0; }
}
```

---

## ФАЗА 4 — категории блога (одноразовый seed)

Создать `seed-blog-categories.php` в корне сайта (рядом с `wp-load.php`). Запуск по URL: сначала dry-run, затем `?run=1`. **После запуска файл удалить.**

```php
<?php
require_once(dirname(__FILE__) . '/wp-load.php');   // ПОДОГНАТЬ путь к wp-load.php
if (!current_user_can('manage_options')) { wp_die('Доступ запрещён'); }
$dry = !isset($_GET['run']);

$cats = [
    ['name' => 'Энциклопедия премиум-сервировки', 'slug' => 'entsiklopediya'],
    ['name' => 'Материалы и уход',                 'slug' => 'materialy-i-ukhod'],
    ['name' => 'Праздничная сервировка',           'slug' => 'prazdnichnaya-servirovka'],
    // Расширение до 6 хабов — по решению SEO-Клода (раскомментировать при подтверждении):
    // ['name' => 'Салфетки и складывание', 'slug' => 'salfetki-i-skladyvanie'],
    // ['name' => 'Сервировка-гайды',        'slug' => 'servirovka-gajdy'],
    // ['name' => 'Подарки',                 'slug' => 'podarki'],
];

echo '<pre>';
foreach ($cats as $c) {
    if (term_exists($c['slug'], 'category')) { echo "= уже есть: {$c['slug']}\n"; continue; }
    if ($dry) { echo "[dry] создал бы: {$c['name']} ({$c['slug']})\n"; continue; }
    $res = wp_insert_term($c['name'], 'category', ['slug' => $c['slug']]);
    echo is_wp_error($res)
        ? "! ошибка {$c['slug']}: " . $res->get_error_message() . "\n"
        : "+ создана: {$c['slug']}\n";
}
echo $dry ? "\nDRY-RUN. Добавь ?run=1 для применения.\n" : "\nГОТОВО. Удали файл после запуска.\n";
echo '</pre>';
```

> Открытый вопрос (НЕ блокирует): SEO-Клод решает, делать ли все 6 хабов сразу и закрывать ли пустые категории от индексации (thin content). Сейчас создаём 3 наполняемые — под три стартовые статьи.

---

## Чек-лист проверки

- [ ] `php -l` без ошибок: `functions.php`, `single.php`, `seed-blog-categories.php`.
- [ ] В редакторе статьи (Записи → Добавить) появился блок «SEO-поля статьи» с тремя полями.
- [ ] В категории (Записи → Рубрики → ред.) появились SEO Title / Description / Text.
- [ ] Seed отработал: 3 категории созданы (Записи → Рубрики).
- [ ] Создать тестовый пост, заполнить `seo_title`/`seo_description`/`seo_faq` (валидный JSON), назначить категорию, опубликовать:
  - [ ] открыть → рендерится `single.php` (hero с крошками, тело, FAQ-аккордеон);
  - [ ] `<title>` страницы = `seo_title`; в `<head>` есть `<meta name="description">` = `seo_description`;
  - [ ] в исходнике есть `<script type="application/ld+json">` с `FAQPage`;
  - [ ] таблица/списки/ссылки в теле стилизованы; на мобильном таблица стекается.
- [ ] Открыть страницу категории → `<title>`/description берутся из term-meta (если заполнены).
- [ ] Регресс: страница цвета и страница сценария по-прежнему отдают свои title/description/FAQ (ничего не сломалось).
- [ ] Консоль браузера чистая.

---

## Что НЕ делаем в этом ТЗ (границы)

- НЕ трогаем генератор схемы FAQPage и существующие ветки цветов/сценариев — только добавляем звенья.
- НЕ создаём листинг `/blog/` и `category.php` — это ТЗ-2.
- НЕ создаём перелинковочные блоки (главная, цвета, сценарии, «похожие») — это ТЗ-2.
- НЕ заливаем статьи — отдельный шаг после этого ТЗ.
- НЕ трогаем `loraleya_disable_gutenberg` (для постов редактор нужен включённым).

---

## Финал

```
git add -A
git commit -m "feat(blog): SEO-поля постов и категорий, обвязка, single.php, seed категорий (ТЗ-1)"
git push
```
Сообщить Борису: прошёл ли чек-лист; какие категории созданы; работает ли FAQ-schema на тестовом посте; удалён ли seed-файл.

## Откат
```
git reset --hard pre-blog-infra
```
И вручную: удалить созданные категории (если seed уже запускался) и `seed-blog-categories.php`.
