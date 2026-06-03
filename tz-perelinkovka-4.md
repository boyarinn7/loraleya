# ТЗ-4: Перелинковка блог ↔ сайт

**Версия:** 1.0
**Дата:** 03.06.2026
**Тип:** новая функция-хелпер в `functions.php` + вставки в 4 шаблона + лёгкий CSS
**Файлы:** `functions.php`, `single.php`, `taxonomy-pa_fabric_color.php`, `single-scenario.php`, `front-page.php`, `style.css`
**Исполнитель:** Claude Code
**Автор ТЗ:** Sprint-Клод

> Завершает блог: связывает его с остальным сайтом двусторонними блоками. Закрывает SEO-задачу распределения веса и даёт навигацию между разделами. Стили карточек переиспользуются из витрины (ТЗ-2) — новых почти нет.

---

## Решения

- Все блоки рендерит **одна хелпер-функция** `loraleya_render_blog_cards()` (DRY) — та же разметка `.blog-card`, что на витрине.
- Блоки показывают **последние статьи блога**, не «по категории». Причина: сейчас по одной статье на категорию, «похожие по категории» дали бы пусто. Когда блог наполнится — переключим первый аргумент на `category__in` (отмечено ниже). Это осознанный V1.
- Места: «Читать дальше» в конце статьи; «Читайте также» на цветовой и сценарной страницах; «Журнал LoraLeya» с ссылкой «Все статьи →» на главной.

PHP-фрагменты проверены php-parser (OK). Перед коммитом — `php -l` на каждом изменённом файле.

---

## ⚠️ ПЕРЕД СТАРТОМ

```
git add -A && git commit -m "checkpoint: перед перелинковкой блога (ТЗ-4)"
git tag pre-blog-links
```
Якоря вставок ниже даны по текущим версиям шаблонов. Перед каждой правкой свери, что искомый комментарий/строка на месте; если нет — сообщи, не применяй вслепую.

---

## ФАЗА 1 — `functions.php`: хелпер рендера

Добавить в конец файла.

```php
/**
 * Рендер грида карточек статей блога. Переиспользует стили .blog-card (витрина).
 */
function loraleya_render_blog_cards($args = [], $heading = '', $eyebrow = '', $show_all = false) {
    $defaults = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 3,
        'ignore_sticky_posts' => true,
    ];
    $q = new WP_Query(array_merge($defaults, $args));
    if (!$q->have_posts()) { wp_reset_postdata(); return; }
    ?>
    <section class="section blog-related">
        <div class="container">
            <?php if ($eyebrow) : ?><div class="eyebrow"><?php echo esc_html($eyebrow); ?></div><?php endif; ?>
            <?php if ($heading) : ?><h2><?php echo esc_html($heading); ?></h2><?php endif; ?>
            <div class="blog-grid">
                <?php while ($q->have_posts()) : $q->the_post();
                    $cats   = get_the_category();
                    $teaser = get_post_meta(get_the_ID(), 'seo_description', true);
                    if (!$teaser) $teaser = get_the_excerpt();
                ?>
                    <a href="<?php the_permalink(); ?>" class="blog-card">
                        <div class="blog-card__cover">
                            <?php if (has_post_thumbnail()) :
                                the_post_thumbnail('large', ['class' => 'blog-card__img']);
                            else : ?><span class="blog-card__seal">&#10022;</span><?php endif; ?>
                        </div>
                        <div class="blog-card__body">
                            <?php if (!empty($cats)) : ?><div class="blog-card__cat"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
                            <div class="blog-card__title"><?php the_title(); ?></div>
                            <div class="blog-card__teaser"><?php echo esc_html($teaser); ?></div>
                            <div class="blog-card__meta"><span><?php echo esc_html(get_the_date()); ?></span><span class="blog-card__arrow">Читать &rarr;</span></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
            <?php if ($show_all) : ?>
                <div class="blog-related__all"><a href="<?php echo esc_url(home_url('/blog/')); ?>">Все статьи &rarr;</a></div>
            <?php endif; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
}
```

---

## ФАЗА 2 — вставки в шаблоны

### 2.1 `single.php` — «Читать дальше» (внутри loop, после FAQ-блока, ПЕРЕД `</article>`)
```php
            <?php
            if (function_exists('loraleya_render_blog_cards')) {
                loraleya_render_blog_cards(
                    ['post__not_in' => [get_the_ID()], 'posts_per_page' => 3],
                    'Читать дальше', 'Журнал LoraLeya'
                );
                // когда статей станет много — заменить на похожие по рубрике:
                // ['category__in' => wp_get_post_categories(get_the_ID()), 'post__not_in' => [get_the_ID()], 'posts_per_page' => 3]
            }
            ?>
```

### 2.2 `taxonomy-pa_fabric_color.php` — «Читайте также» (ПЕРЕД строкой `<!-- FLOATING COLOR SWITCHER -->`)
```php
<?php
if (function_exists('loraleya_render_blog_cards')) {
    loraleya_render_blog_cards(['posts_per_page' => 3], 'Читайте также', 'Журнал LoraLeya');
}
?>
```

### 2.3 `single-scenario.php` — «Читайте также» (ПЕРЕД `<?php get_footer(); ?>`)
```php
<?php
if (function_exists('loraleya_render_blog_cards')) {
    loraleya_render_blog_cards(['posts_per_page' => 3], 'Читайте также', 'Журнал LoraLeya');
}
?>
```

### 2.4 `front-page.php` — «Журнал LoraLeya» (ПЕРЕД строкой `<!-- CTA -->`)
```php
<?php
if (function_exists('loraleya_render_blog_cards')) {
    loraleya_render_blog_cards(['posts_per_page' => 3], 'Журнал LoraLeya', '', true);
}
?>
```

> На цвете/сценарии eyebrow «Журнал LoraLeya» + заголовок «Читайте также». На главной — заголовок «Журнал LoraLeya», без eyebrow, со ссылкой «Все статьи →». На статье — «Журнал LoraLeya» / «Читать дальше», текущая статья исключена.

---

## ФАЗА 3 — `style.css` (немного)

Грид и карточки уже стилизованы (ТЗ-2, `.blog-grid` / `.blog-card`). Добавить только обрамление секции и ссылку «Все статьи». В конец файла:

```css

/* ===== Блоки перелинковки блога (ТЗ-4) ===== */
.blog-related{padding:4rem 0}
.blog-related .eyebrow{text-align:center; display:block; margin-bottom:.8rem}
.blog-related h2{font-family:var(--serif); font-weight:500; font-size:clamp(2rem,4vw,2.8rem); color:var(--cream,#e8e0d0); text-align:center; margin-bottom:2.5rem}
.blog-related .blog-grid{padding-bottom:0}
.blog-related__all{text-align:center; margin-top:2.5rem}
.blog-related__all a{font-size:.72rem; letter-spacing:.2em; text-transform:uppercase; color:var(--gold,#c5a55a); border-bottom:1px solid rgba(197,165,90,.3); padding-bottom:.3rem; transition:.3s}
.blog-related__all a:hover{border-color:var(--gold,#c5a55a)}
```

---

## Чек-лист

- [ ] `php -l` чист на всех пяти PHP-файлах.
- [ ] Конец статьи (`/blog/chto-takoe-kuvert/`): блок «Читать дальше» с 2 другими статьями (текущая исключена), карточки кликабельны.
- [ ] Цветовая страница (`/color/bezhevyj/`): внизу «Читайте также» с 3 статьями; флоат-свитчер палитры по-прежнему работает (блок встал выше него).
- [ ] Сценарий: внизу «Читайте также» перед подвалом, не сломав блок «другие сценарии».
- [ ] Главная: блок «Журнал LoraLeya» с 3 статьями и ссылкой «Все статьи →» (ведёт на `/blog/`), стоит перед финальным CTA.
- [ ] Карточки в блоках выглядят как на витрине (вензель-заглушка, рубрика, заголовок, тизер, дата); hover работает.
- [ ] Мобильный: сетки складываются 3→2→1, ничего не наезжает.
- [ ] Регресс: страницы статьи/цвета/сценария/главной грузятся без ошибок в консоли.

---

## Что НЕ делаем (границы)

- НЕ трогаем логику страниц, SEO-обвязку, корзину, свотчи — только добавляем блоки в конец секционных потоков.
- НЕ переключаем «похожие» на by-category сейчас (мало статей) — закомментированная строка в 2.1 для будущего.
- НЕ ставим фото — карточки показывают вензель-заглушку, как на витрине.

---

## Финал
```
git add -A
git commit -m "feat(blog): перелинковка — похожие статьи, читайте также, Журнал на главной (ТЗ-4)"
git push
```
Сообщить Борису: появились ли блоки на статье/цвете/сценарии/главной; не сломались ли существующие секции (флоат-свитчер, другие сценарии, CTA).

## Откат
```
git reset --hard pre-blog-links
```
