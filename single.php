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
