<?php
/**
 * LoraLeya — интеграция с Rank Math SEO.
 *
 * Автоматически подключается плагином Rank Math,
 * когда файл находится в корне активной темы.
 * Передаёт seo_title и seo_description из meta-полей темы
 * в pipeline Rank Math: title, description, OG Facebook, Twitter.
 *
 * Для редакционного хаба рубрики также передаёт главное изображение
 * в Open Graph и Twitter Card.
 *
 * Что НЕ затрагивает этот файл:
 * - JSON-LD FAQPage schema (functions.php / inc/category-hub.php)
 * - Schema.org для /about/ (functions.php)
 * - canonical, robots, sitemap — полностью за Rank Math
 * - метабоксы и формы сохранения meta-полей темы
 * - /blog/ (is_home()) — намеренно не включён, отдельная задача
 */

defined( 'ABSPATH' ) || exit;

/**
 * Title.
 * Rank Math строит итоговый title через фильтр rank_math/frontend/title.
 * Priority 20: после внутренней логики плагина.
 */
add_filter( 'rank_math/frontend/title', function( $title ) {
    $custom = loraleya_get_seo_field( 'seo_title' );
    return ! empty( $custom ) ? $custom : $title;
}, 20 );

/**
 * Meta description.
 * Rank Math читает этот фильтр перед выводом <meta name="description">.
 * Резервный wp_head action при активном Rank Math не регистрируется,
 * поэтому дублирования тега нет.
 */
add_filter( 'rank_math/frontend/description', function( $desc ) {
    $custom = loraleya_get_seo_field( 'seo_description' );
    return ! empty( $custom ) ? $custom : $desc;
}, 20 );

/**
 * Open Graph: Facebook.
 */
add_filter( 'rank_math/opengraph/facebook/og_title', function( $val ) {
    $custom = loraleya_get_seo_field( 'seo_title' );
    return ! empty( $custom ) ? $custom : $val;
}, 20 );

add_filter( 'rank_math/opengraph/facebook/og_description', function( $val ) {
    $custom = loraleya_get_seo_field( 'seo_description' );
    return ! empty( $custom ) ? $custom : $val;
}, 20 );

/**
 * Twitter Card.
 */
add_filter( 'rank_math/opengraph/twitter/twitter_title', function( $val ) {
    $custom = loraleya_get_seo_field( 'seo_title' );
    return ! empty( $custom ) ? $custom : $val;
}, 20 );

add_filter( 'rank_math/opengraph/twitter/twitter_description', function( $val ) {
    $custom = loraleya_get_seo_field( 'seo_description' );
    return ! empty( $custom ) ? $custom : $val;
}, 20 );

/**
 * Check whether the current request is an enabled editorial category hub.
 *
 * @return bool
 */
function loraleya_is_enabled_category_hub_social_context() {
    if ( ! is_category() ) {
        return false;
    }

    $term = get_queried_object();
    if ( ! $term instanceof WP_Term || 'category' !== $term->taxonomy ) {
        return false;
    }

    return '1' === (string) get_term_meta( $term->term_id, 'hub_enabled', true );
}

/**
 * Return the hero image data for an enabled editorial category hub.
 *
 * Keeping the attachment ID and metadata lets Rank Math output
 * og:image:width, og:image:height, og:image:type and og:image:alt.
 *
 * @return array
 */
function loraleya_get_category_hub_social_image_data() {
    if ( ! loraleya_is_enabled_category_hub_social_context() ) {
        return [];
    }

    $term     = get_queried_object();
    $image_id = absint( get_term_meta( $term->term_id, 'hub_image_id', true ) );
    if ( ! $image_id || 'attachment' !== get_post_type( $image_id ) ) {
        return [];
    }

    $image = wp_get_attachment_image_src( $image_id, 'full' );
    if ( ! is_array( $image ) || empty( $image[0] ) ) {
        return [];
    }

    return [
        'id'     => $image_id,
        'url'    => (string) $image[0],
        'width'  => isset( $image[1] ) ? absint( $image[1] ) : 0,
        'height' => isset( $image[2] ) ? absint( $image[2] ) : 0,
        'alt'    => trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ),
        'type'   => (string) get_post_mime_type( $image_id ),
    ];
}

/**
 * Return the hero image URL for an enabled editorial category hub.
 *
 * @return string
 */
function loraleya_get_category_hub_social_image_url() {
    $image = loraleya_get_category_hub_social_image_data();
    return ! empty( $image['url'] ) ? $image['url'] : '';
}

/**
 * A category hub is a collection/landing page, not an article.
 * Keep articles, products and ordinary archives on Rank Math defaults.
 */
add_filter( 'rank_math/opengraph/type', function( $type ) {
    return loraleya_is_enabled_category_hub_social_context() ? 'website' : $type;
}, 20 );

/**
 * Use the same hub hero image for Facebook Open Graph and Twitter.
 */
$loraleya_hub_social_image = function( $attachment_url ) {
    $hub_image_url = loraleya_get_category_hub_social_image_url();
    return '' !== $hub_image_url ? $hub_image_url : $attachment_url;
};

add_filter( 'rank_math/opengraph/facebook/image', $loraleya_hub_social_image, 20 );
add_filter( 'rank_math/opengraph/twitter/image', $loraleya_hub_social_image, 20 );

/**
 * Preserve the WordPress attachment metadata after overriding the image URL.
 * Without this, Rank Math can output the URL but loses dimensions, MIME and alt.
 */
$loraleya_hub_social_image_array = function( $attachment ) {
    $hub_image = loraleya_get_category_hub_social_image_data();
    return ! empty( $hub_image ) ? $hub_image : $attachment;
};

add_filter( 'rank_math/opengraph/facebook/image_array', $loraleya_hub_social_image_array, 20 );
add_filter( 'rank_math/opengraph/twitter/image_array', $loraleya_hub_social_image_array, 20 );

/**
 * The hub hero is a wide 16:9 image, so use the large Twitter card.
 */
add_filter( 'rank_math/opengraph/twitter/card_type', function( $type ) {
    return '' !== loraleya_get_category_hub_social_image_url() ? 'summary_large_image' : $type;
}, 20 );
