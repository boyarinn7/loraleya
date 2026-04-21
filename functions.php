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

    // Constructor script
    wp_enqueue_script('loraleya-constructor', get_template_directory_uri() . '/assets/js/constructor.js', [], '1.0', true);

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

// ===== FABRIC COLOR HEX FIELD =====
// Поле при ДОБАВЛЕНИИ термина
add_action('fabric_color_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label for="color_hex">HEX цвета</label>
        <input type="text" name="color_hex" id="color_hex" value="" placeholder="#6a3a7a">
        <p>Введите HEX-код цвета (например #6a3a7a)</p>
    </div>
    <?php
});

// Поле при РЕДАКТИРОВАНИИ термина
add_action('fabric_color_edit_form_fields', function($term) {
    $hex = get_term_meta($term->term_id, 'color_hex', true);
    ?>
    <tr class="form-field">
        <th><label for="color_hex">HEX цвета</label></th>
        <td>
            <input type="text" name="color_hex" id="color_hex" value="<?php echo esc_attr($hex); ?>" placeholder="#6a3a7a">
            <p class="description">Введите HEX-код цвета</p>
        </td>
    </tr>
    <?php
});

// Сохранение при создании
add_action('created_fabric_color', function($term_id) {
    if (isset($_POST['color_hex'])) {
        update_term_meta($term_id, 'color_hex', sanitize_text_field($_POST['color_hex']));
    }
});

// Сохранение при редактировании
add_action('edited_fabric_color', function($term_id) {
    if (isset($_POST['color_hex'])) {
        update_term_meta($term_id, 'color_hex', sanitize_text_field($_POST['color_hex']));
    }
});

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

/**
 * Регистрируем fabric_color в WC как атрибут вариаций.
 * Позволяет использовать существующую таксономию для variable products.
 */
add_filter('woocommerce_attribute_taxonomies', function ($taxonomies) {
    $custom = (object) [
        'attribute_id'      => 0,
        'attribute_name'    => 'fabric_color',
        'attribute_label'   => 'Цвет ткани',
        'attribute_type'    => 'select',
        'attribute_orderby' => 'menu_order',
        'attribute_public'  => 1,
    ];
    $taxonomies[] = $custom;
    return $taxonomies;
});

// ===== AJAX ADD TO CART =====
function loraleya_ajax_add_to_cart() {
    check_ajax_referer('loraleya_nonce', 'nonce');

    $product_id   = intval($_POST['product_id'] ?? 0);
    $variation_id = intval($_POST['variation_id'] ?? 0);
    $quantity     = intval($_POST['quantity'] ?? 1);
    if ($quantity < 1) $quantity = 1;

    // Атрибуты вариации (если передаются)
    $variation = [];
    if (!empty($_POST['variation']) && is_array($_POST['variation'])) {
        foreach ($_POST['variation'] as $key => $val) {
            $variation[sanitize_text_field($key)] = sanitize_text_field($val);
        }
    }

    if ($product_id <= 0) {
        wp_send_json_error('Не указан товар');
    }

    $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);

    if ($added) {
        wp_send_json_success([
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_key'   => $added,
        ]);
    }

    wp_send_json_error('Не удалось добавить товар');
}
add_action('wp_ajax_loraleya_add_to_cart', 'loraleya_ajax_add_to_cart');
add_action('wp_ajax_nopriv_loraleya_add_to_cart', 'loraleya_ajax_add_to_cart');

/**
 * Обновить количество товара в корзине
 */
function loraleya_ajax_update_cart_item() {
    check_ajax_referer('loraleya_nonce', 'nonce');

    $cart_key = sanitize_text_field($_POST['cart_key'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);

    if (empty($cart_key)) {
        wp_send_json_error('Не указан ключ товара');
    }

    if ($quantity <= 0) {
        WC()->cart->remove_cart_item($cart_key);
    } else {
        WC()->cart->set_quantity($cart_key, $quantity, true);
    }

    wp_send_json_success([
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
    ]);
}
add_action('wp_ajax_loraleya_update_cart_item', 'loraleya_ajax_update_cart_item');
add_action('wp_ajax_nopriv_loraleya_update_cart_item', 'loraleya_ajax_update_cart_item');

/**
 * Получить содержимое корзины (для модалки)
 */
function loraleya_ajax_get_cart() {
    check_ajax_referer('loraleya_nonce', 'nonce');

    $items = [];
    foreach (WC()->cart->get_cart() as $cart_key => $cart_item) {
        $product = $cart_item['data'];
        $items[] = [
            'cart_key'     => $cart_key,
            'product_id'   => $cart_item['product_id'],
            'variation_id' => $cart_item['variation_id'],
            'name'         => $product->get_name(),
            'price'        => wc_price($product->get_price()),
            'price_raw'    => $product->get_price(),
            'quantity'     => $cart_item['quantity'],
            'subtotal'     => wc_price($product->get_price() * $cart_item['quantity']),
            'image'        => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'variation'    => $cart_item['variation'] ?? [],
        ];
    }

    wp_send_json_success([
        'items'      => $items,
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
    ]);
}
add_action('wp_ajax_loraleya_get_cart', 'loraleya_ajax_get_cart');
add_action('wp_ajax_nopriv_loraleya_get_cart', 'loraleya_ajax_get_cart');

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
