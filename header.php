<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
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
    $is_custom_order  = is_page('custom-order');
    $is_about         = is_page('about');
    ?>
    <nav class="main-nav" role="navigation">
        <a href="<?php echo home_url('/#scenarios'); ?>" class="<?php echo $is_scenario ? 'current-menu-item' : ''; ?>">Сценарии</a>
        <a href="<?php echo home_url('/#palette'); ?>" class="<?php echo $is_palette ? 'current-menu-item' : ''; ?>">Палитра</a>
        <a href="<?php echo home_url('/custom-order/'); ?>" class="<?php echo $is_custom_order ? 'current-menu-item' : ''; ?>">Индивидуальный заказ</a>
        <a href="<?php echo home_url('/about/'); ?>" class="<?php echo $is_about ? 'current-menu-item' : ''; ?>">О бренде</a>
    </nav>

    <a href="<?php echo wc_get_cart_url(); ?>" class="header-cart">
        Корзина
        <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    </a>
</header>

<main id="main" class="site-main">
