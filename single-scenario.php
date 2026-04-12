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
$data = $scenario_data[$slug] ?? $scenario_data['romanticheskij-uzhin'];
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
<section class="section">
    <div class="container">
        <div class="eyebrow">Галерея</div>
        <h2>Как это выглядит</h2>
        <p class="section-desc">Реальные фото сервировок при разном освещении</p>
        <div class="sc-gallery">
            <div class="sc-gallery-item"><div class="sc-gallery-ph">Фото · общий план стола</div></div>
            <div class="sc-gallery-item"><div class="sc-gallery-ph">Детали · салфетка</div></div>
            <div class="sc-gallery-item"><div class="sc-gallery-ph">Детали · куверт</div></div>
            <div class="sc-gallery-item"><div class="sc-gallery-ph">Макро · фактура ткани</div></div>
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
                <a class="sc-cr" href="<?php echo home_url('/color/' . $color['slug'] . '/'); ?>">
                    <div class="sc-cr-dot" style="background:<?php echo $color['hex']; ?>"></div>
                    <span class="sc-cr-name"><?php echo $color['name']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CONSTRUCTOR PLACEHOLDER (Этап 2) -->
<section class="section" id="constructor">
    <div class="container">
        <div class="sc-constructor-placeholder" style="background:var(--bg2);border:1px dashed rgba(197,165,90,.15);padding:3rem;text-align:center">
            <div class="eyebrow">Конструктор</div>
            <h2>Соберите свой комплект</h2>
            <p class="section-desc" style="margin:1rem auto 0">Блок конструктора будет добавлен на этапе 2</p>
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

<?php get_footer(); ?>
