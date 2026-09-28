<?php
/**
 * Server-rendered markup for the Part Details block (AMM-141).
 *
 * Sits in every catalogue card, under the product name, and carries the three
 * facts the Sheet view lays out as columns:
 *
 *  - the line number — this part's position in the whole listing, so page 2
 *    of 24-a-page starts at 025 and "line 031" means the same part to a buyer
 *    and a branch on the phone. Only on catalogue pages; a related-parts list
 *    has no lines;
 *  - the part number (SKU) — on a search, with the matched fragment marked
 *    and an exact hit tagged (inc/search.php);
 *  - the category path, top first: "Irrigation / Filtration".
 *
 * Gallery shows the part number only; Sheet shows all three (see style.scss).
 * Each value carries a screen-reader label, because the Sheet's column
 * headings are visual only.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance (context: postId).
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

$demas_id      = (int) ( $block->context['postId'] ?? get_the_ID() );
$demas_product = $demas_id ? wc_get_product( $demas_id ) : null;

if ( ! $demas_product ) {
	return;
}

// Line number: position in the main query, continued across pages.
$demas_line = '';
global $wp_query;

if ( function_exists( 'demas_theme_is_catalogue_page' ) && demas_theme_is_catalogue_page() && $wp_query instanceof WP_Query && $wp_query->posts ) {
	$demas_ids = array_map( 'intval', wp_list_pluck( $wp_query->posts, 'ID' ) );
	$demas_pos = array_search( $demas_id, $demas_ids, true );

	if ( false !== $demas_pos ) {
		$demas_per   = max( 1, (int) $wp_query->get( 'posts_per_page' ) );
		$demas_paged = max( 1, (int) get_query_var( 'paged' ) );
		$demas_line  = str_pad( (string) ( ( $demas_paged - 1 ) * $demas_per + $demas_pos + 1 ), 3, '0', STR_PAD_LEFT );
	}
}

// Part number, marked up for a search.
$demas_sku      = (string) $demas_product->get_sku();
$demas_sku_html = esc_html( $demas_sku );
$demas_exact    = false;

if ( '' !== $demas_sku && is_search() && function_exists( 'demas_theme_search_terms' ) && function_exists( 'demas_theme_highlight' ) ) {
	$demas_terms    = demas_theme_search_terms();
	$demas_sku_html = demas_theme_highlight( $demas_sku_html, array_merge( $demas_terms['skus'], $demas_terms['words'] ) );
	$demas_exact    = in_array( mb_strtolower( $demas_sku ), array_map( 'mb_strtolower', $demas_terms['skus'] ), true );
}

// Category path, top first.
$demas_path = '';
if ( function_exists( 'demas_theme_get_product_term_chain' ) ) {
	$demas_chain = demas_theme_get_product_term_chain( $demas_id );
	$demas_path  = implode( ' / ', array_reverse( wp_list_pluck( $demas_chain, 'name' ) ) );
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-pmeta' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php if ( '' !== $demas_line ) : ?>
		<span class="dh-pmeta__line dh-mono"><span class="dh-sr"><?php esc_html_e( 'Line', 'demas-theme' ); ?> </span><?php echo esc_html( $demas_line ); ?></span>
	<?php endif; ?>

	<div class="dh-pmeta__ids">
		<?php if ( '' !== $demas_sku ) : ?>
			<p class="dh-pmeta__sku dh-mono">
				<span class="dh-sr"><?php esc_html_e( 'Part number', 'demas-theme' ); ?> </span><?php echo $demas_sku_html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above; highlight adds only <mark>. ?>
				<?php if ( $demas_exact ) : ?>
					<span class="dh-pcard__exact"><?php esc_html_e( 'Exact match', 'demas-theme' ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<?php if ( '' !== $demas_path ) : ?>
			<p class="dh-pmeta__cat"><span class="dh-sr"><?php esc_html_e( 'Category', 'demas-theme' ); ?> </span><?php echo esc_html( $demas_path ); ?></p>
		<?php endif; ?>
	</div>
</div>
