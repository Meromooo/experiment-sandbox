<?php
/**
 * Server-rendered markup for the Add to Quote block.
 *
 * One button, two looks: `full` on a product page (solid water), `compact` on
 * catalogue cards (outlined, so a grid of 24 parts is not 24 loud buttons).
 * Once the part is in the list the button reads "Added to quote", and
 * pressing it again opens the list rather than adding a second line.
 *
 * The button carries a snapshot of the part in its Interactivity context —
 * ID, name, SKU, thumbnail, link — which is what the list stores, so the list
 * draws on any page without asking the server for anything.
 *
 * Without JavaScript the quote list cannot work (it lives in the browser), so
 * the button renders `is-pending` — invisible but holding its space, so
 * nothing shifts when it appears — and the store clears that on load.
 *
 * @param array    $attributes Block attributes: variant, productId, describedBy.
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

$demas_id      = (int) ( $attributes['productId'] ?? 0 );
$demas_id      = $demas_id ? $demas_id : (int) ( $block->context['postId'] ?? get_the_ID() );
$demas_product = wc_get_product( $demas_id );

if ( ! $demas_product ) {
	return;
}

$demas_variant = 'compact' === ( $attributes['variant'] ?? '' ) ? 'compact' : 'full';
$demas_name    = wp_strip_all_tags( get_the_title( $demas_id ) );
$demas_image   = $demas_product->get_image_id()
	? wp_get_attachment_image_url( $demas_product->get_image_id(), 'thumbnail' )
	: wc_placeholder_img_src( 'thumbnail' );

$demas_item = array(
	'id'    => $demas_id,
	'name'  => html_entity_decode( $demas_name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
	'sku'   => (string) $demas_product->get_sku(),
	'image' => (string) $demas_image,
	'url'   => (string) get_permalink( $demas_id ),
);

$demas_described = sanitize_html_class( (string) ( $attributes['describedBy'] ?? '' ) );
$demas_wrapper   = get_block_wrapper_attributes( array( 'class' => 'dh-quote-add dh-quote-add--' . $demas_variant ) );

// On a card the visible label is the same on every button, so a screen reader
// needs the part's name to tell 24 "Add to quote" buttons apart.
$demas_whose = 'compact' === $demas_variant
	? '<span class="dh-sr"> — ' . esc_html( $demas_name ) . '</span>'
	: '';
?>
<div
	<?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>
	data-wp-interactive="demas-theme/quote"
	<?php echo wp_interactivity_data_wp_context( array( 'item' => $demas_item ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>
>
	<button
		type="button"
		class="dh-quote-btn is-pending"
		data-wp-class--is-pending="!state.ready"
		data-wp-class--is-added="state.isAdded"
		data-wp-on--click="actions.addOrOpen"
		<?php echo $demas_described ? 'aria-describedby="' . esc_attr( $demas_described ) . '"' : ''; ?>
	>
		<svg class="dh-quote-btn__icon dh-quote-btn__icon--add" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
		<svg class="dh-quote-btn__icon dh-quote-btn__icon--added" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
		<span class="dh-quote-btn__label dh-quote-btn__label--add"><?php esc_html_e( 'Add to quote', 'demas-theme' ); ?><?php echo $demas_whose; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></span>
		<span class="dh-quote-btn__label dh-quote-btn__label--added"><?php esc_html_e( 'Added to quote', 'demas-theme' ); ?><?php echo $demas_whose; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?><span class="dh-sr"><?php esc_html_e( '. Opens your quote.', 'demas-theme' ); ?></span></span>
	</button>
</div>
