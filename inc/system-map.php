<?php
/**
 * How the catalogue's categories are read: product counts, which categories
 * name a manufacturer or a model family, and the whole category tree read
 * once per request.
 *
 * The product_cat tree is the store's filing system and is shown as it is,
 * under its own names (the live site's). Until 2026-10-08 this file also
 * drew the catalogue as the stages of physical systems ("Source, Carry,
 * Join..."); that map is gone, along with the invented names it showed.
 *
 * The brand lists are filterable so the store can adjust them without a
 * theme change: `demas_theme_brand_term_slugs` and
 * `demas_theme_series_brand_slugs`.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How many products a category holds, children included.
 *
 * The raw term count only covers products filed directly on the term, so a
 * parent whose products all sit in grandchildren (Cutting Tools: 25, all
 * under DHF and Sumitomo) reads 0. WooCommerce keeps the inclusive figure
 * in term meta and swaps it in on some code paths but not all; read the meta
 * directly so every count on the page agrees.
 *
 * @param WP_Term $term A product category.
 */
function demas_theme_term_product_count( WP_Term $term ): int {
	$meta = get_term_meta( $term->term_id, 'product_count_' . $term->taxonomy, true );

	return '' !== $meta && null !== $meta ? (int) $meta : (int) $term->count;
}

/**
 * Category slugs that name a manufacturer rather than a kind of part.
 *
 * @return string[]
 */
function demas_theme_get_brand_term_slugs(): array {
	return apply_filters(
		'demas_theme_brand_term_slugs',
		array(
			'hunter-irrigation',
			'hunter-irrigation-landscape-valves',
			'rain-bird',
			'rain-bird-landscape-valves',
			'irritrol',
			'dhf',
			'sumitomo',
			'honsberg',
			'honsberg-band-saw-accessories',
			'keego',
			'hugong-welding',
		)
	);
}

/**
 * Brands whose sub-categories are model families rather than kinds of part.
 *
 * Under Hunter, Rain Bird and Irritrol the children are product lines — X2,
 * NODE, ESP-TM2, 2400 Series. Under the tool brands they are not: Sumitomo's
 * children are CBN Inserts, Milling, Turning Inserts, and Hugong's are MIG
 * Welders and MMA Welders. Those are types, and calling them series would put
 * "Series: End Mills" on a nameplate.
 *
 * @return string[]
 */
function demas_theme_get_series_brand_slugs(): array {
	return apply_filters(
		'demas_theme_series_brand_slugs',
		array(
			'hunter-irrigation',
			'hunter-irrigation-landscape-valves',
			'rain-bird',
			'rain-bird-landscape-valves',
			'irritrol',
		)
	);
}

/**
 * What kind of choice a term represents: a kind of part, a manufacturer, or
 * one of a manufacturer's model families.
 *
 * @param WP_Term $term A product category.
 * @return string 'type' | 'brand' | 'series'
 */
function demas_theme_get_term_kind( WP_Term $term ): string {
	if ( in_array( $term->slug, demas_theme_get_brand_term_slugs(), true ) ) {
		return 'brand';
	}

	if ( $term->parent ) {
		$parent = get_term( $term->parent, 'product_cat' );

		if ( $parent instanceof WP_Term && in_array( $parent->slug, demas_theme_get_series_brand_slugs(), true ) ) {
			return 'series';
		}
	}

	return 'type';
}

/**
 * Every product category, read once per request: by slug, and by parent.
 *
 * The category index above a listing reads every category's children and
 * product count (term meta). Done one term at a time that was ~105 database
 * queries a page (AMM-164); this one get_terms() call also loads every
 * category's term meta, so those lookups cost nothing. Children keep
 * get_terms()' own order, which WooCommerce sets to the store's category
 * order (the order the live site lists them in).
 *
 * @return array{by_slug: array<string, WP_Term>, children: array<int, WP_Term[]>}
 */
function demas_theme_get_product_cat_index(): array {
	static $index = null;

	if ( null !== $index ) {
		return $index;
	}

	$index = array(
		'by_slug'  => array(),
		'children' => array(),
	);
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false, // Callers filter on the inclusive count instead.
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $index;
	}

	foreach ( $terms as $term ) {
		$index['by_slug'][ $term->slug ]      = $term;
		$index['children'][ $term->parent ][] = $term;
	}

	return $index;
}

/**
 * The word the toolbar rail uses for what its chips narrow by.
 *
 * @param WP_Term[] $terms The chips about to be rendered.
 */
function demas_theme_get_rail_label( array $terms ): string {
	if ( ! $terms ) {
		return '';
	}

	switch ( demas_theme_get_term_kind( $terms[0] ) ) {
		case 'brand':
			return __( 'By brand', 'demas-theme' );
		case 'series':
			return __( 'By series', 'demas-theme' );
		default:
			return __( 'By type', 'demas-theme' );
	}
}
