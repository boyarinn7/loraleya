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
    add_image_size('scenario-card', 600, 800, true);
    add_image_size('product-card', 600, 450, true);
    add_image_size('swatch', 200, 200, true);
    add_image_size('macro', 400, 300, true);
}
add_action('after_setup_theme', 'loraleya_setup');

// ===== ENQUEUE STYLES & SCRIPTS =====
function loraleya_scripts() {
    // Self-hosted fonts (бывш. Google Fonts — убраны для устранения render-blocking)
    $fonts_css = get_template_directory() . '/assets/css/fonts.css';
    wp_enqueue_style(
        'loraleya-fonts',
        get_template_directory_uri() . '/assets/css/fonts.css',
        [],
        file_exists($fonts_css) ? filemtime($fonts_css) : '1.0.0'
    );

    // Main stylesheet
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'loraleya-style',
        get_stylesheet_uri(),
        ['loraleya-fonts'],
        file_exists($style_path) ? filemtime($style_path) : '1.0.0'
    );

    // Main script
    $main_js = get_template_directory() . '/assets/js/main.js';
    wp_enqueue_script('loraleya-main', get_template_directory_uri() . '/assets/js/main.js', [], file_exists($main_js) ? filemtime($main_js) : '1.0.0', true);

    // Конструктор и лайтбокс галереи — только на странице сценария
    if (is_singular('scenario')) {
        $constructor_js = get_template_directory() . '/assets/js/constructor.js';
        wp_enqueue_script('loraleya-constructor', get_template_directory_uri() . '/assets/js/constructor.js', [], file_exists($constructor_js) ? filemtime($constructor_js) : '1.0', true);

        $scenario_js = get_stylesheet_directory() . '/assets/js/ll-scenario.js';
        wp_enqueue_script('ll-scenario', get_stylesheet_directory_uri() . '/assets/js/ll-scenario.js', [], file_exists($scenario_js) ? filemtime($scenario_js) : '1.0.0', true);
    }

    // Pass data to JS
    wp_localize_script('loraleya-main', 'loraleya', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('loraleya_nonce'),
        'cart_url' => wc_get_cart_url(),
        'shop_url' => wc_get_page_permalink('shop'),
    ]);

    // Передать item-map для страницы цвета
    if (is_tax('pa_fabric_color')) {
        $color_term = get_queried_object();
        if ($color_term && !is_wp_error($color_term)) {
            wp_localize_script('loraleya-main', 'LORALEYA_ITEM_MAP', loraleya_build_item_map($color_term->slug));
        }
    }

    // Передать item-map для всех 17 цветов на страницу сценария
    if (is_singular('scenario')) {
        $all_color_slugs = [
            'bezhevyj','belyj','biryuza','blek-zoloto','bronza','goluboj','grafit',
            'zelenyj','melanzh-zoloto','melanzh-serebro','melanzh-seryj','melanzh-chernyj',
            'platina','serebro','sirenevyj','temno-biryuzovyj','fioletovyj',
        ];
        $map_by_color    = [];
        $prices_by_color = [];
        foreach ($all_color_slugs as $cs) {
            $map_by_color[$cs]    = loraleya_build_item_map($cs);
            $prices_by_color[$cs] = loraleya_get_item_prices($cs);
        }
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_MAP_BY_COLOR',    $map_by_color);
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES_BY_COLOR', $prices_by_color);
        // Совместимость: бирюза как дефолт (старый код, ещё не перешедший на BY_COLOR)
        wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES', loraleya_get_item_prices('biryuza'));
    }

    // Передать цены на страницу цвета
    if (is_tax('pa_fabric_color')) {
        $color_term = get_queried_object();
        if ($color_term && !is_wp_error($color_term)) {
            wp_localize_script('loraleya-main', 'LORALEYA_ITEM_PRICES', loraleya_get_item_prices($color_term->slug));
        }
    }

    // Custom order page script
    if (is_page('individualnyy-zakaz')) {
        $custom_order_js = get_template_directory() . '/assets/js/custom-order.js';
        wp_enqueue_script(
            'loraleya-custom-order',
            get_template_directory_uri() . '/assets/js/custom-order.js',
            [],
            file_exists($custom_order_js) ? filemtime($custom_order_js) : '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'loraleya_scripts');

add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        $path = get_stylesheet_directory() . '/assets/css/checkout.css';
        $uri  = get_stylesheet_directory_uri() . '/assets/css/checkout.css';
        if ( file_exists( $path ) ) {
            wp_enqueue_style(
                'loraleya-checkout',
                $uri,
                array( 'loraleya-style' ),
                filemtime( $path )
            );
        }
    }
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_account_page' ) && is_account_page() ) {
        $path = get_stylesheet_directory() . '/assets/css/account.css';
        $uri  = get_stylesheet_directory_uri() . '/assets/css/account.css';
        if ( file_exists( $path ) ) {
            wp_enqueue_style( 'loraleya-account', $uri, array( 'loraleya-style' ), filemtime( $path ) );
        }
    }
}, 20 );

// ===== WOOCOMMERCE ADJUSTMENTS =====

add_action( 'woocommerce_cart_totals_before_order_total', 'loraleya_cart_shipping_info' );
function loraleya_cart_shipping_info() {
    echo '<tr class="ll-cart-shipping-info"><td colspan="2">'
        . loraleya_cart_shipping_info_html()
        . '</td></tr>';
}

function loraleya_cart_shipping_info_html() {
    return 'Доставка <strong>5Post</strong> по Москве и Московской области <strong>БЕСПЛАТНО</strong>, другие регионы <strong>250 руб</strong>.';
}

add_filter( 'render_block_woocommerce/cart', 'loraleya_cart_block_shipping_info', 10, 2 );
function loraleya_cart_block_shipping_info( $block_content, $block ) {
    if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
        return $block_content;
    }

    $shipping_block = '~<div\b(?=[^>]*class=["\'][^"\']*\bwp-block-woocommerce-cart-order-summary-shipping-block\b)[^>]*>\s*</div>~i';
    $shipping_info  = '<div class="wc-block-components-totals-wrapper ll-cart-shipping-info">'
        . '<div class="wc-block-components-totals-item"><span class="wc-block-components-totals-item__label">'
        . loraleya_cart_shipping_info_html()
        . '</span></div></div>';

    return preg_replace( $shipping_block, $shipping_info, $block_content, 1 );
}

add_filter( 'woocommerce_no_shipping_available_html', 'loraleya_no_shipping_text' );
add_filter( 'woocommerce_cart_no_shipping_available_html', 'loraleya_no_shipping_text' );
function loraleya_no_shipping_text( $html ) {
    return '<p>В этом населённом пункте пока нет пунктов выдачи 5Post. Попробуйте ближайший крупный город или напишите нам на loraleya-tex@yandex.ru — подберём доставку.</p>';
}
/**
 * Определяет, относится ли адрес доставки к Москве или Московской области.
 * В российской форме WooCommerce поле региона является свободным текстом,
 * поэтому проверяем регион, город и адрес с учётом русских и латинских вариантов.
 */
function loraleya_is_moscow_shipping_destination( $destination ) {
    $parts = array(
        $destination['state']     ?? '',
        $destination['city']      ?? '',
        $destination['address']   ?? '',
        $destination['address_1'] ?? '',
        $destination['address_2'] ?? '',
    );

    $location = implode( ' ', array_filter( array_map( 'strval', $parts ) ) );
    $location = function_exists( 'mb_strtolower' )
        ? mb_strtolower( $location, 'UTF-8' )
        : strtolower( $location );
    $location = str_replace( 'ё', 'е', $location );

    return 1 === preg_match( '/(?:москв|московск|moscow|moskva|moskovsk)/u', $location );
}

/**
 * Фиксированная стоимость 5Post:
 * Москва и Московская область — бесплатно, остальные регионы — 250 ₽.
 * Сам плагин 5Post продолжает подбирать доступные пункты и сроки доставки.
 */
add_filter( 'woocommerce_package_rates', 'loraleya_fixed_fivepost_shipping_rate', 100, 2 );
function loraleya_fixed_fivepost_shipping_rate( $rates, $package ) {
    if ( ! is_array( $rates ) ) {
        return $rates;
    }

    $destination = isset( $package['destination'] ) && is_array( $package['destination'] )
        ? $package['destination']
        : array();
    $is_moscow = loraleya_is_moscow_shipping_destination( $destination );
    $cost      = $is_moscow ? 0 : 250;

    foreach ( $rates as $rate ) {
        if ( ! $rate instanceof WC_Shipping_Rate || 'fivepost_shipping_method' !== $rate->get_method_id() ) {
            continue;
        }

        $rate->set_cost( $cost );
        $rate->set_taxes( array() );
        $rate->set_label( $is_moscow ? '5Post — бесплатно' : '5Post' );
    }

    return $rates;
}

/**
 * Краткое пояснение правил рядом с итогом доставки на странице заказа.
 */
add_action( 'woocommerce_review_order_after_shipping', 'loraleya_checkout_shipping_rules' );
function loraleya_checkout_shipping_rules() {
    echo '<tr class="ll-shipping-rules"><td colspan="2">'
        . '<small><strong>5Post:</strong> Москва и Московская область — бесплатно; другие регионы России — 250 ₽. '
        . 'СДЭК и Яндекс Доставка — стоимость рассчитывается индивидуально менеджером.</small>'
        . '</td></tr>';
}


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
        'rewrite'      => ['slug' => 'scenarios', 'with_front' => false],
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes'],
        'menu_icon'    => 'dashicons-food',
        'show_in_rest' => true,
    ]);
}
add_action('init', 'loraleya_register_post_types');

// ===== FABRIC COLOR HEX FIELD =====
// Поле при ДОБАВЛЕНИИ термина
add_action('pa_fabric_color_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label for="color_hex">HEX цвета</label>
        <input type="text" name="color_hex" id="color_hex" value="" placeholder="#6a3a7a">
        <p>Введите HEX-код цвета (например #6a3a7a)</p>
    </div>
    <?php
});

// Поле при РЕДАКТИРОВАНИИ термина
add_action('pa_fabric_color_edit_form_fields', function($term) {
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
add_action('created_pa_fabric_color', function($term_id) {
    if (isset($_POST['color_hex'])) {
        update_term_meta($term_id, 'color_hex', sanitize_text_field($_POST['color_hex']));
    }
});

// Сохранение при редактировании
add_action('edited_pa_fabric_color', function($term_id) {
    if (isset($_POST['color_hex'])) {
        update_term_meta($term_id, 'color_hex', sanitize_text_field($_POST['color_hex']));
    }
});

// === SEO-поля для цветовых страниц (Sprint 1, ТЗ E3) ===

add_action('pa_fabric_color_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label for="seo_title">SEO Title</label>
        <input type="text" name="seo_title" id="seo_title" value="" placeholder="Бежевая жаккардовая скатерть... | LoraLeya">
        <p>50-65 символов. Заполняется по контенту из block-b1/b2/c.</p>
    </div>
    <div class="form-field">
        <label for="seo_description">SEO Description</label>
        <textarea name="seo_description" id="seo_description" rows="2"></textarea>
        <p>120-160 символов.</p>
    </div>
    <div class="form-field">
        <label for="seo_text">SEO Text (HTML)</label>
        <textarea name="seo_text" id="seo_text" rows="10"></textarea>
        <p>Расширенный текст с HTML-разметкой. 400-2000 знаков.</p>
    </div>
    <div class="form-field">
        <label for="seo_faq">SEO FAQ (JSON)</label>
        <textarea name="seo_faq" id="seo_faq" rows="8" placeholder='[{"question": "...", "answer": "..."}]'></textarea>
        <p>JSON-массив объектов {question, answer}. Если пусто — используется общий fallback из 3 вопросов.</p>
    </div>
    <?php
});

add_action('pa_fabric_color_edit_form_fields', function($term) {
    $seo_title       = get_term_meta($term->term_id, 'seo_title', true);
    $seo_description = get_term_meta($term->term_id, 'seo_description', true);
    $seo_text        = get_term_meta($term->term_id, 'seo_text', true);
    $seo_faq         = get_term_meta($term->term_id, 'seo_faq', true);
    ?>
    <tr class="form-field">
        <th><label for="seo_title">SEO Title</label></th>
        <td>
            <input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>">
            <p class="description">50-65 символов.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_description">SEO Description</label></th>
        <td>
            <textarea name="seo_description" id="seo_description" rows="2" cols="50"><?php echo esc_textarea($seo_description); ?></textarea>
            <p class="description">120-160 символов.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_text">SEO Text (HTML)</label></th>
        <td>
            <textarea name="seo_text" id="seo_text" rows="15" cols="50" class="large-text"><?php echo esc_textarea($seo_text); ?></textarea>
            <p class="description">Расширенный текст с HTML-разметкой. Допустимы теги h2, h3, p, ul, li, a, strong.</p>
        </td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_faq">SEO FAQ (JSON)</label></th>
        <td>
            <textarea name="seo_faq" id="seo_faq" rows="10" cols="50" class="large-text"><?php echo esc_textarea($seo_faq); ?></textarea>
            <p class="description">JSON-массив объектов {question, answer}. Если пусто — общий fallback.</p>
        </td>
    </tr>
    <?php
});

add_action('created_pa_fabric_color', function($term_id) {
    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_term_meta($term_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_text'])) {
        update_term_meta($term_id, 'seo_text', wp_kses_post($_POST['seo_text']));
    }
    if (isset($_POST['seo_faq'])) {
        update_term_meta($term_id, 'seo_faq', wp_unslash($_POST['seo_faq']));
    }
});

add_action('edited_pa_fabric_color', function($term_id) {
    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_term_meta($term_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_text'])) {
        update_term_meta($term_id, 'seo_text', wp_kses_post($_POST['seo_text']));
    }
    if (isset($_POST['seo_faq'])) {
        update_term_meta($term_id, 'seo_faq', wp_unslash($_POST['seo_faq']));
    }
});


/**
 * Переопределяем rewrite slug для pa_fabric_color: оставляем /color/{slug}/.
 *
 * Без этого фильтра WC подставит дефолтный slug от имени атрибута,
 * и URL страницы цвета превратится в /pa_fabric_color/biryuza/ или аналогичный,
 * что ломает внешние ссылки и внутреннюю навигацию (хлебные крошки, блок палитры).
 */
add_filter('woocommerce_taxonomy_args_pa_fabric_color', function ($args) {
    $args['rewrite'] = [
        'slug'         => 'color',
        'with_front'   => false,
        'hierarchical' => false,
    ];
    $args['hierarchical'] = true;
    $args['show_in_rest'] = true;
    return $args;
});

// ===== HELPERS =====

/**
 * Построить request-level индекс вариаций родительского товара.
 *
 * @param int $product_id ID родительского вариативного товара
 * @return array
 */
function loraleya_get_variation_index($product_id) {
    static $indexes = [];

    $product_id = (int) $product_id;
    if (array_key_exists($product_id, $indexes)) {
        return $indexes[$product_id];
    }

    $indexes[$product_id] = [
        'by_color'           => [],
        'by_color_and_size'  => [],
        'variation_products' => [],
    ];

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) {
        return $indexes[$product_id];
    }

    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) continue;

        $indexes[$product_id]['variation_products'][$variation_id] = $variation;
        $attrs = $variation->get_variation_attributes();
        $color_slug = $attrs['attribute_pa_fabric_color'] ?? '';

        if (!array_key_exists($color_slug, $indexes[$product_id]['by_color'])) {
            $indexes[$product_id]['by_color'][$color_slug] = $variation_id;
        }

        foreach ($attrs as $attr_key => $attr_value) {
            if (strpos($attr_key, 'attribute_pa_') !== 0 || $attr_key === 'attribute_pa_fabric_color') {
                continue;
            }

            $size_taxonomy = substr($attr_key, strlen('attribute_pa_'));
            $size_slug = urldecode($attr_value);
            if (!isset($indexes[$product_id]['by_color_and_size'][$color_slug][$size_taxonomy])
                || !array_key_exists($size_slug, $indexes[$product_id]['by_color_and_size'][$color_slug][$size_taxonomy])) {
                $indexes[$product_id]['by_color_and_size'][$color_slug][$size_taxonomy][$size_slug] = $variation_id;
            }
        }
    }

    return $indexes[$product_id];
}

/**
 * Найти variation_id по комбинации цвета и размера.
 *
 * @param int    $product_id     ID родительского вариативного товара
 * @param string $color_slug     slug термина pa_fabric_color (например 'biryuza')
 * @param string $size_slug      slug термина размера (например '140' или '4p-140')
 * @param string $size_taxonomy  имя таксономии размера БЕЗ префикса pa_ (razmer-dorozhki/razmer-skaterti/razmer-nabora)
 * @return int variation_id или 0 если не найдено
 */
function loraleya_find_variation_id($product_id, $color_slug, $size_slug, $size_taxonomy) {
    $index = loraleya_get_variation_index($product_id);
    $has_size = ($size_slug !== null && $size_taxonomy !== null);

    // Для товара только с цветом сохраняем прежнее поведение: первая подходящая вариация.
    if (!$has_size) {
        return $index['by_color'][$color_slug] ?? 0;
    }

    return $index['by_color_and_size'][$color_slug][$size_taxonomy][$size_slug] ?? 0;
}

/**
 * Карта data-item → WC сущности для страницы цвета.
 * Используется в taxonomy-pa_fabric_color.php при рендере + локализуется в JS.
 *
 * @param string $color_slug текущий цвет (slug термина pa_fabric_color)
 * @return array data-item => ['product_id' => int, 'variation_id' => int, 'attrs' => array]
 */
function loraleya_build_item_map($color_slug) {
    static $maps = [];

    if (array_key_exists($color_slug, $maps)) {
        return $maps[$color_slug];
    }

    $items = [
        // Дорожки (variable, product_id = 39)
        'Дорожка 140'   => [39, '140',    'razmer-dorozhki'],
        'Дорожка 175'   => [39, '175',    'razmer-dorozhki'],
        'Дорожка 240'   => [39, '240',    'razmer-dorozhki'],
        'Дорожка 300'   => [39, '300',    'razmer-dorozhki'],
        // Скатерти (variable, product_id = 44)
        'Скатерть 175'  => [44, '175',    'razmer-skaterti'],
        'Скатерть 220'  => [44, '220',    'razmer-skaterti'],
        'Скатерть 240'  => [44, '240',    'razmer-skaterti'],
        // Салфетка и Куверт (variable, только по цвету — без размера)
        'Салфетка'      => [48, null,     null],
        'Куверт'        => [49, null,     null],
        // Готовые наборы (variable, product_id = 50)
        'Набор 2п/140'  => [50, '2п-140', 'razmer-nabora'],
        'Набор 4п/140'  => [50, '4п-140', 'razmer-nabora'],
        'Набор 4п/175'  => [50, '4п-175', 'razmer-nabora'],
        'Набор 6п/240'  => [50, '6п-240', 'razmer-nabora'],
        'Набор 6п/300'  => [50, '6п-300', 'razmer-nabora'],
    ];

    $map = [];
    foreach ($items as $data_item => [$product_id, $size_slug, $size_taxonomy]) {
        $variation_id = loraleya_find_variation_id($product_id, $color_slug, $size_slug, $size_taxonomy);

        $attrs = [
            'attribute_pa_fabric_color' => $color_slug,
        ];
        if ($size_slug !== null && $size_taxonomy !== null) {
            $attrs['attribute_pa_' . $size_taxonomy] = $size_slug;
        }

        $map[$data_item] = [
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'attrs'        => $attrs,
        ];
    }
    $maps[$color_slug] = $map;
    return $maps[$color_slug];
}

/**
 * Читает реальные цены из WooCommerce для всех item-ключей.
 * Цены одинаковы для всех цветов, поэтому передаём любой валидный $color_slug.
 *
 * @param string $color_slug slug цвета (для получения variation_id через build_item_map)
 * @return array data-item => ['price' => float, 'old_price' => float|null]
 */
function loraleya_get_item_prices($color_slug) {
    static $prices_by_color = [];

    if (array_key_exists($color_slug, $prices_by_color)) {
        return $prices_by_color[$color_slug];
    }

    $map = loraleya_build_item_map($color_slug);
    $prices = [];

    foreach ($map as $item_key => $entry) {
        $variation_id = $entry['variation_id'];
        $price = null;
        $old_price = null;

        if ($variation_id) {
            $variation_index = loraleya_get_variation_index($entry['product_id']);
            $variation = $variation_index['variation_products'][$variation_id] ?? wc_get_product($variation_id);
            if ($variation) {
                $price     = (float) $variation->get_price();
                $sale      = $variation->get_sale_price();
                $regular   = $variation->get_regular_price();
                $old_price = ($sale !== '' && $sale !== null && (float)$regular > (float)$sale)
                    ? (float) $regular
                    : null;
            }
        }

        $prices[$item_key] = [
            'price'     => $price,
            'old_price' => $old_price,
        ];
    }

    $prices_by_color[$color_slug] = $prices;
    return $prices_by_color[$color_slug];
}

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
            // Декодируем slug если он пришёл URL-encoded (4%d0%bf-140 → 4п-140)
            $variation[sanitize_text_field($key)] = sanitize_text_field(urldecode($val));
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

        // Собрать человеческие названия атрибутов вариации
        $variation_labels = [];
        if (!empty($cart_item['variation']) && is_array($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $attr_key => $attr_value) {
                // attr_key вида 'attribute_pa_fabric_color' → таксономия 'pa_fabric_color'
                $taxonomy = str_replace('attribute_', '', $attr_key);
                // Декодируем slug если он URL-encoded ('4%d0%bf-140' → '4п-140')
                $term_slug = urldecode($attr_value);
                $term = get_term_by('slug', $term_slug, $taxonomy);
                $raw_label = $term ? $term->name : $attr_value;
                $variation_labels[] = function_exists('ll_decode_size_code')
                    ? ll_decode_size_code($raw_label)
                    : $raw_label;
            }
        }

        $items[] = [
            'cart_key'         => $cart_key,
            'product_id'       => $cart_item['product_id'],
            'variation_id'     => $cart_item['variation_id'],
            'permalink'        => get_permalink($cart_item['product_id']),
            'name'             => $product->get_name(),
            'price'            => wc_price($product->get_price()),
            'price_raw'        => $product->get_price(),
            'quantity'         => $cart_item['quantity'],
            'subtotal'         => wc_price($product->get_price() * $cart_item['quantity']),
            'image'            => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'variation'        => $cart_item['variation'] ?? [],
            'variation_labels' => $variation_labels,
        ];
    }

    // Стабильная сортировка: product_id → variation_id → cart_key
    usort($items, function($a, $b) {
        if ($a['product_id'] !== $b['product_id']) {
            return $a['product_id'] - $b['product_id'];
        }
        if ($a['variation_id'] !== $b['variation_id']) {
            return $a['variation_id'] - $b['variation_id'];
        }
        return strcmp($a['cart_key'], $b['cart_key']);
    });

    wp_send_json_success([
        'items'      => $items,
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
    ]);
}
add_action('wp_ajax_loraleya_get_cart', 'loraleya_ajax_get_cart');
add_action('wp_ajax_nopriv_loraleya_get_cart', 'loraleya_ajax_get_cart');

/**
 * Полностью очистить корзину (вызывается из модалки по клику на иконку мусорки).
 */
function loraleya_ajax_clear_cart() {
    check_ajax_referer('loraleya_nonce', 'nonce');

    WC()->cart->empty_cart();

    wp_send_json_success([
        'cart_count' => 0,
        'cart_total' => WC()->cart->get_cart_total(),
    ]);
}
add_action('wp_ajax_loraleya_clear_cart', 'loraleya_ajax_clear_cart');
add_action('wp_ajax_nopriv_loraleya_clear_cart', 'loraleya_ajax_clear_cart');

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

// ===== 301 REDIRECTS =====
add_action('template_redirect', function() {
    $redirects = [
        '/blog/kak-vybrat-skatert-hlopok-ili-poliester/' => '/blog/kak-vybrat-skatert/',
    ];
    $request = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') . '/';
    if (isset($redirects[$request])) {
        wp_redirect(home_url($redirects[$request]), 301);
        exit;
    }
});

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

function loraleya_color_swatch_url($slug, $size = 'thumbnail') {
    static $cache = [];
    $ck = $slug . '|' . $size;
    if (isset($cache[$ck])) {
        return $cache[$ck];
    }

    $prefix_map = [
        'belyj'             => 'beliy',
        'bezhevyj'          => 'bezheviy',
        'biryuza'           => 'biruza',
        'blek-zoloto'       => 'blek-zoloto',
        'bronza'            => 'bronza',
        'goluboj'           => 'goluboy',
        'grafit'            => 'grafit',
        'zelenyj'           => 'zeleniy',
        'melanzh-zoloto'    => 'melanzh-zoloto',
        'melanzh-serebro'   => 'melanzh-serebro',
        'melanzh-seryj'     => 'melanzh-seriy',
        'melanzh-chernyj'   => 'melanzh-cherniy',
        'platina'           => 'platina',
        'serebro'           => 'serebro',
        'sirenevyj'         => 'sireneviy',
        'temno-biryuzovyj'  => 'temno-biruza',
        'fioletovyj'        => 'fioletoviy',
    ];

    $prefix = $prefix_map[$slug] ?? $slug;
    $search_title = $prefix . '-macro-faktura';

    $attachment = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'numberposts'    => 1,
        'title'          => $search_title,
    ]);

    if (!empty($attachment)) {
        $url = wp_get_attachment_image_url($attachment[0]->ID, $size);
        $cache[$ck] = $url ?: '';
        return $cache[$ck];
    }

    $cache[$ck] = '';
    return '';
}

/**
 * Возвращает дефолтные цвет и количество персон для конкретного сценария.
 *
 * @param string $scenario_slug Slug сценария (post_name CPT scenario)
 * @return array ['color' => slug_цвета, 'persons' => число_персон]
 */
function loraleya_get_scenario_defaults($scenario_slug) {
    $defaults = [
        'romanticheskij-uzhin' => ['color' => 'fioletovyj',      'persons' => 4, 'hero_nabor' => 'nabor-2-140'],
        'semejnyj-obed'        => ['color' => 'zelenyj',         'persons' => 4, 'hero_nabor' => 'nabor-6-240'],
        'prazdnichnyj-stol'    => ['color' => 'melanzh-zoloto',  'persons' => 6, 'hero_nabor' => 'nabor-6-300'],
        'kazhdyj-den'          => ['color' => 'melanzh-serebro', 'persons' => 4, 'hero_nabor' => 'nabor-4-175'],
        'den-rozhdenija'       => ['color' => 'biryuza',         'persons' => 6, 'hero_nabor' => 'nabor-6-300'],
    ];

    $fallback = ['color' => 'bezhevyj', 'persons' => 4, 'hero_nabor' => 'nabor-4-175'];

    return $defaults[$scenario_slug] ?? $fallback;
}

/* =============================================
   CUSTOM ORDER FORM HANDLER
   ============================================= */

function loraleya_normalize_custom_order_phone($value) {
    $phone = trim((string) $value);
    if ($phone === '' || mb_strlen($phone) > 100 || !preg_match('/^\+?[\d\s().\-–—]+$/u', $phone)) {
        return '';
    }

    $digits = preg_replace('/\D+/u', '', $phone);
    if (strpos($phone, '+') === 0) {
        return preg_match('/^7\d{10}$/', $digits) ? '+' . $digits : '';
    }
    if (preg_match('/^8\d{10}$/', $digits)) {
        return '+7' . substr($digits, 1);
    }
    return preg_match('/^7\d{10}$/', $digits) ? '+' . $digits : '';
}

function loraleya_send_custom_order_email($subject, $body) {
    $email_to = defined('LORALEYA_NOTIFY_EMAIL') ? LORALEYA_NOTIFY_EMAIL : 'loraleya-tex@yandex.ru';
    $headers  = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: LoraLeya <noreply@loraleya.ru>',
    ];
    $sent = wp_mail($email_to, $subject, $body, $headers);
    if (!$sent) {
        error_log('[LoraLeya] Custom order wp_mail failed for ' . $email_to);
    }

    return $sent;
}

function loraleya_handle_custom_order() {

    // 1. Honeypot: если заполнен — это бот, тихо завершаем как успех
    if (!empty($_POST['website'])) {
        wp_send_json_success(['message' => 'OK']);
        return;
    }

    // 2. Проверка nonce
    if (!isset($_POST['co_nonce']) || !wp_verify_nonce($_POST['co_nonce'], 'loraleya_custom_order')) {
        wp_send_json_error(['message' => 'Истёк сеанс. Обновите страницу и попробуйте снова.'], 403);
        return;
    }

    $request_token = isset($_POST['request_token']) ? sanitize_text_field(wp_unslash($_POST['request_token'])) : '';
    if (!preg_match('/^[a-z0-9-]{20,80}$/i', $request_token)) {
        wp_send_json_error(['message' => 'Не удалось подтвердить отправку формы. Обновите страницу и попробуйте снова.'], 400);
        return;
    }

    // 3. Валидация обязательных полей
    $name          = isset($_POST['customer_name']) ? sanitize_text_field(wp_unslash($_POST['customer_name'])) : '';
    $contact_input = isset($_POST['customer_contact']) ? sanitize_text_field(wp_unslash($_POST['customer_contact'])) : '';
    $contact       = loraleya_normalize_custom_order_phone($contact_input);
    $email_input   = isset($_POST['customer_email']) ? trim(wp_unslash($_POST['customer_email'])) : '';
    $email         = sanitize_email($email_input);
    $consent       = !empty($_POST['consent']);

    if ($name === '') {
        wp_send_json_error(['message' => 'Заполните ФИО.'], 400);
        return;
    }

    if ($contact === '') {
        wp_send_json_error(['message' => 'Введите номер телефона, например +79991234567'], 400);
        return;
    }

    if ($email_input === '') {
        wp_send_json_error(['message' => 'Введите электронную почту.'], 400);
        return;
    }

    if ($email === '' || !is_email($email)) {
        wp_send_json_error(['message' => 'Введите корректный адрес электронной почты.'], 400);
        return;
    }

    if (mb_strlen($name) > 120) {
        wp_send_json_error(['message' => 'Слишком длинное значение в ФИО.'], 400);
        return;
    }

    if (!$consent) {
        wp_send_json_error(['message' => 'Нужно согласие на обработку персональных данных.'], 400);
        return;
    }

    // 4. Позиции и данные для будущего согласования доставки.
    $address = isset($_POST['delivery_address']) ? sanitize_text_field(wp_unslash($_POST['delivery_address'])) : '';
    $notes   = isset($_POST['customer_notes']) ? sanitize_textarea_field(wp_unslash($_POST['customer_notes'])) : '';

    if (mb_strlen($notes) > 2000) {
        $notes = mb_substr($notes, 0, 2000) . '…';
    }

    if ($address === '') {
        wp_send_json_error(['message' => 'Укажите адрес доставки.'], 400);
        return;
    }
    if (mb_strlen($address) > 500) {
        wp_send_json_error(['message' => 'Слишком длинное значение в адресе доставки.'], 400);
        return;
    }

    if (!function_exists('loraleya_custom_order_create_request')) {
        error_log('[LoraLeya] Custom order workflow module is unavailable.');
        wp_send_json_error(['message' => 'Не удалось сохранить заявку. Попробуйте позже.'], 500);
        return;
    }

    $raw_items = isset($_POST['items']) && is_array($_POST['items']) ? wp_unslash($_POST['items']) : [];
    $items     = loraleya_custom_order_prepare_items($raw_items, false, true);
    if (is_wp_error($items)) {
        wp_send_json_error(['message' => $items->get_error_message()], 400);
        return;
    }

    $request_result = loraleya_custom_order_create_request([
        'schema'        => 'items_v2',
        'customer_name' => $name,
        'phone'          => $contact,
        'email'          => $email,
        'items'          => $items,
        'delivery_address' => $address,
        'customer_notes' => $notes,
    ], $request_token);
    if (is_wp_error($request_result)) {
        error_log('[LoraLeya] Custom request save failed: ' . $request_result->get_error_code());
        $error_code  = $request_result->get_error_code();
        $http_status = 'custom_request_rate_limited' === $error_code ? 429 : ( 'custom_request_in_progress' === $error_code ? 409 : 500 );
        wp_send_json_error(['message' => $request_result->get_error_message()], $http_status);
        return;
    }
    $request_id     = absint($request_result['request_id']);
    $request_number = loraleya_custom_order_number($request_id);

    if (empty($request_result['created'])) {
        wp_send_json_success([
            'request_number' => $request_number,
            'message'        => "Заявка {$request_number} уже принята. Мы свяжемся с вами для согласования деталей.",
        ]);
        return;
    }

    $lines = [
        '🪡 Новая заявка с loraleya.ru',
        '',
        "Заявка: {$request_number}",
        "ФИО: {$name}",
        "Телефон: {$contact}",
        "Email: {$email}",
        "Адрес доставки: {$address}",
        '',
        '— Изделия —',
    ];

    foreach ($items as $index => $item) {
        $lines[] = ($index + 1) . '. ' . implode(' — ', [
            $item['item_name'],
            $item['size'],
            $item['color_name'],
            $item['quantity'] . ' шт.',
        ]);
        if ($item['comment'] !== '') {
            $lines[] = '   Комментарий: ' . $item['comment'];
        }
    }

    if ($notes !== '') {
        $lines[] = '';
        $lines[] = '— Комментарий —';
        $lines[] = $notes;
    }

    $lines[] = '';
    $lines[] = 'Дата: ' . wp_date('d.m.Y H:i');

    $body    = implode("\n", $lines);
    $subject = "LoraLeya: заявка {$request_number} от {$name}";

    // 6. Email отправляются после сохранения. Их сбой не удаляет заявку.
    $owner_sent = loraleya_send_custom_order_email($subject, $body);

    $customer_lines = [
        "Здравствуйте, {$name}!",
        '',
        "Ваша заявка {$request_number} получена.",
        "Телефон: {$contact}",
        "Email: {$email}",
        "Адрес доставки: {$address}",
        '',
        'Изделия:',
    ];
    foreach ($items as $index => $item) {
        $customer_lines[] = ($index + 1) . '. ' . implode(' — ', [
            $item['item_name'],
            $item['size'],
            $item['color_name'],
            $item['quantity'] . ' шт.',
        ]);
        if ($item['comment'] !== '') {
            $customer_lines[] = '   Комментарий: ' . $item['comment'];
        }
    }
    $customer_lines = array_merge($customer_lines, [
        '',
        'Менеджер свяжется с вами для согласования деталей, стоимости, сроков и доставки.',
        'Только после согласования будет оформлен заказ.',
        'Оплата пока не требуется.',
    ]);
    $customer_body = implode("\n", $customer_lines);
    $customer_sent = loraleya_custom_order_send_customer_receipt(
        $email,
        "LoraLeya: заявка {$request_number} принята",
        $customer_body
    );

    update_post_meta($request_id, '_ll_owner_email_status', $owner_sent ? 'sent' : 'failed');
    update_post_meta($request_id, '_ll_customer_email_status', $customer_sent ? 'sent' : 'failed');
    update_post_meta($request_id, '_ll_notifications_updated_at', current_time('mysql'));

    wp_send_json_success([
        'request_number' => $request_number,
        'message'        => "Заявка {$request_number} принята. Мы свяжемся с вами для согласования деталей.",
    ]);
}
add_action('wp_ajax_loraleya_custom_order',        'loraleya_handle_custom_order');
add_action('wp_ajax_nopriv_loraleya_custom_order', 'loraleya_handle_custom_order');

/**
 * Канальная отправка уведомлений: email + Telegram.
 * Возвращает true если хотя бы один канал сработал.
 */
function loraleya_send_notification($subject, $body) {
    $any_success = false;

    // Канал 1: Email
    $email_to = defined('LORALEYA_NOTIFY_EMAIL') ? LORALEYA_NOTIFY_EMAIL : 'loraleya-tex@yandex.ru';
    $headers  = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: LoraLeya <noreply@loraleya.ru>',
    ];
    $email_ok = wp_mail($email_to, $subject, $body, $headers);
    if ($email_ok) {
        $any_success = true;
    } else {
        error_log('[LoraLeya] wp_mail failed for ' . $email_to);
    }

    // Канал 2: Telegram
    if (defined('LORALEYA_TG_BOT_TOKEN') && defined('LORALEYA_TG_CHAT_ID')) {
        $tg_ok = loraleya_send_telegram(LORALEYA_TG_BOT_TOKEN, LORALEYA_TG_CHAT_ID, $body);
        if ($tg_ok) {
            $any_success = true;
        }
    } else {
        error_log('[LoraLeya] Telegram constants not defined in wp-config.php');
    }

    return $any_success;
}

/**
 * Отправка сообщения в Telegram через Bot API.
 */
function loraleya_send_telegram($token, $chat_id, $text) {
    if (empty($token) || empty($chat_id) || empty($text)) {
        return false;
    }

    if (mb_strlen($text) > 4090) {
        $text = mb_substr($text, 0, 4080) . "\n…";
    }

    $url      = 'https://api.telegram.org/bot' . $token . '/sendMessage';
    $response = wp_remote_post($url, [
        'timeout' => 10,
        'body'    => [
            'chat_id'                  => $chat_id,
            'text'                     => $text,
            'disable_web_page_preview' => true,
        ],
    ]);

    if (is_wp_error($response)) {
        error_log('[LoraLeya] Telegram WP_Error: ' . $response->get_error_message());
        return false;
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($code !== 200 || empty($data['ok'])) {
        error_log('[LoraLeya] Telegram API error: ' . $body);
        return false;
    }

    return true;
}

/**
 * Возвращает URL фото из медиабиблиотеки по slug цвета и типу.
 *
 * @param string $color_slug Slug цвета из таксономии pa_fabric_color
 * @param string $type       Тип: 'salfetka-tsvetok', 'kuvert', 'macro-faktura',
 *                           'nabor-4-140', 'nabor-4-175', 'nabor-6-300' и т.д.
 * @return string URL или пустая строка
 */
function loraleya_get_color_photo_url($color_slug, $type, $size = 'large') {
    static $cache = [];
    static $attachment_ids = [];

    $cache_key = $color_slug . '|' . $type . '|' . $size;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $prefix_map = [
        'bezhevyj'          => 'bezheviy',
        'belyj'             => 'beliy',
        'biryuza'           => 'biruza',
        'blek-zoloto'       => 'blek-zoloto',
        'bronza'            => 'bronza',
        'goluboj'           => 'goluboy',
        'grafit'            => 'grafit',
        'zelyonyj'          => 'zeleniy',
        'zelenyj'           => 'zeleniy',
        'melanzh-zoloto'    => 'melanzh-zoloto',
        'melanzh-serebro'   => 'melanzh-serebro',
        'melanzh-seryj'     => 'melanzh-seriy',
        'melanzh-chyornyj'  => 'melanzh-cherniy',
        'melanzh-chernyj'   => 'melanzh-cherniy',
        'platina'           => 'platina',
        'serebro'           => 'serebro',
        'sirenevyj'         => 'sireneviy',
        'tyomno-biryuzovyj' => 'temno-biruza',
        'temno-biryuzovyj'  => 'temno-biruza',
        'fioletovyj'        => 'fioletoviy',
    ];

    $prefix = $prefix_map[$color_slug] ?? null;
    if (!$prefix) {
        $cache[$cache_key] = '';
        return '';
    }

    $title = $prefix . '-' . $type;

    if (!array_key_exists($title, $attachment_ids)) {
        $attachments = get_posts([
            'post_type'      => 'attachment',
            'name'           => $title,
            'posts_per_page' => 1,
            'post_status'    => 'inherit',
        ]);

        if (empty($attachments)) {
            // Фолбэк: scaled-суффикс
            $attachments = get_posts([
                'post_type'      => 'attachment',
                'name'           => $title . '-scaled',
                'posts_per_page' => 1,
                'post_status'    => 'inherit',
            ]);
        }

        $attachment_ids[$title] = !empty($attachments) ? $attachments[0]->ID : 0;
    }

    $attachment_id = $attachment_ids[$title];
    $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, $size) : '';
    $cache[$cache_key] = $url ?: '';
    return $cache[$cache_key];
}

// === СОГЛАСИЕ НА ОБРАБОТКУ ПЕРСОНАЛЬНЫХ ДАННЫХ В БЛОЧНОМ ЧЕКАУТЕ ===

function loraleya_register_privacy_consent_checkout_field() {
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }

    woocommerce_register_additional_checkout_field([
        'id'       => 'loraleya/privacy-consent',
        'label'    => 'Я согласен(на) с Политикой обработки персональных данных и Условиями оферты',
        'location' => 'order',
        'type'     => 'checkbox',
        'required' => true,
        'attributes' => [
            'data-required-message' => 'Для оформления заказа необходимо согласиться с Политикой обработки персональных данных и Условиями оферты.',
        ],
        'sanitize_callback' => function ($value) {
            return (bool) $value;
        },
        'validate_callback' => function ($value) {
            if (!$value) {
                return new WP_Error(
                    'privacy_consent_required',
                    'Для оформления заказа необходимо согласиться с Политикой обработки персональных данных и Условиями оферты.'
                );
            }
            return true;
        },
    ]);
}
add_action('woocommerce_init', 'loraleya_register_privacy_consent_checkout_field');

function loraleya_privacy_consent_links_script() {
    if (!is_checkout()) return;
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var attempts = 0;
        var interval = setInterval(function() {
            attempts++;
            if (attempts > 50) { clearInterval(interval); return; }

            var labels = document.querySelectorAll('label');
            var targetLabel = null;
            for (var i = 0; i < labels.length; i++) {
                if (labels[i].textContent.indexOf('Политикой обработки персональных данных') !== -1) {
                    targetLabel = labels[i];
                    break;
                }
            }
            if (!targetLabel) return;
            if (targetLabel.parentNode.querySelector('.loraleya-consent-links')) { clearInterval(interval); return; }

            var linksDiv = document.createElement('div');
            linksDiv.className = 'loraleya-consent-links';
            linksDiv.style.cssText = 'margin-top:4px;margin-left:24px;font-size:.85em;';
            linksDiv.innerHTML = '<a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" target="_blank" rel="noopener" style="color:#C5A55A;text-decoration:underline;margin-right:1em;">Политика конфиденциальности</a>' +
                '<a href="<?php echo esc_url(home_url('/oferta/')); ?>" target="_blank" rel="noopener" style="color:#C5A55A;text-decoration:underline;">Условия оферты</a>';
            targetLabel.parentNode.appendChild(linksDiv);
            clearInterval(interval);
        }, 200);
    });
    </script>
    <?php
}
add_action('wp_footer', 'loraleya_privacy_consent_links_script');

// === СОГЛАСИЕ НА ОБРАБОТКУ ПДн НА ФОРМЕ РЕГИСТРАЦИИ (/my-account/) ===

add_action('woocommerce_register_form', function () {
    ?>
    <p class="form-row">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox" style="display:flex;align-items:flex-start;gap:.5rem;text-transform:none;letter-spacing:0;font-size:.9rem;color:var(--text,#c8c0b4);">
            <input type="checkbox" name="loraleya_privacy_consent" id="loraleya_privacy_consent" value="1" style="margin-top:.2rem;flex-shrink:0;">
            <span>Я согласен(на) с <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" target="_blank" rel="noopener" style="color:var(--gold,#c5a55a);">Политикой обработки персональных данных</a> и <a href="<?php echo esc_url(home_url('/oferta/')); ?>" target="_blank" rel="noopener" style="color:var(--gold,#c5a55a);">Условиями оферты</a> <span class="required">*</span></span>
        </label>
    </p>
    <?php
});

add_action('woocommerce_register_post', function ($username, $email, $errors) {
    if (empty($_POST['loraleya_privacy_consent'])) {
        $errors->add('loraleya_privacy_consent', __('Необходимо согласие на обработку персональных данных.', 'loraleya'));
    }
}, 10, 3);

// === ЛИЧНЫЙ КАБИНЕТ: МЕНЮ И СТАРТ ===

add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    unset( $items['dashboard'] );        // Консоль — убрать
    unset( $items['downloads'] );        // Загрузки — убрать
    unset( $items['payment-methods'] );  // Способы оплаты — убрать
    return $items;
}, 20 );

add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    if ( isset( $items['orders'] ) )          $items['orders']          = 'Мои заказы';
    if ( isset( $items['edit-address'] ) )    $items['edit-address']    = 'Адреса';
    if ( isset( $items['edit-account'] ) )    $items['edit-account']    = 'Профиль';
    if ( isset( $items['customer-logout'] ) ) $items['customer-logout'] = 'Выйти';
    return $items;
}, 30 );

add_action( 'template_redirect', function () {
    if ( ! function_exists( 'is_account_page' ) ) return;
    if ( is_account_page() && is_user_logged_in() && empty( WC()->query->get_current_endpoint() ) ) {
        wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
        exit;
    }
} );

// === ЛИЧНЫЙ КАБИНЕТ: ССЫЛКА «В КАТАЛОГ» НА СТРАНИЦЕ ЗАКАЗА ===

add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
    if ( ! $order ) return;
    $shop = wc_get_page_permalink( 'shop' );
    if ( ! $shop ) $shop = home_url( '/#palette' );
    $is_standard_received = function_exists( 'is_order_received_page' )
        && is_order_received_page()
        && 'yes' !== $order->get_meta( '_ll_individual_order' )
        && 'yes' !== $order->get_meta( '_ll_delivery_payment_order' );
    $label = $is_standard_received
        ? '← Вернуться в каталог'
        : 'В каталог — добавить ещё';
    echo '<p class="ll-order-add-more"><a href="' . esc_url( $shop ) . '" class="button ll-btn-outline">' . esc_html( $label ) . '</a></p>';
}, 20 );

// Заголовок вкладки «Профиль» — вместо «Анкета»
add_filter( 'woocommerce_endpoint_edit-account_title', function () {
    return 'Профиль';
} );

// === ЛИЧНЫЙ КАБИНЕТ: ПРИВЕТСТВИЕ-ШАПКА ===

add_action( 'woocommerce_account_content', function () {
    if ( ! is_user_logged_in() ) return;
    $user = wp_get_current_user();
    $name = $user->first_name ? $user->first_name : $user->display_name;
    echo '<div class="ll-account-greeting">';
    echo '<p class="ll-account-greeting__eyebrow">Личный кабинет</p>';
    echo '<h2 class="ll-account-greeting__title">Здравствуйте, ' . esc_html( $name ) . '</h2>';
    echo '</div>';
}, 5 );

// === SEO-поля для страниц сценариев (Sprint 1, ТЗ E3) ===

add_action('add_meta_boxes_scenario', function() {
    add_meta_box(
        'scenario_seo_meta',
        'SEO-поля сценария',
        'loraleya_scenario_seo_meta_box',
        'scenario',
        'normal',
        'high'
    );
});

function loraleya_scenario_seo_meta_box($post) {
    wp_nonce_field('loraleya_scenario_seo_save', 'loraleya_scenario_seo_nonce');
    $seo_title       = get_post_meta($post->ID, 'seo_title', true);
    $seo_description = get_post_meta($post->ID, 'seo_description', true);
    $seo_faq         = get_post_meta($post->ID, 'seo_faq', true);
    ?>
    <p>
        <label for="seo_title"><strong>SEO Title:</strong></label><br>
        <input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width:100%">
        <small>50-65 символов</small>
    </p>
    <p>
        <label for="seo_description"><strong>SEO Description:</strong></label><br>
        <textarea name="seo_description" id="seo_description" rows="3" style="width:100%"><?php echo esc_textarea($seo_description); ?></textarea>
        <small>120-160 символов</small>
    </p>
    <p>
        <label for="seo_faq"><strong>SEO FAQ (JSON):</strong></label><br>
        <textarea name="seo_faq" id="seo_faq" rows="12" style="width:100%; font-family:monospace"><?php echo esc_textarea($seo_faq); ?></textarea>
        <small>JSON-массив объектов {question, answer}.</small>
    </p>
    <?php
}

add_action('save_post_scenario', function($post_id) {
    if (!isset($_POST['loraleya_scenario_seo_nonce']) ||
        !wp_verify_nonce($_POST['loraleya_scenario_seo_nonce'], 'loraleya_scenario_seo_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_faq'])) {
        update_post_meta($post_id, 'seo_faq', wp_unslash($_POST['seo_faq']));
    }
});

// === SEO: TITLE И META DESCRIPTION ===

/**
 * Читает seo_title или seo_description из meta темы для текущего объекта.
 *
 * Охват: pa_fabric_color, scenario, post (статья), category, front_page.
 * is_home() (/blog/) — намеренно не включён, отдельная задача.
 *
 * Для singular-типов использует get_queried_object_id() вместо get_the_ID(),
 * так как функция вызывается в том числе из фильтров Rank Math вне Loop.
 */
function loraleya_get_seo_field( $field ) {
    if ( is_tax( 'pa_fabric_color' ) ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            return (string) get_term_meta( $term->term_id, $field, true );
        }
    }
    if ( is_singular( 'scenario' ) || is_singular( 'post' ) ) {
        return (string) get_post_meta( get_queried_object_id(), $field, true );
    }
    if ( is_category() ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            return (string) get_term_meta( $term->term_id, $field, true );
        }
    }
    if ( is_front_page() ) {
        if ( 'seo_title' === $field ) {
            return 'Красивая сервировка стола — наборы в 17 цветах | LoraLeya';
        }
        if ( 'seo_description' === $field ) {
            return 'Жаккардовая скатерть, дорожка и салфетки в 17 цветах. Готовые наборы и индивидуальный пошив столового текстиля LoraLeya.';
        }
    }
    return '';
}

if ( ! class_exists( 'RankMath' ) ) {
    // Резервный режим: Rank Math не активен.
    // Тема сама выводит title и один <meta name="description">.

    add_filter( 'pre_get_document_title', function( $title ) {
        $custom = loraleya_get_seo_field( 'seo_title' );
        return ! empty( $custom ) ? $custom : $title;
    }, 10 );

    add_action( 'wp_head', function() {
        $description = loraleya_get_seo_field( 'seo_description' );
        if ( ! empty( $description ) ) {
            echo "\n" . '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        }
    }, 5 );
}

// === JSON-LD FAQPage schema ===

add_action('wp_head', function() {
    $faq_json = '';

    if (is_tax('pa_fabric_color')) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {
            $faq_json = get_term_meta($term->term_id, 'seo_faq', true);
            if (empty($faq_json)) {
                $faq_json = loraleya_get_default_color_faq_json();
            }
        }
    } elseif (is_singular('scenario')) {
        $faq_json = get_post_meta(get_the_ID(), 'seo_faq', true);
    } elseif (is_singular('post')) {
        $faq_json = get_post_meta(get_the_ID(), 'seo_faq', true);
    }

    if (empty($faq_json)) return;

    $faq_data = json_decode($faq_json, true);
    if (!is_array($faq_data) || empty($faq_data)) return;

    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(function($item) {
            return [
                '@type'          => 'Question',
                'name'           => $item['question'] ?? '',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $item['answer'] ?? '',
                ],
            ];
        }, $faq_data),
    ];

    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
});

// === Schema.org для страницы /about/ ===

add_action('wp_head', function() {
    if (!is_page('about')) return;

    $schemas = [
        [
            '@context'      => 'https://schema.org',
            '@type'         => ['Organization', 'Brand'],
            'name'          => 'LoraLeya',
            'alternateName' => ['ЛораЛея', 'LoraLeya — столовый текстиль'],
            'url'           => 'https://loraleya.ru/',
            'foundingDate'  => '2022-01',
            'founder'       => [
                '@type'    => 'Person',
                'name'     => 'Наталья Куренкова',
                'jobTitle' => 'Основательница',
                'worksFor' => ['@type' => 'Organization', 'name' => 'LoraLeya'],
            ],
            'description'  => 'Российский бренд жаккардового столового текстиля. Готовые наборы и индивидуальный пошив скатертей, дорожек, салфеток и кувертов в 17 цветах. Мастерская в Раменском районе Подмосковья.',
            'slogan'       => 'Магия сервировки',
            'address'      => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'RU',
                'addressRegion'   => 'Московская область',
                'addressLocality' => 'Раменский район',
            ],
            'contactPoint' => [
                '@type'             => 'ContactPoint',
                'telephone'         => '+79264950210',
                'email'             => 'loraleya-tex@yandex.ru',
                'contactType'       => 'customer service',
                'availableLanguage' => ['ru'],
            ],
            'sameAs' => [],
        ],
        [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => 'LoraLeya — мастерская столового текстиля',
            'address'  => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'RU',
                'addressRegion'   => 'Московская область',
                'addressLocality' => 'Раменский район',
            ],
            'telephone' => '+79264950210',
            'email'     => 'loraleya-tex@yandex.ru',
            'url'       => 'https://loraleya.ru/about/',
            'priceRange'=> '₽₽',
        ],
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => 'Наталья Куренкова',
            'jobTitle'    => 'Основательница',
            'worksFor'    => [
                '@type' => 'Organization',
                'name'  => 'LoraLeya',
                'url'   => 'https://loraleya.ru/',
            ],
            'description' => 'Экономист по образованию. Основательница бренда жаккардового столового текстиля LoraLeya. Создала бренд в январе 2022 года после двух неудачных бизнес-попыток.',
            'knowsAbout'  => ['столовый текстиль', 'жаккард', 'сервировка стола', 'индивидуальный пошив'],
        ],
    ];

    foreach ($schemas as $s) {
        echo "\n" . '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }
});

// === SEO-поля для статей блога (post) — ТЗ-1 ===

add_action('init', function() {
    foreach (['seo_title', 'seo_description', 'seo_faq'] as $key) {
        register_post_meta('post', $key, [
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function() { return current_user_can('edit_posts'); },
        ]);
    }
});

add_action('add_meta_boxes_post', function() {
    add_meta_box('post_seo_meta', 'SEO-поля статьи', 'loraleya_post_seo_meta_box', 'post', 'normal', 'high');
});

function loraleya_post_seo_meta_box($post) {
    wp_nonce_field('loraleya_post_seo_save', 'loraleya_post_seo_nonce');
    $seo_title       = get_post_meta($post->ID, 'seo_title', true);
    $seo_description = get_post_meta($post->ID, 'seo_description', true);
    $seo_faq         = get_post_meta($post->ID, 'seo_faq', true);
    ?>
    <p>
        <label for="seo_title"><strong>SEO Title:</strong></label><br>
        <input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>" style="width:100%">
        <small>50-65 символов</small>
    </p>
    <p>
        <label for="seo_description"><strong>SEO Description:</strong></label><br>
        <textarea name="seo_description" id="seo_description" rows="3" style="width:100%"><?php echo esc_textarea($seo_description); ?></textarea>
        <small>120-160 символов</small>
    </p>
    <p>
        <label for="seo_faq"><strong>SEO FAQ (JSON):</strong></label><br>
        <textarea name="seo_faq" id="seo_faq" rows="12" style="width:100%; font-family:monospace"><?php echo esc_textarea($seo_faq); ?></textarea>
        <small>JSON-массив объектов {question, answer}.</small>
    </p>
    <?php
}

add_action('save_post_post', function($post_id) {
    if (!isset($_POST['loraleya_post_seo_nonce']) ||
        !wp_verify_nonce($_POST['loraleya_post_seo_nonce'], 'loraleya_post_seo_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    if (isset($_POST['seo_faq'])) {
        update_post_meta($post_id, 'seo_faq', wp_unslash($_POST['seo_faq']));
    }
});

// === SEO-поля для категорий блога — ТЗ-1 ===

add_action('category_add_form_fields', function() {
    ?>
    <div class="form-field">
        <label for="seo_title">SEO Title</label>
        <input type="text" name="seo_title" id="seo_title" value="">
        <p>50-65 символов.</p>
    </div>
    <div class="form-field">
        <label for="seo_description">SEO Description</label>
        <textarea name="seo_description" id="seo_description" rows="2"></textarea>
        <p>120-160 символов.</p>
    </div>
    <div class="form-field">
        <label for="seo_text">SEO Text (HTML)</label>
        <textarea name="seo_text" id="seo_text" rows="10"></textarea>
        <p>Описание хаба. Допустимы h2, h3, p, ul, li, a, strong.</p>
    </div>
    <?php
});

add_action('category_edit_form_fields', function($term) {
    $seo_title       = get_term_meta($term->term_id, 'seo_title', true);
    $seo_description = get_term_meta($term->term_id, 'seo_description', true);
    $seo_text        = get_term_meta($term->term_id, 'seo_text', true);
    ?>
    <tr class="form-field">
        <th><label for="seo_title">SEO Title</label></th>
        <td><input type="text" name="seo_title" id="seo_title" value="<?php echo esc_attr($seo_title); ?>"></td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_description">SEO Description</label></th>
        <td><textarea name="seo_description" id="seo_description" rows="2" cols="50"><?php echo esc_textarea($seo_description); ?></textarea></td>
    </tr>
    <tr class="form-field">
        <th><label for="seo_text">SEO Text (HTML)</label></th>
        <td><textarea name="seo_text" id="seo_text" rows="15" cols="50" class="large-text"><?php echo esc_textarea($seo_text); ?></textarea></td>
    </tr>
    <?php
});

$loraleya_cat_seo_save = function($term_id) {
    if (!current_user_can('manage_categories')) {
        return;
    }
    if (!isset($_POST['loraleya_category_hub_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['loraleya_category_hub_nonce'])),
            'loraleya_category_hub_save'
        )) {
        return;
    }
    foreach (['seo_title', 'seo_description'] as $field) {
        if (isset($_POST[$field])) {
            update_term_meta($term_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
        }
    }
    if (isset($_POST['seo_text'])) {
        update_term_meta($term_id, 'seo_text', wp_kses_post(wp_unslash($_POST['seo_text'])));
    }
};
add_action('created_category', $loraleya_cat_seo_save);
add_action('edited_category', $loraleya_cat_seo_save);

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

// === Закрыть комментарии и пинги для статей/страниц, отзывы товаров не трогаем (ТЗ-8) ===
add_filter('comments_open', function ($open, $post_id) {
    $post = get_post($post_id);
    if ($post && in_array($post->post_type, ['post', 'page'], true)) {
        return false;
    }
    return $open;
}, 10, 2);

add_filter('pings_open', function ($open, $post_id) {
    $post = get_post($post_id);
    if ($post && in_array($post->post_type, ['post', 'page'], true)) {
        return false;
    }
    return $open;
}, 10, 2);

// === Доточка карточки товара (ТЗ-6) ===
add_action('after_setup_theme', function () {
    remove_theme_support('wc-product-gallery-zoom');
}, 20);

// === Свотчи на карточке товара (ТЗ-3) ===
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_product') || !is_product()) return;

    $product_swatches_js = get_stylesheet_directory() . '/assets/js/product-swatches.js';
    wp_enqueue_script(
        'loraleya-product-swatches',
        get_stylesheet_directory_uri() . '/assets/js/product-swatches.js',
        ['jquery', 'wc-add-to-cart-variation'],
        file_exists($product_swatches_js) ? filemtime($product_swatches_js) : '1.0',
        true
    );

    $colors = [
        ['fioletovyj','Фиолетовый'],['grafit','Графит'],['bronza','Бронза'],['sirenevyj','Сиреневый'],
        ['bezhevyj','Бежевый'],['belyj','Белый'],['biryuza','Бирюза'],['blek-zoloto','Блек золото'],
        ['goluboj','Голубой'],['zelenyj','Зелёный'],['melanzh-zoloto','Меланж золото'],
        ['melanzh-serebro','Меланж серебро'],['melanzh-seryj','Меланж серый'],['melanzh-chernyj','Меланж чёрный'],
        ['platina','Платина'],['serebro','Серебро'],['temno-biryuzovyj','Тёмно-бирюзовый'],
    ];
    $map = [];
    foreach ($colors as $c) {
        $url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0]) : '';
        $img = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c[0], 'large') : '';
        $map[$c[0]] = ['name' => $c[1], 'url' => $url, 'image' => $img];
    }
    wp_localize_script('loraleya-product-swatches', 'LoraleyaSwatches', ['colors' => $map]);

    // Фото товара по цвету (специфично для ТЕКУЩЕГО товара): цвет → фото вариации
    if (function_exists('is_product') && is_product()) {
        $prod = wc_get_product(get_queried_object_id());
        if ($prod && $prod->is_type('variable')) {
            $color_imgs = [];
            foreach ($prod->get_children() as $vid) {
                $variation = wc_get_product($vid);
                if (!($variation instanceof WC_Product_Variation)) continue;
                $attrs = $variation->get_variation_attributes();
                $color = isset($attrs['attribute_pa_fabric_color']) ? $attrs['attribute_pa_fabric_color'] : '';
                if ($color === '' || isset($color_imgs[$color])) continue;
                $thumb_id = $variation->get_image_id('edit'); // только собственное фото, без fallback на parent product
                if (!$thumb_id) continue;
                $u = wp_get_attachment_image_url($thumb_id, 'woocommerce_single');
                if ($u) $color_imgs[$color] = $u;
            }
            wp_localize_script('loraleya-product-swatches', 'LoraleyaProductColors', ['images' => $color_imgs]);
        }
    }
});

// === Управление видимостью цены вариации (visibility, резерв высоты сохраняется) ===
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_product') || !is_product()) return;
    $f = get_stylesheet_directory() . '/assets/js/ll-variation-price.js';
    wp_enqueue_script(
        'll-variation-price',
        get_stylesheet_directory_uri() . '/assets/js/ll-variation-price.js',
        ['jquery', 'wc-add-to-cart-variation'],
        file_exists($f) ? filemtime($f) : '1.0.0',
        true
    );
});

function loraleya_get_default_color_faq_json() {
    return wp_json_encode([
        [
            'question' => 'Какая это ткань и как за ней ухаживать?',
            'answer'   => 'Это плотный жаккард с характерным мраморным переплетением. Ткань износостойкая и устойчива к выцветанию, глубоко держит цвет, гладится легко и быстро. Стирка при 30°C в машине с любым моющим средством без отбеливателя.',
        ],
        [
            'question' => 'Какой размер дорожки выбрать под мой стол?',
            'answer'   => 'Правило простое: дорожка короче стола на 30–40 см. На стол 170 см берите дорожку 140; на 200–220 см — 175; на овальный или длинный 240+ см — 240 или 300. Для нестандартного стола (круглого, овального, длиннее 300 см) оформите индивидуальный пошив.',
        ],
        [
            'question' => 'Что входит в готовый набор и насколько он выгоднее поштучно?',
            'answer'   => 'Набор включает дорожку, четыре или шесть салфеток 40×40 см и столько же кувертов (конвертов для столовых приборов) 9×24 см — всё в одном цвете и из одной жаккардовой ткани. Готовый набор выгоднее поштучного сбора того же состава на 15%.',
        ],
    ], JSON_UNESCAPED_UNICODE);
}

// Каталог: убрать дефолтный заголовок «Магазин», крошки и вывести кастомный заголовок
add_action( 'init', function () {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	add_filter( 'woocommerce_show_page_title', '__return_false' );
} );

// Вывести кастомный заголовок «Коллекция LoraLeya / Каталог» перед циклом товаров
add_action( 'woocommerce_before_shop_loop', function () {
	echo '<header class="ll-catalog-head">'
	   . '<p class="ll-catalog-eyebrow">Коллекция LoraLeya</p>'
	   . '<h1 class="ll-catalog-title">Каталог</h1>'
	   . '</header>';
}, 5 );

// Каталог: убрать счётчик результатов и выпадашку сортировки
add_action( 'init', function () {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
} );

/* ===================================================================
   LoraLeya — трёхуровневые тексты товара
   Длинный  -> «Описание товара» (post_content)  -> вкладка «Описание»
   Средний  -> «Краткое описание» (post_excerpt) -> каталог
   Коротыш  -> мета _ll_price_teaser             -> карточка, под ценой
   =================================================================== */

/* 1. Поле «Коротыш под ценой» на экране редактирования товара */
add_action('add_meta_boxes_product', function () {
    add_meta_box(
        'll_price_teaser',
        'Коротыш под ценой (карточка товара)',
        function ($post) {
            wp_nonce_field('ll_price_teaser_save', 'll_price_teaser_nonce');
            $val = get_post_meta($post->ID, '_ll_price_teaser', true);
            echo '<p style="margin:0 0 6px;color:#666">Короткая фраза-крючок под ценой на странице товара. '
               . 'В каталоге показывается «Краткое описание», в блоке «Описание» ниже — «Описание товара».</p>';
            echo '<textarea name="ll_price_teaser" style="width:100%;min-height:70px" '
               . 'placeholder="Напр.: Готовая сервировка на 2, 4 или 6 персон">'
               . esc_textarea($val) . '</textarea>';
        },
        'product', 'normal', 'high'
    );
});

/* 2. Сохранение поля */
add_action('save_post_product', function ($post_id) {
    if (!isset($_POST['ll_price_teaser_nonce']) ||
        !wp_verify_nonce($_POST['ll_price_teaser_nonce'], 'll_price_teaser_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['ll_price_teaser'])) {
        update_post_meta($post_id, '_ll_price_teaser',
            sanitize_textarea_field(wp_unslash($_POST['ll_price_teaser'])));
    }
});

/* 3. Вывод: убрать штатное краткое из шапки, поставить коротыш под ценой */
add_action('init', function () {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    add_action('woocommerce_single_product_summary', function () {
        global $post;
        $teaser = trim((string) get_post_meta($post->ID, '_ll_price_teaser', true));
        if ($teaser !== '') {
            echo '<div class="woocommerce-product-details__short-description ll-price-teaser">'
               . wp_kses_post(wpautop($teaser)) . '</div>';
        }
    }, 20);
});

/* ===================================================================
   LoraLeya — селекты вариаций: подпись «Выбрать» + расшифровка опций
   Вывод, не данные: термины в БД не трогаем. В корзине/заказе/5Post
   остаются короткие коды — меняется только текст в выпадашке.
   =================================================================== */

/* Русское склонение слова «персона» по числу */
if (!function_exists('ll_persons_word')) {
    function ll_persons_word($n) {
        $n = abs((int)$n) % 100;
        if ($n >= 11 && $n <= 14) return 'персон';
        $d = $n % 10;
        if ($d === 1) return 'персона';
        if ($d >= 2 && $d <= 4) return 'персоны';
        return 'персон';
    }
}

/* 1. Плейсхолдер селекта: «Выбрать опцию» -> «Выбрать» */
add_filter('woocommerce_dropdown_variation_attribute_options_args', function ($args) {
    $args['show_option_none'] = 'Выбрать';
    return $args;
});

/* 2. Расшифровка подписей опций в выпадашке */
add_filter('woocommerce_variation_option_name', function ($name) {
    $n = trim(wp_strip_all_tags((string)$name));

    // Набор: "2п/140" или "6п-300" -> "на 6 персон, дорожка 300 см"
    if (preg_match('~^(\d+)\s*п\s*[/\-]\s*(\d+)\s*$~u', $n, $m)) {
        return 'на ' . (int)$m[1] . ' ' . ll_persons_word($m[1]) . ', дорожка ' . (int)$m[2] . ' см';
    }
    // Дорожка и Скатерть: чистое число "140" -> "140 см"
    if (preg_match('~^\d+$~', $n)) {
        return $n . ' см';
    }
    return $name;
}, 10, 1);

/* ===================================================================
   LoraLeya — расшифровка кодов вариаций ДЛЯ КЛИЕНТА
   Данные в БД не меняются (заказы/чек/ЧЗ/5Post работают на кодах).
   Расшифровка только в клиентском выводе: корзина, чекаут, ЛК, «Заказ принят».
   Админка и письма — оставляем коды (техдокументация).
   =================================================================== */

if (!function_exists('ll_decode_size_code')) {
    function ll_decode_size_code($value) {
        $n = trim(wp_strip_all_tags((string)$value));
        if ($n === '') return $value;
        if (preg_match('~^(\d+)\s*п\s*[/\-]\s*(\d+)\s*$~u', $n, $m)) {
            return 'на ' . (int)$m[1] . ' ' . ll_persons_word($m[1]) . ', дорожка ' . (int)$m[2] . ' см';
        }
        if (preg_match('~^\d+$~', $n)) {
            return $n . ' см';
        }
        return $value;
    }
}

if (!function_exists('ll_is_customer_view')) {
    function ll_is_customer_view() {
        if (is_admin() && !wp_doing_ajax()) return false;
        return true;
    }
}

add_filter('woocommerce_cart_item_data', function ($item_data, $cart_item) {
    if (!ll_is_customer_view()) return $item_data;
    foreach ($item_data as $i => $row) {
        if (isset($row['value'])) {
            $item_data[$i]['value'] = ll_decode_size_code($row['value']);
        }
    }
    return $item_data;
}, 10, 2);

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!ll_is_customer_view()) return $item_data;
    foreach ($item_data as $i => $row) {
        if (isset($row['value'])) {
            $item_data[$i]['value'] = ll_decode_size_code($row['value']);
        }
    }
    return $item_data;
}, 10, 2);

add_filter('woocommerce_order_item_display_meta_value', function ($value, $meta = null, $item = null) {
    if (!ll_is_customer_view()) return $value;
    if (did_action('woocommerce_email_header')) return $value;
    return ll_decode_size_code($value);
}, 10, 3);

// Инфраструктура редакционных хабов рубрик.
require_once get_template_directory() . '/inc/category-hub.php';

// Оформление заказа с обязательным подтверждением менеджером до оплаты.
$loraleya_checkout_workflow_file = get_template_directory() . '/inc/checkout-workflow.php';
if ( file_exists( $loraleya_checkout_workflow_file ) ) {
    require_once $loraleya_checkout_workflow_file;
}

// Постоянные заявки на индивидуальный пошив и их конвертация в WooCommerce.
$loraleya_custom_order_workflow_file = get_template_directory() . '/inc/custom-order-workflow.php';
if ( file_exists( $loraleya_custom_order_workflow_file ) ) {
    require_once $loraleya_custom_order_workflow_file;
}

// Совместимость productless-позиций индивидуального заказа с маркировкой YooKassa.
$loraleya_yookassa_individual_marking_file = get_template_directory() . '/inc/yookassa-individual-marking.php';
if ( file_exists( $loraleya_yookassa_individual_marking_file ) ) {
    require_once $loraleya_yookassa_individual_marking_file;
}

// Отдельные счета на доставку для индивидуальных заказов.
$loraleya_individual_delivery_payment_file = get_template_directory() . '/inc/individual-delivery-payment.php';
if ( file_exists( $loraleya_individual_delivery_payment_file ) ) {
    require_once $loraleya_individual_delivery_payment_file;
}

/**
 * Страница оплаты заказа: в строке «Доставка» показываем только сумму.
 */
add_filter( 'woocommerce_order_shipping_to_display', function ( $shipping, $order, $tax_display ) {
    if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-pay' ) || ! $order instanceof WC_Order ) {
        return $shipping;
    }

    $amount = (float) $order->get_shipping_total();
    if ( 'incl' === $tax_display ) {
        $amount += (float) $order->get_shipping_tax();
    }

    return wc_price( $amount, array( 'currency' => $order->get_currency() ) );
}, 100, 3 );

/**
 * Страница оплаты заказа: не показываем прежний способ оплаты в итогах.
 */
add_filter( 'woocommerce_get_order_item_totals', function ( $totals, $order, $tax_display ) {
    if (
        function_exists( 'is_wc_endpoint_url' )
        && is_wc_endpoint_url( 'order-pay' )
        && $order instanceof WC_Order
        && 'yes' === $order->get_meta( '_ll_manager_confirmation_required' )
    ) {
        unset( $totals['payment_method'] );
    }

    return $totals;
}, 100, 3 );
