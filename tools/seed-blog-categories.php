<?php
require_once(dirname(__FILE__, 2) . '/wp-load.php');
if (!current_user_can('manage_options')) { wp_die('Доступ запрещён'); }
$dry = !isset($_GET['run']);

$cats = [
    ['name' => 'Энциклопедия премиум-сервировки', 'slug' => 'entsiklopediya'],
    ['name' => 'Материалы и уход',                 'slug' => 'materialy-i-ukhod'],
    ['name' => 'Праздничная сервировка',           'slug' => 'prazdnichnaya-servirovka'],
    // Расширение до 6 хабов — по решению SEO-Клода (раскомментировать при подтверждении):
    // ['name' => 'Салфетки и складывание', 'slug' => 'salfetki-i-skladyvanie'],
    // ['name' => 'Сервировка-гайды',        'slug' => 'servirovka-gajdy'],
    // ['name' => 'Подарки',                 'slug' => 'podarki'],
];

echo '<pre>';
foreach ($cats as $c) {
    if (term_exists($c['slug'], 'category')) { echo "= уже есть: {$c['slug']}\n"; continue; }
    if ($dry) { echo "[dry] создал бы: {$c['name']} ({$c['slug']})\n"; continue; }
    $res = wp_insert_term($c['name'], 'category', ['slug' => $c['slug']]);
    echo is_wp_error($res)
        ? "! ошибка {$c['slug']}: " . $res->get_error_message() . "\n"
        : "+ создана: {$c['slug']}\n";
}
echo $dry ? "\nDRY-RUN. Добавь ?run=1 для применения.\n" : "\nГОТОВО. Удали файл после запуска.\n";
echo '</pre>';
