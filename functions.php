<?php
/**
 * LoraLeya Theme Functions
 */

// ===== THEME SETUP =====
function loraleya_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'primary' => 'Основное меню',
        'footer'  => 'Меню футера',
    ]);

    // Image sizes
    add_image_size('hero', 1920, 1080, true);
    add_image_size('gallery', 800, 600, true);
    add_image_size('product-card', 600, 450, true);
    add_image_size('swatch', 200, 200, true);
    add_image_size('macro', 400, 300, true);
}
add_action('after_setup_theme', 'loraleya_setup');

// ===== ENQUEUE STYLES & SCRIPTS =====
function loraleya_scripts() {
    // Google Fonts
    wp_enqueue_style(
        'loraleya-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Raleway:wght@300;400;500;600&display=swap',
        [],
        null
    );

    // Main stylesheet
    wp_enqueue_style('loraleya-style', get_stylesheet_uri(), ['loraleya-fonts'], '1.0.0');

    // Main script
    wp_enqueue_script('loraleya-main', get_template_directory_uri() . '/assets/js/main.js', [], '1.0.0', true);

    // Pass data to JS
    wp_localize_script('loraleya-main', 'loraleya', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('loraleya_nonce'),
        'cart_url' => wc_get_cart_url(),
    ]);
}
add_action('wp_enqueue_scripts', 'loraleya_scripts');

// ===== WOOCOMMERCE ADJUSTMENTS =====
// Remove default WooCommerce styles
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Change products per page
add_filter('loop_shop_per_page', function() { return 20; });

// Remove sidebar from WooCommerce pages
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// ===== CUSTOM POST TYPES =====
function loraleya_register_post_types() {
    // Scenarios (Сценарии)
    register_post_type('scenario', [
        'labels' => [
            'name'          => 'Сценарии',
            'singular_name' => 'Сценарий',
            'add_new'       => 'Добавить сценарий',
            'add_new_item'  => 'Новый сценарий',
            'edit_item'     => 'Редактировать сценарий',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'scenarios'],
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'],
        'menu_icon'    => 'dashicons-food',
        'show_in_rest' => true,
    ]);
}
add_action('init', 'loraleya_register_post_types');

// ===== CUSTOM TAXONOMY: Colors =====
function loraleya_register_taxonomies() {
    register_taxonomy('fabric_color', ['product'], [
        'labels' => [
            'name'          => 'Цвета ткани',
            'singular_name' => 'Цвет ткани',
            'add_new_item'  => 'Добавить цвет',
        ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'color'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'loraleya_register_taxonomies');

// ===== AJAX ADD TO CART =====
function loraleya_ajax_add_to_cart() {
    check_ajax_referer('loraleya_nonce', 'nonce');

    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']) ?: 1;

    if ($product_id > 0) {
        $added = WC()->cart->add_to_cart($product_id, $quantity);
        if ($added) {
            wp_send_json_success([
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total(),
            ]);
        }
    }

    wp_send_json_error('Не удалось добавить товар');
}
add_action('wp_ajax_loraleya_add_to_cart', 'loraleya_ajax_add_to_cart');
add_action('wp_ajax_nopriv_loraleya_add_to_cart', 'loraleya_ajax_add_to_cart');

// ===== CART COUNT FRAGMENT =====
function loraleya_cart_count_fragment($fragments) {
    $fragments['.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'loraleya_cart_count_fragment');

// ===== DISABLE GUTENBERG FOR CUSTOM PAGES =====
function loraleya_disable_gutenberg($use, $post_type) {
    if (in_array($post_type, ['scenario'])) {
        return false;
    }
    return $use;
}
add_filter('use_block_editor_for_post_type', 'loraleya_disable_gutenberg', 10, 2);

// ===== EXCERPT LENGTH =====
function loraleya_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'loraleya_excerpt_length');

// ===== REMOVE WORDPRESS EMOJI =====
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// ===== REMOVE WORDPRESS VERSION =====
remove_action('wp_head', 'wp_generator');

// ===== BODY CLASS =====
function loraleya_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'is-front';
    }
    if (is_singular('scenario')) {
        $classes[] = 'is-scenario';
    }
    return $classes;
}
add_filter('body_class', 'loraleya_body_classes');
