<?php
/**
 * WooCommerce compatibility declarations and block-theme integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__ );
	}
} );

/**
 * Show "Price on request" instead of a zero price.
 *
 * Every product in the catalogue carries a price of 0, because Demas quotes
 * rather than publishes prices. WooCommerce renders that literally as
 * "SAR 0.00", which reads as free rather than as unpriced. Products that do
 * carry a real price are left alone, so this degrades correctly if pricing is
 * published later.
 */
add_filter(
	'woocommerce_get_price_html',
	function ( $price_html, $product ) {
		$price = $product->get_price();

		if ( '' === $price || 0.0 === (float) $price ) {
			return '<span class="dh-price-request">' . esc_html__( 'Price on request', 'demas-theme' ) . '</span>';
		}

		return $price_html;
	},
	10,
	2
);
