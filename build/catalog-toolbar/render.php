<?php
/**
 * Server-rendered markup for the Catalogue Toolbar block.
 *
 * Three things a buyer scanning a parts catalogue needs before the grid:
 *
 *  - how many products are here (from the main query, so it agrees with
 *    whatever product-collection is about to render);
 *  - a way to narrow — the current category's children, or, on a leaf, its
 *    siblings with the current one marked and a way back up;
 *  - a sort order, as plain links so it works without JavaScript;
 *  - a search field (inc/search.php), which on the search results page is
 *    also the way to refine the query.
 *
 * On a search, the rail offers the categories whose names match the query
 * instead of children, and the default sort is "Best match".
 *
 * Nothing here is a filter facet: the catalogue has no product attributes,
 * so category is the only real axis and this block is built around that.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demas_product_taxonomies = get_object_taxonomies( 'product' );

if ( ! ( is_post_type_archive( 'product' ) || is_tax( $demas_product_taxonomies ) ) ) {
	return;
}

global $wp_query;

$demas_term      = is_tax() ? get_queried_object() : null;
$demas_total     = (int) $wp_query->found_posts;
$demas_is_search = is_search();
$demas_query     = $demas_is_search ? trim( get_search_query( false ) ) : '';

/* Rail ------------------------------------------------------------------ */

$demas_rail       = array();
$demas_active     = 0;
$demas_back       = null;
$demas_rail_label = '';

if ( $demas_is_search && '' !== $demas_query ) {
	// Categories whose names match — the same lookup as the header finder.
	$demas_rail       = function_exists( 'demas_theme_find_categories' ) ? demas_theme_find_categories( $demas_query ) : array();
	$demas_rail_label = __( 'Matching categories', 'demas-theme' );
} elseif ( $demas_term instanceof WP_Term ) {
	$demas_children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $demas_term->term_id,
			'hide_empty' => true, // Two live terms hold zero products; a dead chip is worse than none.
		)
	);

	if ( ! is_wp_error( $demas_children ) && $demas_children ) {
		$demas_rail = $demas_children;
	} elseif ( $demas_term->parent ) {
		// A leaf: offer its siblings, mark the current one, and a way back up.
		$demas_siblings = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $demas_term->parent,
				'hide_empty' => true,
			)
		);

		if ( ! is_wp_error( $demas_siblings ) && count( $demas_siblings ) > 1 ) {
			$demas_rail   = $demas_siblings;
			$demas_active = $demas_term->term_id;
			$demas_back   = get_term( $demas_term->parent, 'product_cat' );
		}
	}
}

/* Sort ------------------------------------------------------------------ */

$demas_sorts = $demas_is_search
	? array(
		''      => __( 'Best match', 'demas-theme' ), // Ranked in inc/search.php.
		'title' => __( 'A–Z', 'demas-theme' ),
		'sku'   => __( 'SKU', 'demas-theme' ),
	)
	: array(
		''      => __( 'Default', 'demas-theme' ),
		'title' => __( 'A–Z', 'demas-theme' ),
		'date'  => __( 'Newest', 'demas-theme' ),
		'sku'   => __( 'SKU', 'demas-theme' ), // Handled by inc/catalog-filters.php; not a native WooCommerce order.
	);

$demas_current_sort = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter.
if ( ! array_key_exists( $demas_current_sort, $demas_sorts ) ) {
	$demas_current_sort = '';
}

// Sort links rebuild from the archive's own URL so they always reset to page 1.
if ( $demas_is_search ) {
	$demas_base = add_query_arg(
		array(
			's'         => rawurlencode( $demas_query ),
			'post_type' => 'product',
		),
		home_url( '/' )
	);
} else {
	$demas_base = $demas_term instanceof WP_Term
		? get_term_link( $demas_term )
		: get_post_type_archive_link( 'product' );
}

/* View (AMM-141) --------------------------------------------------------- */

// The view the URL asked for. A remembered choice is applied in the browser,
// and main.js keeps these links and the sort links in step with it.
$demas_view = function_exists( 'demas_theme_catalogue_view' ) ? demas_theme_catalogue_view() : 'gallery';

if ( 'sheet' === $demas_view ) {
	$demas_base = add_query_arg( 'view', 'sheet', $demas_base );
}

$demas_views = array(
	'gallery' => array(
		'label' => __( 'Gallery', 'demas-theme' ),
		'url'   => remove_query_arg( 'view' ),
	),
	'sheet'   => array(
		'label' => __( 'Sheet', 'demas-theme' ),
		'url'   => add_query_arg( 'view', 'sheet' ),
	),
);

// Print heading: shown only on paper (src/product-meta/style.scss).
if ( $demas_is_search ) {
	/* translators: %s: the search query. */
	$demas_print_title = sprintf( __( 'Search “%s”', 'demas-theme' ), $demas_query );
} elseif ( $demas_term instanceof WP_Term ) {
	$demas_print_title = $demas_term->name;
} else {
	$demas_print_title = post_type_archive_title( '', false );
}

$demas_print_title = html_entity_decode( (string) $demas_print_title, ENT_QUOTES, 'UTF-8' );

/*
 * A page prints its own 24 lines, so a paginated listing says which lines
 * these are — "lines 001–024 of 113 parts" — rather than "113 parts" above a
 * sheet of 24 (AMM-141 audit).
 */
$demas_per_page = max( 1, (int) $wp_query->get( 'posts_per_page' ) );

if ( $demas_total > $demas_per_page ) {
	$demas_paged      = max( 1, (int) get_query_var( 'paged' ) );
	$demas_first_line = min( $demas_total, ( $demas_paged - 1 ) * $demas_per_page + 1 );
	$demas_last_line  = min( $demas_total, $demas_paged * $demas_per_page );

	$demas_print_line = sprintf(
		/* translators: 1: page title, 2: first line number, 3: last line number, 4: total number of parts. */
		__( 'Demas Group · %1$s · lines %2$s–%3$s of %4$s parts', 'demas-theme' ),
		$demas_print_title,
		str_pad( (string) $demas_first_line, 3, '0', STR_PAD_LEFT ),
		str_pad( (string) $demas_last_line, 3, '0', STR_PAD_LEFT ),
		number_format_i18n( $demas_total )
	);
} else {
	$demas_print_line = sprintf(
		/* translators: 1: page title, 2: number of parts. */
		_n( 'Demas Group · %1$s · %2$s part', 'Demas Group · %1$s · %2$s parts', $demas_total, 'demas-theme' ),
		$demas_print_title,
		number_format_i18n( $demas_total )
	);
}

$demas_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-toolbar' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>

	<div class="dh-print-head" aria-hidden="true">
		<p class="dh-print-head__title"><?php echo esc_html( $demas_print_line ); ?></p>
		<p class="dh-print-head__meta dh-mono">
			<?php esc_html_e( 'Printed', 'demas-theme' ); ?> <span data-dh-print-date><?php echo esc_html( wp_date( 'j M Y' ) ); ?></span> · <?php echo esc_html( home_url( $demas_request_uri ) ); ?>
		</p>
	</div>

	<?php if ( $demas_rail ) : ?>
		<nav class="dh-toolbar__rail" aria-label="<?php echo esc_attr( $demas_is_search ? __( 'Categories matching your search', 'demas-theme' ) : __( 'Narrow this category', 'demas-theme' ) ); ?>">
			<?php
			// One word for what the chips choose between — a kind of part, a
			// manufacturer, or a model family — since the tree mixes all three.
			if ( ! $demas_rail_label && function_exists( 'demas_theme_get_rail_label' ) ) {
				$demas_rail_label = demas_theme_get_rail_label( $demas_rail );
			}
			?>
			<?php if ( $demas_rail_label ) : ?>
				<span class="dh-toolbar__rail-label dh-eyebrow"><?php echo esc_html( $demas_rail_label ); ?></span>
			<?php endif; ?>
			<ul class="dh-toolbar__chips">
				<?php if ( $demas_back instanceof WP_Term ) : ?>
					<li>
						<a class="dh-toolbar__chip dh-toolbar__chip--back" href="<?php echo esc_url( get_term_link( $demas_back ) ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
							<span>
								<?php
								/* translators: %s: parent category name. */
								printf( esc_html__( 'All %s', 'demas-theme' ), esc_html( $demas_back->name ) );
								?>
							</span>
						</a>
					</li>
				<?php endif; ?>

				<?php foreach ( $demas_rail as $demas_chip ) : ?>
					<li>
						<a
							class="dh-toolbar__chip"
							href="<?php echo esc_url( get_term_link( $demas_chip ) ); ?>"
							<?php echo $demas_chip->term_id === $demas_active ? 'aria-current="page"' : ''; ?>
						>
							<span class="dh-toolbar__chip-icon" aria-hidden="true"><?php echo demas_theme_get_category_icon( $demas_chip->slug ); // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?></span>
							<span><?php echo esc_html( $demas_chip->name ); ?></span>
							<span class="dh-toolbar__chip-count dh-mono"><?php echo esc_html( (string) demas_theme_term_product_count( $demas_chip ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<div class="dh-toolbar__bar">
		<p class="dh-toolbar__count">
			<span class="dh-toolbar__count-value dh-mono"><?php echo esc_html( str_pad( (string) $demas_total, 3, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="dh-toolbar__count-label"><?php echo esc_html( _n( 'product', 'products', $demas_total, 'demas-theme' ) ); ?></span>
		</p>

		<?php
		if ( function_exists( 'demas_theme_search_form' ) ) {
			echo demas_theme_search_form( array( 'class' => 'dh-toolbar__search' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside the helper.
		}
		?>

		<?php
		/*
		 * Gallery | Sheet. Plain links, so they work without JavaScript; main.js
		 * switches in place instead, remembers the choice, and animates.
		 */
		?>
		<nav class="dh-toolbar__sort dh-toolbar__view" aria-label="<?php esc_attr_e( 'View', 'demas-theme' ); ?>">
			<span class="dh-toolbar__sort-label"><?php esc_html_e( 'View', 'demas-theme' ); ?></span>
			<ul class="dh-toolbar__segments">
				<?php foreach ( $demas_views as $demas_key => $demas_option ) : ?>
					<li>
						<a
							class="dh-toolbar__segment"
							href="<?php echo esc_url( $demas_option['url'] ); ?>"
							data-dh-view="<?php echo esc_attr( $demas_key ); ?>"
							aria-current="<?php echo $demas_key === $demas_view ? 'true' : 'false'; ?>"
						><?php echo esc_html( $demas_option['label'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<nav class="dh-toolbar__sort" aria-label="<?php esc_attr_e( 'Sort products', 'demas-theme' ); ?>">
			<span class="dh-toolbar__sort-label"><?php esc_html_e( 'Sort', 'demas-theme' ); ?></span>
			<ul class="dh-toolbar__segments">
				<?php foreach ( $demas_sorts as $demas_key => $demas_label ) : ?>
					<li>
						<a
							class="dh-toolbar__segment"
							href="<?php echo esc_url( '' === $demas_key ? $demas_base : add_query_arg( 'orderby', $demas_key, $demas_base ) ); ?>"
							<?php echo $demas_key === $demas_current_sort ? 'aria-current="true"' : ''; ?>
						><?php echo esc_html( $demas_label ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</div>
