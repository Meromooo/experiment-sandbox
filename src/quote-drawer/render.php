<?php
/**
 * Server-rendered markup for the Quote List block.
 *
 * Two things, in the header on every page:
 *
 *  - the control: "Your quote", with the number of parts beside it once
 *    there are any;
 *  - the list: a native <dialog>, opened as a modal. The browser then does
 *    the hard accessibility work — focus moves into it, stays inside it,
 *    Escape closes it, the page behind is inert, and focus returns to
 *    whatever opened it.
 *
 * The list's rows are drawn in the browser from the buyer's saved quote, so
 * the server renders them empty; everything that depends on the list starts
 * hidden and is revealed by the store. Strings the store needs are passed as
 * server state so they go through translation like everything else.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_interactivity_state(
	'demas-theme/quote',
	array(
		'strings' => array(
			/* translators: singular noun used after a count, e.g. "001 part". */
			'part'      => __( 'part', 'demas-theme' ),
			/* translators: plural noun used after a count, e.g. "003 parts". */
			'parts'     => __( 'parts', 'demas-theme' ),
			/* translators: %s: product name. */
			'added'     => __( 'Added %s to your quote.', 'demas-theme' ),
			/* translators: %s: product name. */
			'removed'   => __( 'Removed %s from your quote.', 'demas-theme' ),
			'cleared'   => __( 'Your quote is empty.', 'demas-theme' ),
			/* translators: %s: product name. */
			'quantity'  => __( 'Quantity of %s', 'demas-theme' ),
			/* translators: %s: product name. */
			'decrease'  => __( 'One fewer %s', 'demas-theme' ),
			/* translators: %s: product name. */
			'increase'  => __( 'One more %s', 'demas-theme' ),
			/* translators: %s: product name. */
			'remove'    => __( 'Remove %s from your quote', 'demas-theme' ),
		),
	)
);

$demas_catalogue = function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' );
$demas_wrapper   = get_block_wrapper_attributes( array( 'class' => 'dh-quote' ) );
?>
<div
	<?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>
	data-wp-interactive="demas-theme/quote"
	data-wp-init="callbacks.init"
	data-wp-on-window--storage="actions.syncFromStorage"
>
	<button
		type="button"
		class="dh-quote-toggle"
		aria-haspopup="dialog"
		aria-controls="dh-quote-dialog"
		data-wp-on--click="actions.open"
	>
		<svg class="dh-quote-toggle__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1z"/><path d="M8 5H6a1 1 0 0 0-1 1v13a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2"/><path d="M9 11h6M9 15h4"/></svg>
		<span class="dh-quote-toggle__label"><?php esc_html_e( 'Your quote', 'demas-theme' ); ?></span>
		<span class="dh-quote-toggle__count dh-mono" aria-hidden="true" hidden data-wp-bind--hidden="!state.hasItems" data-wp-text="state.countLabel"></span>
		<span class="dh-sr" data-wp-text="state.countSpoken"></span>
	</button>

	<p class="dh-sr" role="status" data-wp-text="state.announcement"></p>

	<dialog
		id="dh-quote-dialog"
		class="dh-quote-dialog"
		aria-labelledby="dh-quote-title"
		data-wp-watch="callbacks.syncDialog"
		data-wp-on--close="actions.onDialogClose"
		data-wp-on--click="actions.onDialogClick"
	>
		<div class="dh-quote-dialog__inner">
			<header class="dh-quote-dialog__head">
				<div class="dh-quote-dialog__heading">
					<h2 id="dh-quote-title" class="dh-quote-dialog__title" tabindex="-1"><?php esc_html_e( 'Your quote', 'demas-theme' ); ?></h2>
					<p class="dh-quote-dialog__count dh-mono" data-wp-text="state.partsLabel"></p>
				</div>
				<button type="button" class="dh-quote-dialog__close" data-wp-on--click="actions.close" aria-label="<?php esc_attr_e( 'Close your quote', 'demas-theme' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
			</header>

			<ol class="dh-quote-lines" aria-labelledby="dh-quote-title" hidden data-wp-bind--hidden="!state.hasItems">
				<template data-wp-each--line="state.lines" data-wp-each-key="context.line.id">
					<li class="dh-quote-line">
						<span class="dh-quote-line__n dh-mono" aria-hidden="true" data-wp-text="context.line.n"></span>
						<img class="dh-quote-line__img" alt="" width="56" height="56" loading="lazy" data-wp-bind--src="context.line.image">
						<div class="dh-quote-line__main">
							<a class="dh-quote-line__name" data-wp-bind--href="context.line.url" data-wp-text="context.line.name"></a>
							<span class="dh-quote-line__sku dh-mono" data-wp-text="context.line.sku"></span>
						</div>
						<div class="dh-qty" role="group" data-wp-bind--aria-label="context.line.quantityLabel">
							<button type="button" class="dh-qty__step" data-wp-on--click="actions.decrement" data-wp-bind--disabled="context.line.atMin" data-wp-bind--aria-label="context.line.decreaseLabel">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 12h12"/></svg>
							</button>
							<input class="dh-qty__input dh-mono" type="number" inputmode="numeric" min="1" max="9999" step="1" data-wp-bind--value="context.line.qty" data-wp-bind--aria-label="context.line.quantityLabel" data-wp-on--change="actions.setQuantity">
							<button type="button" class="dh-qty__step" data-wp-on--click="actions.increment" data-wp-bind--aria-label="context.line.increaseLabel">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M12 6v12M6 12h12"/></svg>
							</button>
						</div>
						<button type="button" class="dh-quote-line__remove" data-wp-on--click="actions.remove" data-wp-bind--aria-label="context.line.removeLabel">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
						</button>
					</li>
				</template>
			</ol>

			<div class="dh-quote-empty" data-wp-bind--hidden="state.hasItems">
				<p class="dh-quote-empty__title"><?php esc_html_e( 'Your quote is empty.', 'demas-theme' ); ?></p>
				<p class="dh-quote-empty__text"><?php esc_html_e( 'Add parts from any product page, or straight from the catalogue.', 'demas-theme' ); ?></p>
				<a class="dh-quote-empty__link" href="<?php echo esc_url( $demas_catalogue ); ?>"><?php esc_html_e( 'Browse the catalogue', 'demas-theme' ); ?></a>
			</div>

			<footer class="dh-quote-dialog__foot" hidden data-wp-bind--hidden="!state.hasItems">
				<p class="dh-quote-dialog__note"><?php esc_html_e( 'Prices come from your nearest Demas branch.', 'demas-theme' ); ?></p>
				<button type="button" class="dh-quote-dialog__clear" data-wp-on--click="actions.clear"><?php esc_html_e( 'Clear all', 'demas-theme' ); ?></button>
			</footer>
		</div>
	</dialog>
</div>
