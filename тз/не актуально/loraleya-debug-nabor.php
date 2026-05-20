<?php
/**
 * Диагностика вариаций товара 50 (Готовый набор).
 * Показывает что get_variation_attributes() возвращает для каждой вариации.
 *
 * URL: https://loraleya.ru/loraleya-debug-nabor.php?key=ll2026nabor
 * УДАЛИТЬ файл сразу после использования.
 */

if (!isset($_GET['key']) || $_GET['key'] !== 'll2026nabor') {
    http_response_code(403);
    die('Forbidden');
}

define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/wp-load.php';

global $wpdb;

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family: monospace; font-size: 13px; line-height: 1.5; background: #f5f5f5; padding: 20px; white-space: pre-wrap;">';

echo "<h2>ДИАГНОСТИКА Вариаций Готового набора (товар 50)</h2>\n";
echo str_repeat('=', 80) . "\n\n";

// 1. Что лежит в БД для каждого термина pa_razmer-nabora
echo "<h3>1. Термины таксономии pa_razmer-nabora в БД</h3>\n";
$terms = $wpdb->get_results("
    SELECT t.term_id, t.name, t.slug, HEX(t.slug) AS slug_hex, LENGTH(t.slug) AS slug_len
    FROM {$wpdb->terms} t
    INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
    WHERE tt.taxonomy = 'pa_razmer-nabora'
");
echo sprintf("%-8s | %-12s | %-12s | %-4s | %s\n", 'term_id', 'name', 'slug', 'len', 'HEX (UTF-8 байты)');
echo str_repeat('-', 90) . "\n";
foreach ($terms as $t) {
    echo sprintf("%-8s | %-12s | %-12s | %-4s | %s\n",
        $t->term_id, $t->name, $t->slug, $t->slug_len, $t->slug_hex);
}
echo "\n";

// 2. Что возвращает get_variation_attributes() для каждой вариации
echo "<h3>2. get_variation_attributes() для каждой вариации товара 50</h3>\n";
$product = wc_get_product(50);
if (!$product) {
    echo "Товар 50 не найден\n";
} else {
    foreach ($product->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) continue;

        echo "--- Вариация #{$variation_id} ({$variation->get_name()}) ---\n";
        $attrs = $variation->get_variation_attributes();
        foreach ($attrs as $key => $value) {
            $value_hex = bin2hex($value);
            echo sprintf("  %s = '%s' (len=%d, HEX=%s)\n",
                $key, $value, strlen($value), $value_hex);
        }
        echo "\n";
    }
}

// 3. Что лежит в wp_postmeta напрямую
echo "<h3>3. wp_postmeta у вариаций товара 50 (только attribute_*)</h3>\n";
$variation_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = 50 AND post_type = 'product_variation'");
foreach ($variation_ids as $vid) {
    echo "--- Вариация #{$vid} ---\n";
    $meta = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value, HEX(meta_value) AS hex FROM {$wpdb->postmeta}
         WHERE post_id = %d AND meta_key LIKE 'attribute_%'",
        $vid
    ));
    foreach ($meta as $m) {
        echo sprintf("  %s = '%s' (len=%d, HEX=%s)\n",
            $m->meta_key, $m->meta_value, strlen($m->meta_value), $m->hex);
    }
    echo "\n";
}

// 4. Проверка — что вернёт наш хелпер
echo "<h3>4. Тест loraleya_find_variation_id()</h3>\n";
if (function_exists('loraleya_find_variation_id')) {
    $tests = [
        ['biryuza', '4п-140', 'razmer-nabora'],
        ['biryuza', '4п-175', 'razmer-nabora'],
        ['biryuza', '6п-140', 'razmer-nabora'],
        ['biryuza', '6п-175', 'razmer-nabora'],
    ];
    foreach ($tests as [$color, $size, $tax]) {
        $vid = loraleya_find_variation_id(50, $color, $size, $tax);
        echo sprintf("loraleya_find_variation_id(50, '%s', '%s', '%s') = %d\n",
            $color, $size, $tax, $vid);
    }
} else {
    echo "Функция loraleya_find_variation_id не найдена\n";
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "ПОСЛЕ ПОЛУЧЕНИЯ СКРИНА — УДАЛИ ФАЙЛ /loraleya-debug-nabor.php\n";
echo '</pre>';