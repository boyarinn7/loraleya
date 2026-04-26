<?php
/**
 * LoraLeya — одноразовый скрипт загрузки фото в WordPress Media Library.
 *
 * Запуск: https://loraleya.ru/wp-content/themes/loraleya/tools/import-photos.php
 * Требует авторизации администратора.
 *
 * Сканирует wp-content/uploads/_import-photos/{color}/ рекурсивно.
 * Идемпотентен: повторный запуск не создаёт дубликаты.
 * После успешного импорта папку _import-photos/ удаляет заказчик вручную.
 */

set_time_limit(0);
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
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

// ===== БЕЗОПАСНОСТЬ =====
if (!current_user_can('manage_options')) {
    status_header(403);
    die('Access denied');
}

// ===== ПАПКА ИМПОРТА =====
$import_dir = WP_CONTENT_DIR . '/uploads/_import-photos';

if (!is_dir($import_dir)) {
    die("Папка не найдена: {$import_dir}<br>Создайте её и загрузите фото через Диспетчер файлов.");
}

$skip_folder  = 'biruza';
$allowed_exts = ['webp', 'jpg', 'jpeg', 'png'];

// ===== СЧЁТЧИКИ =====
$stat_loaded  = 0;
$stat_skipped_exists  = 0;
$stat_skipped_biruza  = 0;
$stat_skipped_prefix  = 0;
$stat_errors  = 0;

// ===== HTML НАЧАЛО =====
header('Content-Type: text/html; charset=utf-8');
// Отключить сжатие для потокового вывода
if (ob_get_level()) ob_end_clean();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>LoraLeya — Импорт фото</title>
    <style>
        body { font-family: monospace; font-size: 13px; padding: 2rem; background: #f5f5f5; line-height: 1.6; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 2rem; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        .ok   { color: #2a7a2a; }
        .warn { color: #a06000; }
        .err  { color: #a00000; }
        .info { color: #0055aa; }
        ul { margin: 0.3rem 0 1rem 1.5rem; padding: 0; }
        li { margin: 2px 0; }
        table { border-collapse: collapse; margin: 1rem 0; }
        td, th { border: 1px solid #ccc; padding: 5px 12px; }
        th { background: #eee; }
    </style>
</head>
<body>
<h1>LoraLeya — Импорт фото в Media Library</h1>
<p><strong>Папка:</strong> <code><?php echo esc_html($import_dir); ?></code></p>
<p><strong>Исключена подпапка:</strong> <code><?php echo esc_html($skip_folder); ?>/</code> (уже загружена вручную)</p>
<hr>
<?php
flush();

// ===== СКАНИРОВАНИЕ ПОДПАПОК ЦВЕТОВ =====
$color_dirs = [];
foreach (scandir($import_dir) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $path = $import_dir . '/' . $entry;
    if (is_dir($path)) {
        $color_dirs[] = $entry;
    }
}
sort($color_dirs);

if (empty($color_dirs)) {
    echo "<p class='warn'>⚠ Подпапок не найдено в {$import_dir}. Загрузите файлы и повторите.</p>";
    echo '</body></html>';
    exit;
}

// ===== ОБРАБОТКА ПО ПОДПАПКАМ =====
foreach ($color_dirs as $color_folder) {
    $color_path = $import_dir . '/' . $color_folder;

    // Собрать файлы с нужными расширениями
    $files = [];
    foreach (scandir($color_path) as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts, true)) {
            $files[] = $file;
        }
    }
    sort($files);
    $file_count = count($files);

    echo "<h2>" . esc_html($color_folder) . " ({$file_count} " . _n('файл', 'файлов', $file_count, 'loraleya') . ")</h2><ul>";
    flush();

    foreach ($files as $filename) {
        $filepath  = $color_path . '/' . $filename;
        $basename  = pathinfo($filename, PATHINFO_FILENAME);
        $ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // --- Пропустить папку biruza ---
        if ($color_folder === $skip_folder) {
            echo "<li class='info'>⏭ " . esc_html($filename) . " → пропущен (папка biruza исключена)</li>";
            $stat_skipped_biruza++;
            flush();
            continue;
        }

        // --- Проверить что префикс файла совпадает с папкой ---
        if (strpos($basename, $color_folder . '-') !== 0 && $basename !== $color_folder) {
            echo "<li class='warn'>⚠ " . esc_html($filename) . " → пропущен (префикс не совпадает с папкой «{$color_folder}»)</li>";
            $stat_skipped_prefix++;
            flush();
            continue;
        }

        // --- Проверить что файл ещё не в Media Library (идемпотентность) ---
        $existing = get_posts([
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'title'       => $basename,
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);
        if (!empty($existing)) {
            echo "<li class='warn'>⚠ " . esc_html($filename) . " → пропущен (уже существует в Media Library, post_id " . $existing[0] . ")</li>";
            $stat_skipped_exists++;
            flush();
            continue;
        }

        // --- Загрузить через wp_handle_sideload ---
        $file_array = [
            'name'     => $filename,
            'tmp_name' => $filepath,
        ];

        // wp_handle_sideload перемещает файл — нам нужно работать с копией
        // Создаём временную копию
        $tmp_file = wp_tempnam($filename);
        if (!copy($filepath, $tmp_file)) {
            echo "<li class='err'>❌ " . esc_html($filename) . " → ошибка: не удалось создать временный файл</li>";
            $stat_errors++;
            flush();
            continue;
        }
        $file_array['tmp_name'] = $tmp_file;

        $overrides = ['test_form' => false, 'test_size' => true];
        $uploaded  = wp_handle_sideload($file_array, $overrides);

        if (isset($uploaded['error'])) {
            echo "<li class='err'>❌ " . esc_html($filename) . " → ошибка: " . esc_html($uploaded['error']) . "</li>";
            @unlink($tmp_file);
            $stat_errors++;
            flush();
            continue;
        }

        // --- Создать запись в Media Library ---
        $attachment = [
            'post_mime_type' => $uploaded['type'],
            'post_title'     => $basename,
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_excerpt'   => '',
        ];

        $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

        if (is_wp_error($attachment_id)) {
            echo "<li class='err'>❌ " . esc_html($filename) . " → ошибка: " . esc_html($attachment_id->get_error_message()) . "</li>";
            $stat_errors++;
            flush();
            continue;
        }

        // Генерировать миниатюры
        $metadata = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);

        echo "<li class='ok'>✅ " . esc_html($filename) . " → загружен (post_id {$attachment_id})</li>";
        $stat_loaded++;
        flush();
    }

    echo "</ul>";
    flush();
}

// ===== ИТОГОВАЯ СТАТИСТИКА =====
?>
<hr>
<h2>Итог</h2>
<table>
    <tr><th>Статус</th><th>Количество</th></tr>
    <tr><td class="ok">✅ Загружено</td><td><?php echo $stat_loaded; ?></td></tr>
    <tr><td class="warn">⚠ Уже существовало (пропущено)</td><td><?php echo $stat_skipped_exists; ?></td></tr>
    <tr><td class="info">⏭ Пропущено (папка biruza)</td><td><?php echo $stat_skipped_biruza; ?></td></tr>
    <tr><td class="warn">⚠ Пропущено (префикс ≠ папка)</td><td><?php echo $stat_skipped_prefix; ?></td></tr>
    <tr><td class="err">❌ Ошибки</td><td><?php echo $stat_errors; ?></td></tr>
</table>

<?php if ($stat_errors === 0 && $stat_loaded > 0): ?>
<p class="ok">✅ Импорт завершён без ошибок. Папку <code>_import-photos/</code> можно удалить через Диспетчер файлов.</p>
<?php elseif ($stat_errors > 0): ?>
<p class="err">❌ Есть ошибки — проверьте лог выше. Повторный запуск безопасен (дубликаты не создаются).</p>
<?php else: ?>
<p class="info">Новых файлов для загрузки не найдено.</p>
<?php endif; ?>

<p style="color:#999;margin-top:3rem;font-size:12px">
    После успешного импорта всех цветов этот файл можно удалить:<br>
    <code>wp-content/themes/loraleya/tools/import-photos.php</code>
</p>
</body>
</html>
