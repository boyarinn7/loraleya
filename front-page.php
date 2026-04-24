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
        <div class="eyebrow">Столовый текстиль &middot; Сделано в России с любовью</div>
        <h1>Начните с <em class="italic-accent">настроения</em>,<br>мы соберём ваш стол</h1>
        <p class="hero-sub">17 оттенков жаккардового текстиля для сервировки.<br>Выберите сценарий — мы подберём идеальный комплект.</p>
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
        <p class="section-desc">Не ищите товары — выберите настроение. Мы покажем готовую сервировку и предложим собрать комплект в один клик.</p>

        <div class="scenarios-grid">
            <?php
            $scenarios = new WP_Query([
                'post_type'      => 'scenario',
                'posts_per_page' => 4,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            ]);
            $num = 1;
            if ($scenarios->have_posts()) :
                while ($scenarios->have_posts()) : $scenarios->the_post();
            ?>
                <a href="<?php the_permalink(); ?>" class="scenario-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="scenario-bg" style="background-image:url(<?php echo get_the_post_thumbnail_url(get_the_ID(), 'gallery'); ?>)"></div>
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
        </div>
    </div>
</section>

<!-- PALETTE -->
<section class="section section--alt" id="palette">
    <div class="container">
        <div class="eyebrow">Палитра LoraLeya</div>
        <h2>17 оттенков для вашего стола</h2>
        <p class="section-desc">Каждый цвет — это отдельный мир сервировки. Нажмите на оттенок, чтобы увидеть все изделия и готовые комплекты.</p>

        <div class="colors-wheel">
            <?php
            $colors = get_terms(['taxonomy' => 'pa_fabric_color', 'hide_empty' => false]);
            if (!empty($colors) && !is_wp_error($colors)) :
                foreach ($colors as $color) :
                    $hex = get_term_meta($color->term_id, 'color_hex', true) ?: '#888';
                    $link = get_term_link($color);
            ?>
                <a href="<?php echo $link; ?>" class="color-swatch" style="background:<?php echo esc_attr($hex); ?>" title="<?php echo esc_attr($color->name); ?>"></a>
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
<section class="section">
    <div class="container">
        <div class="eyebrow">Изделия</div>
        <h2>Что входит в сервировку</h2>
        <p class="section-desc">Дорожки, салфетки, куверты для приборов — всё из одной ткани, в едином стиле.</p>

        <div class="products-strip">
            <div class="product-preview">
                <div class="product-preview__label">Дорожки</div>
                <div class="product-preview__size">4 размера: от 140 до 300 см</div>
                <div class="product-preview__price">от 890 ₽</div>
            </div>
            <div class="product-preview">
                <div class="product-preview__label">Скатерти</div>
                <div class="product-preview__size">3 размера: от 175 до 240 см</div>
                <div class="product-preview__price">от 2 490 ₽</div>
            </div>
            <div class="product-preview">
                <div class="product-preview__label">Салфетки</div>
                <div class="product-preview__size">40 × 40 см</div>
                <div class="product-preview__price">от 350 ₽</div>
            </div>
            <div class="product-preview">
                <div class="product-preview__label">Куверты</div>
                <div class="product-preview__size">9 × 24 см</div>
                <div class="product-preview__price">от 250 ₽</div>
            </div>
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
                    <span>100% полиэстер · Жаккардовое плетение</span>
                    <span>17 цветов · Наборы от 2 до 12 персон</span>
                    <span>Индивидуальный пошив · Монограммы</span>
                </div>
            </div>
            <div class="brand-right">
                <div class="eyebrow">О бренде</div>
                <blockquote class="brand-quote">«Красиво накрытый стол — это не роскошь, а ежедневный ритуал, который делает жизнь теплее»</blockquote>
                <p class="brand-text">LoraLeya — российский бренд столового текстиля. Каждое изделие создаётся из жаккардовой ткани с характерным «мраморным» переливом, который играет по-разному при любом освещении. Мы не просто шьём скатерти — мы помогаем вам создать стол, за который хочется собираться.</p>
                <a href="<?php echo home_url('/about/'); ?>" class="btn btn--outline" style="margin-top:1.5rem">
                    Подробнее о бренде
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section" style="text-align:center">
    <div class="container">
        <h2>Готовы собрать свой стол?</h2>
        <p class="section-desc" style="margin:0 auto 2rem">Выберите сценарий или цвет — и соберите идеальный комплект за 2 минуты</p>
        <a href="#scenarios" class="btn btn--outline">
            Начать
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
        </a>
    </div>
</section>

<?php get_footer(); ?>
