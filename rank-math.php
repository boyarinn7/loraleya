<?php
/**
 * LoraLeya — интеграция с Rank Math SEO.
 *
 * Подключается из functions.php только при активном Rank Math
 * (class_exists('RankMath')).
 * Передаёт seo_title и seo_description из meta-полей темы
 * в pipeline Rank Math: title, description, OG Facebook, Twitter.
 *
 * Что НЕ затрагивает этот файл:
 * - JSON-LD FAQPage schema (functions.php)
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
