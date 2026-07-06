<?php
/**
 * LoraLeya — карточка товара в каталоге (лукбук-разворот).
 * Переопределяет woocommerce/templates/content-product.php.
 *
 * Структура: li.product.ll-card
 *   ├── a.ll-card__photo  (зона A — фото)
 *   └── div.ll-card__info (зона B — название, описание, цена, кнопка)
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Убедиться что объект товара доступен.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

// Классы li — WC добавляет свои (product, type-product, post-*, instock…)
$classes = wc_get_product_class( 'll-card', $product );
?>
<li <?php wc_product_class( 'll-card', $product ); ?>>

    <?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

    <!-- Зона A: фото -->
    <a class="ll-card__photo" href="<?php echo esc_url( get_permalink() ); ?>">
        <?php
        /**
         * woocommerce_before_shop_loop_item_title — сюда WC выводит flash-бейджи (sale, featured).
         * Оставляем для совместимости с плагинами.
         */
        do_action( 'woocommerce_before_shop_loop_item_title' );

        // Изображение товара
        echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' );
        ?>
    </a>

    <!-- Зона B: информация -->
    <div class="ll-card__info">

        <h2 class="ll-card__title">
            <a href="<?php echo esc_url( get_permalink() ); ?>">
                <?php echo get_the_title(); ?>
            </a>
        </h2>

        <?php
        // C2: краткое описание из поля товара (Наталья правит через админку)
        $excerpt = $product->get_short_description();
        if ( $excerpt ) :
        ?>
        <div class="ll-card__excerpt">
            <?php echo wp_kses_post( $excerpt ); ?>
        </div>
        <?php endif; ?>

        <div class="ll-card__price">
            <?php echo $product->get_price_html(); // C1: только динамическая цена ?>
        </div>

        <a class="ll-card__btn" href="<?php echo esc_url( get_permalink() ); ?>">
            Выбрать
        </a>

        <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>

    </div>

</li>
