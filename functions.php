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

    // Передать item-map для страницы цвета
    if (is_tax('pa_fabric_color')) {
        $color_term = get_queried_object();
        if ($color_term && !is_wp_error($color_term)) {
            wp_localize_script('loraleya-main', 'LORALEYA_ITEM_MAP', loraleya_build_item_map($color_term->slug));
        }
    }

    // Custom order page script
    if (is_page('custom-order')) {
        wp_enqueue_script('loraleya-custom-order', get_template_directory_uri() . '/assets/js/custom-order.js', [], '1.0.0', true);
    }
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
 * Найти variation_id по комбинации цвета и размера.
 *
 * @param int    $product_id     ID родительского вариативного товара
 * @param string $color_slug     slug термина pa_fabric_color (например 'biryuza')
 * @param string $size_slug      slug термина размера (например '140' или '4p-140')
 * @param string $size_taxonomy  имя таксономии размера БЕЗ префикса pa_ (razmer-dorozhki/razmer-skaterti/razmer-nabora)
 * @return int variation_id или 0 если не найдено
 */
function loraleya_find_variation_id($product_id, $color_slug, $size_slug, $size_taxonomy) {
    $product = wc_get_product($product_id);
    if (!$product || !$product->is_type('variable')) return 0;

    $size_attr_key = 'attribute_pa_' . $size_taxonomy;

    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) continue;

        $attrs = $variation->get_variation_attributes();
        $matches_color = ($attrs['attribute_pa_fabric_color'] ?? '') === $color_slug;
        $matches_size  = urldecode($attrs[$size_attr_key] ?? '') === $size_slug;

        if ($matches_color && $matches_size) {
            return $variation_id;
        }
    }
    return 0;
}

/**
 * Карта data-item → WC сущности для страницы цвета.
 * Используется в taxonomy-pa_fabric_color.php при рендере + локализуется в JS.
 *
 * @param string $color_slug текущий цвет (slug термина pa_fabric_color)
 * @return array data-item => ['product_id' => int, 'variation_id' => int, 'attrs' => array]
 */
function loraleya_build_item_map($color_slug) {
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
        // Простые (simple, без variation)
        'Салфетка'      => [48, null,     null],
        'Куверт'        => [49, null,     null],
        // Готовые наборы (variable, product_id = 50)
        'Набор 4п/140'  => [50, '4п-140', 'razmer-nabora'],
        'Набор 4п/175'  => [50, '4п-175', 'razmer-nabora'],
        'Набор 6п/240'  => [50, '6п-140', 'razmer-nabora'],
        'Набор 6п/175'  => [50, '6п-175', 'razmer-nabora'],
    ];

    $map = [];
    foreach ($items as $data_item => [$product_id, $size_slug, $size_taxonomy]) {
        if ($size_slug === null) {
            $map[$data_item] = [
                'product_id'   => $product_id,
                'variation_id' => 0,
                'attrs'        => new stdClass(),
            ];
        } else {
            $variation_id = loraleya_find_variation_id($product_id, $color_slug, $size_slug, $size_taxonomy);
            $map[$data_item] = [
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'attrs'        => [
                    'attribute_pa_fabric_color'      => $color_slug,
                    'attribute_pa_' . $size_taxonomy => $size_slug,
                ],
            ];
        }
    }
    return $map;
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
                $variation_labels[] = $term ? $term->name : $attr_value;
            }
        }

        $items[] = [
            'cart_key'         => $cart_key,
            'product_id'       => $cart_item['product_id'],
            'variation_id'     => $cart_item['variation_id'],
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

function loraleya_color_swatch_url($slug) {
    static $cache = [];
    if (isset($cache[$slug])) {
        return $cache[$slug];
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
        $url = wp_get_attachment_image_url($attachment[0]->ID, 'thumbnail');
        $cache[$slug] = $url ?: '';
        return $cache[$slug];
    }

    $cache[$slug] = '';
    return '';
}

/* =============================================
   CUSTOM ORDER FORM HANDLER
   ============================================= */

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

    // 3. Валидация обязательных полей
    $name    = isset($_POST['customer_name'])    ? sanitize_text_field(wp_unslash($_POST['customer_name']))    : '';
    $contact = isset($_POST['customer_contact']) ? sanitize_text_field(wp_unslash($_POST['customer_contact'])) : '';
    $consent = !empty($_POST['consent']);

    if ($name === '' || $contact === '') {
        wp_send_json_error(['message' => 'Заполните имя и контакт.'], 400);
        return;
    }

    if (mb_strlen($name) > 100 || mb_strlen($contact) > 100) {
        wp_send_json_error(['message' => 'Слишком длинные значения в имени или контакте.'], 400);
        return;
    }

    if (!$consent) {
        wp_send_json_error(['message' => 'Нужно согласие на обработку персональных данных.'], 400);
        return;
    }

    // 4. Сбор остальных полей формы (необязательные)
    $shape_name  = isset($_POST['shape_name'])      ? sanitize_text_field(wp_unslash($_POST['shape_name']))      : '';
    $dim_length  = isset($_POST['dim_length'])      ? absint($_POST['dim_length'])                               : 0;
    $dim_width   = isset($_POST['dim_width'])       ? absint($_POST['dim_width'])                                : 0;
    $persons     = isset($_POST['persons'])         ? absint($_POST['persons'])                                  : 0;
    $color_name  = isset($_POST['color_name'])      ? sanitize_text_field(wp_unslash($_POST['color_name']))      : '';
    $items_text  = isset($_POST['items_summary'])   ? sanitize_text_field(wp_unslash($_POST['items_summary']))   : '';
    $opt_mono    = !empty($_POST['opt_monogram']);
    $opt_edge    = !empty($_POST['opt_edge']);
    $opt_rings   = !empty($_POST['opt_rings']);
    $notes       = isset($_POST['customer_notes'])  ? sanitize_textarea_field(wp_unslash($_POST['customer_notes'])) : '';

    if (mb_strlen($notes) > 2000) {
        $notes = mb_substr($notes, 0, 2000) . '…';
    }

    // 5. Сборка тела сообщения
    $opts_list = [];
    if ($opt_mono)  $opts_list[] = 'Монограмма / вышивка';
    if ($opt_edge)  $opts_list[] = 'Декоративная обработка края';
    if ($opt_rings) $opts_list[] = 'Кольца для салфеток';

    $size_text = ($dim_length && $dim_width) ? "{$dim_length} × {$dim_width} см" : 'Не указан';

    $lines = [
        '🪡 Новая заявка с loraleya.ru',
        '',
        "Имя: {$name}",
        "Контакт: {$contact}",
        '',
        '— Параметры заказа —',
        "Форма стола: " . ($shape_name ?: '—'),
        "Размер: {$size_text}",
        "Персон: " . ($persons ?: '—'),
        "Цвет: " . ($color_name ?: '—'),
        "Изделия: " . ($items_text ?: '—'),
    ];

    if (!empty($opts_list)) {
        $lines[] = "Опции: " . implode(', ', $opts_list);
    }

    if ($notes !== '') {
        $lines[] = '';
        $lines[] = '— Комментарий —';
        $lines[] = $notes;
    }

    $lines[] = '';
    $lines[] = 'Дата: ' . wp_date('d.m.Y H:i');

    $body    = implode("\n", $lines);
    $subject = "LoraLeya: заявка от {$name}";

    // 6. Отправка через канальный helper
    $sent = loraleya_send_notification($subject, $body);

    if ($sent) {
        wp_send_json_success(['message' => 'Заявка отправлена. Свяжемся в течение 2 часов.']);
    } else {
        wp_send_json_error(['message' => 'Не удалось отправить заявку. Попробуйте позже или напишите нам напрямую.'], 500);
    }
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
