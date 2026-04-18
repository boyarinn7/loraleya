<?php
/**
 * Template: Страница цвета ткани (taxonomy: fabric_color)
 */

$scenario_meta = [
    'romanticheskij-uzhin' => ['num' => '01', 'name' => 'Романтический ужин', 'hint' => '2 персоны · свечи · вечер'],
    'semejnyj-obed'        => ['num' => '02', 'name' => 'Семейный обед',       'hint' => '4–6 персон · дневной свет · тепло'],
    'prazdnichnyj-stol'    => ['num' => '03', 'name' => 'Праздничный стол',    'hint' => '6+ персон · декор · шампанское'],
    'kazhdyj-den'          => ['num' => '04', 'name' => 'Каждый день',         'hint' => '2–4 персоны · минимализм · уют'],
];

$colors_data = [
    'fioletovyj' => [
        'name'         => 'Фиолетовый',
        'subtitle'     => 'Глубокий и таинственный',
        'hex'          => '#6a3a7a',
        'accent'       => '#6a3a7a',
        'accent_light' => '#8b5a9a',
        'gradient'     => 'linear-gradient(135deg, #2d1a38 0%, #1a0f22 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(106,58,122,.12)',
        'desc'         => 'Насыщенный фиолетовый с характерным жаккардовым переливом. При свечах приобретает глубину, при дневном свете — благородный блеск. Один из самых популярных оттенков коллекции.',
        'tags'         => ['Романтический ужин', 'Праздничный стол', 'Свечи'],
        'scenarios'    => ['romanticheskij-uzhin', 'prazdnichnyj-stol'],
    ],
    'grafit' => [
        'name'         => 'Графит',
        'subtitle'     => 'Сдержанный и элегантный',
        'hex'          => '#4a4844',
        'accent'       => '#4a4844',
        'accent_light' => '#6a6862',
        'gradient'     => 'linear-gradient(135deg, #252320 0%, #1a1918 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(74,72,68,.12)',
        'desc'         => 'Благородный графитовый с тёплым подтоном. Универсальный цвет, который подходит к любой посуде. Жаккардовый перелив особенно заметен при боковом освещении.',
        'tags'         => ['Романтический ужин', 'Каждый день', 'Минимализм'],
        'scenarios'    => ['romanticheskij-uzhin', 'kazhdyj-den'],
    ],
    'bronza' => [
        'name'         => 'Бронза',
        'subtitle'     => 'Тёплый и торжественный',
        'hex'          => '#8b6e3a',
        'accent'       => '#8b6e3a',
        'accent_light' => '#a8884a',
        'gradient'     => 'linear-gradient(135deg, #2a2010 0%, #1a150a 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(139,110,58,.12)',
        'desc'         => 'Глубокий бронзовый с золотистым переливом. Создаёт ощущение тепла и торжественности. Идеален для праздничных сервировок и особых случаев.',
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'Тёплый свет'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin'],
    ],
    'sirenevyj' => [
        'name'         => 'Сиреневый',
        'subtitle'     => 'Нежный и утончённый',
        'hex'          => '#b088b0',
        'accent'       => '#b088b0',
        'accent_light' => '#c4a0c4',
        'gradient'     => 'linear-gradient(135deg, #2a1a2a 0%, #1a1018 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(176,136,176,.12)',
        'desc'         => 'Мягкий сиреневый с деликатным жаккардовым плетением. Привносит лёгкость и романтику. Прекрасно сочетается с белой и серебряной посудой.',
        'tags'         => ['Романтический ужин', 'Нежность', 'Весна'],
        'scenarios'    => ['romanticheskij-uzhin', 'semejnyj-obed'],
    ],
    'bezhevyj' => [
        'name'         => 'Бежевый',
        'subtitle'     => 'Классический и тёплый',
        'hex'          => '#d4c5a0',
        'accent'       => '#d4c5a0',
        'accent_light' => '#e0d4b4',
        'gradient'     => 'linear-gradient(135deg, #2a2518 0%, #1a1810 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(212,197,160,.10)',
        'desc'         => 'Тёплый бежевый — основа элегантной сервировки. Подходит к любому стилю и сезону. Жаккардовый перелив придаёт ткани мягкое свечение.',
        'tags'         => ['Каждый день', 'Семейный обед', 'Универсальный'],
        'scenarios'    => ['kazhdyj-den', 'semejnyj-obed'],
    ],
    'belyj' => [
        'name'         => 'Белый',
        'subtitle'     => 'Чистый и торжественный',
        'hex'          => '#f0ece4',
        'accent'       => '#f0ece4',
        'accent_light' => '#f5f2ec',
        'gradient'     => 'linear-gradient(135deg, #2a2a28 0%, #1a1a18 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(240,236,228,.08)',
        'desc'         => 'Молочно-белый с тёплым подтоном. Классика праздничной сервировки. Жаккардовое плетение создаёт изысканную игру света и тени.',
        'tags'         => ['Праздничный стол', 'Свадьба', 'Элегантность'],
        'scenarios'    => ['prazdnichnyj-stol', 'semejnyj-obed'],
    ],
    'biryuza' => [
        'name'         => 'Бирюза',
        'subtitle'     => 'Свежий и яркий',
        'hex'          => '#5eb8a8',
        'accent'       => '#5eb8a8',
        'accent_light' => '#78ccbc',
        'gradient'     => 'linear-gradient(135deg, #122a25 0%, #0e1a18 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(94,184,168,.12)',
        'desc'         => 'Яркая бирюза с жаккардовым переливом. Добавляет цвета и свежести в сервировку. Эффектно смотрится с белой посудой и натуральным деревом.',
        'tags'         => ['Семейный обед', 'Лето', 'Свежесть'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den'],
        'photo_prefix' => 'biruza',
    ],
    'blek-zoloto' => [
        'name'         => 'Блек золото',
        'subtitle'     => 'Роскошный и глубокий',
        'hex'          => '#2a2520',
        'accent'       => '#2a2520',
        'accent_light' => '#4a4035',
        'gradient'     => 'linear-gradient(135deg, #1a1510 0%, #100e0a 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(42,37,32,.15)',
        'desc'         => 'Почти чёрный с тёплым золотистым подтоном. Ультра-элегантный выбор для особых случаев. Жаккардовый рисунок раскрывается при свечах.',
        'tags'         => ['Романтический ужин', 'Роскошь', 'Вечер'],
        'scenarios'    => ['romanticheskij-uzhin', 'prazdnichnyj-stol'],
    ],
    'goluboj' => [
        'name'         => 'Голубой',
        'subtitle'     => 'Спокойный и воздушный',
        'hex'          => '#8bb8d0',
        'accent'       => '#8bb8d0',
        'accent_light' => '#a0cce0',
        'gradient'     => 'linear-gradient(135deg, #1a2530 0%, #101820 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(139,184,208,.12)',
        'desc'         => 'Небесно-голубой с нежным жаккардовым рисунком. Создаёт атмосферу спокойствия и свежести. Идеален для летних обедов и завтраков.',
        'tags'         => ['Семейный обед', 'Завтрак', 'Лето'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den'],
    ],
    'zelenyj' => [
        'name'         => 'Зелёный',
        'subtitle'     => 'Природный и уютный',
        'hex'          => '#6b8a5e',
        'accent'       => '#6b8a5e',
        'accent_light' => '#82a472',
        'gradient'     => 'linear-gradient(135deg, #1a2518 0%, #101a0e 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(107,138,94,.12)',
        'desc'         => 'Натуральный зелёный с характерным жаккардовым плетением. Привносит ощущение природы и свежести. Отлично сочетается с деревянной посудой.',
        'tags'         => ['Семейный обед', 'Природа', 'Уют'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den'],
    ],
    'melanzh-zoloto' => [
        'name'         => 'Меланж золото',
        'subtitle'     => 'Праздничный и сияющий',
        'hex'          => '#c8a85a',
        'accent'       => '#c8a85a',
        'accent_light' => '#d8bc70',
        'gradient'     => 'linear-gradient(135deg, #2a2210 0%, #1a1808 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(200,168,90,.12)',
        'desc'         => 'Золотистый меланж с выраженным жаккардовым переливом. Главный цвет для праздничных сервировок. Сияет при любом освещении.',
        'tags'         => ['Праздничный стол', 'Новый год', 'Торжество'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin'],
    ],
    'melanzh-serebro' => [
        'name'         => 'Меланж серебро',
        'subtitle'     => 'Универсальный и стильный',
        'hex'          => '#b0b0a8',
        'accent'       => '#b0b0a8',
        'accent_light' => '#c4c4bc',
        'gradient'     => 'linear-gradient(135deg, #222220 0%, #181818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(176,176,168,.10)',
        'desc'         => 'Серебристый меланж — универсальная база для любой сервировки. Жаккардовый перелив придаёт ткани благородство и глубину.',
        'tags'         => ['Каждый день', 'Минимализм', 'Универсальный'],
        'scenarios'    => ['kazhdyj-den', 'semejnyj-obed'],
    ],
    'melanzh-seryj' => [
        'name'         => 'Меланж серый',
        'subtitle'     => 'Сдержанный и практичный',
        'hex'          => '#787874',
        'accent'       => '#787874',
        'accent_light' => '#929290',
        'gradient'     => 'linear-gradient(135deg, #222220 0%, #161614 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(120,120,116,.12)',
        'desc'         => 'Средний серый с тёплым подтоном. Практичный выбор на каждый день. Не маркий, легко сочетается с любой посудой.',
        'tags'         => ['Каждый день', 'Практичный', 'Минимализм'],
        'scenarios'    => ['kazhdyj-den', 'semejnyj-obed'],
    ],
    'melanzh-chernyj' => [
        'name'         => 'Меланж чёрный',
        'subtitle'     => 'Строгий и современный',
        'hex'          => '#2e2e2c',
        'accent'       => '#2e2e2c',
        'accent_light' => '#484846',
        'gradient'     => 'linear-gradient(135deg, #1a1a18 0%, #101010 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(46,46,44,.15)',
        'desc'         => 'Глубокий чёрный меланж с едва заметным жаккардовым рисунком. Современный минимализм для тех, кто ценит сдержанность и стиль.',
        'tags'         => ['Каждый день', 'Минимализм', 'Строгий'],
        'scenarios'    => ['kazhdyj-den', 'romanticheskij-uzhin'],
    ],
    'platina' => [
        'name'         => 'Платина',
        'subtitle'     => 'Благородный и мягкий',
        'hex'          => '#c8c0b8',
        'accent'       => '#c8c0b8',
        'accent_light' => '#d8d2ca',
        'gradient'     => 'linear-gradient(135deg, #242220 0%, #1a1818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(200,192,184,.10)',
        'desc'         => 'Тёплая платина с деликатным жаккардовым плетением. Универсальный нейтральный оттенок, который подходит к любому случаю.',
        'tags'         => ['Каждый день', 'Семейный обед', 'Нейтральный'],
        'scenarios'    => ['kazhdyj-den', 'semejnyj-obed'],
    ],
    'serebro' => [
        'name'         => 'Серебро',
        'subtitle'     => 'Холодный и изысканный',
        'hex'          => '#c0c0c0',
        'accent'       => '#c0c0c0',
        'accent_light' => '#d4d4d4',
        'gradient'     => 'linear-gradient(135deg, #222222 0%, #181818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(192,192,192,.10)',
        'desc'         => 'Холодное серебро с чистым жаккардовым переливом. Элегантный выбор для праздничных и торжественных сервировок.',
        'tags'         => ['Праздничный стол', 'Элегантность', 'Холодный свет'],
        'scenarios'    => ['prazdnichnyj-stol', 'semejnyj-obed'],
    ],
    'temno-biryuzovyj' => [
        'name'         => 'Тёмно-бирюзовый',
        'subtitle'     => 'Глубокий и благородный',
        'hex'          => '#3a7878',
        'accent'       => '#3a7878',
        'accent_light' => '#4a9090',
        'gradient'     => 'linear-gradient(135deg, #122220 0%, #0e1818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(58,120,120,.12)',
        'desc'         => 'Тёмная бирюза с насыщенным жаккардовым рисунком. Глубокий и благородный оттенок, который выделяется при любом освещении.',
        'tags'         => ['Праздничный стол', 'Глубина', 'Вечер'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin'],
    ],
];

$term  = get_queried_object();
$slug  = $term->slug ?? 'fioletovyj';
$color = $colors_data[$slug] ?? $colors_data['fioletovyj'];
$photo_prefix = $color['photo_prefix'] ?? $slug;

// Получаем путь к uploads
$upload_dir = wp_get_upload_dir();
$upload_url = $upload_dir['baseurl'];

// Функция для получения URL фото по prefix и типу
function loraleya_color_photo($upload_url, $prefix, $type, $ext = 'webp') {
    // Имя файла без расширения — это заголовок вложения в WP
    $search_title = $prefix . '-' . $type;
    $attachment = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'numberposts' => 1,
        'title'       => $search_title,
    ]);
    if (!empty($attachment)) {
        return wp_get_attachment_url($attachment[0]->ID);
    }
    // Fallback: поиск через 's'
    $attachment = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'numberposts' => 1,
        's'           => $search_title,
    ]);
    if (!empty($attachment)) {
        return wp_get_attachment_url($attachment[0]->ID);
    }
    return '';
}

get_header();
?>

<style>
    .color-hero-bg  { background: <?php echo $color['gradient']; ?>; }
    .color-hero-glow { background: radial-gradient(ellipse, <?php echo $color['glow_rgba']; ?> 0%, transparent 70%); }
    .chc-tag        { border-color: rgba(106,58,122,.3); color: <?php echo $color['accent_light']; ?>; }
</style>

<!-- 1. HERO -->
<section class="color-hero">
    <div class="color-hero-bg"></div>
    <div class="color-hero-glow"></div>
    <div class="color-hero-content">
        <div class="chc-left">
            <div class="chc-bc">
                <a href="<?php echo home_url('/'); ?>">Главная</a> →
                <a href="<?php echo home_url('/palette/'); ?>">Палитра</a> →
                <?php echo esc_html($color['name']); ?>
            </div>
            <h1 class="chc-title"><?php echo esc_html($color['name']); ?></h1>
            <div class="chc-sub"><?php echo esc_html($color['subtitle']); ?></div>
            <p class="chc-desc"><?php echo esc_html($color['desc']); ?></p>
            <div class="chc-tags">
                <?php foreach ($color['tags'] as $tag) : ?>
                    <span class="chc-tag"><?php echo esc_html($tag); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="chc-right">
            <?php
            $hero_main    = loraleya_color_photo($upload_url, $photo_prefix, 'hero-servirovka');
            $hero_faktura = loraleya_color_photo($upload_url, $photo_prefix, 'kuvert');
            $hero_detail  = loraleya_color_photo($upload_url, $photo_prefix, 'hero-detail');
            ?>
            <?php if ($hero_main) : ?>
                <div class="chc-img"><img src="<?php echo esc_url($hero_main); ?>" alt="Сервировка <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php else : ?>
                <div class="chc-img">Фото · сервировка в этом цвете</div>
            <?php endif; ?>
            <?php if ($hero_faktura) : ?>
                <div class="chc-img"><img src="<?php echo esc_url($hero_faktura); ?>" alt="Фактура <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php else : ?>
                <div class="chc-img">Макро · фактура</div>
            <?php endif; ?>
            <?php if ($hero_detail) : ?>
                <div class="chc-img"><img src="<?php echo esc_url($hero_detail); ?>" alt="Детали <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php else : ?>
                <div class="chc-img">Детали · салфетка</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 2. VIDEO/PHOTO PLACEHOLDER -->
<section class="video-sec">
    <div class="video-box">
        <div class="vplay">
            <svg viewBox="0 0 24 24"><polygon points="8,5 19,12 8,19"/></svg>
        </div>
        <div class="vlabel">Видео · сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
    </div>
</section>

<!-- 3. MACRO STRIP -->
<div class="macro-strip">
    <?php
    $macros       = ['macro-faktura', 'macro-strochka', 'macro-pereliv'];
    $macro_labels = ['Макро · плетение', 'Макро · строчка', 'Макро · перелив'];
    foreach ($macros as $i => $m) :
        $macro_url = loraleya_color_photo($upload_url, $photo_prefix, $m);
    ?>
        <?php if ($macro_url) : ?>
            <div class="macro-item"><img src="<?php echo esc_url($macro_url); ?>" alt="<?php echo esc_attr($macro_labels[$i]); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover"></div>
        <?php else : ?>
            <div class="macro-item"><?php echo $macro_labels[$i]; ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- 4. SETS -->
<section class="sec sets-bg">
    <div class="sec-ey">Готовые наборы</div>
    <div class="sec-t">Выгоднее на 15% — всё в одном цвете</div>
    <div class="sec-d">Комплект сразу готов к сервировке. Выберите размер под свой стол.</div>
    <div class="sets-grid">
        <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'nabor-4'); ?>

        <div class="set">
            <?php if ($nabor_photo) : ?><div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор <?php echo esc_attr($color['name']); ?>" loading="lazy" style="width:100%;height:auto;margin-bottom:1rem;border:1px solid rgba(197,165,90,.06)"></div><?php endif; ?>
            <div class="set-badge">Хит</div>
            <div class="set-name">Набор на 4 персоны · дорожка 140</div>
            <div class="set-contents">Дорожка 40×140 + 4 салфетки 40×40 + 4 куверта 9×24</div>
            <div class="set-bottom">
                <div class="set-prices">
                    <span class="set-old">3 290 ₽</span>
                    <span class="set-new">2 790 ₽</span>
                </div>
                <button class="btn-set" data-item="Набор 4п/140">В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php if ($nabor_photo) : ?><div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор <?php echo esc_attr($color['name']); ?>" loading="lazy" style="width:100%;height:auto;margin-bottom:1rem;border:1px solid rgba(197,165,90,.06)"></div><?php endif; ?>
            <div class="set-badge">Хит плюс</div>
            <div class="set-name">Набор на 4 персоны · дорожка 175</div>
            <div class="set-contents">Дорожка 40×175 + 4 салфетки 40×40 + 4 куверта 9×24</div>
            <div class="set-bottom">
                <div class="set-prices">
                    <span class="set-old">3 490 ₽</span>
                    <span class="set-new">2 970 ₽</span>
                </div>
                <button class="btn-set" data-item="Набор 4п/175">В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php if ($nabor_photo) : ?><div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор <?php echo esc_attr($color['name']); ?>" loading="lazy" style="width:100%;height:auto;margin-bottom:1rem;border:1px solid rgba(197,165,90,.06)"></div><?php endif; ?>
            <div class="set-badge">Семейный</div>
            <div class="set-name">Набор на 6 персон · дорожка 240</div>
            <div class="set-contents">Дорожка 40×240 + 6 салфеток 40×40 + 6 кувертов 9×24</div>
            <div class="set-bottom">
                <div class="set-prices">
                    <span class="set-old">4 490 ₽</span>
                    <span class="set-new">3 820 ₽</span>
                </div>
                <button class="btn-set" data-item="Набор 6п/140">В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php if ($nabor_photo) : ?><div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор <?php echo esc_attr($color['name']); ?>" loading="lazy" style="width:100%;height:auto;margin-bottom:1rem;border:1px solid rgba(197,165,90,.06)"></div><?php endif; ?>
            <div class="set-badge">Для большого стола</div>
            <div class="set-name">Набор на 6 персон · дорожка 300</div>
            <div class="set-contents">Дорожка 40×300 + 6 салфеток 40×40 + 6 кувертов 9×24</div>
            <div class="set-bottom">
                <div class="set-prices">
                    <span class="set-old">4 690 ₽</span>
                    <span class="set-new">3 990 ₽</span>
                </div>
                <button class="btn-set" data-item="Набор 6п/175">В корзину</button>
            </div>
        </div>

    </div>
</section>

<!-- 5. ALL PRODUCTS -->
<section class="sec">
    <div class="sec-ey">Все изделия · <?php echo esc_html($color['name']); ?></div>
    <div class="sec-t">Поштучно</div>
    <div class="sec-d">Для нестандартных столов или когда нужно дополнить набор</div>
    <div class="products">
        <?php
        $products = [
            [
                'cat'   => 'Дорожка',
                'name'  => 'Дорожка на стол',
                'photo' => 'dorozhka',
                'default' => 1,
                'variants' => [
                    ['label' => '140', 'size' => '40 × 140 см · Входит в наборы',    'price' => '890 ₽',   'item' => 'Дорожка 140'],
                    ['label' => '175', 'size' => '40 × 175 см · Входит в наборы',    'price' => '990 ₽',   'item' => 'Дорожка 175'],
                    ['label' => '240', 'size' => '40 × 240 см · Для длинных столов', 'price' => '1 290 ₽', 'item' => 'Дорожка 240'],
                    ['label' => '300', 'size' => '40 × 300 см · Максимальный размер','price' => '1 590 ₽', 'item' => 'Дорожка 300'],
                ],
            ],
            [
                'cat'   => 'Скатерть',
                'name'  => 'Скатерть',
                'photo' => 'dorozhka',
                'default' => 0,
                'variants' => [
                    ['label' => '175', 'size' => '140 × 175 см · На 4 персоны', 'price' => '2 490 ₽', 'item' => 'Скатерть 175'],
                    ['label' => '220', 'size' => '140 × 220 см · На 6 персон',  'price' => '2 990 ₽', 'item' => 'Скатерть 220'],
                    ['label' => '240', 'size' => '140 × 240 см · На 8 персон',  'price' => '3 490 ₽', 'item' => 'Скатерть 240'],
                ],
            ],
            [
                'cat'   => 'Салфетка',
                'name'  => 'Салфетка сервировочная',
                'photo' => 'salfetka-tsvetok',
                'size'  => '40 × 40 см · Цена за 1 шт',
                'price' => '350 ₽ <span>/ шт</span>',
                'item'  => 'Салфетка',
            ],
            [
                'cat'   => 'Куверт',
                'name'  => 'Куверт для приборов',
                'photo' => 'kuvert',
                'size'  => '9 × 24 см · Цена за 1 шт',
                'price' => '250 ₽ <span>/ шт</span>',
                'item'  => 'Куверт',
            ],
        ];

        foreach ($products as $p) :
            $photo_url   = loraleya_color_photo($upload_url, $photo_prefix, $p['photo']);
            $has_variants = !empty($p['variants']);
            $default     = $has_variants ? ($p['default'] ?? 0) : 0;
            $current     = $has_variants ? $p['variants'][$default] : $p;
        ?>
        <div class="prod<?php echo $has_variants ? ' prod--variants' : ''; ?>">
            <?php if ($photo_url) : ?>
                <div class="prod-img"><img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($p['name']); ?>" loading="lazy"></div>
            <?php else : ?>
                <div class="prod-img">Фото <?php echo esc_html(mb_strtolower($p['cat'])); ?></div>
            <?php endif; ?>

            <div class="prod-cat"><?php echo esc_html($p['cat']); ?></div>
            <div class="prod-name"><?php echo esc_html($p['name']); ?></div>

            <?php if ($has_variants) : ?>
                <div class="prod-sizes">
                    <?php foreach ($p['variants'] as $i => $v) :
                        preg_match('/([\d\s]+)\s*₽/u', $v['price'], $vm);
                        $v_num = isset($vm[1]) ? (int) str_replace(' ', '', $vm[1]) : 0;
                        $v_old = $v_num > 0 ? round($v_num / 0.85 / 10) * 10 : 0;
                    ?>
                        <button
                            class="prod-size-btn<?php echo $i === $default ? ' is-active' : ''; ?>"
                            data-size="<?php echo esc_attr($v['size']); ?>"
                            data-price="<?php echo esc_attr($v['price']); ?>"
                            data-price-old="<?php echo $v_old > 0 ? number_format($v_old, 0, '.', ' ') . ' ₽' : ''; ?>"
                            data-item="<?php echo esc_attr($v['item']); ?>"
                            type="button"
                        ><?php echo esc_html($v['label']); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="prod-size"><?php echo esc_html($current['size']); ?></div>

            <div class="prod-bottom">
                <div class="prod-price">
                    <?php
                    preg_match('/([\d\s]+)\s*₽/u', $current['price'], $m);
                    $price_num = isset($m[1]) ? (int) str_replace(' ', '', $m[1]) : 0;
                    $old_price = $price_num > 0 ? round($price_num / 0.85 / 10) * 10 : 0;
                    $suffix = '';
                    if (preg_match('/₽\s*(<span[^>]*>.*?<\/span>)/iu', $current['price'], $sm)) {
                        $suffix = ' ' . $sm[1];
                    }
                    ?>
                    <?php if ($old_price > 0) : ?>
                        <span class="prod-price-old"><?php echo number_format($old_price, 0, '.', ' '); ?> ₽</span>
                    <?php endif; ?>
                    <span class="prod-price-now"><?php echo number_format($price_num, 0, '.', ' '); ?> ₽<?php echo $suffix; ?></span>
                </div>
                <button class="btn-prod" data-item="<?php echo esc_attr($current['item']); ?>">В корзину</button>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- 6. CARE -->
<section class="sec" style="padding-top:0">
    <div class="care-box">
        <h3>Уход за изделиями</h3>
        <div class="care-item">Машинная стирка при 30°C, деликатный режим</div>
        <div class="care-item">Не использовать отбеливатель</div>
        <div class="care-item">Гладить при средней температуре через ткань</div>
        <div class="care-item">Не сушить в барабане</div>
        <div class="care-item">100% полиэстер — быстро сохнет, не мнётся</div>
    </div>
</section>

<!-- 7. SCENARIOS -->
<section class="sec" style="border-top:1px solid rgba(197,165,90,.06)">
    <div class="sec-ey">Этот цвет подходит для</div>
    <div class="sec-t">Рекомендуемые сценарии</div>
    <div class="scen-grid">
        <?php foreach ($color['scenarios'] as $sc_slug) :
            $sc = $scenario_meta[$sc_slug] ?? null;
            if (!$sc) continue;
        ?>
            <a href="<?php echo home_url('/scenario/' . $sc_slug . '/'); ?>" class="scen">
                <div class="scen-num"><?php echo esc_html($sc['num']); ?></div>
                <div class="scen-name"><?php echo esc_html($sc['name']); ?></div>
                <div class="scen-hint"><?php echo esc_html($sc['hint']); ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- 8. OTHER COLORS -->
<section class="sec" style="border-top:1px solid rgba(197,165,90,.06)">
    <div class="sec-ey">Другие цвета</div>
    <div class="sec-t">Вся палитра LoraLeya</div>
    <div class="other-colors">
        <?php
        $all_oc = [
            ['slug' => 'bezhevyj',        'hex' => '#d4c5a0', 'name' => 'Бежевый',        'border' => ''],
            ['slug' => 'belyj',           'hex' => '#f0ece4', 'name' => 'Белый',           'border' => 'border:1px solid #aaa;'],
            ['slug' => 'biryuza',         'hex' => '#5eb8a8', 'name' => 'Бирюза',          'border' => ''],
            ['slug' => 'blek-zoloto',     'hex' => '#2a2520', 'name' => 'Блек золото',     'border' => 'border:1px solid #555;'],
            ['slug' => 'bronza',          'hex' => '#8b6e3a', 'name' => 'Бронза',          'border' => ''],
            ['slug' => 'goluboj',         'hex' => '#8bb8d0', 'name' => 'Голубой',         'border' => ''],
            ['slug' => 'grafit',          'hex' => '#4a4844', 'name' => 'Графит',          'border' => ''],
            ['slug' => 'zelenyj',         'hex' => '#6b8a5e', 'name' => 'Зелёный',         'border' => ''],
            ['slug' => 'melanzh-zoloto',  'hex' => '#c8a85a', 'name' => 'Меланж золото',   'border' => ''],
            ['slug' => 'melanzh-serebro', 'hex' => '#b0b0a8', 'name' => 'Меланж серебро',  'border' => ''],
            ['slug' => 'melanzh-seryj',   'hex' => '#787874', 'name' => 'Меланж серый',    'border' => ''],
            ['slug' => 'melanzh-chernyj', 'hex' => '#2e2e2c', 'name' => 'Меланж чёрный',  'border' => 'border:1px solid #555;'],
            ['slug' => 'platina',         'hex' => '#c8c0b8', 'name' => 'Платина',         'border' => ''],
            ['slug' => 'serebro',         'hex' => '#c0c0c0', 'name' => 'Серебро',         'border' => ''],
            ['slug' => 'sirenevyj',       'hex' => '#b088b0', 'name' => 'Сиреневый',       'border' => ''],
            ['slug' => 'temno-biryuzovyj','hex' => '#3a7878', 'name' => 'Тёмно-бирюзовый','border' => ''],
            ['slug' => 'fioletovyj',      'hex' => '#6a3a7a', 'name' => 'Фиолетовый',     'border' => ''],
        ];
        foreach ($all_oc as $oc) :
            $is_current = ($oc['slug'] === $slug);
            $active_style = $is_current ? 'border-color:var(--gold);' : '';
        ?>
            <?php if ($is_current) : ?>
                <span class="oc-link" style="background:<?php echo $oc['hex']; ?>;<?php echo $oc['border']; ?><?php echo $active_style; ?>" title="<?php echo esc_attr($oc['name']); ?>"></span>
            <?php else : ?>
                <a class="oc-link" href="<?php echo home_url('/fabric_color/' . $oc['slug'] . '/'); ?>" style="background:<?php echo $oc['hex']; ?>;<?php echo $oc['border']; ?>" title="<?php echo esc_attr($oc['name']); ?>"></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- 9. STICKY BAR -->
<div class="sticky-bar" id="stickyBar">
    <div class="sb-info">
        <div class="sb-dot" style="background:<?php echo $color['hex']; ?>"></div>
        <span class="sb-text"><?php echo esc_html($color['name']); ?></span>
        <span class="sb-total" id="sbTotal">0 ₽</span>
    </div>
    <button class="sb-btn" id="sbBtn">Оформить заказ →</button>
</div>

<?php get_footer(); ?>
