<?php
/**
 * Data-driven category hub.
 *
 * Loaded only when loraleya_is_category_hub() returns true.
 */

if (!defined('ABSPATH')) {
    exit;
}

$term = get_queried_object();
if (!$term instanceof WP_Term) {
    return;
}

$term_id       = (int) $term->term_id;
$h1            = get_term_meta($term_id, 'hub_h1', true) ?: $term->name;
$lead          = get_term_meta($term_id, 'hub_lead', true);
$image_id      = absint(get_term_meta($term_id, 'hub_image_id', true));
$image_caption = get_term_meta($term_id, 'hub_image_caption', true);
$featured_id   = absint(get_term_meta($term_id, 'hub_featured_post_id', true));
$position      = get_term_meta($term_id, 'hub_position_text', true);
$seo_text      = get_term_meta($term_id, 'seo_text', true);
$steps         = loraleya_get_category_hub_json($term_id, 'hub_steps_json');
$care          = loraleya_get_category_hub_json($term_id, 'hub_care_json');
$links         = loraleya_get_category_hub_json($term_id, 'hub_links_json');
$faq           = loraleya_get_category_hub_json($term_id, 'seo_faq');
$featured      = $featured_id ? get_post($featured_id) : null;
?>

<main class="ll-category-hub">
    <section class="ll-category-hub__hero">
        <div class="container ll-category-hub__hero-grid">
            <div class="ll-category-hub__hero-copy">
                <div class="ll-category-hub__eyebrow">
                    <a href="<?php echo esc_url(home_url('/blog/')); ?>">Журнал LoraLeya</a>
                    <span aria-hidden="true">/</span>
                    <span><?php echo esc_html($term->name); ?></span>
                </div>
                <h1><?php echo esc_html($h1); ?></h1>
                <?php if ($lead) : ?>
                    <p class="ll-category-hub__lead"><?php echo esc_html($lead); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($image_id) : ?>
                <figure class="ll-category-hub__hero-media">
                    <?php echo wp_get_attachment_image($image_id, 'full', false, ['class' => 'll-category-hub__hero-image']); ?>
                    <?php if ($image_caption) : ?>
                        <figcaption><?php echo esc_html($image_caption); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($position) : ?>
        <section class="ll-category-hub__section ll-category-hub__position">
            <div class="container ll-category-hub__narrow">
                <div class="ll-category-hub__eyebrow">Подход LoraLeya</div>
                <div class="ll-category-hub__prose"><?php echo wp_kses_post($position); ?></div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($featured instanceof WP_Post && 'publish' === $featured->post_status) :
        $featured_teaser = get_post_meta($featured->ID, 'seo_description', true);
        if (!$featured_teaser) {
            $featured_teaser = get_the_excerpt($featured);
        }
        ?>
        <section class="ll-category-hub__section ll-category-hub__featured">
            <div class="container">
                <div class="ll-category-hub__eyebrow">Главный материал</div>
                <a class="ll-category-hub__featured-card" href="<?php echo esc_url(get_permalink($featured)); ?>">
                    <div class="ll-category-hub__featured-media">
                        <?php if (has_post_thumbnail($featured)) :
                            echo get_the_post_thumbnail($featured, 'large', ['class' => 'll-category-hub__featured-image']);
                        else : ?>
                            <span class="ll-category-hub__seal" aria-hidden="true">&#10022;</span>
                        <?php endif; ?>
                    </div>
                    <div class="ll-category-hub__featured-copy">
                        <time datetime="<?php echo esc_attr(get_the_date('c', $featured)); ?>"><?php echo esc_html(get_the_date('', $featured)); ?></time>
                        <h2><?php echo esc_html(get_the_title($featured)); ?></h2>
                        <?php if ($featured_teaser) : ?><p><?php echo esc_html($featured_teaser); ?></p><?php endif; ?>
                        <span class="ll-category-hub__text-link">Разобраться в материалах &rarr;</span>
                    </div>
                </a>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($steps) : ?>
        <nav class="ll-category-hub__section ll-category-hub__steps" aria-label="Разобраться по шагам">
            <div class="container">
                <div class="ll-category-hub__eyebrow">Разобраться по шагам</div>
                <ol class="ll-category-hub__steps-list">
                    <?php foreach ($steps as $index => $step) : ?>
                        <li>
                            <a href="#<?php echo esc_attr($step['target']); ?>">
                                <span><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
                                <?php echo esc_html($step['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </nav>
    <?php endif; ?>

    <?php if ($seo_text) : ?>
        <section class="ll-category-hub__section ll-category-hub__editorial">
            <div class="container ll-category-hub__narrow">
                <div class="ll-category-hub__prose"><?php echo wp_kses_post($seo_text); ?></div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($care) : ?>
        <section class="ll-category-hub__section ll-category-hub__care">
            <div class="container">
                <div class="ll-category-hub__eyebrow">Коротко об уходе</div>
                <div class="ll-category-hub__care-grid">
                    <?php foreach ($care as $item) : ?>
                        <div class="ll-category-hub__care-card">
                            <strong><?php echo esc_html($item['value']); ?></strong>
                            <span><?php echo esc_html($item['label']); ?></span>
                            <?php if (!empty($item['note'])) : ?><small><?php echo esc_html($item['note']); ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
        <section class="ll-category-hub__section ll-category-hub__articles">
            <div class="container">
                <div class="ll-category-hub__eyebrow">Ещё по теме</div>
                <div class="blog-grid">
                    <?php while (have_posts()) : the_post();
                        $cats   = get_the_category();
                        $teaser = get_post_meta(get_the_ID(), 'seo_description', true);
                        if (!$teaser) {
                            $teaser = get_the_excerpt();
                        }
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
                                <?php if ($cats) : ?><div class="blog-card__cat"><?php echo esc_html($cats[0]->name); ?></div><?php endif; ?>
                                <div class="blog-card__title"><?php the_title(); ?></div>
                                <div class="blog-card__teaser"><?php echo esc_html($teaser); ?></div>
                                <div class="blog-card__meta">
                                    <span><?php echo esc_html(get_the_date()); ?></span>
                                    <span class="blog-card__arrow">Читать &rarr;</span>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
                <?php the_posts_pagination(['mid_size' => 1]); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($links) : ?>
        <section class="ll-category-hub__section ll-category-hub__links">
            <div class="container">
                <div class="ll-category-hub__eyebrow">Перейти к изделиям</div>
                <div class="ll-category-hub__links-grid">
                    <?php foreach ($links as $link) : ?>
                        <a href="<?php echo esc_url(loraleya_category_hub_url($link['url'])); ?>">
                            <span><?php echo esc_html($link['label']); ?></span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($faq) : ?>
        <section class="ll-category-hub__section ll-category-hub__faq">
            <div class="container ll-category-hub__narrow">
                <div class="ll-category-hub__eyebrow">Вопросы и ответы</div>
                <h2>Частые вопросы</h2>
                <div class="ll-category-hub__faq-list">
                    <?php foreach ($faq as $index => $item) : ?>
                        <details<?php echo 0 === $index ? ' open' : ''; ?>>
                            <summary><?php echo esc_html($item['question']); ?></summary>
                            <div><?php echo wp_kses_post(wpautop($item['answer'])); ?></div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
