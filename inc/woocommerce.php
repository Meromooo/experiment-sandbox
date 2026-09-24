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

/* ------------------------------------------------------------------------ */
/* No cart, no checkout (AMM-139, decided 2026-09-24)                       */
/* ------------------------------------------------------------------------ */

/*
 * Every product is SAR 0.00 because Demas quotes rather than publishes prices,
 * so a cart could only take a zero-value order. Buyers build a quote list
 * instead (inc/quote.php) and send it to a branch.
 *
 * Nothing is deleted: WooCommerce's cart and checkout pages still exist in the
 * database and come back the moment these filters are removed.
 */

/**
 * The catalogue's front door, for redirects and "browse the catalogue" links.
 *
 * Not get_post_type_archive_link( 'product' ) yet: that resolves to /shop/,
 * where a static "All Products" page carried over from the clone shadows the
 * product archive (AMM-147). Once that is fixed, this returns the archive link
 * and every caller follows.
 */
function demas_theme_catalogue_url(): string {
	return (string) apply_filters( 'demas_theme_catalogue_url', add_query_arg( 'post_type', 'product', home_url( '/' ) ) );
}

// Nothing can be added to a cart — in any block, the classic templates, or the
// Store API — so no "Add to cart" button renders anywhere.
add_filter( 'woocommerce_is_purchasable', '__return_false' );

// The cart and checkout routes send the buyer to the catalogue instead.
// 302, not 301: this is a decision about how the site sells, and it should be
// reversible without browsers having cached a permanent redirect.
add_action(
	'template_redirect',
	function () {
		if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
			wp_safe_redirect( demas_theme_catalogue_url(), 302 );
			exit;
		}
	}
);

/*
 * The mini-cart icon and the "Cart" / "Checkout" links live inside the
 * navigation menu that came over with the clone (stored in the database, not
 * in parts/header.html). They are stopped before rendering rather than hidden
 * afterwards, so the mini-cart's scripts and its drawer markup are never
 * enqueued either. The rest of that menu is AMM-145.
 */
add_filter(
	'pre_render_block',
	function ( $pre_render, $parsed_block ) {
		if ( null !== $pre_render || ! function_exists( 'wc_get_cart_url' ) ) {
			return $pre_render;
		}

		$name = $parsed_block['blockName'] ?? '';

		if ( 'woocommerce/mini-cart' === $name ) {
			return '';
		}

		if ( in_array( $name, array( 'core/navigation-link', 'core/navigation-submenu' ), true ) ) {
			$url = untrailingslashit( (string) ( $parsed_block['attrs']['url'] ?? '' ) );

			if ( '' !== $url && in_array( $url, array( untrailingslashit( wc_get_cart_url() ), untrailingslashit( wc_get_checkout_url() ) ), true ) ) {
				return '';
			}
		}

		return $pre_render;
	},
	10,
	2
);
