<?php
/**
 * Template: Страница цвета ткани (taxonomy: pa_fabric_color)
 */

$scenario_meta = [
    'romanticheskij-uzhin' => ['num' => '01', 'name' => 'Романтический ужин', 'hint' => '2 персоны · свечи · вечер'],
    'semejnyj-obed'        => ['num' => '02', 'name' => 'Семейный обед',       'hint' => '4–6 персон · дневной свет · тепло'],
    'prazdnichnyj-stol'    => ['num' => '03', 'name' => 'Праздничный стол',    'hint' => '6+ персон · декор · шампанское'],
    'kazhdyj-den'          => ['num' => '04', 'name' => 'Каждый день',         'hint' => '2–4 персоны · минимализм · уют'],
    'den-rozhdenija'       => ['num' => '05', 'name' => 'День рождения',       'hint' => '4–8 персон · цветной декор · праздник'],
];

$colors_data = [
    'fioletovyj' => [
        'name'         => 'Фиолетовый',
        'subtitle'     => 'Глубокий и драматичный — для камерных вечеров',
        'hex'          => '#6a3a7a',
        'accent'       => '#6a3a7a',
        'accent_light' => '#8b5a9a',
        'gradient'     => 'linear-gradient(135deg, #2d1a38 0%, #1a0f22 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(106,58,122,.12)',
        'desc'         => 'Глубокий фиолетовый — один из самых выразительных оттенков коллекции. Жаккард переливается от баклажанового до сливового в зависимости от света, в полумраке становится почти чёрным. Главный цвет камерного романтического стола.',
        'tags'         => ['Романтический ужин', 'Праздничный стол', 'День рождения'],
        'scenarios'    => ['romanticheskij-uzhin', 'prazdnichnyj-stol', 'den-rozhdenija'],
        'photo_prefix' => 'fioletoviy',
    ],
    'grafit' => [
        'name'         => 'Графит',
        'subtitle'     => 'Сдержанный и графичный — современная классика',
        'hex'          => '#4a4844',
        'accent'       => '#4a4844',
        'accent_light' => '#6a6862',
        'gradient'     => 'linear-gradient(135deg, #252320 0%, #1a1918 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(74,72,68,.12)',
        'desc'         => 'Графит — глубокий тёмно-серый с холодным подтоном и контрастным мраморным рисунком. Не чёрный, но почти: на свету выглядит как угольно-серый, в полумраке становится темнее. Один из самых универсальных тёмных оттенков для современной сервировки.',
        'tags'         => ['Романтический ужин', 'Каждый день', 'Праздничный стол'],
        'scenarios'    => ['romanticheskij-uzhin', 'kazhdyj-den', 'prazdnichnyj-stol'],
        'photo_prefix' => 'grafit',
    ],
    'bronza' => [
        'name'         => 'Бронза',
        'subtitle'     => 'Тёплый и торжественный — золотой свет жаккарда',
        'hex'          => '#8b6e3a',
        'accent'       => '#8b6e3a',
        'accent_light' => '#a8884a',
        'gradient'     => 'linear-gradient(135deg, #2a2010 0%, #1a150a 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(139,110,58,.12)',
        'desc'         => 'Бронза — тёплый золотисто-коричневый оттенок с медовым переливом. Самый «праздничный» из тёмных тонов коллекции: создаёт ощущение тепла, торжественности и обжитой роскоши. Особенно красив при свечах и тёплом освещении.',
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'День рождения'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija'],
        'photo_prefix' => 'bronza',
    ],
    'sirenevyj' => [
        'name'         => 'Сиреневый',
        'subtitle'     => 'Нежный и романтичный — пыльная сирень в жаккарде',
        'hex'          => '#b088b0',
        'accent'       => '#b088b0',
        'accent_light' => '#c4a0c4',
        'gradient'     => 'linear-gradient(135deg, #2a1a2a 0%, #1a1018 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(176,136,176,.12)',
        'desc'         => 'Сиреневый — нежный фиалково-розовый оттенок с пыльным подтоном. Не «детский» розовый и не строгий лавандовый, а спокойная сирень с мраморным переливом. Подходит для романтических и весенних сервировок, особенно красив при дневном свете.',
        'tags'         => ['Романтический ужин', 'День рождения', 'Семейный обед'],
        'scenarios'    => ['romanticheskij-uzhin', 'den-rozhdenija', 'semejnyj-obed'],
        'photo_prefix' => 'sireneviy',
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
        'tags'         => ['Семейный обед', 'Каждый день', 'Праздничный стол'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den', 'prazdnichnyj-stol'],
        'photo_prefix' => 'bezheviy',
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
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'Каждый день'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin', 'kazhdyj-den'],
        'photo_prefix' => 'beliy',
    ],
    'biryuza' => [
        'name'         => 'Бирюза',
        'subtitle'     => 'Свежий и средиземноморский — летний жаккард',
        'hex'          => '#5eb8a8',
        'accent'       => '#5eb8a8',
        'accent_light' => '#78ccbc',
        'gradient'     => 'linear-gradient(135deg, #122a25 0%, #0e1a18 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(94,184,168,.12)',
        'desc'         => 'Бирюза — насыщенный голубовато-зелёный с морским подтоном. Один из самых «летних» оттенков коллекции: вызывает ассоциации со средиземноморской верандой, белым фарфором и солнечным днём. Не «детский» бирюзовый, а ткань с глубиной и мраморным переливом.',
        'tags'         => ['Семейный обед', 'День рождения', 'Каждый день'],
        'scenarios'    => ['semejnyj-obed', 'den-rozhdenija', 'kazhdyj-den'],
        'photo_prefix' => 'biruza',
    ],
    'blek-zoloto' => [
        'name'         => 'Блек золото',
        'subtitle'     => 'Графичный и торжественный — золото на чёрном',
        'hex'          => '#2a2520',
        'accent'       => '#2a2520',
        'accent_light' => '#4a4035',
        'gradient'     => 'linear-gradient(135deg, #1a1510 0%, #100e0a 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(42,37,32,.15)',
        'desc'         => 'Золотая жаккардовая ткань с глубоким переливом; изнанка чёрная, что делает золото на лице плотным и благородным. Парадный выбор для торжественного стола. Особенно красив при свечах.',
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'День рождения'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija'],
        'photo_prefix' => 'blek-zoloto',
    ],
    'goluboj' => [
        'name'         => 'Голубой',
        'subtitle'     => 'Прохладный и спокойный — небесный жаккард',
        'hex'          => '#8bb8d0',
        'accent'       => '#8bb8d0',
        'accent_light' => '#a0cce0',
        'gradient'     => 'linear-gradient(135deg, #1a2530 0%, #101820 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(139,184,208,.12)',
        'desc'         => 'Голубой — небесно-голубой с лёгким серым подтоном. Спокойный и неутомляющий, в отличие от насыщенной бирюзы. Подходит для светлой повседневной и семейной сервировки, особенно красиво смотрится при дневном свете и в интерьерах с большими окнами.',
        'tags'         => ['Семейный обед', 'Каждый день', 'День рождения'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den', 'den-rozhdenija'],
        'photo_prefix' => 'goluboy',
    ],
    'zelenyj' => [
        'name'         => 'Зелёный',
        'subtitle'     => 'Природный и тёплый — оливково-травяной жаккард',
        'hex'          => '#6b8a5e',
        'accent'       => '#6b8a5e',
        'accent_light' => '#82a472',
        'gradient'     => 'linear-gradient(135deg, #1a2518 0%, #101a0e 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(107,138,94,.12)',
        'desc'         => 'Зелёный — тёплый травяно-оливковый оттенок с природным подтоном. Не яркий и не холодный, а спокойная глубокая зелень с мраморным переливом. Главный «природный» цвет коллекции, особенно красив на семейном столе с натуральной посудой и деревянными элементами.',
        'tags'         => ['Семейный обед', 'Каждый день', 'Праздничный стол'],
        'scenarios'    => ['semejnyj-obed', 'kazhdyj-den', 'prazdnichnyj-stol'],
        'photo_prefix' => 'zeleniy',
    ],
    'melanzh-zoloto' => [
        'name'         => 'Меланж золото',
        'subtitle'     => 'Торжественный и многослойный — меланжевое золото',
        'hex'          => '#c8a85a',
        'accent'       => '#c8a85a',
        'accent_light' => '#d8bc70',
        'gradient'     => 'linear-gradient(135deg, #2a2210 0%, #1a1808 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(200,168,90,.12)',
        'desc'         => 'Меланж золото — золотистый оттенок с тёплым многослойным переливом. Не однотонный «жёлтый», а ткань с глубоким мраморным рисунком: одни нити отливают медью, другие — пшеницей, третьи — янтарём. Базовый цвет торжественного и юбилейного стола.',
        'tags'         => ['Праздничный стол', 'День рождения', 'Романтический ужин'],
        'scenarios'    => ['prazdnichnyj-stol', 'den-rozhdenija', 'romanticheskij-uzhin'],
        'photo_prefix' => 'melanzh-zoloto',
    ],
    'melanzh-serebro' => [
        'name'         => 'Меланж серебро',
        'subtitle'     => 'Холодный и нейтральный — серебристый меланж',
        'hex'          => '#b0b0a8',
        'accent'       => '#b0b0a8',
        'accent_light' => '#c4c4bc',
        'gradient'     => 'linear-gradient(135deg, #222220 0%, #181818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(176,176,168,.10)',
        'desc'         => 'Меланж серебро — холодный серебристый оттенок со светлым многослойным переливом. Не «металл», а ткань с серым подтоном и жемчужным свечением. Универсален для современной минималистичной и сдержанно-торжественной сервировки. Особенно красив под холодным дневным светом.',
        'tags'         => ['Праздничный стол', 'Каждый день', 'Семейный обед'],
        'scenarios'    => ['prazdnichnyj-stol', 'kazhdyj-den', 'semejnyj-obed'],
        'photo_prefix' => 'melanzh-serebro',
    ],
    'melanzh-seryj' => [
        'name'         => 'Меланж серый',
        'subtitle'     => 'Универсальный и спокойный — серый меланж',
        'hex'          => '#787874',
        'accent'       => '#787874',
        'accent_light' => '#929290',
        'gradient'     => 'linear-gradient(135deg, #222220 0%, #161614 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(120,120,116,.12)',
        'desc'         => 'Меланж серый — нейтральный среднесерый с глубоким мраморным переливом. Не «офисный» серый и не графит, а спокойный универсальный нейтраль между двумя крайностями. Самый «универсальный» из тёмных оттенков — подходит почти к любой посуде и любому интерьеру.',
        'tags'         => ['Каждый день', 'Семейный обед', 'Романтический ужин'],
        'scenarios'    => ['kazhdyj-den', 'semejnyj-obed', 'romanticheskij-uzhin'],
        'photo_prefix' => 'melanzh-seriy',
    ],
    'melanzh-chernyj' => [
        'name'         => 'Меланж чёрный',
        'subtitle'     => 'Графический и драматичный — чёрный меланж',
        'hex'          => '#2e2e2c',
        'accent'       => '#2e2e2c',
        'accent_light' => '#484846',
        'gradient'     => 'linear-gradient(135deg, #1a1a18 0%, #101010 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(46,46,44,.15)',
        'desc'         => 'Меланж чёрный — глубокий чёрный с серебристым мраморным переливом. Не «плоский» чёрный, а ткань с подсветкой переплетения: рисунок проявляется при близком свете. Для самой драматичной и графической сервировки — преимущественно вечерней.',
        'tags'         => ['Романтический ужин', 'Праздничный стол', 'День рождения'],
        'scenarios'    => ['romanticheskij-uzhin', 'prazdnichnyj-stol', 'den-rozhdenija'],
        'photo_prefix' => 'melanzh-cherniy',
    ],
    'platina' => [
        'name'         => 'Платина',
        'subtitle'     => 'Холодный и современный — платиновый жаккард',
        'hex'          => '#c8c0b8',
        'accent'       => '#c8c0b8',
        'accent_light' => '#d8d2ca',
        'gradient'     => 'linear-gradient(135deg, #242220 0%, #1a1818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(200,192,184,.10)',
        'desc'         => 'Платина — холодный жемчужно-серебристый оттенок со светлым подтоном. Не белый и не серый, а «третий путь» между ними: ровный, прохладный, с тонким переливом. Идеальная база для современной минималистичной сервировки и торжественного стола в холодной палитре.',
        'tags'         => ['Праздничный стол', 'Каждый день', 'Романтический ужин'],
        'scenarios'    => ['prazdnichnyj-stol', 'kazhdyj-den', 'romanticheskij-uzhin'],
        'photo_prefix' => 'platina',
    ],
    'serebro' => [
        'name'         => 'Серебро',
        'subtitle'     => 'Торжественный и сияющий — серебро жаккарда',
        'hex'          => '#c0c0c0',
        'accent'       => '#c0c0c0',
        'accent_light' => '#d4d4d4',
        'gradient'     => 'linear-gradient(135deg, #222222 0%, #181818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(192,192,192,.10)',
        'desc'         => 'Серебро — выраженно-серебристый оттенок с холодным металлическим переливом. Самый «сияющий» из светлых тонов коллекции: эффект отражения света создаёт визуальный объём, идеальный для зимнего и новогоднего стола. Альтернатива меланж-серебру с большей интенсивностью блика.',
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'День рождения'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija'],
        'photo_prefix' => 'serebro',
    ],
    'temno-biryuzovyj' => [
        'name'         => 'Тёмно-бирюзовый',
        'subtitle'     => 'Глубокий и драгоценный — изумрудный жаккард',
        'hex'          => '#3a7878',
        'accent'       => '#3a7878',
        'accent_light' => '#4a9090',
        'gradient'     => 'linear-gradient(135deg, #122220 0%, #0e1818 30%, #0e0e0c 70%)',
        'glow_rgba'    => 'rgba(58,120,120,.12)',
        'desc'         => 'Тёмно-бирюзовый — глубокий сине-зелёный оттенок с изумрудным подтоном. Не яркая бирюза, а её «вечерняя» драгоценная версия: ткань кажется почти чёрной в полумраке, проявляет насыщенный изумруд под прямым светом. Один из самых «дорогих» по виду цветов коллекции.',
        'tags'         => ['Праздничный стол', 'Романтический ужин', 'День рождения'],
        'scenarios'    => ['prazdnichnyj-stol', 'romanticheskij-uzhin', 'den-rozhdenija'],
        'photo_prefix' => 'temno-biruza',
    ],
];

$loraleya_default_faq = [
    [
        'question' => 'Из чего сделана ткань — это полиэстер или хлопок?',
        'answer'   => 'Жаккард LoraLeya — это 100% полиэстер с характерным мраморным переплетением. Полиэстер не вытирается, не выцветает после стирок, держит цвет глубже, чем смесовые ткани, и быстро гладится. Стирка при 30°C в машине с любым моющим средством без отбеливателя.',
    ],
    [
        'question' => 'Какой размер дорожки выбрать под мой стол?',
        'answer'   => 'Правило простое: дорожка короче стола на 30–40 см. На стол 170 см берите дорожку 140; на 200–220 см — 175; на овальный или длинный 240+ см — 240 или 300. Для нестандартного стола (круглого, овального, длиннее 300 см) оформите индивидуальный пошив.',
    ],
    [
        'question' => 'Что входит в готовый набор и насколько он выгоднее поштучно?',
        'answer'   => 'Набор включает дорожку, четыре или шесть салфеток 40×40 см и столько же кувертов (конвертов для столовых приборов) 9×24 см — всё в одном цвете и из одной жаккардовой ткани. Готовый набор выгоднее поштучного сбора того же состава на 15%.',
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

function loraleya_color_video($prefix) {
    $search_title = $prefix . '-video';

    $attachment = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'video',
        'numberposts'    => 1,
        'title'          => $search_title,
    ]);

    if (!empty($attachment)) {
        return [
            'url'  => wp_get_attachment_url($attachment[0]->ID),
            'mime' => get_post_mime_type($attachment[0]->ID),
        ];
    }

    $attachment = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'video',
        'numberposts'    => 1,
        's'              => $search_title,
    ]);

    if (!empty($attachment)) {
        return [
            'url'  => wp_get_attachment_url($attachment[0]->ID),
            'mime' => get_post_mime_type($attachment[0]->ID),
        ];
    }

    return null;
}

get_header();
?>

<style>
    .color-hero-bg  { background: <?php echo $color['gradient']; ?>; }
    .color-hero-glow { background: radial-gradient(ellipse, <?php echo $color['glow_rgba']; ?> 0%, transparent 70%); }
    .chc-tag        { border-color: rgba(197,165,90,.35); color: #c5a55a; text-decoration: none; }
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
                <?php
                $tag_links = isset($color['scenarios']) ? $color['scenarios'] : [];
                foreach ($color['tags'] as $i => $tag) :
                    $href = isset($tag_links[$i]) ? home_url('/scenarios/' . $tag_links[$i] . '/') : '';
                ?>
                    <?php if ($href) : ?>
                        <a href="<?php echo esc_url($href); ?>" class="chc-tag"><?php echo esc_html($tag); ?></a>
                    <?php else : ?>
                        <span class="chc-tag"><?php echo esc_html($tag); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="chc-right">
            <?php
            $hero_main   = loraleya_color_photo($upload_url, $photo_prefix, 'hero-servirovka');
            $hero_kuvert = loraleya_color_photo($upload_url, $photo_prefix, 'kuvert');
            $hero_detail = loraleya_color_photo($upload_url, $photo_prefix, 'hero-detail');
            ?>

            <div class="chc-main">
                <?php if ($hero_main) : ?>
                    <img src="<?php echo esc_url($hero_main); ?>" alt="Сервировка <?php echo esc_attr($color['name']); ?>" loading="lazy">
                <?php else : ?>
                    <span class="chc-ph">Фото · сервировка в этом цвете</span>
                <?php endif; ?>
            </div>

            <div class="chc-side">
                <div class="chc-detail">
                    <?php if ($hero_detail) : ?>
                        <img src="<?php echo esc_url($hero_detail); ?>" alt="Детали · салфетка <?php echo esc_attr($color['name']); ?>" loading="lazy">
                    <?php else : ?>
                        <span class="chc-ph">Детали · салфетка</span>
                    <?php endif; ?>
                </div>
                <div class="chc-kuvert">
                    <?php if ($hero_kuvert) : ?>
                        <img src="<?php echo esc_url($hero_kuvert); ?>" alt="Куверт <?php echo esc_attr($color['name']); ?>" loading="lazy">
                    <?php else : ?>
                        <span class="chc-ph">Куверт · веер</span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. VIDEO -->
<section class="video-sec">
    <?php
    $color_video = loraleya_color_video($photo_prefix);

    // Ken Burns fallback: если видео нет — собрать до 3 фото-кадров.
    // Приоритет — сервировка и перелив (показывают «при разном освещении»).
    // НАСТРАИВАЕТСЯ: порядок типов и максимум кадров.
    $kb_types  = ['hero-servirovka', 'macro-pereliv', 'hero-detail', 'macro-faktura', 'salfetka-tsvetok'];
    $kb_max    = 3;
    $kb_frames = [];
    if (!$color_video) {
        foreach ($kb_types as $kb_t) {
            $kb_u = loraleya_color_photo($upload_url, $photo_prefix, $kb_t);
            if ($kb_u) {
                $kb_frames[] = $kb_u;
                if (count($kb_frames) >= $kb_max) break;
            }
        }
    }
    $kb_count = count($kb_frames);
    ?>

    <?php if ($color_video) : ?>
        <div class="video-box video-box--playable">
            <video
                src="<?php echo esc_url($color_video['url']); ?>"
                preload="metadata"
                controls
                playsinline
                aria-label="Сервировка <?php echo esc_attr(mb_strtolower($color['name'])); ?> при разном освещении"
            ></video>
        </div>
    <?php elseif ($kb_count > 0) : ?>
        <div class="video-box video-box--kenburns kb--<?php echo (int)$kb_count; ?>"
             role="img"
             aria-label="Сервировка <?php echo esc_attr(mb_strtolower($color['name'])); ?> при разном освещении">
            <?php foreach ($kb_frames as $kb_i => $kb_src) : ?>
                <div class="kb-frame">
                    <img src="<?php echo esc_url($kb_src); ?>" alt="" <?php echo $kb_i === 0 ? '' : 'loading="lazy"'; ?>>
                </div>
            <?php endforeach; ?>
            <div class="vlabel vlabel--overlay">Сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
        </div>
    <?php else : ?>
        <div class="video-box video-box--empty">
            <div class="vlabel">Видео · сервировка <?php echo esc_html(mb_strtolower($color['name'])); ?> при разном освещении</div>
        </div>
    <?php endif; ?>
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
        <?php
        $item_map    = loraleya_build_item_map($slug);
        $item_prices = loraleya_get_item_prices($slug);

        // Хелпер: форматирует цену в "1 290 ₽" или "" если null
        $fmt_price = function($p) {
            return $p !== null ? number_format((int)$p, 0, '.', ' ') . ' ₽' : '';
        };
        ?>

        <div class="set">
            <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'nabor-4-140'); ?>
            <?php if ($nabor_photo) : ?>
                <div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор 4п · дорожка 140 · <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php endif; ?>
            <div class="set-badge">Хит</div>
            <div class="set-name">Набор на 4 персоны · дорожка 140</div>
            <div class="set-contents">Дорожка 40×140 + 4 салфетки 40×40 + 4 куверта 9×24</div>
            <div class="set-bottom">
                <?php
                $ip = $item_prices['Набор 4п/140'] ?? [];
                $new_p = $ip['price'] ?? null;
                $old_p = $ip['old_price'] ?? null;
                ?>
                <div class="set-prices">
                    <?php if ($old_p) : ?><span class="set-old"><?php echo $fmt_price($old_p); ?></span><?php endif; ?>
                    <?php if ($new_p) : ?><span class="set-new"><?php echo $fmt_price($new_p); ?></span><?php endif; ?>
                </div>
                <?php $bi = $item_map['Набор 4п/140'] ?? null; ?>
                <button
                    class="btn-set"
                    data-item="Набор 4п/140"
                    data-product-id="<?php echo (int)($bi['product_id'] ?? 0); ?>"
                    data-variation-id="<?php echo (int)($bi['variation_id'] ?? 0); ?>"
                >В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'nabor-4-175'); ?>
            <?php if ($nabor_photo) : ?>
                <div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор 4п · дорожка 175 · <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php endif; ?>
            <div class="set-badge">Хит плюс</div>
            <div class="set-name">Набор на 4 персоны · дорожка 175</div>
            <div class="set-contents">Дорожка 40×175 + 4 салфетки 40×40 + 4 куверта 9×24</div>
            <div class="set-bottom">
                <?php
                $ip = $item_prices['Набор 4п/175'] ?? [];
                $new_p = $ip['price'] ?? null;
                $old_p = $ip['old_price'] ?? null;
                ?>
                <div class="set-prices">
                    <?php if ($old_p) : ?><span class="set-old"><?php echo $fmt_price($old_p); ?></span><?php endif; ?>
                    <?php if ($new_p) : ?><span class="set-new"><?php echo $fmt_price($new_p); ?></span><?php endif; ?>
                </div>
                <?php $bi = $item_map['Набор 4п/175'] ?? null; ?>
                <button
                    class="btn-set"
                    data-item="Набор 4п/175"
                    data-product-id="<?php echo (int)($bi['product_id'] ?? 0); ?>"
                    data-variation-id="<?php echo (int)($bi['variation_id'] ?? 0); ?>"
                >В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'nabor-6-240'); ?>
            <?php if ($nabor_photo) : ?>
                <div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор 6п · дорожка 240 · <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php endif; ?>
            <div class="set-badge">Семейный</div>
            <div class="set-name">Набор на 6 персон · дорожка 240</div>
            <div class="set-contents">Дорожка 40×240 + 6 салфеток 40×40 + 6 кувертов 9×24</div>
            <div class="set-bottom">
                <?php
                $ip = $item_prices['Набор 6п/240'] ?? [];
                $new_p = $ip['price'] ?? null;
                $old_p = $ip['old_price'] ?? null;
                ?>
                <div class="set-prices">
                    <?php if ($old_p) : ?><span class="set-old"><?php echo $fmt_price($old_p); ?></span><?php endif; ?>
                    <?php if ($new_p) : ?><span class="set-new"><?php echo $fmt_price($new_p); ?></span><?php endif; ?>
                </div>
                <?php $bi = $item_map['Набор 6п/240'] ?? null; ?>
                <button
                    class="btn-set"
                    data-item="Набор 6п/240"
                    data-product-id="<?php echo (int)($bi['product_id'] ?? 0); ?>"
                    data-variation-id="<?php echo (int)($bi['variation_id'] ?? 0); ?>"
                >В корзину</button>
            </div>
        </div>

        <div class="set">
            <?php $nabor_photo = loraleya_color_photo($upload_url, $photo_prefix, 'nabor-6-300'); ?>
            <?php if ($nabor_photo) : ?>
                <div class="set-img"><img src="<?php echo esc_url($nabor_photo); ?>" alt="Набор 6п · дорожка 300 · <?php echo esc_attr($color['name']); ?>" loading="lazy"></div>
            <?php endif; ?>
            <div class="set-badge">Для большого стола</div>
            <div class="set-name">Набор на 6 персон · дорожка 300</div>
            <div class="set-contents">Дорожка 40×300 + 6 салфеток 40×40 + 6 кувертов 9×24</div>
            <div class="set-bottom">
                <?php
                $ip = $item_prices['Набор 6п/300'] ?? [];
                $new_p = $ip['price'] ?? null;
                $old_p = $ip['old_price'] ?? null;
                ?>
                <div class="set-prices">
                    <?php if ($old_p) : ?><span class="set-old"><?php echo $fmt_price($old_p); ?></span><?php endif; ?>
                    <?php if ($new_p) : ?><span class="set-new"><?php echo $fmt_price($new_p); ?></span><?php endif; ?>
                </div>
                <?php $bi = $item_map['Набор 6п/300'] ?? null; ?>
                <button
                    class="btn-set"
                    data-item="Набор 6п/300"
                    data-product-id="<?php echo (int)($bi['product_id'] ?? 0); ?>"
                    data-variation-id="<?php echo (int)($bi['variation_id'] ?? 0); ?>"
                >В корзину</button>
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
                'cat'     => 'Дорожка',
                'name'    => 'Дорожка на стол',
                'photo'   => 'dorozhka',
                'default' => 1,
                'variants' => [
                    ['label' => '140', 'size' => '40 × 140 см · Входит в наборы',     'item' => 'Дорожка 140'],
                    ['label' => '175', 'size' => '40 × 175 см · Входит в наборы',     'item' => 'Дорожка 175'],
                    ['label' => '240', 'size' => '40 × 240 см · Для длинных столов',  'item' => 'Дорожка 240'],
                    ['label' => '300', 'size' => '40 × 300 см · Максимальный размер', 'item' => 'Дорожка 300'],
                ],
            ],
            [
                'cat'     => 'Скатерть',
                'name'    => 'Скатерть',
                'photo'   => 'skatert',
                'default' => 0,
                'variants' => [
                    ['label' => '175', 'size' => '140 × 175 см · На 4 персоны', 'item' => 'Скатерть 175'],
                    ['label' => '220', 'size' => '140 × 220 см · На 6 персон',  'item' => 'Скатерть 220'],
                    ['label' => '240', 'size' => '140 × 240 см · На 8 персон',  'item' => 'Скатерть 240'],
                ],
            ],
            [
                'cat'   => 'Салфетка',
                'name'  => 'Салфетка сервировочная',
                'photo' => 'salfetka-tsvetok',
                'size'  => '40 × 40 см · Цена за 1 шт',
                'item'  => 'Салфетка',
                'suffix' => ' <span>/ шт</span>',
            ],
            [
                'cat'   => 'Куверт',
                'name'  => 'Куверт для приборов',
                'photo' => 'kuvert-vert',
                'size'  => '9 × 24 см · Цена за 1 шт',
                'item'  => 'Куверт',
                'suffix' => ' <span>/ шт</span>',
            ],
        ];

        foreach ($products as $p) :
            $photo_url    = loraleya_color_photo($upload_url, $photo_prefix, $p['photo']);
            $has_variants = !empty($p['variants']);
            $default      = $has_variants ? ($p['default'] ?? 0) : 0;
            $current_item = $has_variants ? $p['variants'][$default]['item'] : $p['item'];
            $current_size = $has_variants ? $p['variants'][$default]['size'] : $p['size'];
            $cur_ip       = $item_prices[$current_item] ?? [];
            $cur_price    = $cur_ip['price'] ?? null;
            $cur_old      = $cur_ip['old_price'] ?? null;
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
                        $v_ip  = $item_prices[$v['item']] ?? [];
                        $v_new = $v_ip['price'] ?? null;
                        $v_old = $v_ip['old_price'] ?? null;
                        $bi_v  = $item_map[$v['item']] ?? null;
                    ?>
                        <button
                            class="prod-size-btn<?php echo $i === $default ? ' is-active' : ''; ?>"
                            data-size="<?php echo esc_attr($v['size']); ?>"
                            data-price="<?php echo $v_new !== null ? esc_attr($fmt_price($v_new)) : ''; ?>"
                            data-price-old="<?php echo $v_old !== null ? esc_attr($fmt_price($v_old)) : ''; ?>"
                            data-item="<?php echo esc_attr($v['item']); ?>"
                            data-product-id="<?php echo (int)($bi_v['product_id'] ?? 0); ?>"
                            data-variation-id="<?php echo (int)($bi_v['variation_id'] ?? 0); ?>"
                            type="button"
                        ><?php echo esc_html($v['label']); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="prod-size"><?php echo esc_html($current_size); ?></div>

            <div class="prod-bottom">
                <div class="prod-price">
                    <?php if ($cur_old !== null) : ?>
                        <span class="prod-price-old"><?php echo $fmt_price($cur_old); ?></span>
                    <?php endif; ?>
                    <?php if ($cur_price !== null) : ?>
                        <span class="prod-price-now"><?php echo $fmt_price($cur_price) . ($p['suffix'] ?? ''); ?></span>
                    <?php endif; ?>
                </div>
                <?php $bi = $item_map[$current_item] ?? null; ?>
                <button
                    class="btn-prod"
                    data-item="<?php echo esc_attr($current_item); ?>"
                    data-product-id="<?php echo (int)($bi['product_id'] ?? 0); ?>"
                    data-variation-id="<?php echo (int)($bi['variation_id'] ?? 0); ?>"
                >В корзину</button>
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

<!-- SEO-ТЕКСТ -->
<?php
$seo_text = $term ? get_term_meta($term->term_id, 'seo_text', true) : '';
if (!empty($seo_text)) :
?>
<section class="color-seo-text">
    <div class="container">
        <div class="color-seo-text__inner">
            <?php echo wp_kses_post($seo_text); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<?php
$faq_json = $term ? get_term_meta($term->term_id, 'seo_faq', true) : '';
if (empty($faq_json) && function_exists('loraleya_get_default_color_faq_json')) {
    $faq_json = loraleya_get_default_color_faq_json();
}
$faq_data = !empty($faq_json) ? json_decode($faq_json, true) : [];
if (is_array($faq_data) && !empty($faq_data)) :
?>
<section class="color-faq">
    <div class="container">
        <div class="color-faq__inner">
            <div class="eyebrow">Частые вопросы</div>
            <h2>Что важно знать об этом оттенке</h2>
            <div class="color-faq__list">
                <?php foreach ($faq_data as $item) : ?>
                    <details class="color-faq__item">
                        <summary class="color-faq__question"><?php echo esc_html($item['question'] ?? ''); ?></summary>
                        <div class="color-faq__answer"><?php echo wp_kses_post($item['answer'] ?? ''); ?></div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 7. SCENARIOS -->
<section class="sec" style="border-top:1px solid rgba(197,165,90,.06)">
    <div class="sec-ey">Этот цвет подходит для</div>
    <div class="sec-t">Рекомендуемые сценарии</div>
    <div class="scen-grid">
        <?php foreach ($color['scenarios'] as $sc_slug) :
            $sc = $scenario_meta[$sc_slug] ?? null;
            if (!$sc) continue;
        ?>
            <a href="<?php echo home_url('/scenarios/' . $sc_slug . '/'); ?>" class="scen">
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
            // Ряд 1 — светлые → тёплые → холодные цветные
            ['slug' => 'belyj',           'hex' => '#f0ece4', 'name' => 'Белый',           'border' => 'border:1px solid #aaa;'],
            ['slug' => 'bezhevyj',        'hex' => '#d4c5a0', 'name' => 'Бежевый',        'border' => ''],
            ['slug' => 'platina',         'hex' => '#c8c0b8', 'name' => 'Платина',         'border' => ''],
            ['slug' => 'melanzh-zoloto',  'hex' => '#c8a85a', 'name' => 'Меланж золото',   'border' => ''],
            ['slug' => 'bronza',          'hex' => '#8b6e3a', 'name' => 'Бронза',          'border' => ''],
            ['slug' => 'sirenevyj',       'hex' => '#b088b0', 'name' => 'Сиреневый',       'border' => ''],
            ['slug' => 'fioletovyj',      'hex' => '#6a3a7a', 'name' => 'Фиолетовый',     'border' => ''],
            ['slug' => 'goluboj',         'hex' => '#8bb8d0', 'name' => 'Голубой',         'border' => ''],
            ['slug' => 'biryuza',         'hex' => '#5eb8a8', 'name' => 'Бирюза',          'border' => ''],
            // Ряд 2 — средние → серые → тёмные
            ['slug' => 'zelenyj',         'hex' => '#6b8a5e', 'name' => 'Зелёный',         'border' => ''],
            ['slug' => 'temno-biryuzovyj','hex' => '#3a7878', 'name' => 'Тёмно-бирюзовый','border' => ''],
            ['slug' => 'serebro',         'hex' => '#c0c0c0', 'name' => 'Серебро',         'border' => ''],
            ['slug' => 'melanzh-serebro', 'hex' => '#b0b0a8', 'name' => 'Меланж серебро',  'border' => ''],
            ['slug' => 'melanzh-seryj',   'hex' => '#787874', 'name' => 'Меланж серый',    'border' => ''],
            ['slug' => 'grafit',          'hex' => '#4a4844', 'name' => 'Графит',          'border' => ''],
            ['slug' => 'melanzh-chernyj', 'hex' => '#2e2e2c', 'name' => 'Меланж чёрный',  'border' => 'border:1px solid #555;'],
            ['slug' => 'blek-zoloto',     'hex' => '#2a2520', 'name' => 'Блек золото',     'border' => 'border:1px solid #555;'],
        ];
        foreach ($all_oc as $oc) :
            $is_current = ($oc['slug'] === $slug);
            $active_style = $is_current ? 'border-color:var(--gold);' : '';
            $swatch_url   = loraleya_color_swatch_url($oc['slug']);
            $bg_style = $swatch_url
                ? 'background-image:url(' . esc_url($swatch_url) . ');background-size:cover;background-position:center;'
                : 'background:' . $oc['hex'] . ';';
        ?>
            <?php if ($is_current) : ?>
                <span class="oc-link" style="<?php echo $bg_style; ?><?php echo $oc['border']; ?><?php echo $active_style; ?>" title="<?php echo esc_attr($oc['name']); ?>"></span>
            <?php else : ?>
                <a class="oc-link" href="<?php echo home_url('/color/' . $oc['slug'] . '/'); ?>" style="<?php echo $bg_style; ?><?php echo $oc['border']; ?>" title="<?php echo esc_attr($oc['name']); ?>"></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>


<!-- FLOATING COLOR SWITCHER -->
<?php
// Текущий цвет — для подсветки на иконке-палитре и обводки в шторке
$current_hex = $color['hex'] ?? '#c8a85a';
$current_name = $color['name'] ?? '';
?>
<div class="lcs-root" id="lcsRoot">

  <!-- Кнопка-палитра -->
  <button class="lcs-btn" id="lcsBtn" aria-label="Открыть палитру цветов" aria-expanded="false">
    <svg class="lcs-icon" viewBox="0 0 32 32" width="36" height="36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="11" cy="9" r="3.5" fill="#c8a85a"/>
      <circle cx="21" cy="11" r="3.5" fill="#5eb8a8"/>
      <circle cx="9" cy="20" r="3.5" fill="#b088b0"/>
      <circle cx="22" cy="22" r="3.5" fill="#8bb8d0"/>
      <circle class="lcs-dot-active" cx="16" cy="16" r="3.5" fill="<?php echo esc_attr($current_hex); ?>"/>
    </svg>
  </button>

  <!-- Шторка с палитрой -->
  <div class="lcs-panel" id="lcsPanel" role="dialog" aria-label="Палитра цветов" aria-hidden="true">
    <div class="lcs-panel-title">палитра</div>
    <div class="lcs-grid">
      <?php foreach ($all_oc as $oc) :
        $is_current = ($oc['slug'] === $slug);
      ?>
        <?php if ($is_current) : ?>
          <span class="lcs-swatch lcs-swatch--active"
                style="background:<?php echo $oc['hex']; ?>;<?php echo $oc['border']; ?>"
                data-name="<?php echo esc_attr($oc['name']); ?>"
                title="<?php echo esc_attr($oc['name']); ?> · текущий"></span>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/color/' . $oc['slug'] . '/')); ?>"
             class="lcs-swatch"
             style="background:<?php echo $oc['hex']; ?>;<?php echo $oc['border']; ?>"
             data-name="<?php echo esc_attr($oc['name']); ?>"
             title="<?php echo esc_attr($oc['name']); ?>"
             aria-label="<?php echo esc_attr($oc['name']); ?>"></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="lcs-current" id="lcsCurrent"><?php echo esc_html($current_name); ?> · текущий</div>
    <button class="lcs-close" id="lcsClose" aria-label="Закрыть палитру">×</button>
  </div>

</div>

<style>
/* === Floating Color Switcher === */
.lcs-root {
    position: fixed;
    right: 30px;
    bottom: 120px; /* над корзиной WC, корзина обычно на bottom: 18px */
    z-index: 9998; /* ниже корзины WC чтобы корзина не перекрывалась если они окажутся рядом */
    pointer-events: none; /* для panel; кнопка ниже включает обратно */
}

.lcs-btn {
    pointer-events: auto;
    width: 69px;
    height: 69px;
    border-radius: 50%;
    background: #1a1814;
    border: 0.5px solid rgba(200,168,90,.4);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    transition: border-color .25s ease, box-shadow .25s ease, transform .15s ease;
    -webkit-tap-highlight-color: transparent;
}
.lcs-btn:hover { border-color: rgba(200,168,90,.7); }
.lcs-btn:active { transform: scale(.96); }
.lcs-btn[aria-expanded="true"] {
    border-color: rgba(200,168,90,.7);
    box-shadow: 0 0 0 2px rgba(200,168,90,.15);
}

.lcs-icon { display: block; }
.lcs-dot-active {
    /* мягкая пульсация активного цвета на палитре */
    animation: lcsDotPulse 3s ease-in-out infinite;
    transform-origin: 16px 16px;
}
@keyframes lcsDotPulse {
    0%, 100% { transform: scale(1); }
    50%      { transform: scale(1.25); }
}

.lcs-panel {
    pointer-events: none;
    position: absolute;
    right: 0;
    bottom: 60px; /* над кнопкой с зазором 14px */
    width: 250px;
    background: rgba(20,18,14,.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 0.5px solid rgba(200,168,90,.3);
    border-radius: 10px;
    padding: 14px 12px 12px;
    opacity: 0;
    transform: scale(.95) translateY(6px);
    transition: opacity .25s ease, transform .25s ease;
}
.lcs-panel--open {
    pointer-events: auto;
    opacity: 1;
    transform: scale(1) translateY(0);
}

.lcs-panel-title {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .18em;
    color: #8a7a55;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 10px;
}

.lcs-grid {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 6px;
}

.lcs-swatch {
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    display: block;
    cursor: pointer;
    transition: transform .15s ease, outline-color .15s ease;
    text-decoration: none;
}
.lcs-swatch:hover { transform: scale(1.15); }

.lcs-swatch--active {
    outline: 1.5px solid #c8a85a;
    outline-offset: 1.5px;
    cursor: default;
}
.lcs-swatch--active:hover { transform: none; }

.lcs-current {
    text-align: center;
    font-size: 11px;
    color: #aaa49a;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 0.5px solid rgba(200,168,90,.15);
    transition: opacity .15s ease;
}

.lcs-close {
    display: none; /* по умолчанию скрыт, на мобиле показываем */
    position: absolute;
    top: 6px;
    right: 8px;
    background: transparent;
    border: 0;
    color: #aaa49a;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    padding: 4px 8px;
}

/* Мобильная версия — фуллскрин */
@media (max-width: 640px) {
    .lcs-panel {
        position: fixed;
        right: 0;
        left: 0;
        bottom: 0;
        top: 0;
        width: auto;
        border-radius: 0;
        padding: 60px 24px 24px;
        background: rgba(14,14,12,.97);
        transform: translateY(100%);
        transition: transform .3s ease, opacity .3s ease;
    }
    .lcs-panel--open {
        transform: translateY(0);
    }
    .lcs-panel-title {
        font-size: 11px;
        margin-bottom: 24px;
    }
    .lcs-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        max-width: 360px;
        margin: 0 auto;
    }
    .lcs-swatch {
        position: relative;
    }
    /* Имя цвета снизу под каждым кружком */
    .lcs-swatch::after {
        content: attr(data-name);
        position: absolute;
        bottom: -22px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        color: #aaa49a;
        white-space: nowrap;
        font-family: 'Cormorant Garamond', Georgia, serif;
        font-style: italic;
    }
    .lcs-grid {
        gap: 18px 8px;
        row-gap: 36px;
    }
    .lcs-current { display: none; }
    .lcs-close { display: block; top: 16px; right: 16px; font-size: 28px; }
}
</style>

<script>
(function(){
    var root = document.getElementById('lcsRoot');
    if (!root) return;
    var btn = document.getElementById('lcsBtn');
    var panel = document.getElementById('lcsPanel');
    var closeBtn = document.getElementById('lcsClose');
    var current = document.getElementById('lcsCurrent');
    var defaultCurrentText = current ? current.textContent : '';

    function open(){
        panel.classList.add('lcs-panel--open');
        btn.setAttribute('aria-expanded', 'true');
        panel.setAttribute('aria-hidden', 'false');
    }
    function close(){
        panel.classList.remove('lcs-panel--open');
        btn.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
        if (current) current.textContent = defaultCurrentText;
    }
    function toggle(){
        if (panel.classList.contains('lcs-panel--open')) close(); else open();
    }

    btn.addEventListener('click', function(e){ e.stopPropagation(); toggle(); });
    if (closeBtn) closeBtn.addEventListener('click', close);

    // Клик вне панели — закрыть
    document.addEventListener('click', function(e){
        if (!root.contains(e.target)) close();
    });

    // Esc — закрыть
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') close();
    });

    // Hover по кружкам — обновляем подпись (только на десктопе, на мобиле подпись под каждым)
    if (current) {
        var swatches = panel.querySelectorAll('.lcs-swatch');
        swatches.forEach(function(s){
            s.addEventListener('mouseenter', function(){
                var name = s.getAttribute('data-name');
                if (name) current.textContent = name;
            });
            s.addEventListener('mouseleave', function(){
                current.textContent = defaultCurrentText;
            });
        });
    }
})();
</script>
<!-- /FLOATING COLOR SWITCHER -->

<?php get_footer(); ?>
