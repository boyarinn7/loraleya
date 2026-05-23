<?php
/**
 * LoraLeya — диагностика после миграции fabric_color → pa_fabric_color
 *
 * КАК ЗАПУСТИТЬ:
 * 1. Загрузи этот файл в корень сайта (/public_html/ или аналогичное) через Диспетчер файлов WP
 * 2. Открой в браузере: https://loraleya.ru/loraleya-debug.php?key=ll2026migration
 * 3. Сделай скрин всего вывода
 * 4. УДАЛИ файл с сервера сразу после использования
 *
 * Файл НЕ трогает БД, только читает.
 */

// Простой ключ-защита — чтобы никто случайный не запустил
if (!isset($_GET['key']) || $_GET['key'] !== 'll2026migration') {
    http_response_code(403);
    die('Forbidden');
}

// Загружаем WordPress
define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/wp-load.php';

global $wpdb;

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family: monospace; font-size: 13px; line-height: 1.5; background: #f5f5f5; padding: 20px; white-space: pre-wrap;">';

echo "<h2>ДИАГНОСТИКА LoraLeya</h2>\n";
echo "Дата: " . date('Y-m-d H:i:s') . "\n";
echo "Префикс таблиц: {$wpdb->prefix}\n";
echo str_repeat('=', 80) . "\n\n";

// ---- 1. ТЕРМИНЫ и их таксономии ----
echo "<h3>1. ТЕРМИНЫ ЦВЕТОВ (должно быть 17 штук с taxonomy = 'pa_fabric_color')</h3>\n";
$sql = "SELECT t.term_id, t.name, t.slug, tt.taxonomy, tt.count, tt.term_taxonomy_id
        FROM {$wpdb->terms} t
        INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy IN ('fabric_color', 'pa_fabric_color')
        ORDER BY tt.taxonomy, t.name";
$terms = $wpdb->get_results($sql);
echo sprintf("%-8s | %-22s | %-22s | %-18s | %-5s\n", 'term_id', 'name', 'slug', 'taxonomy', 'count');
echo str_repeat('-', 90) . "\n";
foreach ($terms as $t) {
    echo sprintf("%-8s | %-22s | %-22s | %-18s | %-5s\n",
        $t->term_id, $t->name, $t->slug, $t->taxonomy, $t->count);
}
echo "\n";

// ---- 2. РЕЕСТР АТРИБУТОВ WC ----
echo "<h3>2. РЕЕСТР WC-АТРИБУТОВ (таблица woocommerce_attribute_taxonomies)</h3>\n";
$attrs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
echo sprintf("%-5s | %-25s | %-20s | %-8s | %-12s\n", 'id', 'name', 'label', 'type', 'orderby');
echo str_repeat('-', 80) . "\n";
foreach ($attrs as $a) {
    echo sprintf("%-5s | %-25s | %-20s | %-8s | %-12s\n",
        $a->attribute_id, $a->attribute_name, $a->attribute_label, $a->attribute_type, $a->attribute_orderby);
}
echo "\n";

// ---- 3. РОДИТЕЛЬСКИЙ ТОВАР 39 — его мета _product_attributes ----
echo "<h3>3. ТОВАР 39 (Дорожка) — мета _product_attributes</h3>\n";
$attrs_meta = get_post_meta(39, '_product_attributes', true);
echo "Raw value (unserialized):\n";
echo var_export($attrs_meta, true) . "\n\n";

// ---- 4. ПРИВЯЗКА ТОВАРА 39 К ТЕРМИНАМ ----
echo "<h3>4. ТОВАР 39 — привязки к терминам через term_relationships</h3>\n";
$sql = "SELECT t.term_id, t.name, t.slug, tt.taxonomy, tr.term_taxonomy_id
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE tr.object_id = 39";
$relations = $wpdb->get_results($sql);
echo sprintf("%-8s | %-22s | %-22s | %-20s\n", 'term_id', 'name', 'slug', 'taxonomy');
echo str_repeat('-', 80) . "\n";
foreach ($relations as $r) {
    echo sprintf("%-8s | %-22s | %-22s | %-20s\n",
        $r->term_id, $r->name, $r->slug, $r->taxonomy);
}
echo "\n";

// ---- 5. ВАРИАЦИИ ТОВАРА 39 — их мета атрибутов ----
echo "<h3>5. ВАРИАЦИИ ТОВАРА 39 — мета attribute_* (ожидаем attribute_pa_fabric_color)</h3>\n";
$variations = $wpdb->get_results("SELECT ID, post_title, post_status FROM {$wpdb->posts}
                                   WHERE post_parent = 39 AND post_type = 'product_variation'
                                   ORDER BY ID");
foreach ($variations as $v) {
    echo "--- Вариация #{$v->ID} ({$v->post_title}, status={$v->post_status}) ---\n";
    $meta_keys = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta}
         WHERE post_id = %d AND (meta_key LIKE 'attribute_%' OR meta_key = '_price' OR meta_key = '_stock_status')",
        $v->ID
    ));
    foreach ($meta_keys as $m) {
        echo "  {$m->meta_key} = '{$m->meta_value}'\n";
    }
    echo "\n";
}

// ---- 6. ПРОВЕРКА: зарегистрирована ли pa_fabric_color в WC ----
echo "<h3>6. WC РЕЕСТР АТРИБУТОВ через WC API</h3>\n";
if (function_exists('wc_get_attribute_taxonomies')) {
    $wc_taxes = wc_get_attribute_taxonomies();
    foreach ($wc_taxes as $wt) {
        echo sprintf("id=%s name=%s label=%s → taxonomy name via wc_attribute_taxonomy_name: %s\n",
            $wt->attribute_id, $wt->attribute_name, $wt->attribute_label,
            wc_attribute_taxonomy_name($wt->attribute_name)
        );
    }
} else {
    echo "WC API не загружен\n";
}
echo "\n";

// ---- 7. ТРАНЗИЕНТЫ WC (могут кэшировать старое состояние) ----
echo "<h3>7. ТРАНЗИЕНТЫ WC в options (кэш атрибутов)</h3>\n";
$transients = $wpdb->get_results("SELECT option_name, LENGTH(option_value) AS size FROM {$wpdb->options}
                                   WHERE option_name LIKE '%wc_attribute%'
                                      OR option_name LIKE '%product_attributes%'
                                      OR option_name LIKE '%_transient_wc%'
                                   LIMIT 30");
foreach ($transients as $tr) {
    echo "  {$tr->option_name} (size={$tr->size})\n";
}
echo "\n";

// ---- 8. REWRITE RULES — есть ли pa_fabric_color ----
echo "<h3>8. REWRITE RULES (ищем pa_fabric_color и /color/)</h3>\n";
$rules = get_option('rewrite_rules');
if (is_array($rules)) {
    $found = [];
    foreach ($rules as $pattern => $target) {
        if (strpos($pattern, 'color') !== false || strpos($target, 'fabric_color') !== false) {
            $found[$pattern] = $target;
        }
    }
    if ($found) {
        foreach ($found as $p => $t) {
            echo "  $p → $t\n";
        }
    } else {
        echo "  НЕТ правил с 'color' или 'fabric_color' — rewrite rules не перегенерированы!\n";
    }
} else {
    echo "  rewrite_rules не массив\n";
}
echo "\n";

echo str_repeat('=', 80) . "\n";
echo "Готово. Скинь скрин всего вывода.\n";
echo "ПОСЛЕ ПОЛУЧЕНИЯ СКРИНА — УДАЛИ ФАЙЛ /loraleya-debug.php\n";
echo '</pre>';