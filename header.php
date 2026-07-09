<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="siteHeader">
    <a href="<?php echo home_url(); ?>" class="site-logo">
        Lora<em>Leya</em>
    </a>

    <?php
    $is_scenario      = is_singular('scenario') || is_post_type_archive('scenario');
    $is_palette       = is_tax('pa_fabric_color');
    $is_blog          = is_home() || is_category() || is_singular('post');
    $is_custom_order  = is_page('individualnyy-zakaz');
    $is_about         = is_page('about');
    $is_shop          = function_exists('is_shop') && ( is_shop() || is_product_category() );
    ?>
    <nav class="main-nav" role="navigation">
        <a href="<?php echo home_url('/#scenarios'); ?>" class="<?php echo $is_scenario ? 'current-menu-item' : ''; ?>">Сценарии</a>
        <a href="<?php echo home_url('/#palette'); ?>" class="<?php echo $is_palette ? 'current-menu-item' : ''; ?>">Палитра</a>
        <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url( wc_get_page_permalink('shop') ) : esc_url( home_url('/shop/') ); ?>" class="<?php echo $is_shop ? 'current-menu-item' : ''; ?>">Каталог</a>
        <a href="<?php echo home_url('/blog/'); ?>" class="<?php echo $is_blog ? 'current-menu-item' : ''; ?>">Блог</a>
        <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>" class="<?php echo $is_custom_order ? 'current-menu-item' : ''; ?>">Индивидуальный заказ</a>
        <a href="<?php echo home_url('/about/'); ?>" class="<?php echo $is_about ? 'current-menu-item' : ''; ?>">О бренде</a>
    </nav>

    <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(home_url('/my-account/')); ?>" class="header-account" aria-label="Личный кабинет">
        Личный кабинет
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobileNav">
        <span></span><span></span><span></span>
    </button>
</header>

<!-- Мобильная навигация (выезжает на ≤1024) -->
<div class="mobile-nav-overlay" id="mobileNavOverlay"></div>
<nav id="mobileNav" class="mobile-nav" aria-label="Мобильное меню" aria-hidden="true">
    <a href="<?php echo home_url('/#scenarios'); ?>" class="<?php echo $is_scenario ? 'current-menu-item' : ''; ?>">Сценарии</a>
    <a href="<?php echo home_url('/#palette'); ?>" class="<?php echo $is_palette ? 'current-menu-item' : ''; ?>">Палитра</a>
    <a href="<?php echo home_url('/blog/'); ?>" class="<?php echo $is_blog ? 'current-menu-item' : ''; ?>">Блог</a>
    <a href="<?php echo home_url('/individualnyy-zakaz/'); ?>" class="<?php echo $is_custom_order ? 'current-menu-item' : ''; ?>">Индивидуальный заказ</a>
    <a href="<?php echo home_url('/about/'); ?>" class="<?php echo $is_about ? 'current-menu-item' : ''; ?>">О бренде</a>
    <a href="<?php echo home_url('/shop/'); ?>">Каталог</a>
    <a href="<?php echo function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(home_url('/my-account/')); ?>">Личный кабинет</a>
</nav>

<main id="main" class="site-main">
