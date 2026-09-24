<?php
/**
 * Server-rendered markup for the Product Datasheet block.
 *
 * Everything on the page beside the photo, in reading order:
 *
 *  - the part's own category as an eyebrow, and its name;
 *  - a lede from the short description, when it says something the full
 *    description does not (124 products store the same text in both);
 *  - the nameplate: SKU, brand and series when the category tree knows them,
 *    and the stage this part sits at on its system line;
 *  - the quote action;
 *  - the specification — the description, cleaned by
 *    demas_theme_clean_description() — or a direction when there is none
 *    (122 products have no description at all).
 *
 * Helpers live in inc/product-page.php and inc/system-map.php.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_product_term_chain' ) || ! function_exists( 'wc_get_product' ) ) {
	return;
}

$demas_id      = (int) ( $block->context['postId'] ?? get_the_ID() );
$demas_product = wc_get_product( $demas_id );

if ( ! $demas_product ) {
	return;
}

$demas_title = get_the_title( $demas_id );
$demas_chain = demas_theme_get_product_term_chain( $demas_id );
$demas_leaf  = $demas_chain[0] ?? null;
$demas_top   = $demas_chain ? end( $demas_chain ) : null;
$demas_make  = demas_theme_get_product_make( $demas_chain );
$demas_stage = demas_theme_get_product_stage( $demas_chain );
$demas_sku   = trim( (string) $demas_product->get_sku() );

/* Description and lede ---------------------------------------------------- */

$demas_spec = demas_theme_clean_description( (string) apply_filters( 'the_content', $demas_product->get_description() ) );

// The lede is prose: plain text, trimmed, and dropped when it only repeats
// the opening of the description or is itself a pasted stylesheet.
$demas_lede       = demas_theme_plain_text( (string) $demas_product->get_short_description() );
$demas_spec_plain = demas_theme_plain_text( $demas_spec );

if (
	'' === $demas_lede
	|| demas_theme_looks_like_css( $demas_lede )
	|| ( '' !== $demas_spec_plain && 0 === strpos( $demas_spec_plain, mb_substr( $demas_lede, 0, 160 ) ) )
) {
	$demas_lede = '';
} else {
	$demas_lede = wp_trim_words( $demas_lede, 60, '…' );
}

/* Nameplate rows ---------------------------------------------------------- */

$demas_rows = array();

if ( '' !== $demas_sku ) {
	$demas_rows[] = array(
		'label' => __( 'SKU', 'demas-theme' ),
		'value' => $demas_sku,
		'class' => 'dh-plate__row--sku',
	);
}

if ( $demas_make['brand'] ) {
	$demas_rows[] = array(
		'label' => __( 'Brand', 'demas-theme' ),
		'value' => $demas_make['brand']->name,
		'class' => '',
	);
}

if ( $demas_make['series'] ) {
	$demas_rows[] = array(
		'label' => __( 'Series', 'demas-theme' ),
		'value' => $demas_make['series']->name,
		'class' => '',
	);
}

$demas_note_id = 'dh-quote-note-' . $demas_id;
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-summary' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>

	<header class="dh-summary__head">
		<?php if ( $demas_leaf ) : ?>
			<p class="dh-summary__eyebrow dh-eyebrow">
				<a href="<?php echo esc_url( get_term_link( $demas_leaf ) ); ?>"><?php echo esc_html( $demas_leaf->name ); ?></a>
			</p>
		<?php endif; ?>

		<h1 class="dh-summary__title <?php echo esc_attr( demas_theme_title_tier( $demas_title ) ); ?>"><?php echo esc_html( wp_strip_all_tags( $demas_title ) ); ?></h1>

		<?php if ( '' !== $demas_lede ) : ?>
			<p class="dh-summary__lede"><?php echo esc_html( $demas_lede ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $demas_rows || $demas_stage ) : ?>
		<section class="dh-plate" aria-label="<?php esc_attr_e( 'Part identification', 'demas-theme' ); ?>">
			<?php if ( $demas_rows ) : ?>
				<dl class="dh-plate__rows">
					<?php foreach ( $demas_rows as $demas_row ) : ?>
						<div class="dh-plate__row <?php echo esc_attr( $demas_row['class'] ); ?>">
							<dt class="dh-plate__label"><?php echo esc_html( $demas_row['label'] ); ?></dt>
							<dd class="dh-plate__value"><?php echo esc_html( $demas_row['value'] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>

			<?php if ( $demas_stage ) : ?>
				<?php
				$demas_stages  = $demas_stage['system']['stages'];
				$demas_current = $demas_stage['index'];
				?>
				<div class="dh-plate__line" style="--_n: <?php echo (int) count( $demas_stages ); ?>" data-reveal="line">
					<p class="dh-plate__system">
						<span class="dh-plate__label"><?php echo esc_html( $demas_stage['system']['label'] ); ?></span>
						<?php if ( $demas_top instanceof WP_Term ) : ?>
							<a class="dh-plate__more" href="<?php echo esc_url( get_term_link( $demas_top ) ); ?>"><?php esc_html_e( 'See the whole line', 'demas-theme' ); ?></a>
						<?php endif; ?>
					</p>
					<ol class="dh-plate__stages">
						<?php foreach ( $demas_stages as $demas_i => $demas_s ) : ?>
							<li
								class="dh-plate__stage<?php echo $demas_i === $demas_current ? ' is-current' : ''; ?>"
								style="--i: <?php echo (int) $demas_i; ?>"
								<?php echo $demas_i === $demas_current ? 'aria-current="step"' : ''; ?>
							>
								<span class="dh-plate__node" aria-hidden="true"></span>
								<span class="dh-plate__stage-name"><?php echo esc_html( $demas_s['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<div class="dh-summary__action">
		<?php
		/*
		 * The quote list (AMM-139) wires this button through the Interactivity
		 * API. Its name is already the one it keeps — "Add to quote" — so the
		 * template does not change when that lands, only this block.
		 */
		?>
		<button type="button" class="dh-quote-btn" data-product-id="<?php echo esc_attr( (string) $demas_id ); ?>" aria-describedby="<?php echo esc_attr( $demas_note_id ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M12 5v14M5 12h14"/></svg>
			<span><?php esc_html_e( 'Add to quote', 'demas-theme' ); ?></span>
		</button>
		<p class="dh-summary__note" id="<?php echo esc_attr( $demas_note_id ); ?>"><?php esc_html_e( 'Priced on request by your nearest Demas branch.', 'demas-theme' ); ?></p>
	</div>

	<section class="dh-spec" aria-labelledby="dh-spec-title-<?php echo esc_attr( (string) $demas_id ); ?>">
		<h2 class="dh-spec__title dh-eyebrow" id="dh-spec-title-<?php echo esc_attr( (string) $demas_id ); ?>"><?php esc_html_e( 'Specification', 'demas-theme' ); ?></h2>

		<?php if ( '' !== $demas_spec ) : ?>
			<div class="dh-spec__body">
				<?php echo $demas_spec; // phpcs:ignore WordPress.Security.EscapeOutput -- post content passed through the_content, then cleaned. ?>
			</div>
		<?php else : ?>
			<p class="dh-spec__empty"><?php esc_html_e( 'No published specification for this part yet. Ask for the datasheet with your quote.', 'demas-theme' ); ?></p>
		<?php endif; ?>
	</section>
</div>
