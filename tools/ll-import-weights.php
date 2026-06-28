<?php
/**
 * LoraLeya — импорт весов/габаритов на вариации и товары.
 * Запуск: https://loraleya.ru/ll-import-weights.php?k=ll2026         (превью)
 *         https://loraleya.ru/ll-import-weights.php?k=ll2026&apply=YES (запись)
 * УДАЛИТЬ ФАЙЛ С СЕРВЕРА ПОСЛЕ ИСПОЛЬЗОВАНИЯ.
 */
require __DIR__ . '/wp-load.php';

if ( ( $_GET['k'] ?? '' ) !== 'll2026' ) { http_response_code( 403 ); exit( 'Forbidden' ); }
$APPLY = ( ( $_GET['apply'] ?? '' ) === 'YES' );

// Карта: значение размера => [вес, Д, Ш, В] (кг, см)
$MAP = array(
    39 => array( // Дорожка, razmer-dorozhki
        '140' => array( 0.18,  23, 24, 2 ),
        '175' => array( 0.215, 23, 24, 2.5 ),
        '240' => array( 0.28,  23, 24, 3 ),
        '300' => array( 0.35,  23, 24, 3.5 ),
    ),
    44 => array( // Скатерть, razmer-skaterti
        '175' => array( 0.625, 26, 27, 5 ),
        '220' => array( 0.78,  27, 29, 5 ),
        '240' => array( 0.84,  26, 27, 7 ),
    ),
    50 => array( // Набор, razmer-nabora
        '2п/140' => array( 0.39, 24, 24, 3 ),
        '4п/140' => array( 0.58, 24, 24, 5 ),
        '4п/175' => array( 0.58, 24, 24, 5 ),
        '6п/240' => array( 0.88, 23, 32, 6 ),
        '6п/300' => array( 0.88, 23, 32, 6 ),
    ),
);
// Простые товары: ID => [вес, Д, Ш, В]
$SIMPLE = array(
    48 => array( 0.07, 21, 21, 1 ), // Салфетка
    49 => array( 0.03, 25, 13, 1 ), // Куверт
);

$attr_slug = array( 39 => 'razmer-dorozhki', 44 => 'razmer-skaterti', 50 => 'razmer-nabora' );

echo '<style>body{font-family:sans-serif;font-size:13px}table{border-collapse:collapse;margin:1em 0}td,th{border:1px solid #ccc;padding:4px 8px}.miss{background:#fde2e2}.ok{background:#e6f6e6}</style>';
echo '<h2>LoraLeya — импорт весов/габаритов</h2>';
echo $APPLY
    ? '<p style="color:#a14a3a"><b>РЕЖИМ ЗАПИСИ (apply=YES)</b></p>'
    : '<p><b>РЕЖИМ ПРЕВЬЮ.</b> Для записи добавьте <code>&apply=YES</code> к ссылке.</p>';
echo '<p>⚠ Перед записью сделайте бэкап БД. После использования удалите этот файл с сервера.</p>';

$found = $upd = $miss = 0;

// --- Вариативные товары ---
foreach ( $attr_slug as $pid => $tax ) {
    $product = wc_get_product( $pid );
    if ( ! $product ) { echo "<p class='miss'>Товар $pid не найден</p>"; continue; }
    echo "<h3>Товар $pid — " . esc_html( $product->get_name() ) . " (атрибут $tax)</h3><table>";
    echo '<tr><th>Вар.</th><th>Цвет</th><th>Размер</th><th>Вес→</th><th>Габариты→</th><th>Статус</th></tr>';

    foreach ( $product->get_children() as $vid ) {
        $v = wc_get_product( $vid );
        if ( ! $v ) continue;
        $found++;
        $size  = $v->get_attribute( $tax );           // значение размера у вариации
        $color = $v->get_attribute( 'fabric_color' );
        $data  = $MAP[ $pid ][ $size ] ?? null;

        if ( ! $data ) {
            $miss++;
            echo "<tr class='miss'><td>$vid</td><td>" . esc_html( $color ) . "</td><td>" . esc_html( $size ) .
                 "</td><td>—</td><td>—</td><td>НЕТ СОВПАДЕНИЯ</td></tr>";
            continue;
        }
        list( $w, $l, $wd, $h ) = $data;
        if ( $APPLY ) {
            $v->set_weight( $w ); $v->set_length( $l ); $v->set_width( $wd ); $v->set_height( $h );
            $v->save();
            wc_delete_product_transients( $vid );
            $upd++;
        }
        echo "<tr class='ok'><td>$vid</td><td>" . esc_html( $color ) . "</td><td>" . esc_html( $size ) .
             "</td><td>$w кг</td><td>{$l}×{$wd}×{$h}</td><td>" . ( $APPLY ? 'обновлено' : 'будет обновлено' ) . "</td></tr>";
    }
    echo '</table>';
    if ( $APPLY ) wc_delete_product_transients( $pid );
}

// --- Простые товары ---
echo '<h3>Простые товары (салфетка, куверт)</h3><table><tr><th>ID</th><th>Имя</th><th>Вес→</th><th>Габариты→</th><th>Статус</th></tr>';
foreach ( $SIMPLE as $pid => $d ) {
    $p = wc_get_product( $pid );
    if ( ! $p ) { echo "<tr class='miss'><td>$pid</td><td>не найден</td><td>—</td><td>—</td><td>НЕТ</td></tr>"; continue; }
    $found++;
    list( $w, $l, $wd, $h ) = $d;
    if ( $APPLY ) {
        $p->set_weight( $w ); $p->set_length( $l ); $p->set_width( $wd ); $p->set_height( $h );
        $p->save(); wc_delete_product_transients( $pid ); $upd++;
    }
    echo "<tr class='ok'><td>$pid</td><td>" . esc_html( $p->get_name() ) . "</td><td>$w кг</td><td>{$l}×{$wd}×{$h}</td><td>" .
         ( $APPLY ? 'обновлено' : 'будет обновлено' ) . "</td></tr>";
}
echo '</table>';

echo "<h3>Итого: найдено $found, " . ( $APPLY ? "обновлено $upd" : 'к обновлению ' . ( $found - $miss ) ) . ", без совпадения $miss.</h3>";
if ( $miss ) echo "<p class='miss'><b>Есть вариации без совпадения размера — разберитесь до apply.</b></p>";