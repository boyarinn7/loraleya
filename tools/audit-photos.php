<?php
/**
 * LoraLeya — аудит фото в Media Library.
 *
 * Запуск: https://loraleya.ru/wp-content/themes/loraleya/tools/audit-photos.php
 * Требует авторизации администратора.
 * Только читает БД — ничего не меняет.
 */

set_time_limit(120);
ini_set('memory_limit', '256M');

// ===== ЗАГРУЗКА WORDPRESS =====
$wp_load = __DIR__ . '/../../../../../wp-load.php';
if (!file_exists($wp_load)) {
    $wp_load = __DIR__ . '/../../../../wp-load.php';
}
if (!file_exists($wp_load)) {
    die('Не найден wp-load.php. Проверьте путь к файлу скрипта.');
}
require_once $wp_load;

// ===== БЕЗОПАСНОСТЬ =====
if (!current_user_can('manage_options')) {
    wp_die('Нет прав. Войдите как администратор.');
}

// ===== КАРТА SLUG → ПРЕФИКС =====
$color_map = [
    'bezhevyj'         => 'bezheviy',
    'belyj'            => 'beliy',
    'biryuza'          => 'biruza',
    'blek-zoloto'      => 'blek-zoloto',
    'bronza'           => 'bronza',
    'goluboj'          => 'goluboy',
    'grafit'           => 'grafit',
    'zelenyj'          => 'zeleniy',
    'melanzh-zoloto'   => 'melanzh-zoloto',
    'melanzh-serebro'  => 'melanzh-serebro',
    'melanzh-seryj'    => 'melanzh-seriy',
    'melanzh-chernyj'  => 'melanzh-cherniy',
    'platina'          => 'platina',
    'serebro'          => 'serebro',
    'sirenevyj'        => 'sireneviy',
    'temno-biryuzovyj' => 'temno-biruza',
    'fioletovyj'       => 'fioletoviy',
];

// ===== 12 ТИПОВ =====
$types = [
    'macro-faktura',
    'macro-pereliv',
    'macro-strochka',
    'nabor-4-140',
    'nabor-4-175',
    'nabor-6-240',
    'nabor-6-300',
    'dorozhka',
    'skatert',
    'salfetka-tsvetok',
    'kuvert-vert',
    'kuvert',
];

// Короткие метки для заголовков таблицы
$type_labels = [
    'macro-faktura'    => 'macro-f',
    'macro-pereliv'    => 'macro-p',
    'macro-strochka'   => 'macro-s',
    'nabor-4-140'      => 'n-4-140',
    'nabor-4-175'      => 'n-4-175',
    'nabor-6-240'      => 'n-6-240',
    'nabor-6-300'      => 'n-6-300',
    'dorozhka'         => 'dorozhka',
    'skatert'          => 'skatert',
    'salfetka-tsvetok' => 'salf-tsv',
    'kuvert-vert'      => 'kuv-vert',
    'kuvert'           => 'kuvert',
];

// ===== ФУНКЦИЯ ПОИСКА ATTACHMENT ПО ИМЕНИ =====
function loraleya_audit_find_attachment($name) {
    global $wpdb;
    // Ищем по post_name (slug) — это самое надёжное
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT p.ID, p.post_name, p.post_title
         FROM {$wpdb->posts} p
         WHERE p.post_type = 'attachment'
           AND p.post_status = 'inherit'
           AND (p.post_name = %s OR p.post_title = %s)
         LIMIT 1",
        $name, $name
    ));
    return $result ?: null;
}

// ===== СБОР ДАННЫХ =====
$matrix  = []; // [slug][type] = ['found'=>bool, 'id'=>int, 'w'=>int, 'h'=>int, 'name'=>str]
$gaps    = []; // [slug] = [type1, type2, ...]
$all_expected_names = []; // все ожидаемые имена для поиска осиротевших

foreach ($color_map as $slug => $prefix) {
    $matrix[$slug] = [];
    $gaps[$slug]   = [];
    foreach ($types as $type) {
        $expected      = $prefix . '-' . $type;
        $expected_scaled = $prefix . '-' . $type . '-scaled';
        $all_expected_names[] = $expected;
        $all_expected_names[] = $expected_scaled;

        $attachment = loraleya_audit_find_attachment($expected);
        if (!$attachment) {
            $attachment = loraleya_audit_find_attachment($expected_scaled);
        }

        if ($attachment) {
            $meta = wp_get_attachment_metadata($attachment->ID);
            $matrix[$slug][$type] = [
                'found' => true,
                'id'    => $attachment->ID,
                'name'  => $attachment->post_name,
                'w'     => $meta['width']  ?? 0,
                'h'     => $meta['height'] ?? 0,
            ];
        } else {
            $matrix[$slug][$type] = ['found' => false];
            $gaps[$slug][]        = $type;
        }
    }
}

// ===== СЧЁТЧИКИ =====
$total_needed  = count($color_map) * count($types); // 204
$total_found   = 0;
foreach ($matrix as $slug => $row) {
    foreach ($row as $type => $cell) {
        if ($cell['found']) $total_found++;
    }
}
$total_gaps = $total_needed - $total_found;
$pct        = round($total_found / $total_needed * 100);

// ===== ОСИРОТЕВШИЕ =====
global $wpdb;
$all_prefixes = array_values($color_map);
$orphans = [];

// Все attachments, чьё имя начинается с любого префикса
$prefix_likes = implode(' OR ', array_map(
    fn($p) => $wpdb->prepare("p.post_name LIKE %s", $p . '-%'),
    $all_prefixes
));
$all_color_attachments = $wpdb->get_results(
    "SELECT p.ID, p.post_name, p.post_title
     FROM {$wpdb->posts} p
     WHERE p.post_type = 'attachment'
       AND p.post_status = 'inherit'
       AND ({$prefix_likes})
     ORDER BY p.post_name"
);

foreach ($all_color_attachments as $att) {
    if (!in_array($att->post_name, $all_expected_names)) {
        $orphans[] = $att;
    }
}

// ===== ДИАГНОСТИКА СКАТЕРТИ =====
$skatert_all = $wpdb->get_results(
    "SELECT p.ID, p.post_name, p.post_title
     FROM {$wpdb->posts} p
     WHERE p.post_type = 'attachment'
       AND p.post_status = 'inherit'
       AND (p.post_name LIKE '%skatert%' OR p.post_title LIKE '%skatert%')
     ORDER BY p.post_name"
);

// ===== ОБЩЕЕ КОЛИЧЕСТВО ATTACHMENTS =====
$total_attachments = (int) $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->posts}
     WHERE post_type = 'attachment' AND post_status = 'inherit'"
);

// ===== ВЫВОД =====
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Аудит фото LoraLeya</title>
<style>
body { font-family: monospace; font-size: 13px; background: #f5f5f5; padding: 20px; color: #222; }
h2 { font-size: 16px; margin: 2rem 0 0.5rem; border-bottom: 2px solid #333; padding-bottom: 4px; }
h3 { font-size: 14px; margin: 1.5rem 0 0.4rem; color: #555; }
table { border-collapse: collapse; font-size: 11px; margin-bottom: 1rem; }
th, td { border: 1px solid #ccc; padding: 3px 6px; white-space: nowrap; }
th { background: #ddd; }
td.ok  { background: #d4edda; color: #155724; }
td.no  { background: #f8d7da; color: #721c24; text-align: center; }
td.name { font-weight: bold; background: #eee; }
.gap-list { margin: 0.2rem 0 0.8rem; padding-left: 1.5rem; }
.gap-list li { margin: 0.1rem 0; }
.stat { font-size: 14px; font-weight: bold; margin: 0.3rem 0; }
.ok-stat { color: green; }
.no-stat { color: red; }
pre { white-space: pre-wrap; word-break: break-all; }
</style>
</head>
<body>

<h1>📸 АУДИТ ФОТО LORALEYA</h1>
<p>Дата: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp; Всего attachment'ов в библиотеке: <strong><?php echo $total_attachments; ?></strong></p>

<!-- ===== 1. МАТРИЦА ===== -->
<h2>1. МАТРИЦА (17 цветов × 12 типов)</h2>
<table>
<tr>
    <th>slug (префикс)</th>
    <?php foreach ($types as $type): ?>
        <th><?php echo $type_labels[$type]; ?></th>
    <?php endforeach; ?>
    <th>✅</th><th>❌</th>
</tr>
<?php foreach ($color_map as $slug => $prefix):
    $row_found = 0;
    $row_miss  = 0;
    foreach ($matrix[$slug] as $cell) {
        $cell['found'] ? $row_found++ : $row_miss++;
    }
?>
<tr>
    <td class="name"><?php echo $slug; ?><br><small style="color:#888"><?php echo $prefix; ?></small></td>
    <?php foreach ($types as $type):
        $cell = $matrix[$slug][$type];
        if ($cell['found']):
            $dim = ($cell['w'] && $cell['h']) ? $cell['w'] . '×' . $cell['h'] : 'нет размеров';
    ?>
        <td class="ok" title="ID:<?php echo $cell['id']; ?> name:<?php echo $cell['name']; ?>">✅<br><small><?php echo $dim; ?></small></td>
    <?php else: ?>
        <td class="no">❌</td>
    <?php endif; ?>
    <?php endforeach; ?>
    <td style="text-align:center;font-weight:bold;color:green"><?php echo $row_found; ?></td>
    <td style="text-align:center;font-weight:bold;color:red"><?php echo $row_miss; ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- ===== 2. СПИСОК ПРОБЕЛОВ ===== -->
<h2>2. СПИСОК ПРОБЕЛОВ (по цветам)</h2>
<?php
$has_any_gap = false;
foreach ($color_map as $slug => $prefix):
    if (empty($gaps[$slug])) continue;
    $has_any_gap = true;
?>
<p><strong><?php echo $slug; ?></strong> (<?php echo $prefix; ?>) — не хватает:</p>
<ul class="gap-list">
    <?php foreach ($gaps[$slug] as $g): ?>
        <li><?php echo $g; ?> → ожидается: <code><?php echo $prefix . '-' . $g; ?></code></li>
    <?php endforeach; ?>
</ul>
<?php endforeach;
if (!$has_any_gap) echo '<p style="color:green">✅ Все 204 фото найдены!</p>';
?>

<!-- ===== 3. СКАТЕРТЬ — ДИАГНОСТИКА ===== -->
<h2>3. СКАТЕРТЬ — диагностика</h2>

<h3>platina-skatert (запрашивается на главной)</h3>
<?php
$platina_skatert = loraleya_audit_find_attachment('platina-skatert');
$platina_skatert_scaled = loraleya_audit_find_attachment('platina-skatert-scaled');
if ($platina_skatert): ?>
    <p>✅ <strong>platina-skatert</strong> найден: ID <?php echo $platina_skatert->ID; ?>, slug: <?php echo $platina_skatert->post_name; ?></p>
<?php elseif ($platina_skatert_scaled): ?>
    <p>✅ <strong>platina-skatert-scaled</strong> найден (фолбэк): ID <?php echo $platina_skatert_scaled->ID; ?></p>
<?php else: ?>
    <p style="color:red">❌ <strong>platina-skatert</strong> НЕ найден (ни основной, ни -scaled)</p>
    <?php
    // Поиск похожих
    $similar = $wpdb->get_results(
        "SELECT ID, post_name, post_title FROM {$wpdb->posts}
         WHERE post_type='attachment' AND post_status='inherit'
           AND (post_name LIKE 'platina-skatert%' OR post_name LIKE '%platina%skatert%'
             OR post_title LIKE 'platina-skatert%' OR post_title LIKE '%platina%skatert%'
             OR post_name LIKE 'skatert-platina%' OR post_name LIKE 'platina%skater%')
         ORDER BY post_name"
    );
    if ($similar): ?>
        <p>Похожие найдены:</p>
        <ul>
        <?php foreach ($similar as $s): ?>
            <li>ID <?php echo $s->ID; ?> — slug: <code><?php echo $s->post_name; ?></code>, title: <?php echo esc_html($s->post_title); ?></li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Похожих не найдено.</p>
    <?php endif;
endif; ?>

<h3>Все attachment'ы со словом «skatert» в имени/заголовке</h3>
<?php if ($skatert_all): ?>
<table>
<tr><th>ID</th><th>post_name (slug)</th><th>post_title</th></tr>
<?php foreach ($skatert_all as $s): ?>
<tr>
    <td><?php echo $s->ID; ?></td>
    <td><code><?php echo esc_html($s->post_name); ?></code></td>
    <td><?php echo esc_html($s->post_title); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p style="color:red">❌ В библиотеке нет ни одного attachment'а с «skatert» в имени.</p>
<?php endif; ?>

<!-- ===== 4. ОСИРОТЕВШИЕ ФАЙЛЫ ===== -->
<h2>4. ОСИРОТЕВШИЕ ФАЙЛЫ (имя начинается с префикса цвета, но не совпадает ни с одним ожидаемым)</h2>
<?php if ($orphans): ?>
<p>Найдено <strong><?php echo count($orphans); ?></strong> осиротевших файлов:</p>
<table>
<tr><th>ID</th><th>post_name (slug)</th><th>post_title</th></tr>
<?php foreach ($orphans as $o): ?>
<tr>
    <td><?php echo $o->ID; ?></td>
    <td><code><?php echo esc_html($o->post_name); ?></code></td>
    <td><?php echo esc_html($o->post_title); ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p style="color:green">✅ Осиротевших файлов не найдено.</p>
<?php endif; ?>

<!-- ===== 5. ИТОГ ===== -->
<h2>5. ИТОГ</h2>
<p class="stat">Всего нужно: <?php echo $total_needed; ?> (17×12)</p>
<p class="stat ok-stat">В наличии: <?php echo $total_found; ?></p>
<p class="stat no-stat">Пробелов: <?php echo $total_gaps; ?></p>
<p class="stat">Готовность: <?php echo $pct; ?>%</p>
<p style="margin-top:2rem;color:#888;font-size:11px">
    Скрипт только читает БД. Ничего не изменено. После получения отчёта файл можно оставить — повторный запуск безопасен.
</p>

</body>
</html>
