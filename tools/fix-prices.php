<?php
/**
 * LoraLeya — фикс цен всех товаров (variable + simple).
 *
 * DRY-RUN (по умолчанию):
 *   https://loraleya.ru/wp-content/themes/loraleya/tools/fix-prices.php
 * LIVE (запись):
 *   https://loraleya.ru/wp-content/themes/loraleya/tools/fix-prices.php?live=1
 *
 * Цены из xlsx Куренковой (25.05.2026), подтверждены Борисом.
 *
 * Логика:
 *  - Меланж (slug цвета начинается с 'melanzh-') дороже мрамора на 7-17%.
 *  - sale = «Моя цена» Куренковой. regular = sale / 0.85, округление до 10₽.
 *  - Variable товары: цена в каждой вариации (по цвету+размеру).
 *  - Simple товары (салфетка, куверт): одна цена на товар, ПО МРАМОРУ (см. примечание).
 *
 * ПРИМЕЧАНИЕ ПО SIMPLE-ТОВАРАМ:
 * Салфетка и куверт — simple-товары, у них нет вариаций по цвету,
 * поэтому деление мрамор/меланж к ним не применимо (одна цена на товар).
 * Беру цену МРАМОРА (как нижнюю границу — справедливо для всех цветов).
 *
 * Идемпотентен. Slug-based. Не падает на отсутствующих товарах/размерах.
 */

set_time_limit(0);
ini_set('memory_limit', '256M');

$wp_load = __DIR__ . '/../../../../../wp-load.php';
if (!file_exists($wp_load)) $wp_load = __DIR__ . '/../../../../wp-load.php';
if (!file_exists($wp_load)) die('wp-load.php не найден');
require_once $wp_load;

if (!current_user_can('manage_options')) { status_header(403); die('Access denied'); }

$LIVE = isset($_GET['live']) && $_GET['live'] === '1';

/**
 * Карта цен. Тип товара определяет поведение.
 *
 *  'variable' — vars[size_slug] = [sale_мрамор, sale_меланж]
 *  'simple'   — single = sale (одна цена, ПО МРАМОРУ)
 */
$pricing = [
    'dorozhka-na-stol' => [
        'type' => 'variable',
        'size_taxonomy' => 'razmer-dorozhki',
        'vars' => [
            '140' => [980, 1150],
            '175' => [1100, 1250],
            '240' => [1220, 1390],
            '300' => [1380, 1500],
        ],
    ],
    'skatert' => [
        'type' => 'variable',
        'size_taxonomy' => 'razmer-skaterti',
        'vars' => [
            '175' => [1800, 2050],
            '220' => [2150, 2400],
            '240' => [2400, 2600],
        ],
    ],
    'salfetka-servirovochnaya' => [
        'type' => 'variable-color-only',
        'price_mr' => 350,
        'price_ml' => 375,
    ],
    'kuvert-dlya-priborov' => [
        'type' => 'variable-color-only',
        'price_mr' => 190,
        'price_ml' => 210,
    ],
    'gotovyj-nabor' => [
        'type' => 'variable',
        'size_taxonomy' => 'razmer-nabora',
        'vars' => [
            '2п-140' => [1700, 1900],
            '4п-140' => [2500, 2700],
            '4п-175' => [2600, 2800],
            '6п-240' => [3400, 3700],
            '6п-300' => [3500, 3900],
        ],
    ],
];

function calc_regular($sale) { return (int)(round($sale / 0.85 / 10) * 10); }

/**
 * Дорогая группа цвета (выше мрамора): меланж (4 цвета) + блек-золото.
 * По правилу Куренковой: блек-золото — дорогая жаккардовая ткань,
 * по цене идёт на уровне меланжа.
 */
function is_melanzh($color_slug) {
    return strpos($color_slug, 'melanzh-') === 0
        || $color_slug === 'blek-zoloto';
}

header('Content-Type: text/html; charset=utf-8');
if (ob_get_level()) ob_end_clean();
?>
<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Фикс цен LoraLeya</title>
<style>
body{font-family:monospace;font-size:12px;padding:1.5rem;background:#f5f5f5;line-height:1.5}
h1{color:#333}h2{color:#555;margin-top:1.5rem;border-bottom:1px solid #ccc;padding-bottom:4px}
table{border-collapse:collapse;margin:.5rem 0}td,th{border:1px solid #ccc;padding:3px 8px;font-size:11px}th{background:#eee}
.ok{color:#2a7a2a}.warn{color:#a06000}.err{color:#a00}.dry{color:#7a00a0}
.melanzh{background:#fff3e0}.mramor{background:#f0f4f0}.simple-row{background:#e8f0ff}
.mode{padding:.4rem 1rem;border-radius:4px;display:inline-block;font-weight:bold}
.mode-dry{background:#f0e0ff;color:#7a00a0}.mode-live{background:#d0f0d0;color:#2a7a2a}
</style></head><body>
<h1>LoraLeya — Фикс цен товаров</h1>
<p><span class="mode <?php echo $LIVE?'mode-live':'mode-dry'; ?>">
<?php echo $LIVE?'РЕЖИМ: LIVE (запись)':'РЕЖИМ: DRY-RUN (симуляция)'; ?></span></p>
<?php if(!$LIVE): ?><p class="dry">Симуляция. Если ок — добавь <code>?live=1</code>.</p><?php endif; ?>
<p><strong>Дата:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
<?php
flush();

$stat = ['ok'=>0,'skip'=>0,'products_found'=>0,'products_missing'=>0];

foreach ($pricing as $product_slug => $config) {
    echo '<h2>Товар: ' . esc_html($product_slug) . ' (' . $config['type'] . ')</h2>';
    flush();

    $product_post = get_page_by_path($product_slug, OBJECT, 'product');
    if (!$product_post) {
        echo '<p class="err">[SKIP товара] Товар не найден по slug</p>';
        $stat['products_missing']++;
        continue;
    }
    $product = wc_get_product($product_post->ID);
    if (!$product) {
        echo '<p class="err">[SKIP товара] wc_get_product вернул null</p>';
        $stat['products_missing']++;
        continue;
    }

    $real_type = $product->get_type();
    $expected_type_for_check = ($config['type'] === 'variable-color-only') ? 'variable' : $config['type'];
    if ($real_type !== $expected_type_for_check) {
        echo '<p class="err">[SKIP товара] Тип не совпадает: ожидался ' . $expected_type_for_check . ', реально ' . esc_html($real_type) . '</p>';
        $stat['products_missing']++;
        continue;
    }

    $stat['products_found']++;
    echo '<p class="ok">✅ Найден (ID ' . $product->get_id() . ', тип ' . $real_type . ', логика: ' . $config['type'] . ')</p>';

    // ===== SIMPLE =====
    if ($config['type'] === 'simple') {
        $sale = (int)$config['single'];
        $regular = calc_regular($sale);
        $old_reg = $product->get_regular_price();
        $old_sale = $product->get_sale_price();

        if ($LIVE) {
            update_post_meta($product->get_id(), '_regular_price', (string)$regular);
            update_post_meta($product->get_id(), '_sale_price', (string)$sale);
            update_post_meta($product->get_id(), '_price', (string)$sale);
            wc_delete_product_transients($product->get_id());
        }

        $tag = $LIVE ? "<span class='ok'>[OK]</span>" : "<span class='dry'>[DRY]</span>";
        echo '<table><tr><th>regular старая</th><th>sale старая</th><th>regular новая</th><th>sale новая</th><th>Статус</th></tr>';
        echo "<tr class='simple-row'><td>" . esc_html($old_reg) . "</td><td>" . esc_html($old_sale) . "</td><td><strong>{$regular}</strong></td><td><strong>{$sale}</strong></td><td>{$tag}</td></tr>";
        echo '</table>';
        $stat['ok']++;
        flush();
        continue;
    }

    // ===== VARIABLE-COLOR-ONLY (variable без размера, только по цвету) =====
    if ($config['type'] === 'variable-color-only') {
        echo '<table><tr><th>#</th><th>Цвет</th><th>Тип</th><th>reg ста.</th><th>sale ста.</th><th>reg нов.</th><th>sale нов.</th><th>Статус</th></tr>';

        $i = 0;
        foreach ($product->get_children() as $variation_id) {
            $i++;
            $v = wc_get_product($variation_id);
            if (!$v) {
                echo "<tr><td>{$i}</td><td colspan='7' class='err'>[SKIP] вариация не загружена</td></tr>";
                $stat['skip']++;
                continue;
            }
            $attrs = $v->get_variation_attributes();
            $color = $attrs['attribute_pa_fabric_color'] ?? '';

            if (!$color) {
                echo "<tr><td>{$i}</td><td colspan='7' class='warn'>[SKIP] нет атрибута цвета</td></tr>";
                $stat['skip']++;
                continue;
            }

            $melanzh = is_melanzh($color);
            $sale = $melanzh ? $config['price_ml'] : $config['price_mr'];
            $regular = calc_regular($sale);

            $old_reg = $v->get_regular_price();
            $old_sale = $v->get_sale_price();

            if ($LIVE) {
                update_post_meta($v->get_id(), '_regular_price', (string)$regular);
                update_post_meta($v->get_id(), '_sale_price', (string)$sale);
                update_post_meta($v->get_id(), '_price', (string)$sale);
            }

            $cls = $melanzh ? 'melanzh' : 'mramor';
            $tcls = $melanzh ? 'меланж' : 'мрамор';
            $tag = $LIVE ? "<span class='ok'>[OK]</span>" : "<span class='dry'>[DRY]</span>";
            echo "<tr class='$cls'><td>{$i}</td><td>" . esc_html($color) . "</td><td>$tcls</td><td>" . esc_html($old_reg) . "</td><td>" . esc_html($old_sale) . "</td><td><strong>{$regular}</strong></td><td><strong>{$sale}</strong></td><td>{$tag}</td></tr>";
            $stat['ok']++;
            if ($i % 20 === 0) flush();
        }
        echo '</table>';

        if ($LIVE) {
            wc_delete_product_transients($product->get_id());
        }
        flush();
        continue;
    }

    // ===== VARIABLE (по цвету + размеру) =====
    $size_attr_key = 'attribute_pa_' . $config['size_taxonomy'];
    echo '<table><tr><th>#</th><th>Цвет</th><th>Размер</th><th>Тип</th><th>reg ста.</th><th>sale ста.</th><th>reg нов.</th><th>sale нов.</th><th>Статус</th></tr>';

    $i = 0;
    foreach ($product->get_children() as $variation_id) {
        $i++;
        $v = wc_get_product($variation_id);
        if (!$v) {
            echo "<tr><td>{$i}</td><td colspan='8' class='err'>[SKIP] вариация не загружена</td></tr>";
            $stat['skip']++;
            continue;
        }
        $attrs = $v->get_variation_attributes();
        $color = $attrs['attribute_pa_fabric_color'] ?? '';
        $raw = $attrs[$size_attr_key] ?? '';
        $size = urldecode($raw); // 4%d0%bf-140 → 4п-140

        if (!isset($config['vars'][$size])) {
            $cls = is_melanzh($color) ? 'melanzh' : 'mramor';
            $tcls = is_melanzh($color) ? 'меланж' : 'мрамор';
            echo "<tr class='$cls'><td>{$i}</td><td>" . esc_html($color) . "</td><td>" . esc_html($size) . " (raw: " . esc_html($raw) . ")</td><td>$tcls</td><td colspan='5' class='warn'>[SKIP] размер не в карте цен</td></tr>";
            $stat['skip']++;
            continue;
        }

        [$sale_mr, $sale_ml] = $config['vars'][$size];
        $melanzh = is_melanzh($color);
        $sale = $melanzh ? $sale_ml : $sale_mr;
        $regular = calc_regular($sale);

        $old_reg = $v->get_regular_price();
        $old_sale = $v->get_sale_price();

        if ($LIVE) {
            update_post_meta($v->get_id(), '_regular_price', (string)$regular);
            update_post_meta($v->get_id(), '_sale_price', (string)$sale);
            update_post_meta($v->get_id(), '_price', (string)$sale);
        }

        $cls = $melanzh ? 'melanzh' : 'mramor';
        $tcls = $melanzh ? 'меланж' : 'мрамор';
        $tag = $LIVE ? "<span class='ok'>[OK]</span>" : "<span class='dry'>[DRY]</span>";
        echo "<tr class='$cls'><td>{$i}</td><td>" . esc_html($color) . "</td><td>" . esc_html($size) . "</td><td>$tcls</td><td>" . esc_html($old_reg) . "</td><td>" . esc_html($old_sale) . "</td><td><strong>{$regular}</strong></td><td><strong>{$sale}</strong></td><td>{$tag}</td></tr>";
        $stat['ok']++;
        if ($i % 20 === 0) flush();
    }
    echo '</table>';

    if ($LIVE) {
        wc_delete_product_transients($product->get_id());
    }
    flush();
}
?>
<hr><h2>Итог</h2>
<table>
<tr><th>Метрика</th><th>Значение</th></tr>
<tr><td class="ok">Товаров обработано</td><td><?php echo $stat['products_found']; ?> из <?php echo count($pricing); ?></td></tr>
<tr><td class="err">Товаров не найдено / неверный тип</td><td><?php echo $stat['products_missing']; ?></td></tr>
<tr><td class="ok">Цен записано (вариации + simple)</td><td><?php echo $stat['ok']; ?></td></tr>
<tr><td class="warn">Пропущено (размер не в карте)</td><td><?php echo $stat['skip']; ?></td></tr>
</table>

<?php if(!$LIVE): ?>
<p class="dry"><strong>Это был DRY-RUN.</strong> Проверь: regular > sale, цена меняется по размеру И по типу (меланж дороже мрамора). Если ок — <code>?live=1</code>.</p>
<?php elseif ($stat['skip'] === 0 && $stat['products_missing'] === 0): ?>
<p class="ok"><strong>✅ Цены записаны.</strong> Проверь /color/bezhevyj/ → блок «Готовые наборы» → цены со скидкой.</p>
<?php else: ?>
<p class="warn"><strong>⚠ С предупреждениями</strong> — проверь таблицы выше.</p>
<?php endif; ?>

<p style="color:#999;margin-top:2rem;font-size:11px">
SKIP размер — нормально, если в БД остались вариации с размерами вне xlsx Куренковой.<br>
После заливки на всех средах файл удалить: tools/fix-prices.php
</p>
</body></html>