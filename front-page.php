<?php
/**
 * Template Name: Главная LoraLeya
 */
get_header();
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-texture"></div>
    <div class="hero-content">
        <div class="eyebrow">Жаккардовая сервировка стола &middot; Сделано в России</div>
        <h1>Начните с <em class="italic-accent">настроения</em>,<br>мы соберём ваш стол</h1>
        <p class="hero-sub">17 оттенков жаккарда, готовые наборы для красивой сервировки стола.<br>Выберите сценарий — мы соберём идеальный комплект.</p>
        <a class="btn btn--outline" href="#scenarios">
            Выбрать сценарий
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
    </div>
    <div class="hero-scroll">
        <span>Листайте</span>
        <div class="scroll-line"></div>
    </div>
</section>

<!-- SCENARIOS -->
<section class="section" id="scenarios">
    <div class="container">
        <div class="eyebrow">Выберите сценарий</div>
        <h2>Какой стол вы накрываете сегодня?</h2>
        <p class="section-desc">Сервировка — это не выбор товаров, а выбор настроения. От романтического ужина до семейного обеда: пять готовых сценариев под любой повод.</p>

        <div class="scenarios-grid">
            <?php
            $scenarios = new WP_Query([
                'post_type'      => 'scenario',
                'posts_per_page' => 5,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]);
            $num = 1;
            if ($scenarios->have_posts()) :
                while ($scenarios->have_posts()) : $scenarios->the_post();
            ?>
                <a href="<?php the_permalink(); ?>" class="scenario-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="scenario-bg" style="background-image:url(<?php echo get_the_post_thumbnail_url(get_the_ID(), 'scenario-card'); ?>)"></div>
                    <?php else : ?>
                        <div class="scenario-bg scenario-bg--placeholder"></div>
                    <?php endif; ?>
                    <div class="scenario-content">
                        <div class="scenario-num"><?php echo str_pad($num, 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="scenario-name"><?php the_title(); ?></div>
                        <div class="scenario-hint"><?php echo get_the_excerpt(); ?></div>
                    </div>
                </a>
            <?php
                $num++;
                endwhile;
                wp_reset_postdata();
            else :
                // Placeholder scenarios if none created yet
                $placeholders = [
                    ['Романтический ужин', '2 персоны · свечи · приглушённый свет', 'sc-romantic'],
                    ['Семейный обед', '4–6 персон · дневной свет · тепло', 'sc-family'],
                    ['Праздничный стол', '6+ персон · декор · шампанское', 'sc-festive'],
                    ['День рождения', '4–8 персон · цветной декор · праздник', 'sc-birthday'],
                    ['Каждый день', '2–4 персоны · минимализм · уют', 'sc-everyday'],
                ];
                foreach ($placeholders as $i => $p) :
            ?>
                <div class="scenario-card <?php echo $p[2]; ?>">
                    <div class="scenario-bg scenario-bg--placeholder"></div>
                    <div class="scenario-content">
                        <div class="scenario-num"><?php echo str_pad($i + 1, 2, '0', STR_PAD_LEFT); ?></div>
                        <div class="scenario-name"><?php echo $p[0]; ?></div>
                        <div class="scenario-hint"><?php echo $p[1]; ?></div>
                    </div>
                </div>
            <?php endforeach; endif; ?>

            <!-- 6-я ячейка: CTA на индивидуальный заказ -->
            <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>" class="scenario-card scenario-card--cta">
                <div class="scenario-card--cta__bg"></div>
                <div class="scenario-card--cta__content">
                    <div class="scenario-card--cta__mark">✦</div>
                    <div class="scenario-card--cta__name">Свой стол, свой повод</div>
                    <div class="scenario-card--cta__hint">Не подошёл ни один сценарий? Соберём индивидуально под форму, размер и историю стола.</div>
                    <span class="scenario-card--cta__arrow" aria-hidden="true">→</span>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- PALETTE -->
<section class="section section--alt" id="palette">
    <div class="container">
        <div class="eyebrow">Палитра LoraLeya</div>
        <h2>17 оттенков для вашего стола</h2>
        <p class="section-desc">Каждый оттенок жаккарда — это самостоятельный мир сервировки. Однотонные ткани с характерным мраморным переливом: от классической бежевой базы до глубокого графита.</p>

        <div class="colors-wheel">
            <?php
            $colors = get_terms(['taxonomy' => 'pa_fabric_color', 'hide_empty' => false]);
            if (!empty($colors) && !is_wp_error($colors)) :
                foreach ($colors as $color) :
                    $hex = get_term_meta($color->term_id, 'color_hex', true) ?: '#888';
                    $link = get_term_link($color);
                    $swatch_url = function_exists('loraleya_color_swatch_url') ? loraleya_color_swatch_url($color->slug) : '';
                    $bg_style = $swatch_url
                        ? 'background-image:url(' . esc_url($swatch_url) . ');background-size:cover;background-position:center;'
                        : 'background:' . esc_attr($hex) . ';';
            ?>
                <a href="<?php echo $link; ?>" class="color-swatch" style="<?php echo $bg_style; ?>" title="<?php echo esc_attr($color->name); ?>"></a>
            <?php
                endforeach;
            else :
                // Placeholder swatches
                $swatches = [
                    ['#d4c5a0','Бежевый'],['#f0ece4','Белый'],['#5eb8a8','Бирюза'],
                    ['#2a2520','Блек золото'],['#8b6e3a','Бронза'],['#8bb8d0','Голубой'],
                    ['#4a4844','Графит'],['#6b8a5e','Зелёный'],['#c8a85a','Меланж золото'],
                    ['#b0b0a8','Меланж серебро'],['#787874','Меланж серый'],['#2e2e2c','Меланж чёрный'],
                    ['#c8c0b8','Платина'],['#c0c0c0','Серебро'],['#b088b0','Сиреневый'],
                    ['#3a7878','Тёмно-бирюзовый'],['#6a3a7a','Фиолетовый'],
                ];
                foreach ($swatches as $s) :
            ?>
                <div class="color-swatch" style="background:<?php echo $s[0]; ?>" title="<?php echo $s[1]; ?>"></div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<!-- PRODUCTS PREVIEW -->
<?php
$pp_runner_url     = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('melanzh-zoloto', 'dorozhka')      : '';
$pp_tablecloth_url = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('belyj', 'skatert')               : '';
$pp_napkin_url     = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('fioletovyj', 'salfetka-tsvetok')  : '';
$pp_kuvert_url     = function_exists('loraleya_get_color_photo_url') ? loraleya_get_color_photo_url('blek-zoloto', 'kuvert-vert')      : '';
$pp_links = [
    'runner'     => 39,
    'tablecloth' => 44,
    'napkin'     => 48,
    'kuvert'     => 49,
];
$pp_href = [];
foreach ($pp_links as $k => $pid) {
    $pp_href[$k] = ($pid && get_post_status($pid) === 'publish') ? get_permalink($pid) : '';
}

// Минимальная цена товара из WooCommerce (берём min_price вариаций)
function loraleya_pp_min_price($pid) {
    if (!$pid || get_post_status($pid) !== 'publish') return '';
    $product = wc_get_product($pid);
    if (!$product) return '';
    $price = $product->get_price();
    if ($price === '' || $price === null) return '';
    return 'от ' . number_format((float)$price, 0, '.', ' ') . ' ₽';
}
$pp_price = [];
foreach ($pp_links as $k => $pid) {
    $pp_price[$k] = loraleya_pp_min_price($pid);
}
?>
<section class="section">
    <div class="container">
        <div class="eyebrow">Изделия</div>
        <h2>Что входит в сервировку</h2>
        <p class="section-desc">Дорожки на стол, скатерти, тканевые салфетки и куверты (конверты для столовых приборов) — всё из одной жаккардовой ткани, в едином цвете.</p>

        <div class="products-strip">
            <a href="<?php echo esc_url($pp_href['runner']); ?>" class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_runner_url ? 'style="background-image:url(' . esc_url($pp_runner_url) . ')"' : ''; ?>></div>
                <div class="product-preview__text">
                    <div class="product-preview__label">Дорожки</div>
                    <div class="product-preview__size">4 размера: от 140 до 300 см</div>
                    <?php if ($pp_price['runner']) : ?><div class="product-preview__price"><?php echo esc_html($pp_price['runner']); ?></div><?php endif; ?>
                </div>
            </a>
            <a href="<?php echo esc_url($pp_href['tablecloth']); ?>" class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_tablecloth_url ? 'style="background-image:url(' . esc_url($pp_tablecloth_url) . ')"' : ''; ?>></div>
                <div class="product-preview__text">
                    <div class="product-preview__label">Скатерти</div>
                    <div class="product-preview__size">3 размера: от 175 до 240 см</div>
                    <?php if ($pp_price['tablecloth']) : ?><div class="product-preview__price"><?php echo esc_html($pp_price['tablecloth']); ?></div><?php endif; ?>
                </div>
            </a>
            <a href="<?php echo esc_url($pp_href['napkin']); ?>" class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_napkin_url ? 'style="background-image:url(' . esc_url($pp_napkin_url) . ')"' : ''; ?>></div>
                <div class="product-preview__text">
                    <div class="product-preview__label">Салфетки</div>
                    <div class="product-preview__size">40 × 40 см</div>
                    <?php if ($pp_price['napkin']) : ?><div class="product-preview__price"><?php echo esc_html($pp_price['napkin']); ?></div><?php endif; ?>
                </div>
            </a>
            <a href="<?php echo esc_url($pp_href['kuvert']); ?>" class="product-preview">
                <div class="product-preview__photo" <?php echo $pp_kuvert_url ? 'style="background-image:url(' . esc_url($pp_kuvert_url) . ')"' : ''; ?>></div>
                <div class="product-preview__text">
                    <div class="product-preview__label">Куверты</div>
                    <div class="product-preview__size">9 × 24 см</div>
                    <?php if ($pp_price['kuvert']) : ?><div class="product-preview__price"><?php echo esc_html($pp_price['kuvert']); ?></div><?php endif; ?>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- BRAND -->
<section class="section section--alt">
    <div class="container">
        <div class="brand-grid">
            <div class="brand-left">
                <div class="brand-seal">
                    <span class="brand-seal__top">Сделано в России</span>
                    <span class="brand-seal__logo">LoraLeya</span>
                    <span class="brand-seal__bottom">С любовью</span>
                </div>
                <div class="brand-features">
                    <span>Жаккардовое плетение · мраморный перелив</span>
                    <span>17 цветов · Наборы на 2, 4 и 6 персон</span>
                    <span>Индивидуальный пошив под форму стола</span>
                </div>
            </div>
            <div class="brand-right">
                <div class="eyebrow">О бренде</div>
                <blockquote class="brand-quote">«Красиво накрытый стол — это не роскошь, а ежедневный ритуал, который делает жизнь теплее»</blockquote>
                <p class="brand-text">LoraLeya — российский бренд жаккардовых тканей для сервировки стола. Наша мастерская в Подмосковье шьёт скатерти, дорожки, салфетки и куверты из плотного жаккарда с характерным «мраморным» переливом — этот рисунок играет по-разному при любом освещении. Семнадцать оттенков, готовые наборы на 2, 4 и 6 персон, индивидуальный пошив под любую форму стола. Мы не просто шьём текстиль — мы помогаем создать ритуал, к которому хочется возвращаться каждый день.</p>
                <a href="<?php echo home_url('/about/'); ?>" class="btn btn--outline" style="margin-top:1.5rem">
                    Подробнее о бренде
                </a>
            </div>
        </div>
    </div>
</section>

<?php
if (function_exists('loraleya_render_blog_cards')) {
    loraleya_render_blog_cards(['posts_per_page' => 3], 'Журнал LoraLeya', '', true);
}
?>

<!-- CTA -->
<section class="section" style="text-align:center">
    <div class="container">
        <h2>Готовы собрать свой стол?</h2>
        <p class="section-desc" style="margin:0 auto 2rem">Выберите сценарий, цвет или индивидуальный размер — и соберите идеальный комплект под свой стол за 2 минуты.</p>
        <a href="#scenarios" class="btn btn--outline">
            Начать
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
    </div>
</section>

<?php get_footer(); ?>
