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
 * does not know "sku". Setting meta_key/orderby on the main query is not
 * enough: the product-collection block rebuilds an inherited query and the
 * meta_key does not survive the trip, leaving an "ORDER BY meta_value" with
 * no join — invalid SQL, zero products. So this does what WooCommerce does
 * for price: join the product lookup table in posts_clauses, which applies
 * to whichever query actually renders the grid. Every product carries an
 * SKU, so nothing drops out of the sort.
 */
add_filter(
	'posts_clauses',
	function ( $clauses, $query ) {
		if ( is_admin() ) {
			return $clauses;
		}

		if ( ! ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) ) {
			return $clauses;
		}

		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter.

		if ( 'sku' !== $orderby ) {
			return $clauses;
		}

		$post_type = $query->get( 'post_type' );
		$is_products = 'product' === $post_type || ( is_array( $post_type ) && in_array( 'product', $post_type, true ) );

		if ( ! $is_products ) {
			return $clauses;
		}

		global $wpdb;

		// Own alias, so it cannot collide with a join WooCommerce may already have added.
		$clauses['join']   .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} dh_sku ON {$wpdb->posts}.ID = dh_sku.product_id ";
		$clauses['orderby'] = "dh_sku.sku ASC, {$wpdb->posts}.ID ASC";

		return $clauses;
	},
	20,
	2
);
