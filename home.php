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
