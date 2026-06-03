# ТЗ-2: Витрина блога — листинг, рубрики, меню

**Версия:** 1.0
**Дата:** 03.06.2026
**Тип:** новые `home.php`, `category.php`; правки `header.php`, `style.css`; ручная настройка страницы записей
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клод
**Основа:** утверждённый прототип `proto-blog/index.html` (вид 1:1).

> Завершает блог: появляется витрина `/blog/` со списком статей, страницы рубрик и пункт «Блог» в меню. ТЗ-1 (бэкенд, `single.php`, категории) уже применён.

---

## Решения (из обсуждения прототипа)

- Вид витрины — как в прототипе: hero, фильтр рубрик, сетка карточек 3-в-ряд, подвал темы.
- Фильтр рубрик — **только реальные непустые** рубрики, выводятся динамически (новые появятся сами по мере наполнения). Затемнённых «будущих» нет.
- Обложка карточки — **золотой вензель-заглушка**, пока нет фото. Есть `featured image` у поста → показывается фото, нет → вензель. Переверстки при появлении фото не требуется.
- Тизер в карточке — из `seo_description` (SEO-выверен), fallback на `the_excerpt()`. Отдельные тизеры не пишем.

---

## ⚠️ ПЕРЕД СТАРТОМ

```
git add -A && git commit -m "checkpoint: перед витриной блога (ТЗ-2)"
git tag pre-blog-index
```

---

## ФАЗА 1 — страница записей (ручная настройка в админке)

Чтобы `/blog/` отдавал список статей, нужна «Страница записей».

1. Страницы → Добавить новую: заголовок **Блог**, ярлык (slug) — **`blog`**. Опубликовать (контент не нужен — его заменит шаблон).
2. Настройки → Чтение → «На главной странице отображать» оставить как есть (статичная главная); в поле **«Страница записей»** выбрать **Блог**. Сохранить.
3. Настройки → Постоянные ссылки → просто «Сохранить» (сброс rewrite).

**Проверка совместимости** (важно — у нас permalink `/blog/%postname%/` и страница записей со slug `blog`): после настройки открыть `loraleya.ru/blog/` (должен быть список) и `loraleya.ru/blog/chto-takoe-kuvert/` (должна быть статья). Если статья отдаёт 404 или список — сообщить, не продолжать: значит конфликт slug страницы и базы постов, решим отдельно.

---

## ФАЗА 2 — `home.php` (витрина) и `category.php` (рубрика)

Создать два новых файла в корне темы. `home.php` — шаблон страницы записей (`/blog/`), `category.php` — страницы рубрик. Оба используют одинаковую сетку карточек.

> Классы намеренно `blog-index-hero` / `blog-filters` / `blog-grid` / `blog-card*` — НЕ конфликтуют с `.blog-hero` и `.article-body` из ТЗ-1 (те для одиночной статьи).

### Файл `home.php`
```php
<?php
/**
 * Template: home.php — витрина блога (/blog/)
 */
get_header(); ?>

<section class="blog-index-hero">
    <div class="container">
        <div class="eyebrow">Журнал LoraLeya</div>
        <h1>Искусство <em>сервировки</em></h1>
        <p>Гайды по сервировке стола, уходу за текстилем и деталям, из которых рождается красивый стол.</p>
    </div>
</section>

<div class="container">
    <nav class="blog-filters">
        <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="active">Все</a>
        <?php
        $blog_cats = get_categories(['hide_empty' => true]);
        foreach ($blog_cats as $bc) {
            if ($bc->slug === 'uncategorized') continue;
            echo '<a href="' . esc_url(get_category_link($bc->term_id)) . '">' . esc_html($bc->name) . '</a>';
        }
        ?>
    </nav>
</div>

<div class="container">
    <div class="blog-grid">
        <?php if (have_posts()) : while (have_posts()) : the_post();
            $cats   = get_the_category();
            $teaser = get_post_meta(get_the_ID(), 'seo_description', true);
            if (!$teaser) $teaser = get_the_excerpt();
        ?>
            <a href="<?php the_permalink(); ?>" class="blog-card">
                <div class="blog-card__cover">
                    <?php if (has_post_thumbnail()) :
                        the_post_thumbnail('large', ['class' => 'blog-card__img']);
                    else : ?>
                        <span class="blog-card__seal">&#10022;</span>
                    <?php endif; ?>
                </div>
                <div class="blog-card__body">
                    <?php if (!empty($cats)) : ?><div class="blog-card__cat"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
                    <div class="blog-card__title"><?php the_title(); ?></div>
                    <div class="blog-card__teaser"><?php echo esc_html($teaser); ?></div>
                    <div class="blog-card__meta"><span><?php echo esc_html(get_the_date()); ?></span><span class="blog-card__arrow">Читать &rarr;</span></div>
                </div>
            </a>
        <?php endwhile; else : ?>
            <p>Статьи скоро появятся.</p>
        <?php endif; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 1]); ?>
</div>

<?php get_footer(); ?>
```

### Файл `category.php`
```php
<?php
/**
 * Template: category.php — страница рубрики блога
 */
get_header();
$term     = get_queried_object();
$seo_text = $term ? get_term_meta($term->term_id, 'seo_text', true) : '';
?>

<section class="blog-index-hero">
    <div class="container">
        <div class="eyebrow"><a href="<?php echo esc_url(home_url('/blog/')); ?>">Журнал LoraLeya</a></div>
        <h1><?php single_cat_title(); ?></h1>
        <?php if ($seo_text) : ?>
            <div class="blog-cat-intro"><?php echo wp_kses_post($seo_text); ?></div>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <nav class="blog-filters">
        <a href="<?php echo esc_url(home_url('/blog/')); ?>">Все</a>
        <?php
        $blog_cats = get_categories(['hide_empty' => true]);
        foreach ($blog_cats as $bc) {
            if ($bc->slug === 'uncategorized') continue;
            $active = (is_category($bc->term_id)) ? ' class="active"' : '';
            echo '<a href="' . esc_url(get_category_link($bc->term_id)) . '"' . $active . '>' . esc_html($bc->name) . '</a>';
        }
        ?>
    </nav>
</div>

<div class="container">
    <div class="blog-grid">
        <?php if (have_posts()) : while (have_posts()) : the_post();
            $cats   = get_the_category();
            $teaser = get_post_meta(get_the_ID(), 'seo_description', true);
            if (!$teaser) $teaser = get_the_excerpt();
        ?>
            <a href="<?php the_permalink(); ?>" class="blog-card">
                <div class="blog-card__cover">
                    <?php if (has_post_thumbnail()) :
                        the_post_thumbnail('large', ['class' => 'blog-card__img']);
                    else : ?>
                        <span class="blog-card__seal">&#10022;</span>
                    <?php endif; ?>
                </div>
                <div class="blog-card__body">
                    <?php if (!empty($cats)) : ?><div class="blog-card__cat"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
                    <div class="blog-card__title"><?php the_title(); ?></div>
                    <div class="blog-card__teaser"><?php echo esc_html($teaser); ?></div>
                    <div class="blog-card__meta"><span><?php echo esc_html(get_the_date()); ?></span><span class="blog-card__arrow">Читать &rarr;</span></div>
                </div>
            </a>
        <?php endwhile; else : ?>
            <p>В этой рубрике пока нет статей.</p>
        <?php endif; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 1]); ?>
</div>

<?php get_footer(); ?>
```

Синтаксис обоих проверен php-parser (OK). Перед коммитом — `php -l` на каждом.

---

## ФАЗА 3 — пункт «Блог» в меню (`header.php`)

В `header.php` уже есть блок с переменными `$is_scenario`, `$is_palette` и т.д. и `<nav class="main-nav">`.

**Правка 3.1** — в блоке переменных, ПОСЛЕ строки `$is_palette = is_tax('pa_fabric_color');`, добавить:
```php
    $is_blog          = is_home() || is_category() || is_singular('post');
```

**Правка 3.2** — в `<nav class="main-nav">`, между ссылкой «Палитра» и «Индивидуальный заказ», вставить:
```php
        <a href="<?php echo home_url('/blog/'); ?>" class="<?php echo $is_blog ? 'current-menu-item' : ''; ?>">Блог</a>
```

(По желанию — то же добавить в подвал, если правишь `footer.php`: ссылка «Блог» → `home_url('/blog/')`.)

---

## ФАЗА 4 — `style.css` (стили витрины)

Добавить в конец файла. Это перенос из утверждённого прототипа с финальными правками (вензель-заглушка, без подписи).

```css

/* ===== Витрина блога (ТЗ-2) ===== */
.blog-index-hero{text-align:center; padding:6rem 0 3.5rem}
.blog-index-hero .eyebrow{margin-bottom:1.3rem}
.blog-index-hero h1{font-family:var(--serif); font-weight:500; font-size:clamp(3rem,7vw,5rem); line-height:1; color:var(--cream,#e8e0d0); letter-spacing:.02em}
.blog-index-hero h1 em{font-style:italic; color:var(--gold,#c5a55a)}
.blog-index-hero p{max-width:560px; margin:1.6rem auto 0; color:var(--gold-dim,#8a7a4a); font-size:1.05rem; font-weight:300}
.blog-cat-intro{max-width:680px; margin:1.6rem auto 0; color:var(--cream,#e8e0d0); font-weight:300}
.blog-cat-intro p{margin:0 0 1rem}

.blog-filters{
    display:flex; flex-wrap:wrap; justify-content:center; gap:.4rem 2rem;
    padding:0 0 4rem; border-bottom:1px solid rgba(197,165,90,.08); margin-bottom:4rem;
    font-size:.72rem; letter-spacing:.18em; text-transform:uppercase;
}
.blog-filters a{color:var(--gold-dim,#8a7a4a); padding-bottom:.4rem; border-bottom:1px solid transparent; transition:.3s}
.blog-filters a:hover{color:var(--cream,#e8e0d0)}
.blog-filters a.active{color:var(--gold,#c5a55a); border-bottom-color:var(--gold,#c5a55a)}

.blog-grid{display:grid; grid-template-columns:repeat(3,1fr); gap:2.2rem; padding-bottom:4rem}
.blog-card{
    display:flex; flex-direction:column; border:1px solid rgba(197,165,90,.12); background:var(--bg2,#1a1917);
    opacity:0; transform:translateY(24px); animation:blogRise .8s cubic-bezier(.2,.7,.2,1) forwards;
    transition:transform .45s ease, border-color .45s ease, box-shadow .45s ease;
}
.blog-card:nth-child(1){animation-delay:.05s}
.blog-card:nth-child(2){animation-delay:.16s}
.blog-card:nth-child(3){animation-delay:.27s}
@keyframes blogRise{to{opacity:1;transform:none}}
.blog-card:hover{transform:translateY(-8px); border-color:rgba(197,165,90,.4); box-shadow:0 24px 50px -24px rgba(0,0,0,.7)}
.blog-card__cover{
    position:relative; aspect-ratio:3/2; overflow:hidden; display:flex; align-items:center; justify-content:center;
    background:radial-gradient(80% 120% at 50% 0%, rgba(197,165,90,.10), transparent 55%), linear-gradient(150deg,#211f1b,#161512 60%,#100f0d);
    border-bottom:1px solid rgba(197,165,90,.1);
}
.blog-card__img{width:100%; height:100%; object-fit:cover; display:block}
.blog-card__seal{font-size:1.8rem; color:var(--gold-dim,#8a7a4a); opacity:.5; transition:.45s}
.blog-card:hover .blog-card__seal{color:var(--gold,#c5a55a); opacity:.85}
.blog-card__body{padding:1.7rem 1.5rem 1.5rem; display:flex; flex-direction:column; flex:1}
.blog-card__cat{font-size:.65rem; letter-spacing:.22em; text-transform:uppercase; color:var(--gold,#c5a55a); margin-bottom:.8rem}
.blog-card__title{font-family:var(--serif); font-weight:500; font-size:1.55rem; line-height:1.18; color:var(--cream,#e8e0d0); margin-bottom:.7rem; transition:color .35s}
.blog-card:hover .blog-card__title{color:var(--gold-light,#d4bc7c)}
.blog-card__teaser{font-size:.92rem; color:var(--gold-dim,#8a7a4a); font-weight:300; flex:1; margin-bottom:1.4rem}
.blog-card__meta{display:flex; align-items:center; justify-content:space-between; font-size:.7rem; letter-spacing:.12em; text-transform:uppercase; color:var(--gold-dim,#8a7a4a); padding-top:1rem; border-top:1px solid rgba(197,165,90,.1)}
.blog-card__arrow{color:var(--gold,#c5a55a); transition:transform .35s}
.blog-card:hover .blog-card__arrow{transform:translateX(6px)}

@media(max-width:900px){.blog-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.blog-grid{grid-template-columns:1fr}}
```

---

## Чек-лист

- [ ] `php -l` чист: `home.php`, `category.php`, `header.php`.
- [ ] `/blog/` открывается, показывает 3 карточки статей, hero, фильтр.
- [ ] Карточки: вензель-заглушка (фото нет), рубрика, заголовок, тизер из `seo_description`, дата, «Читать →»; hover-анимация работает.
- [ ] `/blog/chto-takoe-kuvert/` по-прежнему открывает статью (не сломалось от страницы записей).
- [ ] Клик по рубрике в фильтре → `category.php`: список только этой рубрики + её название; если задан `seo_text` — выводится intro.
- [ ] В меню сайта появился пункт «Блог», ведёт на `/blog/`, подсвечивается на блоге/рубрике/статье.
- [ ] Хлебная крошка «Блог» в одиночной статье теперь ведёт на рабочий `/blog/`.
- [ ] Мобильный: сетка 3→2→1, ничего не ломается.

---

## Что НЕ делаем (границы)

- НЕ трогаем `single.php`, SEO-обвязку, поля — это ТЗ-1, оно работает.
- НЕ делаем блок «Журнал LoraLeya» на главной (`front-page.php`) и блоки «похожие/читайте также» на статьях, цветах, сценариях — это отдельный, следующий шаг перелинковки.
- НЕ заливаем фото статей — отдельная задача; шаблон уже готов их принять.

---

## Финал
```
git add -A
git commit -m "feat(blog): витрина /blog/, страницы рубрик, пункт меню (ТЗ-2)"
git push
```
Сообщить Борису: открывается ли `/blog/`, корректны ли рубрики и одиночные статьи, появился ли пункт меню.

## Откат
```
git reset --hard pre-blog-index
```
Плюс вручную: удалить страницу «Блог» и снять её из Настройки → Чтение, если откатываемся.
