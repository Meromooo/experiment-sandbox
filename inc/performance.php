<?php
/**
 * What the front end does not load (AMM-154).
 *
 * WooCommerce and WordPress enqueue assets for features this site does not
 * have, on every page, and most of them block rendering. Measured on
 * 2026-09-28 (see AMM-154 for the before/after table):
 *
 *  - WooCommerce's classic stylesheets and scripts — jQuery, jQuery Migrate,
 *    blockUI, add-to-cart, js-cookie, woocommerce.js — about 70 KB, with
 *    jQuery render-blocking in <head>. Nothing here is purchasable (see
 *    inc/woocommerce.php), no classic WooCommerce template renders, and no
 *    theme code uses jQuery. WooCommerce's *block* styles (wc-blocks-style*,
 *    product-collection, product-image, breadcrumbs) are left alone: the
 *    catalogue and product templates are built from those blocks.
 *  - Order attribution (sourcebuster + wc-order-attribution): records where a
 *    buyer came from for the order screen. With no checkout there is never an
 *    order to attribute, and it set sbjs_* cookies on every visitor.
 *  - The emoji detection script and styles: every supported browser draws
 *    emoji natively.
 *
 * Fonts are deliberately *not* preloaded. Tested on a throttled phone profile
 * with Archivo + Plex Sans preloaded, first paint came 150–200 ms later on
 * home, category and product pages: the 133 KB of fonts competed with the
 * render-blocking CSS. The metric-matched stand-in faces in style.css already
 * stop text from moving when the fonts swap in, so a preload bought nothing.
 *
 * Nothing here changes a WooCommerce or WordPress setting — delete this file
 * from functions.php and everything comes back.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WooCommerce's classic stylesheets: woocommerce-general, -layout, -smallscreen.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/*
 * WooCommerce's classic front-end scripts. jQuery is only ever a dependency
 * of these, so it drops out with them. Late priority: WooCommerce enqueues
 * them at the default one.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$handles = array(
			'woocommerce',
			'wc-add-to-cart',
			'wc-jquery-blockui',
			'jquery-blockui',
			'wc-js-cookie',
			'js-cookie',
			'wc-cart-fragments',
			'sourcebuster-js',
			'wc-order-attribution',
		);

		foreach ( $handles as $handle ) {
			wp_dequeue_script( $handle );
		}

		// One rule for classic checkout forms (.form-row .required); there are none.
		wp_dequeue_style( 'woocommerce-inline' );

		// Classic cart, account, variation and review rules for block themes,
		// enqueued outside woocommerce_enqueue_styles.
		wp_dequeue_style( 'woocommerce-blocktheme' );
	},
	100
);

// Emoji: the detection script (and its loader), and the inline styles it brings.
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
);
