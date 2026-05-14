<?php
/**
 * Template: Страница сценария
 */
get_header();

// Данные сценария (хардкод — потом заменим на ACF/meta)
$scenario_data = [
    'romanticheskij-uzhin' => [
        'gradient'    => 'linear-gradient(160deg, #2d1828 0%, #1a0f1e 40%, #0e0e0c 100%)',
        'persons'     => '2 персоны',
        'time'        => '~5 мин на сборку',
        'default_persons' => 2,
        'colors'      => [
            ['hex' => '#6a3a7a', 'name' => 'Фиолетовый', 'slug' => 'fioletovyj'],
            ['hex' => '#4a4844', 'name' => 'Графит', 'slug' => 'grafit'],
            ['hex' => '#8b6e3a', 'name' => 'Бронза', 'slug' => 'bronza'],
            ['hex' => '#b088b0', 'name' => 'Сиреневый', 'slug' => 'sirenevyj'],
        ],
        'tips' => [
            ['num' => '01', 'title' => 'Свечи и свет', 'text' => 'Жаккард красиво переливается при мерцающем свете. Используйте свечи разной высоты — длинные по центру, чайные по периметру.'],
            ['num' => '02', 'title' => 'Складка салфеток', 'text' => 'Для романтического стола идеальна складка «цветок». Закрепите кольцом — золотым для тёмных тканей, серебряным для светлых.'],
            ['num' => '03', 'title' => 'Контрастная посуда', 'text' => 'К фиолетовому — белая или золотая посуда. К графиту — белая с золотой каймой. Контраст подчёркивает глубину цвета ткани.'],
        ],
    ],
    'semejnyj-obed' => [
        'gradient'    => 'linear-gradient(160deg, #1e2418 0%, #141a0e 40%, #0e0e0c 100%)',
        'persons'     => '4–6 персон',
        'time'        => '~5 мин на сборку',
        'default_persons' => 4,
        'colors'      => [
            ['hex' => '#6b8a5e', 'name' => 'Зелёный', 'slug' => 'zelenyj'],
            ['hex' => '#d4c5a0', 'name' => 'Бежевый', 'slug' => 'bezhevyj'],
            ['hex' => '#c8c0b8', 'name' => 'Платина', 'slug' => 'platina'],
            ['hex' => '#f0ece4', 'name' => 'Белый', 'slug' => 'belyj'],
        ],
        'tips' => [
            ['num' => '01', 'title' => 'Центральная композиция', 'text' => 'Поставьте низкую вазу с сезонными цветами или фруктами в центр — она не мешает общению и задаёт настроение.'],
            ['num' => '02', 'title' => 'Сервировка для детей', 'text' => 'Используйте куверты для приборов — дети легко найдут свой набор. Салфетки из жаккарда практичнее бумажных.'],
            ['num' => '03', 'title' => 'Размер дорожки', 'text' => 'Для стола на 4–6 персон подойдёт дорожка 175 или 240 см. Свес с каждой стороны — 15–20 см.'],
        ],
    ],
    'prazdnichnyj-stol' => [
        'gradient'    => 'linear-gradient(160deg, #2a2010 0%, #1a150a 40%, #0e0e0c 100%)',
        'persons'     => '6+ персон',
        'time'        => '~10 мин на сборку',
        'default_persons' => 6,
        'colors'      => [
            ['hex' => '#c8a85a', 'name' => 'Меланж золото', 'slug' => 'melanzh-zoloto'],
            ['hex' => '#8b6e3a', 'name' => 'Бронза', 'slug' => 'bronza'],
            ['hex' => '#f0ece4', 'name' => 'Белый', 'slug' => 'belyj'],
            ['hex' => '#c0c0c0', 'name' => 'Серебро', 'slug' => 'serebro'],
        ],
        'tips' => [
            ['num' => '01', 'title' => 'Многоуровневый стол', 'text' => 'Используйте этажерки и подставки для закусок — это освобождает место и добавляет объёма сервировке.'],
            ['num' => '02', 'title' => 'Единый стиль', 'text' => 'Все текстильные элементы одного цвета — дорожка, салфетки, куверты. Разнобой цветов на праздничном столе выглядит хаотично.'],
            ['num' => '03', 'title' => 'Именные карточки', 'text' => 'Положите карточку с именем гостя на куверт — это элегантно и помогает рассадке.'],
        ],
    ],
    'kazhdyj-den' => [
        'gradient'    => 'linear-gradient(160deg, #181e24 0%, #0e1318 40%, #0e0e0c 100%)',
        'persons'     => '2–4 персоны',
        'time'        => '~3 мин на сборку',
        'default_persons' => 4,
        'colors'      => [
            ['hex' => '#b0b0a8', 'name' => 'Меланж серебро', 'slug' => 'melanzh-serebro'],
            ['hex' => '#787874', 'name' => 'Меланж серый', 'slug' => 'melanzh-seryj'],
            ['hex' => '#d4c5a0', 'name' => 'Бежевый', 'slug' => 'bezhevyj'],
            ['hex' => '#c8c0b8', 'name' => 'Платина', 'slug' => 'platina'],
        ],
        'tips' => [
            ['num' => '01', 'title' => 'Утренний ритуал', 'text' => 'Дорожка на столе каждый день — это 30 секунд на сервировку, которые меняют ощущение от завтрака.'],
            ['num' => '02', 'title' => 'Практичность', 'text' => 'Жаккард из полиэстера стирается при 30–40°C, быстро сохнет. Можно использовать каждый день без страха.'],
            ['num' => '03', 'title' => 'Минимализм', 'text' => 'Для повседневного стола достаточно дорожки и двух салфеток. Куверты — по желанию. Чем проще, тем элегантнее.'],
        ],
    ],
];

$slug = get_post_field('post_name', get_the_ID());
// Алиасы для кириллических slug'ов
$slug_aliases = [
    'романтический-ужин' => 'romanticheskij-uzhin',
    'семейный-обед'      => 'semejnyj-obed',
    'праздничный-стол'   => 'prazdnichnyj-stol',
    'каждый-день'        => 'kazhdyj-den',
];
if (isset($slug_aliases[$slug])) {
    $slug = $slug_aliases[$slug];
}
$data = $scenario_data[$slug] ?? $scenario_data['romanticheskij-uzhin'];

// Дефолтный цвет и персоны для текущего сценария
$scenario_defaults = loraleya_get_scenario_defaults($slug);
$default_color     = $scenario_defaults['color'];

// Готовим карту фото для всех цветов — для динамического обновления галереи в JS
$gallery_photo_map = [];
$all_color_slugs = [
    'fioletovyj','grafit','bronza','sirenevyj','bezhevyj','belyj','biryuza',
    'blek-zoloto','goluboj','zelenyj','melanzh-zoloto','melanzh-serebro',
    'melanzh-seryj','melanzh-chernyj','platina','serebro','tyomno-biryuzovyj',
];
foreach ($all_color_slugs as $cs) {
    $gallery_photo_map[$cs] = [
        'napkin'      => loraleya_get_color_photo_url($cs, 'salfetka-tsvetok'),
        'kuvert'      => loraleya_get_color_photo_url($cs, 'kuvert'),
        'faktura'     => loraleya_get_color_photo_url($cs, 'macro-faktura'),
        'nabor-4-140' => loraleya_get_color_photo_url($cs, 'nabor-4-140'),
        'nabor-4-175' => loraleya_get_color_photo_url($cs, 'nabor-4-175'),
        'nabor-6-300' => loraleya_get_color_photo_url($cs, 'nabor-6-300'),
    ];
}
$item_prices = loraleya_get_item_prices($default_color);

// Хелпер: возвращает price (int) из $item_prices или fallback
$ip_price = function($key) use ($item_prices) {
    return (int)($item_prices[$key]['price'] ?? 0);
};
// Хелпер: форматирует "1 290 ₽ / шт" из цены
$ip_fmt = function($key, $suffix = ' / шт') use ($item_prices) {
    $p = (int)($item_prices[$key]['price'] ?? 0);
    return $p > 0 ? number_format($p, 0, '.', ' ') . ' ₽' . $suffix : '';
};
?>

<!-- HERO -->
<section class="sc-hero" style="background:<?php echo $data['gradient']; ?>">
    <div class="sc-hero-overlay"></div>
    <div class="sc-hero-content">
        <div class="sc-hero-bc">
            <a href="<?php echo home_url(); ?>">Главная</a> →
            <a href="<?php echo home_url(); ?>/#scenarios">Сценарии</a> →
            <?php the_title(); ?>
        </div>
        <?php
        $title_words = explode(' ', get_the_title());
        $last_word = array_pop($title_words);
        ?>
        <h1><?php echo implode(' ', $title_words); ?> <em><?php echo $last_word; ?></em></h1>
        <p class="sc-hero-desc"><?php echo get_the_excerpt(); ?></p>
        <div class="sc-hero-meta">
            <span>👤 <?php echo $data['persons']; ?></span>
            <span>⏱ <?php echo $data['time']; ?></span>
        </div>
    </div>
</section>

<!-- GALLERY -->
<?php
$nabor_suffix_by_persons = [
    2 => 'nabor-4-140',
    4 => 'nabor-4-175',
    6 => 'nabor-6-300',
];
$default_persons_int = (int) $data['default_persons'];
$default_nabor_type  = $nabor_suffix_by_persons[$default_persons_int] ?? 'nabor-4-175';

$slot_overall = loraleya_get_color_photo_url($default_color, $default_nabor_type);
$slot_napkin  = loraleya_get_color_photo_url($default_color, 'salfetka-tsvetok');
$slot_kuvert  = loraleya_get_color_photo_url($default_color, 'kuvert');
$slot_faktura = loraleya_get_color_photo_url($default_color, 'macro-faktura');
?>
<section class="section" id="gallery">
    <div class="container">
        <div class="eyebrow">Галерея</div>
        <h2>Как это выглядит</h2>
        <p class="section-desc">Реальные фото сервировок при разном освещении</p>
        <div class="sc-gallery"
             data-color="<?php echo esc_attr($default_color); ?>"
             data-persons="<?php echo esc_attr($default_persons_int); ?>">

            <div class="sc-gallery-item" data-slot="overall"
                 style="<?php echo $slot_overall ? 'background-image:url(' . esc_url($slot_overall) . ')' : ''; ?>">
                <?php if (!$slot_overall): ?>
                    <div class="sc-gallery-ph">Фото · общий план стола</div>
                <?php endif; ?>
            </div>

            <div class="sc-gallery-item" data-slot="napkin"
                 style="<?php echo $slot_napkin ? 'background-image:url(' . esc_url($slot_napkin) . ')' : ''; ?>">
                <?php if (!$slot_napkin): ?>
                    <div class="sc-gallery-ph">Детали · салфетка</div>
                <?php endif; ?>
            </div>

            <div class="sc-gallery-item" data-slot="kuvert"
                 style="<?php echo $slot_kuvert ? 'background-image:url(' . esc_url($slot_kuvert) . ')' : ''; ?>">
                <?php if (!$slot_kuvert): ?>
                    <div class="sc-gallery-ph">Детали · куверт</div>
                <?php endif; ?>
            </div>

            <div class="sc-gallery-item" data-slot="faktura"
                 style="<?php echo $slot_faktura ? 'background-image:url(' . esc_url($slot_faktura) . ')' : ''; ?>">
                <?php if (!$slot_faktura): ?>
                    <div class="sc-gallery-ph">Макро · фактура ткани</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- RECOMMENDED COLORS -->
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="eyebrow">Для этого сценария</div>
        <h2>Рекомендуемые оттенки</h2>
        <div class="sc-colors-rec">
            <?php foreach ($data['colors'] as $color) : ?>
                <a class="sc-cr" href="#gallery" data-color-name="<?php echo esc_attr($color['name']); ?>">
                    <div class="sc-cr-dot" style="background:<?php echo $color['hex']; ?>"></div>
                    <span class="sc-cr-name"><?php echo $color['name']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CONSTRUCTOR -->
<section class="section" id="constructor" data-default-persons="<?php echo $data['default_persons']; ?>">
    <div class="container">
        <div class="con">

            <div class="con-head">
                <div>
                    <div class="eyebrow">Конструктор</div>
                    <h2>Соберите свой комплект</h2>
                </div>
            </div>

            <!-- 1. Color -->
            <div class="grp">
                <div class="grp-title"><span class="grp-num">1</span> Выберите цвет</div>
                <div class="swatches" id="swatches" data-default-color="<?php echo esc_attr($default_color); ?>">
                    <?php
                    // Рекомендуемые цвета первыми
                    $rec_hexes = array_column($data['colors'], 'hex');
                    $all_colors = [
                        ['slug'=>'fioletovyj','hex'=>'#6a3a7a','name'=>'Фиолетовый'],
                        ['slug'=>'grafit','hex'=>'#4a4844','name'=>'Графит'],
                        ['slug'=>'bronza','hex'=>'#8b6e3a','name'=>'Бронза'],
                        ['slug'=>'sirenevyj','hex'=>'#b088b0','name'=>'Сиреневый'],
                        ['slug'=>'bezhevyj','hex'=>'#d4c5a0','name'=>'Бежевый'],
                        ['slug'=>'belyj','hex'=>'#f0ece4','name'=>'Белый'],
                        ['slug'=>'biryuza','hex'=>'#5eb8a8','name'=>'Бирюза'],
                        ['slug'=>'blek-zoloto','hex'=>'#2a2520','name'=>'Блек золото'],
                        ['slug'=>'goluboj','hex'=>'#8bb8d0','name'=>'Голубой'],
                        ['slug'=>'zelenyj','hex'=>'#6b8a5e','name'=>'Зелёный'],
                        ['slug'=>'melanzh-zoloto','hex'=>'#c8a85a','name'=>'Меланж золото'],
                        ['slug'=>'melanzh-serebro','hex'=>'#b0b0a8','name'=>'Меланж серебро'],
                        ['slug'=>'melanzh-seryj','hex'=>'#787874','name'=>'Меланж серый'],
                        ['slug'=>'melanzh-chernyj','hex'=>'#2e2e2c','name'=>'Меланж чёрный'],
                        ['slug'=>'platina','hex'=>'#c8c0b8','name'=>'Платина'],
                        ['slug'=>'serebro','hex'=>'#c0c0c0','name'=>'Серебро'],
                        ['slug'=>'temno-biryuzovyj','hex'=>'#3a7878','name'=>'Тёмно-бирюзовый'],
                    ];
                    // Сначала рекомендуемые, потом остальные
                    $rec = array_filter($all_colors, fn($c) => in_array($c['hex'], $rec_hexes));
                    $rest = array_filter($all_colors, fn($c) => !in_array($c['hex'], $rec_hexes));
                    $sorted = array_merge(array_values($rec), array_values($rest));
                    $dark_borders = ['#f0ece4','#2a2520','#2e2e2c'];
                    foreach ($sorted as $c) :
                        $border = in_array($c['hex'], $dark_borders) ? 'border:1px solid rgba(197,165,90,.2);' : '';
                        $on = ($c['slug'] === $default_color) ? ' on' : '';
                        $swatch_url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($c['slug']) : '';
                        $bg_style = $swatch_url
                            ? 'background-image:url(' . esc_url($swatch_url) . ');background-size:cover;background-position:center;'
                            : 'background:' . $c['hex'] . ';';
                    ?>
                        <div class="sw<?php echo $on; ?>" style="<?php echo $bg_style; ?><?php echo $border; ?>" data-color="<?php echo esc_attr($c['slug']); ?>" data-name="<?php echo esc_attr($c['name']); ?>"></div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- 2. Persons -->
            <div class="grp">
                <div class="grp-title"><span class="grp-num">2</span> Количество персон</div>
                <div class="pbtn-row">
                    <button class="pbtn" data-persons="2">2 персоны</button>
                    <button class="pbtn" data-persons="4">4 персоны</button>
                    <button class="pbtn" data-persons="6">6 персон</button>
                </div>
            </div>

            <!-- SET SHORTCUT -->
            <div class="set-box" id="setBox">
                <div>
                    <h4 id="setTitle">Готовый набор на <?php echo $data['default_persons']; ?> персон<?php echo $data['default_persons'] === 4 ? 'ы' : ''; ?> — выгоднее на 15%</h4>
                    <p id="setDesc">Дорожка 40×140 + <?php echo $data['default_persons']; ?> салфеток + <?php echo $data['default_persons']; ?> кувертов</p>
                </div>
                <div class="set-prices">
                    <span class="sp-old" id="setOld"></span>
                    <span class="sp-new" id="setNew"></span>
                </div>
                <button class="btn-s" id="addSetBtn" data-set-base="4">Добавить набор</button>
            </div>

            <!-- 3. ITEMS -->
            <div class="grp">
                <div class="grp-title"><span class="grp-num">3</span> Или соберите сами</div>

                <!-- Дорожки -->
                <div class="cat">
                    <div class="cat-label">Дорожки на стол</div>
                    <div class="ir" data-price="<?php echo $ip_price('Дорожка 140'); ?>" data-item="Дорожка 140">
                        <div><div class="ir-name">Дорожка 40 × 140 см</div><div class="ir-size">Жаккард · 100% полиэстер · Входит в наборы</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Дорожка 140'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                    <div class="ir" data-price="<?php echo $ip_price('Дорожка 175'); ?>" data-item="Дорожка 175">
                        <div><div class="ir-name">Дорожка 40 × 175 см</div><div class="ir-size">Жаккард · 100% полиэстер · Входит в наборы</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Дорожка 175'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                    <div class="ir" data-price="<?php echo $ip_price('Дорожка 240'); ?>" data-item="Дорожка 240">
                        <div><div class="ir-name">Дорожка 40 × 240 см</div><div class="ir-size">Жаккард · 100% полиэстер · Для длинных столов</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Дорожка 240'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                    <div class="ir" data-price="<?php echo $ip_price('Дорожка 300'); ?>" data-item="Дорожка 300">
                        <div><div class="ir-name">Дорожка 40 × 300 см</div><div class="ir-size">Жаккард · 100% полиэстер · Максимальный размер</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Дорожка 300'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                </div>

                <!-- Скатерти -->
                <div class="cat">
                    <div class="cat-label">Скатерти</div>
                    <div class="ir" data-price="<?php echo $ip_price('Скатерть 175'); ?>" data-item="Скатерть 175">
                        <div><div class="ir-name">Скатерть 140 × 175 см</div><div class="ir-size">Жаккард · 100% полиэстер · На 4 персоны</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Скатерть 175'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                    <div class="ir" data-price="<?php echo $ip_price('Скатерть 220'); ?>" data-item="Скатерть 220">
                        <div><div class="ir-name">Скатерть 140 × 220 см</div><div class="ir-size">Жаккард · 100% полиэстер · На 6 персон</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Скатерть 220'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                    <div class="ir" data-price="<?php echo $ip_price('Скатерть 240'); ?>" data-item="Скатерть 240">
                        <div><div class="ir-name">Скатерть 140 × 240 см</div><div class="ir-size">Жаккард · 100% полиэстер · На 8 персон</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Скатерть 240'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                </div>

                <!-- Салфетки -->
                <div class="cat">
                    <div class="cat-label">Салфетки</div>
                    <div class="ir" data-price="<?php echo $ip_price('Салфетка'); ?>" data-role="napkin" data-item="Салфетка">
                        <div><div class="ir-name">Салфетка 40 × 40 см</div><div class="ir-size">Жаккард · 100% полиэстер</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Салфетка'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                </div>

                <!-- Куверты -->
                <div class="cat">
                    <div class="cat-label">Куверты для приборов</div>
                    <div class="ir" data-price="<?php echo $ip_price('Куверт'); ?>" data-role="couverte" data-item="Куверт">
                        <div><div class="ir-name">Куверт 9 × 24 см</div><div class="ir-size">Жаккард · 100% полиэстер</div></div>
                        <div class="ir-price"><?php echo $ip_fmt('Куверт'); ?></div>
                        <div class="ir-qty"><button class="qb qb-minus">−</button><span class="qv">0</span><button class="qb qb-plus">+</button></div>
                        <div class="ir-sub">0 ₽</div>
                    </div>
                </div>

            </div>

            <div class="note-box">
                <strong>Совет:</strong> Для стандартного стола подойдёт дорожка 140 или 175 см. Для длинных столов (от 200 см) выбирайте дорожку 240 или 300 см — в этом случае набор не подойдёт, собирайте поштучно.
            </div>

            <!-- TOTAL (sticky) -->
            <div class="total-sticky" id="totalSticky">
                <div class="total-inner">
                    <div class="total-left">
                        <div class="total-row">
                            <span class="total-lbl">Итого</span>
                            <span class="total-sum" id="totalSum">0 ₽</span>
                            <span class="total-items" id="totalItems">—</span>
                        </div>
                        <div class="total-hint">Сумма выбранных позиций. Чтобы увидеть полный заказ, откройте корзину</div>
                    </div>
                    <button class="btn-cart" id="addCartBtn">В корзину →</button>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- TIPS -->
<section class="section sc-tips-sec">
    <div class="container">
        <div class="eyebrow">Советы</div>
        <h2>Как создать атмосферу</h2>
        <div class="sc-tips-grid">
            <?php foreach ($data['tips'] as $tip) : ?>
                <div class="sc-tip">
                    <div class="sc-tip-num"><?php echo $tip['num']; ?></div>
                    <div class="sc-tip-title"><?php echo $tip['title']; ?></div>
                    <div class="sc-tip-text"><?php echo $tip['text']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- OTHER SCENARIOS -->
<section class="section sc-other-sec">
    <div class="container">
        <div class="eyebrow">Другие сценарии</div>
        <h2>Смотрите также</h2>
        <div class="sc-other-grid">
            <?php
            $others = new WP_Query([
                'post_type'      => 'scenario',
                'posts_per_page' => 3,
                'post__not_in'   => [get_the_ID()],
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]);
            $n = 1;
            while ($others->have_posts()) : $others->the_post();
            ?>
                <a href="<?php the_permalink(); ?>" class="sc-oc">
                    <div class="sc-oc-num"><?php echo str_pad($n, 2, '0', STR_PAD_LEFT); ?></div>
                    <div class="sc-oc-name"><?php the_title(); ?></div>
                    <div class="sc-oc-hint"><?php echo get_the_excerpt(); ?></div>
                </a>
            <?php
                $n++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>

<script>
window.LORALEYA_GALLERY_PHOTOS = <?php echo wp_json_encode($gallery_photo_map); ?>;
</script>
<?php get_footer(); ?>
