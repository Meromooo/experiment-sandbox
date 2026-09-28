<?php
/**
 * WooCommerce compatibility declarations and block-theme integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__ );
		}
	}
);

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

/*
------------------------------------------------------------------------ */
/*
No cart, no checkout (AMM-139, decided 2026-09-24)                       */
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
 * WooCommerce's shop page is the cloned "Products" page, so the product
 * archive lives at /products/ and that is what the archive link returns.
 * (An earlier version of this function assumed the archive was /shop/ and
 * linked to /?post_type=product instead. /shop/ is an unrelated, empty
 * "All Products" page — AMM-147.)
 */
function demas_theme_catalogue_url(): string {
	$url = get_post_type_archive_link( 'product' );

	return (string) apply_filters(
		'demas_theme_catalogue_url',
		$url ? $url : add_query_arg( 'post_type', 'product', home_url( '/' ) )
	);
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
 * WooCommerce hooks its account icon and mini-cart into the header, after the
 * navigation block (block hooks, since WC 8.4 — they are not in
 * parts/header.html). Neither has a job here: there is no cart, and a buyer
 * has nothing to sign in for, since the quote list lives in the browser.
 * Taken out at the hook (WooCommerce adds them at priority 9), so they are
 * never inserted. The account page itself still exists at /my-account/.
 */
add_filter(
	'hooked_block_types',
	function ( $hooked_blocks, $position, $anchor_block ) {
		if ( 'after' !== $position || 'core/navigation' !== $anchor_block || ! is_array( $hooked_blocks ) ) {
			return $hooked_blocks;
		}

		return array_values( array_diff( $hooked_blocks, array( 'woocommerce/customer-account', 'woocommerce/mini-cart' ) ) );
	},
	20,
	3
);

/*
 * Guards for anywhere else a mini-cart or a cart/checkout link turns up: a
 * mini-cart placed by hand, a navigation-link to either page. Stopped before
 * rendering rather than hidden afterwards, so the mini-cart's scripts and
 * drawer markup are never enqueued either.
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

/*
 * Any Page List block lists every published page — Cart and Checkout
 * included — with no navigation-link of its own to stop. The header no longer
 * uses one (AMM-145), but the cloned site's navigation post is still a Page
 * List, and a future footer or sitemap may be too. Filtering get_pages() for
 * the length of its render had no effect (the block evidently queries pages
 * another way), so the two items are taken out of its finished markup.
 */
add_filter(
	'render_block_core/page-list',
	function ( $block_content ) {
		if ( ! function_exists( 'wc_get_cart_url' ) ) {
			return $block_content;
		}

		foreach ( array( wc_get_cart_url(), wc_get_checkout_url() ) as $url ) {
			$block_content = preg_replace(
				'#<li\b[^>]*>\s*<a\b[^>]*\bhref="' . preg_quote( esc_url( $url ), '#' ) . '"[^>]*>.*?</a>\s*</li>#s',
				'',
				$block_content
			) ?? $block_content;
		}

		return $block_content;
	}
);
