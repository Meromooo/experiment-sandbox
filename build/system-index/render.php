<?php
/**
 * Server-rendered markup for the category index above a catalogue listing.
 *
 * The store's own category tree, under the store's own names and in the
 * store's own order, filed exactly as the live site files it. On a category
 * page: the top-level category the page sits in, one column per
 * sub-category, each with its own sub-categories beneath it. On the
 * catalogue root, the 404 and an empty search: every top-level category
 * that has sub-categories. The buyer's position is marked with
 * aria-current. Categories holding no products are left out, as everywhere
 * else on the site.
 *
 * Nothing is renamed or regrouped. Until 2026-10-08 this block drew the
 * catalogue as the stages of a system ("Source, Carry, Join, Control,
 * Deliver"), which put invented names over the real ones and showed
 * Irrigation's pipes and fittings on the Landscape page.
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

if ( ! function_exists( 'demas_theme_get_product_cat_index' ) ) {
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
// search has results (AMM-164).
if ( is_search() && $GLOBALS['wp_query']->found_posts ) {
	return;
}

$demas_children = demas_theme_get_product_cat_index()['children'];

// A category's sub-categories that hold products, in the store's order.
$demas_kids = static function ( int $parent_id ) use ( $demas_children ): array {
	return array_values(
		array_filter(
			$demas_children[ $parent_id ] ?? array(),
			static fn( WP_Term $t ) => demas_theme_term_product_count( $t ) > 0
		)
	);
};

$demas_term = is_tax( 'product_cat' ) ? get_queried_object() : null;
$demas_term = $demas_term instanceof WP_Term ? $demas_term : null;
$demas_here = array(); // The queried term and everything above it.
$demas_tops = $demas_kids( 0 );

if ( $demas_term ) {
	$demas_here   = array_merge( array( $demas_term->term_id ), get_ancestors( $demas_term->term_id, 'product_cat', 'taxonomy' ) );
	$demas_top_id = end( $demas_here );
	$demas_tops   = array_filter( $demas_tops, static fn( WP_Term $t ) => $t->term_id === $demas_top_id );
}

$demas_sections = array();

foreach ( $demas_tops as $demas_top ) {
	$demas_groups = array();

	foreach ( $demas_kids( $demas_top->term_id ) as $demas_group ) {
		$demas_groups[] = array(
			'term'  => $demas_group,
			'items' => $demas_kids( $demas_group->term_id ),
		);
	}

	// A category with no sub-categories (Swimming Pool) has nothing to index.
	if ( $demas_groups ) {
		$demas_sections[] = array(
			'top'    => $demas_top,
			'groups' => $demas_groups,
		);
	}
}

if ( ! $demas_sections ) {
	return;
}

// One link: the category's name and its product count, marked when it is
// this page or a category above it.
$demas_link = static function ( WP_Term $t, string $css_class ) use ( $demas_term, $demas_here ): string {
	$current = '';
	if ( $demas_term && $t->term_id === $demas_term->term_id ) {
		$current = ' aria-current="page"';
	} elseif ( in_array( $t->term_id, $demas_here, true ) ) {
		$current = ' aria-current="true"';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s><span class="dh-line__name">%4$s</span><span class="dh-line__count dh-mono">%5$s</span></a>',
		esc_attr( $css_class ),
		esc_url( get_term_link( $t ) ),
		$current,
		esc_html( $t->name ),
		esc_html( str_pad( (string) demas_theme_term_product_count( $t ), 3, '0', STR_PAD_LEFT ) )
	);
};

$demas_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'dh-lines' . ( count( $demas_sections ) > 1 ? ' dh-lines--all' : '' ),
	)
);
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php foreach ( $demas_sections as $demas_section ) : ?>
		<?php
		/* translators: %s: a top-level product category, e.g. Landscape. */
		$demas_label = sprintf( __( '%s categories', 'demas-theme' ), $demas_section['top']->name );
		?>
		<nav class="dh-line" aria-label="<?php echo esc_attr( $demas_label ); ?>" data-reveal="rise">
			<?php // An h2 styled as an eyebrow: the sub-category names below are h3s, and the page's h1 is above. ?>
			<h2 class="dh-line__eyebrow dh-eyebrow">
				<?php echo $demas_link( $demas_section['top'], 'dh-line__top' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in $demas_link. ?>
			</h2>

			<ul class="dh-line__groups">
				<?php foreach ( $demas_section['groups'] as $demas_group ) : ?>
					<li class="dh-line__group">
						<h3 class="dh-line__head">
							<?php echo $demas_link( $demas_group['term'], 'dh-line__term dh-line__term--head' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in $demas_link. ?>
						</h3>

						<?php if ( $demas_group['items'] ) : ?>
							<ul class="dh-line__terms">
								<?php foreach ( $demas_group['items'] as $demas_item ) : ?>
									<li class="dh-line__item">
										<?php echo $demas_link( $demas_item, 'dh-line__term' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in $demas_link. ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endforeach; ?>
</div>
