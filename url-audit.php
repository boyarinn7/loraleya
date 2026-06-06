<?php
require_once(dirname(__FILE__, 3) . '/wp-load.php');   // файл в wp-content/themes/ ; путь подогнать при необходимости
if (!current_user_can('manage_options')) { wp_die('Доступ запрещён'); }

echo '<pre style="font:14px monospace">';
echo "СТРУКТУРА ССЫЛОК: " . (get_option('permalink_structure') ?: '(по умолчанию)') . "\n";
echo "====================================================\n\n";

echo "=== СЦЕНАРИИ (CPT scenario) ===\n";
$sc = get_posts(['post_type' => 'scenario', 'numberposts' => -1, 'post_status' => 'publish']);
if ($sc) { foreach ($sc as $s) echo "  {$s->post_name}  →  " . get_permalink($s->ID) . "\n"; }
else { echo "  (нет опубликованных сценариев)\n"; }
echo "  archive → " . (get_post_type_archive_link('scenario') ?: '—') . "\n\n";

echo "=== ЦВЕТА (taxonomy pa_fabric_color, 3 примера) ===\n";
foreach (get_terms(['taxonomy' => 'pa_fabric_color', 'number' => 3, 'hide_empty' => false]) as $c)
    echo "  {$c->slug}  →  " . get_term_link($c) . "\n";
echo "\n";

echo "=== СТРАНИЦА ПАЛИТРЫ (если есть страница 'palette') ===\n";
$pal = get_page_by_path('palette');
echo $pal ? "  →  " . get_permalink($pal->ID) . "\n\n" : "  (страницы palette нет)\n\n";

echo "=== РУБРИКИ БЛОГА (category) ===\n";
foreach (get_terms(['taxonomy' => 'category', 'hide_empty' => false]) as $c)
    echo "  {$c->slug}  →  " . get_term_link($c) . "\n";
echo "\n";

echo "=== СТАТЬИ БЛОГА (post) ===\n";
foreach (get_posts(['post_type' => 'post', 'numberposts' => -1]) as $p)
    echo "  {$p->post_name}  →  " . get_permalink($p->ID) . "\n";
$pfp = get_option('page_for_posts');
echo "  страница записей → " . ($pfp ? get_permalink($pfp) : '—') . "\n\n";

echo "=== ТОВАРЫ (product) ===\n";
foreach (get_posts(['post_type' => 'product', 'numberposts' => -1]) as $p)
    echo "  [{$p->ID}] {$p->post_name}  →  " . get_permalink($p->ID) . "\n";
echo "\n";

echo "=== КАТЕГОРИИ ТОВАРОВ (product_cat) ===\n";
foreach (get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]) as $c)
    echo "  {$c->slug}  →  " . get_term_link($c) . "\n";
echo "\n";

echo "=== СТРАНИЦЫ (page) ===\n";
foreach (get_posts(['post_type' => 'page', 'numberposts' => -1, 'post_status' => 'publish']) as $p)
    echo "  {$p->post_name}  →  " . get_permalink($p->ID) . "\n";

echo '</pre>';
