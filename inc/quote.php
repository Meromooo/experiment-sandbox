<?php
/**
 * The quote list: collect parts across a browse, set quantities, review.
 *
 * Every price in the catalogue is zero — Demas quotes rather than publishes
 * prices — so there is no cart. Buyers here (contractors, procurement,
 * landscape firms) never ask about one part anyway; they build a list and
 * send it for pricing. This is that list.
 *
 * Two blocks share one Interactivity API store, `demas-theme/quote`:
 *
 *  - demas-theme/quote-button — "Add to quote" on a product page and on
 *    every catalogue card;
 *  - demas-theme/quote-drawer — the header control ("Your quote · 003") and
 *    the list itself, a native <dialog>. The store is defined in this
 *    block's view module; the header carries it on every page, which is
 *    what lets the buttons work wherever they appear.
 *
 * The list lives in the buyer's browser (localStorage) as a snapshot of each
 * part — ID, quantity, name, SKU, thumbnail, link — so it draws instantly on
 * every page without a request. Sending it to a branch is AMM-140; nothing
 * here leaves the browser.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	foreach ( array( 'quote-button', 'quote-drawer' ) as $block ) {
		$build_path = DEMAS_THEME_DIR . '/build/' . $block;

		if ( file_exists( $build_path . '/block.json' ) ) {
			register_block_type( $build_path );
		}
	}
} );
