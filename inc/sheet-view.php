<?php
/**
 * Sheet view (AMM-141): the catalogue as a parts sheet.
 *
 * Every catalogue page — the product archive, all categories, search results —
 * can show its parts two ways from one set of markup:
 *
 *  - Gallery: the photographic grid;
 *  - Sheet: numbered rows (line · thumbnail · name · part number · category ·
 *    quantity · add to quote) you could print and hand to a supplier.
 *
 * CSS reflows the same cards between the two (src/product-meta/style.scss);
 * nothing is rendered twice. The view is chosen three ways, in this order:
 *
 *  1. `?view=sheet` in the URL — honoured here on the server, so a shared link
 *     or a visit without JavaScript gets the sheet;
 *  2. the visitor's last choice, remembered in localStorage and applied by a
 *     tiny script at the top of <head> before the page paints, so there is no
 *     flash of the gallery. (Not a cookie read on the server: a CDN-cached page
 *     would ignore it.)
 *  3. otherwise Gallery.
 *
 * The toggle itself is in the catalogue toolbar; switching without a reload is
 * in assets/js/main.js. Printing any catalogue page gives the sheet.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** localStorage key shared with main.js and the head script. */
const DEMAS_THEME_VIEW_KEY = 'demas-theme/view';

add_action( 'init', function () {
	$build_path = DEMAS_THEME_DIR . '/build/product-meta';

	if ( file_exists( $build_path . '/block.json' ) ) {
		register_block_type( $build_path );
	}
} );

/**
 * The view the URL asks for. Only the URL: the remembered choice lives in
 * the visitor's browser and is applied there.
 *
 * @return string 'sheet' or 'gallery'.
 */
function demas_theme_catalogue_view(): string {
	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display parameter.

	return 'sheet' === $view ? 'sheet' : 'gallery';
}

/**
 * Is this a page whose grid can switch? The catalogue, its categories, and
 * search results.
 */
function demas_theme_is_catalogue_page(): bool {
	return is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) || is_search();
}

/*
 * 24 parts a page in both views (was WooCommerce's 4 × 3 = 12): twelve rows
 * make a short sheet, and the grid reads as well at 24.
 */
add_filter( 'loop_shop_per_page', static fn() => 24, 20 );

// ?view=sheet: the switchable grid renders as a sheet from the server.
add_filter(
	'render_block_woocommerce/product-collection',
	function ( $content, $block ) {
		if ( 'sheet' !== demas_theme_catalogue_view() ) {
			return $content;
		}

		if ( false === strpos( (string) ( $block['attrs']['className'] ?? '' ), 'dh-grid--catalogue' ) ) {
			return $content;
		}

		$processor = new WP_HTML_Tag_Processor( (string) $content );

		if ( $processor->next_tag() ) {
			$processor->add_class( 'is-sheet' );
		}

		return $processor->get_updated_html();
	},
	10,
	2
);

/*
 * Before first paint: apply the visitor's remembered choice, unless the URL
 * names a view. Adds .dh-view-sheet to <html>; the sheet styles key on it as
 * well as on the server's .is-sheet. Wrapped in try: storage can be blocked.
 */
add_action(
	'wp_head',
	function () {
		if ( ! demas_theme_is_catalogue_page() ) {
			return;
		}

		$script = sprintf(
			'(function(){try{var q=new URLSearchParams(location.search).get("view");var v=q||localStorage.getItem(%s);if(v==="sheet"){document.documentElement.classList.add("dh-view-sheet");}}catch(e){}})();',
			wp_json_encode( DEMAS_THEME_VIEW_KEY )
		);

		wp_print_inline_script_tag( $script );
	},
	1
);
