<?php
/**
 * Server-rendered markup for the Search: No Results block.
 *
 * Direction, not mood: what was searched, then what to try. When the query
 * looks like a part number the likeliest miss is a wrong suffix, and part
 * numbers match from the start, so it offers the same number without its
 * last segment as a one-click search. Then the two ways onward that always
 * work — the catalogue, and a branch that can source the part. The template
 * places the Browse-by-system index after this block.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_search() || ! function_exists( 'demas_theme_search_terms' ) ) {
	return;
}

$demas_terms     = demas_theme_search_terms();
$demas_query     = $demas_terms['raw'];
$demas_catalogue = function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' );

// "TCN-FT-999" → "TCN-FT": a prefix of letters and a separator, then more.
$demas_sku    = end( $demas_terms['skus'] );
$demas_prefix = '';
if ( is_string( $demas_sku ) && preg_match( '/^[a-z]{2,6}(-[a-z0-9]+)+$/i', $demas_sku ) ) {
	$demas_prefix = strtoupper( (string) preg_replace( '/-[^-]+$/', '', $demas_sku ) );
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-empty' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<h2 class="dh-empty__title">
		<?php
		/* translators: %s: the search query. */
		printf( esc_html__( 'No parts match “%s”.', 'demas-theme' ), esc_html( $demas_query ) );
		?>
	</h2>

	<p class="dh-empty__hint">
		<?php if ( '' !== $demas_prefix ) : ?>
			<?php esc_html_e( 'Part numbers match from the start. Try the family without its last part:', 'demas-theme' ); ?>
			<a class="dh-empty__try dh-mono" href="<?php echo esc_url( add_query_arg( array( 's' => rawurlencode( $demas_prefix ), 'post_type' => 'product' ), home_url( '/' ) ) ); ?>"><?php echo esc_html( $demas_prefix ); ?></a>
		<?php else : ?>
			<?php esc_html_e( 'Check the spelling, or search by part number — house references look like', 'demas-theme' ); ?>
			<span class="dh-mono">DMS-FIL-009</span>.
		<?php endif; ?>
	</p>

	<ul class="dh-empty__actions">
		<li><a class="dh-pill dh-pill--solid" href="<?php echo esc_url( $demas_catalogue ); ?>"><?php esc_html_e( 'Browse the catalogue', 'demas-theme' ); ?></a></li>
		<li><a class="dh-pill dh-pill--outline" href="<?php echo esc_url( home_url( '/#find-your-branch' ) ); ?>"><?php esc_html_e( 'Ask a branch to source it', 'demas-theme' ); ?></a></li>
	</ul>
</div>
