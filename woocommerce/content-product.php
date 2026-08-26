<?php
/**
 * LoraLeya catalog card — lookbook layout (photo | info).
 * Overrides WooCommerce default content-product.php.
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$permalink   = get_permalink( $product->get_id() );
$title       = $product->get_name();
$price_html  = $product->get_price_html();
$short_desc  = $product->get_short_description();
?>
<li <?php wc_product_class( 'll-card', $product ); ?>>

	<a class="ll-card__photo" href="<?php echo esc_url( $permalink ); ?>">
		<?php
		if ( $product->is_on_sale() ) {
			echo '<span class="onsale">' . esc_html__( 'Распродажа!', 'loraleya' ) . '</span>';
		}
		$ll_thumb_id = $product->get_image_id();
		if ( $ll_thumb_id ) {
			echo wp_get_attachment_image(
				$ll_thumb_id,
				'scenario-card',
				false,
				array(
					'class'   => 'll-card__img',
					'loading' => 'lazy',
					'alt'     => esc_attr( $product->get_name() ),
				)
			);
		} else {
			echo wc_placeholder_img( 'scenario-card' );
		}
		?>
	</a>

	<div class="ll-card__info">
		<h2 class="ll-card__title"><?php echo esc_html( $title ); ?></h2>

		<?php if ( $short_desc ) : ?>
			<div class="ll-card__excerpt"><?php echo wp_kses_post( wpautop( $short_desc ) ); ?></div>
		<?php endif; ?>

		<?php if ( $price_html ) : ?>
			<div class="ll-card__price"><?php echo wp_kses_post( $price_html ); ?></div>
		<?php endif; ?>

		<a class="ll-card__btn" href="<?php echo esc_url( $permalink ); ?>">Выбрать</a>
	</div>

</li>
