<?php
/**
 * LoraLeya — одноразовый скрипт импорта товаров в WooCommerce.
 *
 * Запуск: https://loraleya.ru/wp-content/themes/loraleya/tools/import-products.php?color=biruza
 * Требует авторизации администратора.
 *
 * Идемпотентен: повторный запуск не создаёт дубликатов.
 * После успешного импорта всех цветов файл можно удалить.
 */

// ===== ЗАГРУЗКА WORDPRESS =====
$wp_load = __DIR__ . '/../../../../../wp-load.php';
if (!file_exists($wp_load)) {
    // Попробовать путь для структуры /wp-content/themes/loraleya/tools/
    $wp_load = __DIR__ . '/../../../../wp-load.php';
}
if (!file_exists($wp_load)) {
    die('Не найден wp-load.php. Проверьте путь к файлу скрипта.');
}
require_once $wp_load;

// ===== БЕЗОПАСНОСТЬ =====
if (!current_user_can('manage_options')) {
    status_header(403);
    die('Access denied');
}
if (!class_exists('WooCommerce')) {
    die('WooCommerce не активен');
}

// ===== ПАРАМЕТР ЦВЕТА =====
$color_slug = sanitize_title($_GET['color'] ?? 'biruza');
if (empty($color_slug)) {
    die('Укажите параметр color= в URL. Пример: ?color=biruza');
}

$color_term = get_term_by('slug', $color_slug, 'fabric_color');
if (!$color_term) {
    die("Термин «{$color_slug}» не найден в таксономии fabric_color. Проверьте slug в Товары → Цвета ткани.");
}
$color_term_id = $color_term->term_id;
$color_name    = $color_term->name;

// ===== КАТАЛОГ ТОВАРОВ =====
$catalog = [
    // 1. Дорожка на стол
    [
        'type'              => 'variable',
        'slug'              => 'dorozhka-na-stol',
        'name'              => 'Дорожка на стол',
        'category'          => 'Дорожки',
        'description'       => 'Жаккардовая дорожка на стол с характерным переливом. Изготавливается из 100% полиэстера.',
        'short_description' => '4 размера · жаккардовое плетение · 100% полиэстер',
        'main_photo'        => $color_slug . '-dorozhka',
        'gallery_photos'    => [
            $color_slug . '-hero-servirovka',
            $color_slug . '-macro-pereliv',
            $color_slug . '-gallery-1',
        ],
        'size_attribute'    => 'pa_razmer-dorozhki',
        'variants'          => [
            ['size_label' => '140', 'price' => 890,  'regular_price' => 1050, 'sku_suffix' => '140'],
            ['size_label' => '175', 'price' => 990,  'regular_price' => 1160, 'sku_suffix' => '175'],
            ['size_label' => '240', 'price' => 1290, 'regular_price' => 1520, 'sku_suffix' => '240'],
            ['size_label' => '300', 'price' => 1590, 'regular_price' => 1870, 'sku_suffix' => '300'],
        ],
    ],
    // 2. Скатерть
    [
        'type'              => 'variable',
        'slug'              => 'skatert',
        'name'              => 'Скатерть',
        'category'          => 'Скатерти',
        'description'       => 'Жаккардовая скатерть. Подходит для сервировки обычных и праздничных столов.',
        'short_description' => '3 размера · жаккардовое плетение · 100% полиэстер',
        'main_photo'        => $color_slug . '-dorozhka',
        'gallery_photos'    => [
            $color_slug . '-hero-servirovka',
            $color_slug . '-macro-faktura',
        ],
        'size_attribute'    => 'pa_razmer-skaterti',
        'variants'          => [
            ['size_label' => '175', 'price' => 2490, 'regular_price' => 2930, 'sku_suffix' => '175'],
            ['size_label' => '220', 'price' => 2990, 'regular_price' => 3520, 'sku_suffix' => '220'],
            ['size_label' => '240', 'price' => 3490, 'regular_price' => 4110, 'sku_suffix' => '240'],
        ],
    ],
    // 3. Салфетка (простой товар)
    [
        'type'              => 'simple',
        'slug'              => 'salfetka-servirovochnaya',
        'name'              => 'Салфетка сервировочная',
        'category'          => 'Салфетки',
        'description'       => 'Жаккардовая сервировочная салфетка 40×40 см. Продаётся поштучно.',
        'short_description' => '40 × 40 см · цена за 1 шт',
        'main_photo'        => $color_slug . '-salfetka-tsvetok',
        'gallery_photos'    => [
            $color_slug . '-salfetka-razvernutaya',
            $color_slug . '-salfetka-koltso2',
            $color_slug . '-macro-strochka',
        ],
        'price'             => 350,
        'regular_price'     => 410,
    ],
    // 4. Куверт (простой товар)
    [
        'type'              => 'simple',
        'slug'              => 'kuvert-dlya-priborov',
        'name'              => 'Куверт для приборов',
        'category'          => 'Куверты',
        'description'       => 'Жаккардовый куверт для столовых приборов 9×24 см.',
        'short_description' => '9 × 24 см · цена за 1 шт',
        'main_photo'        => $color_slug . '-kuvert',
        'gallery_photos'    => [
            $color_slug . '-kuvert-reserve',
            $color_slug . '-hero-detail',
        ],
        'price'             => 250,
        'regular_price'     => 290,
    ],
    // 5. Готовый набор (вариативный)
    [
        'type'              => 'variable',
        'slug'              => 'gotovyj-nabor',
        'name'              => 'Готовый набор — всё в одном цвете',
        'category'          => 'Готовые наборы',
        'description'       => 'Готовый комплект из дорожки, салфеток и кувертов. Выгоднее поштучных покупок на 15%.',
        'short_description' => 'Комплект сразу для сервировки',
        'main_photo'        => $color_slug . '-nabor-4',
        'gallery_photos'    => [
            $color_slug . '-hero-servirovka',
            $color_slug . '-gallery-1',
            $color_slug . '-gallery-2',
        ],
        'size_attribute'    => 'pa_razmer-nabora',
        'variants'          => [
            ['size_label' => '4п/140', 'price' => 2790, 'regular_price' => 3290, 'sku_suffix' => '4p-140'],
            ['size_label' => '4п/175', 'price' => 2970, 'regular_price' => 3490, 'sku_suffix' => '4p-175'],
            ['size_label' => '6п/140', 'price' => 3820, 'regular_price' => 4490, 'sku_suffix' => '6p-140'],
            ['size_label' => '6п/175', 'price' => 3990, 'regular_price' => 4690, 'sku_suffix' => '6p-175'],
        ],
    ],
];

// ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====

/**
 * Найти ID вложения в медиатеке по post_name (slug файла без расширения).
 */
function ll_find_attachment_by_name(string $name): int {
    global $wpdb;
    $id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'attachment'
           AND post_status = 'inherit'
           AND post_name = %s
         LIMIT 1",
        $name
    ));
    if ($id) return $id;

    // Запасной вариант: поиск по заголовку
    $id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'attachment'
           AND post_status = 'inherit'
           AND post_title = %s
         LIMIT 1",
        $name
    ));
    return $id;
}

/**
 * Получить все термины атрибута — возвращает map [name => slug, ...].
 */
function ll_get_attribute_terms(string $taxonomy): array {
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
    if (is_wp_error($terms)) return [];
    $map = [];
    foreach ($terms as $t) {
        $map[$t->name] = $t->slug;
    }
    return $map;
}

/**
 * Найти slug термина по label (имени).
 * Ищет точное совпадение и нормализованное (без слешей, транслит).
 */
function ll_resolve_size_slug(string $label, array $terms_map): ?string {
    // Точное совпадение по имени
    if (isset($terms_map[$label])) {
        return $terms_map[$label];
    }
    // Попытка через sanitize_title — WP так генерирует slug
    $candidate = sanitize_title($label);
    // Поиск среди slug-ов
    $slugs = array_values($terms_map);
    if (in_array($candidate, $slugs)) {
        return $candidate;
    }
    return null;
}

// ===== НАЧАЛО ВЫВОДА =====
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>LoraLeya — Импорт товаров</title>
    <style>
        body { font-family: monospace; font-size: 14px; padding: 2rem; background: #f5f5f5; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 2rem; border-bottom: 1px solid #ccc; }
        .ok   { color: #2a7a2a; }
        .warn { color: #a06000; }
        .err  { color: #a00000; }
        .info { color: #0055aa; }
        ul { margin: 0.3rem 0 1rem 1.5rem; }
        a { color: #0055aa; }
        table { border-collapse: collapse; margin: 1rem 0; }
        td, th { border: 1px solid #ccc; padding: 4px 10px; }
        th { background: #eee; }
    </style>
</head>
<body>
<h1>LoraLeya — Импорт товаров WooCommerce</h1>
<p><strong>Цвет:</strong> <?php echo esc_html($color_name); ?> (slug: <code><?php echo esc_html($color_slug); ?></code>, term_id: <?php echo $color_term_id; ?>)</p>

<?php

$report_products   = [];
$report_warnings   = [];
$total_created     = 0;
$total_updated     = 0;
$total_variations  = 0;

// ===== ФАЗА 1: КАТЕГОРИИ ТОВАРОВ =====
echo '<h2>Фаза 1: Категории товаров</h2><ul>';

$categories = [
    'Дорожки'       => 'dorozhki',
    'Скатерти'      => 'skaterti',
    'Салфетки'      => 'salfetki',
    'Куверты'       => 'kuverty',
    'Готовые наборы'=> 'gotovye-nabory',
];

foreach ($categories as $cat_name => $cat_slug) {
    $existing = get_term_by('slug', $cat_slug, 'product_cat');
    if ($existing) {
        echo "<li class='ok'>✓ «{$cat_name}» — уже существует (ID: {$existing->term_id})</li>";
    } else {
        $result = wp_insert_term($cat_name, 'product_cat', ['slug' => $cat_slug]);
        if (is_wp_error($result)) {
            echo "<li class='err'>✗ «{$cat_name}» — ошибка: " . esc_html($result->get_error_message()) . "</li>";
        } else {
            echo "<li class='info'>+ «{$cat_name}» создана (ID: {$result['term_id']})</li>";
        }
    }
}
echo '</ul>';

// ===== ФАЗА 2: ТОВАРЫ =====
echo '<h2>Фаза 2: Товары</h2>';

foreach ($catalog as $item) {
    echo '<hr><h3>' . esc_html($item['name']) . ' <small>(' . $item['type'] . ')</small></h3><ul>';

    // --- 2.1. Создать или найти товар ---
    $existing_post = get_page_by_path($item['slug'], OBJECT, 'product');
    if ($existing_post) {
        $product_id = $existing_post->ID;
        // Обновить заголовок и описание на случай изменений
        wp_update_post([
            'ID'           => $product_id,
            'post_title'   => $item['name'],
            'post_content' => $item['description'],
            'post_excerpt' => $item['short_description'],
            'post_status'  => 'publish',
        ]);
        echo "<li class='ok'>✓ Товар существует (ID: {$product_id}) — обновлён</li>";
        $total_updated++;
    } else {
        $product_id = wp_insert_post([
            'post_type'    => 'product',
            'post_status'  => 'publish',
            'post_name'    => $item['slug'],
            'post_title'   => $item['name'],
            'post_content' => $item['description'],
            'post_excerpt' => $item['short_description'],
        ]);
        if (is_wp_error($product_id)) {
            echo "<li class='err'>✗ Не удалось создать товар: " . esc_html($product_id->get_error_message()) . "</li></ul>";
            continue;
        }
        echo "<li class='info'>+ Товар создан (ID: {$product_id})</li>";
        $total_created++;
    }

    // --- 2.2. Тип товара ---
    wp_set_object_terms($product_id, $item['type'], 'product_type');

    // --- 2.3. Категория ---
    $cat_term = get_term_by('name', $item['category'], 'product_cat');
    if ($cat_term) {
        wp_set_object_terms($product_id, [$cat_term->term_id], 'product_cat');
        echo "<li class='ok'>✓ Категория: «{$item['category']}»</li>";
    } else {
        echo "<li class='warn'>⚠ Категория «{$item['category']}» не найдена</li>";
        $report_warnings[] = "Товар «{$item['name']}»: категория «{$item['category']}» не найдена";
    }

    // --- 2.4. Цвет (fabric_color) — append=true, не перезаписывает существующие ---
    wp_set_object_terms($product_id, [$color_term_id], 'fabric_color', true);
    echo "<li class='ok'>✓ Цвет привязан: «{$color_name}» (term_id: {$color_term_id})</li>";

    // --- 2.5. Фото ---
    $main_photo_id = ll_find_attachment_by_name($item['main_photo']);
    if ($main_photo_id) {
        set_post_thumbnail($product_id, $main_photo_id);
        echo "<li class='ok'>✓ Главное фото: «{$item['main_photo']}» (ID: {$main_photo_id})</li>";
    } else {
        echo "<li class='warn'>⚠ Главное фото не найдено: «{$item['main_photo']}»</li>";
        $report_warnings[] = "Товар «{$item['name']}»: главное фото «{$item['main_photo']}» не найдено в медиатеке";
    }

    $gallery_ids = [];
    foreach ($item['gallery_photos'] as $photo_name) {
        $pid = ll_find_attachment_by_name($photo_name);
        if ($pid) {
            $gallery_ids[] = $pid;
        } else {
            echo "<li class='warn'>⚠ Фото галереи не найдено: «{$photo_name}»</li>";
            $report_warnings[] = "Товар «{$item['name']}»: фото галереи «{$photo_name}» не найдено";
        }
    }
    if (!empty($gallery_ids)) {
        update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
        echo "<li class='ok'>✓ Галерея: " . count($gallery_ids) . " фото</li>";
    }

    // ===== ПРОСТОЙ ТОВАР =====
    if ($item['type'] === 'simple') {
        update_post_meta($product_id, '_price',         $item['price']);
        update_post_meta($product_id, '_regular_price', $item['regular_price']);
        update_post_meta($product_id, '_sale_price',    $item['price']);
        update_post_meta($product_id, '_sku',           "LL-{$color_slug}-{$item['slug']}");
        update_post_meta($product_id, '_manage_stock',  'no');
        update_post_meta($product_id, '_stock_status',  'instock');
        update_post_meta($product_id, '_virtual',       'no');
        update_post_meta($product_id, '_visibility',    'visible');

        echo "<li class='ok'>✓ Цена: {$item['price']} ₽ (обычная: {$item['regular_price']} ₽)</li>";
        echo "<li class='ok'>✓ SKU: LL-{$color_slug}-{$item['slug']}</li>";

        wc_delete_product_transients($product_id);
        $report_products[] = ['id' => $product_id, 'name' => $item['name'], 'type' => 'simple', 'variations' => 0];
    }

    // ===== ВАРИАТИВНЫЙ ТОВАР =====
    if ($item['type'] === 'variable') {
        $attr_taxonomy = $item['size_attribute'];

        // Получить все термины атрибута размера
        $attr_terms = ll_get_attribute_terms($attr_taxonomy);
        if (empty($attr_terms)) {
            echo "<li class='err'>✗ Атрибут «{$attr_taxonomy}» не содержит терминов. Создайте значения в Товары → Атрибуты.</li></ul>";
            $report_warnings[] = "Товар «{$item['name']}»: атрибут «{$attr_taxonomy}» пуст";
            continue;
        }
        echo "<li class='info'>Найдены термины «{$attr_taxonomy}»: " . implode(', ', array_keys($attr_terms)) . "</li>";

        // Для каждой вариации — сматчить slug размера
        $size_slugs_to_attach = [];
        $variants_resolved    = [];
        $has_unresolved       = false;

        foreach ($item['variants'] as $variant) {
            $size_slug = ll_resolve_size_slug($variant['size_label'], $attr_terms);
            if (!$size_slug) {
                echo "<li class='err'>✗ Не найден термин размера «{$variant['size_label']}» в «{$attr_taxonomy}». Проверьте значения в Товары → Атрибуты → Настроить значения.</li>";
                $report_warnings[] = "Товар «{$item['name']}»: не сматчен размер «{$variant['size_label']}» в «{$attr_taxonomy}»";
                $has_unresolved = true;
                continue;
            }
            $size_slugs_to_attach[] = $size_slug;
            $variants_resolved[] = array_merge($variant, ['size_slug' => $size_slug]);
        }

        // Привязать термины размеров к родителю
        if (!empty($size_slugs_to_attach)) {
            wp_set_object_terms($product_id, $size_slugs_to_attach, $attr_taxonomy, true);
        }

        // Привязать цвет к родителю
        wp_set_object_terms($product_id, [$color_slug], 'fabric_color', true);

        // Сохранить _product_attributes
        $product_attributes = [
            'fabric_color' => [
                'name'         => 'fabric_color',
                'value'        => '',
                'position'     => 0,
                'is_visible'   => 1,
                'is_variation' => 1,
                'is_taxonomy'  => 1,
            ],
            $attr_taxonomy => [
                'name'         => $attr_taxonomy,
                'value'        => '',
                'position'     => 1,
                'is_visible'   => 1,
                'is_variation' => 1,
                'is_taxonomy'  => 1,
            ],
        ];
        update_post_meta($product_id, '_product_attributes', $product_attributes);
        echo "<li class='ok'>✓ Атрибуты товара зарегистрированы</li>";

        // Создать вариации
        $variations_created = 0;
        $variations_updated = 0;

        foreach ($variants_resolved as $index => $variant) {
            $size_slug = $variant['size_slug'];

            // Проверить — вариация с этим сочетанием уже есть?
            $existing_variation_id = 0;
            $children = get_posts([
                'post_type'   => 'product_variation',
                'post_parent' => $product_id,
                'post_status' => ['publish', 'private'],
                'numberposts' => -1,
                'fields'      => 'ids',
            ]);
            foreach ($children as $child_id) {
                $meta_color = get_post_meta($child_id, 'attribute_fabric_color', true);
                $meta_size  = get_post_meta($child_id, "attribute_{$attr_taxonomy}", true);
                if ($meta_color === $color_slug && $meta_size === $size_slug) {
                    $existing_variation_id = $child_id;
                    break;
                }
            }

            if ($existing_variation_id) {
                $variation_id = $existing_variation_id;
                $variations_updated++;
                echo "<li class='ok'>✓ Вариация {$color_name}/{$variant['size_label']} существует (ID: {$variation_id}) — обновлена</li>";
            } else {
                $variation_id = wp_insert_post([
                    'post_type'   => 'product_variation',
                    'post_status' => 'publish',
                    'post_parent' => $product_id,
                    'post_title'  => "{$item['name']} — {$color_name} / {$variant['size_label']}",
                    'menu_order'  => $index,
                ]);
                if (is_wp_error($variation_id)) {
                    echo "<li class='err'>✗ Не удалось создать вариацию {$variant['size_label']}: " . esc_html($variation_id->get_error_message()) . "</li>";
                    continue;
                }
                $variations_created++;
                $total_variations++;
                echo "<li class='info'>+ Вариация {$color_name}/{$variant['size_label']} создана (ID: {$variation_id})</li>";
            }

            // Записать мета вариации
            update_post_meta($variation_id, '_price',                           $variant['price']);
            update_post_meta($variation_id, '_regular_price',                   $variant['regular_price']);
            update_post_meta($variation_id, '_sale_price',                      $variant['price']);
            update_post_meta($variation_id, '_sku',                             "LL-{$color_slug}-{$item['slug']}-{$variant['sku_suffix']}");
            update_post_meta($variation_id, '_manage_stock',                    'no');
            update_post_meta($variation_id, '_stock_status',                    'instock');
            update_post_meta($variation_id, '_virtual',                         'no');
            update_post_meta($variation_id, 'attribute_fabric_color',           $color_slug);
            update_post_meta($variation_id, "attribute_{$attr_taxonomy}",       $size_slug);
        }

        // Синхронизировать цены родителя
        WC_Product_Variable::sync($product_id);
        wc_delete_product_transients($product_id);

        $created_str = $variations_created > 0 ? "+{$variations_created} созданы" : '';
        $updated_str = $variations_updated > 0 ? "{$variations_updated} обновлены" : '';
        $summary     = implode(', ', array_filter([$created_str, $updated_str]));
        echo "<li class='ok'>✓ Вариации: {$summary}</li>";

        $report_products[] = [
            'id'         => $product_id,
            'name'       => $item['name'],
            'type'       => 'variable',
            'variations' => $variations_created + $variations_updated,
        ];
    }

    echo '</ul>';
}

// ===== ФАЗА 3: ОТЧЁТ =====
?>

<h2>Итог</h2>
<table>
    <tr><th>Цвет</th><th>Товар</th><th>ID</th><th>Тип</th><th>Вариации</th><th>Ссылка</th></tr>
    <?php foreach ($report_products as $rp): ?>
    <tr>
        <td><?php echo esc_html($color_name); ?></td>
        <td><?php echo esc_html($rp['name']); ?></td>
        <td><?php echo $rp['id']; ?></td>
        <td><?php echo $rp['type']; ?></td>
        <td><?php echo $rp['variations'] > 0 ? $rp['variations'] : '—'; ?></td>
        <td><a href="<?php echo get_admin_url(null, 'post.php?post=' . $rp['id'] . '&action=edit'); ?>" target="_blank">Открыть в админке</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<p>
    <strong>Товаров создано:</strong> <?php echo $total_created; ?> &nbsp;
    <strong>Товаров обновлено:</strong> <?php echo $total_updated; ?> &nbsp;
    <strong>Вариаций создано:</strong> <?php echo $total_variations; ?>
</p>

<?php if (!empty($report_warnings)): ?>
<h2 style="color:#a06000">Предупреждения (<?php echo count($report_warnings); ?>)</h2>
<ul>
    <?php foreach ($report_warnings as $w): ?>
    <li class="warn">⚠ <?php echo esc_html($w); ?></li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p class="ok">✓ Предупреждений нет.</p>
<?php endif; ?>

<h2>Ссылки для проверки</h2>
<ul>
    <li><a href="<?php echo get_admin_url(null, 'edit.php?post_type=product'); ?>" target="_blank">Все товары в админке</a></li>
    <li><a href="<?php echo get_admin_url(null, 'edit-tags.php?taxonomy=product_cat&post_type=product'); ?>" target="_blank">Категории товаров</a></li>
    <li><a href="<?php echo get_admin_url(null, 'edit-tags.php?taxonomy=fabric_color&post_type=product'); ?>" target="_blank">Цвета ткани</a></li>
    <li><a href="<?php echo site_url('/shop/'); ?>" target="_blank">Магазин на фронте</a></li>
</ul>

<p style="color:#999;margin-top:3rem;font-size:12px">
    После успешного импорта всех цветов этот файл можно удалить:<br>
    <code>wp-content/themes/loraleya/tools/import-products.php</code>
</p>

</body>
</html>
