<?php
/**
 * Server-rendered markup for the category index above a catalogue listing:
 * where a buyer can go next, before the products (AMM-200, direction A).
 *
 *  - The catalogue root, the 404 and an empty search: the homepage's drawn
 *    category cards, one per top-level category in the header menu's order
 *    (Non-Woven as the sister-site card), each with its product count.
 *  - A category with sub-categories: one tile per sub-category that holds
 *    products, in the store's order (the cards' "List" style, without icons).
 *  - A deepest category: nothing; the toolbar's chips to its siblings do the
 *    job there.
 *
 * Both are drawn by the Category Cards block (src/category-cards), so there
 * is one card design. On page 2 and later nothing is shown: the products come
 * first (Ammar, 2026-10-17).
 *
 * Nothing is renamed or regrouped: names, parents and order are the store's.
 * Until AMM-200 this block drew the tree as a nested link list (.dh-lines).
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_product_cat_index' ) || ! function_exists( 'demas_theme_get_catalogue_columns' ) ) {
	return;
}

// Product archives, and the 404 page — where the catalogue is the way back in.
// A search is a product archive too, but there the index belongs only to the
// empty state (templates/product-search-results.html places it inside it).
if ( ! ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) || is_404() ) ) {
	return;
}

// WordPress renders a block's inner blocks before the block itself, so inside
// a search's empty state this would be built, then thrown away, whenever the
// search has results (AMM-164). Page 2 onward opens on the products.
if ( ( is_search() && $GLOBALS['wp_query']->found_posts ) || is_paged() ) {
	return;
}

$demas_term = is_tax( 'product_cat' ) ? get_queried_object() : null;

if ( $demas_term instanceof WP_Term ) {
	// The category's sub-categories that hold products, in the store's order.
	$demas_kids = array_values(
		array_filter(
			demas_theme_get_product_cat_index()['children'][ $demas_term->term_id ] ?? array(),
			static fn( WP_Term $t ) => demas_theme_term_product_count( $t ) > 0
		)
	);

	if ( ! $demas_kids ) {
		return;
	}

	$demas_label   = function_exists( 'demas_theme_get_rail_label' ) ? demas_theme_get_rail_label( $demas_kids ) : '';
	$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-subcats' ) );
	$demas_cards   = render_block(
		array(
			'blockName'   => 'demas-theme/category-cards',
			'attrs'       => array(
				'className' => 'is-style-list dh-subcats__grid',
				'cards'     => array_map( static fn( WP_Term $t ) => array( 'category' => $t->slug ), $demas_kids ),
			),
			'innerBlocks' => array(),
		)
	);
	/* translators: %s: a product category, e.g. Landscape. */
	$demas_nav_label = sprintf( __( '%s categories', 'demas-theme' ), $demas_term->name );
	?>
<nav <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> aria-label="<?php echo esc_attr( $demas_nav_label ); ?>">
	<p class="dh-subcats__label dh-eyebrow"><?php echo esc_html( '' !== $demas_label ? $demas_label : __( 'By type', 'demas-theme' ) ); ?></p>
	<?php echo $demas_cards; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in src/category-cards/render.php. ?>
</nav>
	<?php
	return;
}

// Every top-level category, in the header menu's order, plus the outbound
// ones (Non-Woven) the menu lists as links.
$demas_slugs = array_unique(
	array_merge(
		array_keys( demas_theme_get_catalogue_columns() ),
		array_keys( demas_theme_get_catalogue_external_links() )
	)
);

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-catnav' ) );
$demas_cards   = render_block(
	array(
		'blockName'   => 'demas-theme/category-cards',
		'attrs'       => array(
			'cards' => array_map( static fn( string $slug ) => array( 'category' => $slug ), $demas_slugs ),
		),
		'innerBlocks' => array(),
	)
);

if ( '' === trim( $demas_cards ) ) {
	return;
}
?>
<nav <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> aria-label="<?php esc_attr_e( 'Product categories', 'demas-theme' ); ?>">
	<?php echo $demas_cards; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in src/category-cards/render.php. ?>
</nav>
