<?php
/**
 * LoraLeya — диагностика: страницы (для поиска индив. заказа) + теги цветов.
 * ТОЛЬКО ЧТЕНИЕ. Ничего не меняет.
 * Запуск: https://loraleya.ru/wp-content/themes/loraleya/tools/diag-pages-tags.php
 */
$wp_load = __DIR__ . '/../../../../../wp-load.php';
if (!file_exists($wp_load)) $wp_load = __DIR__ . '/../../../../wp-load.php';
if (!file_exists($wp_load)) die('wp-load.php не найден');
require_once $wp_load;
if (!current_user_can('manage_options')) { status_header(403); die('Access denied'); }

header('Content-Type: text/html; charset=utf-8');
if (ob_get_level()) ob_end_clean();
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Диагностика</title>
<style>
body{font-family:monospace;font-size:13px;padding:2rem;background:#f5f5f5;line-height:1.5}
h1{color:#333}h2{color:#555;margin-top:1.5rem;border-bottom:1px solid #ccc;padding-bottom:4px}
table{border-collapse:collapse;margin:1rem 0}td,th{border:1px solid #ccc;padding:4px 10px}th{background:#eee}
.hit{background:#d0f0d0;font-weight:bold}.miss{color:#a00}.ok{color:#2a7a2a}
</style></head><body>
<h1>LoraLeya — Диагностика страниц и тегов</h1>
<p><strong>Дата:</strong> <?php echo date('Y-m-d H:i:s'); ?> | Только чтение, ничего не меняется.</p>

<h2>1. ВСЕ СТРАНИЦЫ (page) — ищем индивидуальный заказ</h2>
<p>Ссылки в контенте ведут на <code>/individualnyy-zakaz/</code>. Ищем, есть ли такая страница и какой у неё реальный slug.</p>
<table>
<tr><th>ID</th><th>slug (post_name)</th><th>Заголовок</th><th>Статус</th></tr>
<?php
$pages = get_posts([
    'post_type'      => 'page',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);
foreach ($pages as $p) {
    // подсветим всё, что похоже на индивидуальный заказ
    $hit = (stripos($p->post_name, 'individ') !== false || stripos($p->post_name, 'zakaz') !== false
            || stripos($p->post_title, 'индивид') !== false || stripos($p->post_title, 'заказ') !== false);
    $cls = $hit ? ' class="hit"' : '';
    echo "<tr{$cls}><td>{$p->ID}</td><td>" . esc_html($p->post_name) . "</td><td>" . esc_html($p->post_title) . "</td><td>" . esc_html($p->post_status) . "</td></tr>";
}
?>
</table>
<?php
// Проверка прямого совпадения
$target = get_page_by_path('individualnyy-zakaz');
if ($target) {
    echo '<p class="ok">✅ Страница со slug <code>individualnyy-zakaz</code> СУЩЕСТВУЕТ (ID ' . $target->ID . ', статус ' . esc_html($target->post_status) . '). Ссылки должны работать — проверь статус (publish?).</p>';
} else {
    echo '<p class="miss">❌ Страницы со slug <code>individualnyy-zakaz</code> НЕТ. Вот почему ссылки дают 404. Смотри подсвеченные строки выше — там реальный slug страницы индив. заказа (если она вообще создана).</p>';
}
?>

<h2>2. ТЕГИ ЦВЕТОВ — что реально в массиве $colors_data</h2>
<p>Проверяем, применился ли багфикс тегов. Читаем term-meta и сверяем. (Теги хранятся в шаблоне-хардкоде, но проверим и реальный вывод.)</p>
<p><strong>Внимание:</strong> теги (<code>tags</code>) и сценарии (<code>scenarios</code>) лежат в хардкод-массиве <code>$colors_data</code> внутри <code>taxonomy-pa_fabric_color.php</code>, а не в БД — этот скрипт их прочитать из БД не может. Поэтому ниже выведем то, что доступно из БД (seo-поля), а теги нужно проверить через grep в файле (см. инструкцию Бориса).</p>
<table>
<tr><th>slug</th><th>seo_title (из БД, должен быть заполнен после E4)</th></tr>
<?php
$color_slugs = ['bezhevyj','belyj','biryuza','blek-zoloto','bronza','goluboj','grafit','zelenyj',
    'melanzh-zoloto','melanzh-serebro','melanzh-seryj','melanzh-chernyj','platina','serebro',
    'sirenevyj','temno-biryuzovyj','fioletovyj'];
foreach ($color_slugs as $slug) {
    $term = get_term_by('slug', $slug, 'pa_fabric_color');
    if (!$term) { echo "<tr><td>{$slug}</td><td class='miss'>термин не найден</td></tr>"; continue; }
    $st = get_term_meta($term->term_id, 'seo_title', true);
    echo "<tr><td>{$slug}</td><td>" . esc_html($st ?: '— пусто —') . "</td></tr>";
}
?>
</table>

<p style="color:#999;margin-top:2rem;font-size:12px">
Скрипт только читает БД. После использования можно удалить: tools/diag-pages-tags.php
</p>
</body></html>
