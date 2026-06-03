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
