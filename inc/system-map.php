<?php
/**
 * The catalogue read as physical systems.
 *
 * The product_cat tree is the store's filing system and stays exactly as it
 * is. But the tree mixes two kinds of level on the same rung — "Fittings /
 * Barbed Fittings" is what a part is, "Valves / Irritrol / 2400 Series" is
 * who makes it — and it buries emitters under Fittings, where nobody looking
 * for a dripper thinks to look. So this file describes the same terms a
 * second way: as the stages of the systems Demas actually sells, in the
 * order water moves through them. Nothing here re-parents a term; it only
 * decides where a term is *shown*.
 *
 * Both maps are filterable so the store can adjust them without a theme
 * change: `demas_theme_system_map` and `demas_theme_brand_term_slugs`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Systems, their top-level categories, and their stages.
 *
 * Each stage lists term slugs. A term whose parent is also in the same stage
 * renders indented beneath it. Terms that do not exist or hold no products
 * are skipped at render time, so a stage can name a term before the store
 * has anything in it.
 *
 * @return array<string, array{label:string, kind:string, tops:string[], stages:array<int, array{label:string, terms:string[]}>}>
 */
function demas_theme_get_system_map(): array {
	$map = array(
		'irrigation' => array(
			'label'  => __( 'Irrigation system', 'demas-theme' ),
			'kind'   => 'stage',
			// Controllers, valves and rotors are filed under Landscape but sit on the same line.
			'tops'   => array( 'irrigation', 'landscape' ),
			'stages' => array(
				array(
					'label' => __( 'Source', 'demas-theme' ),
					'terms' => array( 'filtration' ),
				),
				array(
					'label' => __( 'Carry', 'demas-theme' ),
					'terms' => array( 'pipes', 'hdpe-pipes', 'lldpe-pipes', 'drip-system' ),
				),
				array(
					'label' => __( 'Join', 'demas-theme' ),
					'terms' => array(
						'fittings',
						'barbed-fittings',
						'compression-fittings',
						'pp-threaded-fittings',
						'butt-fusion-fittings',
						'electro-fusion-fittings',
						'clamp-saddle-pn6-pn16',
						'mais-valves-fittings',
						'valve-boxes-fittings',
						'cp-accessories',
					),
				),
				array(
					'label' => __( 'Control', 'demas-theme' ),
					'terms' => array( 'controllers', 'landscape-valves' ),
				),
				array(
					'label' => __( 'Deliver', 'demas-theme' ),
					'terms' => array( 'rotors', 'drippers', 'on-line-dripper-fittings', 'bubblers' ),
				),
			),
		),
		'fog'        => array(
			'label'  => __( 'Fog system', 'demas-theme' ),
			'kind'   => 'stage',
			'tops'   => array( 'fog-systems' ),
			'stages' => array(
				array(
					'label' => __( 'Treat', 'demas-theme' ),
					'terms' => array( 'water-treatment' ),
				),
				array(
					'label' => __( 'Pump', 'demas-theme' ),
					'terms' => array( 'high-pressure-pumps' ),
				),
				array(
					'label' => __( 'Control', 'demas-theme' ),
					'terms' => array( 'controllers-dosingpumps-electromagneticvalves', 'accessories-for-injectors' ),
				),
				array(
					'label' => __( 'Carry', 'demas-theme' ),
					'terms' => array( 'nylon-and-stainless-steel-pipes', 'tecnocooling-fittings', 'clamps-and-fasteners' ),
				),
				array(
					'label' => __( 'Atomise', 'demas-theme' ),
					'terms' => array( 'nozzles-and-extensions', 'foggy-rings' ),
				),
				array(
					'label' => __( 'Move air', 'demas-theme' ),
					'terms' => array( 'mist-fans', 'industrial-fans' ),
				),
			),
		),
		'tools'      => array(
			'label'  => __( 'Workshop', 'demas-theme' ),
			// Not a flow — a set of trades. Same index, different eyebrow.
			'kind'   => 'trade',
			'tops'   => array( 'industrial-tools-services' ),
			'stages' => array(
				array(
					'label' => __( 'Cut', 'demas-theme' ),
					'terms' => array( 'cutting-tools' ),
				),
				array(
					'label' => __( 'Drill', 'demas-theme' ),
					'terms' => array( 'magnetic-drills' ),
				),
				array(
					'label' => __( 'Saw', 'demas-theme' ),
					'terms' => array( 'band-saw-blades', 'band-saw-accessories' ),
				),
				array(
					'label' => __( 'Weld', 'demas-theme' ),
					'terms' => array( 'welding-machines', 'welding-accessories' ),
				),
			),
		),
		// Swimming Pool has no children and no flow — nothing to index, so no entry.
	);

	return apply_filters( 'demas_theme_system_map', $map );
}

/**
 * Which systems to show for a queried term: all of them on the shop root,
 * the one whose top-level category the term belongs to otherwise.
 *
 * @param WP_Term|null $term The queried category, or null on the shop root.
 * @return array<string, array> Subset of the system map, keyed as the map is.
 */
function demas_theme_get_systems_for_term( ?WP_Term $term ): array {
	$map = demas_theme_get_system_map();

	if ( ! $term instanceof WP_Term ) {
		return $map;
	}

	$ancestors = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );
	$top_id    = $ancestors ? end( $ancestors ) : $term->term_id;
	$top       = get_term( $top_id, 'product_cat' );

	if ( ! $top instanceof WP_Term ) {
		return array();
	}

	foreach ( $map as $key => $system ) {
		if ( in_array( $top->slug, $system['tops'], true ) ) {
			return array( $key => $system );
		}
	}

	return array();
}

/**
 * How many products a category holds, children included.
 *
 * The raw term count only covers products filed directly on the term, so a
 * parent whose products all sit in grandchildren (Cutting Tools: 25, all
 * under DHF and Sumitomo) reads 0. WooCommerce keeps the inclusive figure
 * in term meta and swaps it in on some code paths but not all; read the meta
 * directly so every count on the page agrees.
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
 * What kind of choice a term represents: a kind of part, a manufacturer, or
 * one of a manufacturer's model families.
 *
 * @return string 'type' | 'brand' | 'series'
 */
function demas_theme_get_term_kind( WP_Term $term ): string {
	$brands = demas_theme_get_brand_term_slugs();

	if ( in_array( $term->slug, $brands, true ) ) {
		return 'brand';
	}

	if ( $term->parent ) {
		$parent = get_term( $term->parent, 'product_cat' );

		if ( $parent instanceof WP_Term && in_array( $parent->slug, $brands, true ) ) {
			return 'series';
		}
	}

	return 'type';
}

/**
 * A short, human label for a term's children — "2 brands", "9 types",
 * "10 series" — so a buyer knows what the next click asks before making it.
 *
 * @return string Empty when the term has no children with products.
 */
function demas_theme_describe_children( WP_Term $term ): string {
	$children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term->term_id,
			'hide_empty' => false, // Filtered below on the inclusive count instead.
		)
	);

	if ( is_wp_error( $children ) ) {
		return '';
	}

	$children = array_values( array_filter( $children, fn( $c ) => demas_theme_term_product_count( $c ) > 0 ) );

	if ( ! $children ) {
		return '';
	}

	$count = count( $children );
	$kind  = demas_theme_get_term_kind( $children[0] );

	switch ( $kind ) {
		case 'brand':
			/* translators: %d: number of manufacturer sub-categories. */
			return sprintf( _n( '%d brand', '%d brands', $count, 'demas-theme' ), $count );
		case 'series':
			/* translators: %d: number of model-family sub-categories. */
			return sprintf( _n( '%d series', '%d series', $count, 'demas-theme' ), $count );
		default:
			/* translators: %d: number of part-type sub-categories. */
			return sprintf( _n( '%d type', '%d types', $count, 'demas-theme' ), $count );
	}
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
