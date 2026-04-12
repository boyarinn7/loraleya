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

    <nav class="main-nav" role="navigation">
        <a href="<?php echo home_url('/#scenarios'); ?>">Сценарии</a>
        <a href="<?php echo home_url('/#palette'); ?>">Палитра</a>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Каталог</a>
        <a href="<?php echo home_url('/custom-order/'); ?>">Индивидуальный заказ</a>
        <a href="<?php echo home_url('/about/'); ?>">О бренде</a>
    </nav>

    <a href="<?php echo wc_get_cart_url(); ?>" class="header-cart">
        Корзина
        <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    </a>
</header>

<main id="main" class="site-main">
