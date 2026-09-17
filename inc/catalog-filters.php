<?php
/**
 * Product catalogue navigation: the toolbar block and the sort orders it
 * offers beyond WooCommerce's own.
 *
 * The catalogue has no product attributes — specifications live inside each
 * description — so there are no facets to filter by. What a buyer can
 * navigate by is category, brand-as-category, SKU and name, and this file
 * exists to serve exactly that.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$build_path = DEMAS_THEME_DIR . '/build/catalog-toolbar';

	if ( file_exists( $build_path . '/block.json' ) ) {
		register_block_type( $build_path );
	}
} );

/**
 * Order the archive by SKU when the toolbar asks for it.
 *
 * WooCommerce's own ordering (menu_order, title, date, price, popularity)
 * runs on pre_get_posts at priority 10 and does not know "sku"; this runs
 * afterwards and orders on the _sku meta value. Every product in the
 * catalogue carries one, so nothing drops out of the sort.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			return;
		}

		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter.

		if ( 'sku' !== $orderby ) {
			return;
		}

		$query->set( 'meta_key', '_sku' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'ASC' );
	},
	20
);
