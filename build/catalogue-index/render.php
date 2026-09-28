<?php
/**
 * Server-rendered markup for the Catalogue Index block — the footer's site index.
 *
 * Every product group with its subcategories and outbound links, from
 * demas_theme_get_catalogue_columns(): the same resolved list the mega menu
 * renders, so the two can never disagree. Below 40em only the groups show —
 * each group's archive lists its own subcategories.
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

if ( ! function_exists( 'demas_theme_get_catalogue_columns' ) ) {
	return;
}

$demas_columns = demas_theme_get_catalogue_columns();

if ( ! $demas_columns ) {
	return;
}

$demas_heading_id    = wp_unique_id( 'dh-cindex-' );
$demas_counts        = wp_count_posts( 'product' );
$demas_total         = isset( $demas_counts->publish ) ? (int) $demas_counts->publish : 0;
$demas_catalogue_url = function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' );
$demas_wrapper       = get_block_wrapper_attributes(
	array(
		'class'           => 'dh-cindex',
		'aria-labelledby' => $demas_heading_id,
	)
);
$demas_arrow         = '<svg class="dh-cindex__arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M3 8h10M9 4l4 4-4 4"/></svg>';
$demas_outbound      = '<svg class="dh-cindex__arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M5 11 11 5M6 5h5v5"/></svg>';
?>
<nav <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<div class="dh-cindex__head">
		<h2 class="dh-foot-label" id="<?php echo esc_attr( $demas_heading_id ); ?>"><?php esc_html_e( 'Catalogue', 'demas-theme' ); ?></h2>

		<?php if ( $demas_total > 0 ) : ?>
			<a class="dh-cindex__all" href="<?php echo esc_url( $demas_catalogue_url ); ?>">
				<?php
				/* translators: %s: number of products in the catalogue. */
				printf( esc_html__( 'All %s products', 'demas-theme' ), esc_html( number_format_i18n( $demas_total ) ) );
				echo $demas_arrow; // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG.
				?>
			</a>
		<?php endif; ?>
	</div>

	<ul class="dh-cindex__groups">
		<?php foreach ( $demas_columns as $demas_column ) : ?>
			<?php
			$demas_parent   = $demas_column['term'];
			$demas_children = $demas_column['children'];
			$demas_links    = $demas_column['links'];
			$demas_is_leaf  = ! $demas_children && ! $demas_links;
			// Groups with one row or none (Swimming Pool, Non-Woven) share a
			// column on wide screens instead of each standing half-empty.
			$demas_is_short = count( $demas_children ) + count( $demas_links ) <= 1;
			?>
			<li class="dh-cindex__group<?php echo $demas_is_short ? ' is-short' : ''; ?>">
				<a class="dh-cindex__group-link" href="<?php echo esc_url( get_term_link( $demas_parent ) ); ?>"><?php echo esc_html( $demas_parent->name ); ?></a>

				<?php if ( ! $demas_is_leaf ) : ?>
					<ul class="dh-cindex__list">
						<?php foreach ( $demas_children as $demas_child ) : ?>
							<li><a class="dh-cindex__link" href="<?php echo esc_url( get_term_link( $demas_child ) ); ?>"><?php echo esc_html( $demas_child->name ); ?></a></li>
						<?php endforeach; ?>

						<?php foreach ( $demas_links as $demas_link ) : ?>
							<li>
								<a class="dh-cindex__link is-external" href="<?php echo esc_url( $demas_link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html( $demas_link['label'] ); ?>
									<?php echo $demas_outbound; // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?>
									<span class="dh-sr"><?php esc_html_e( '(opens in a new tab)', 'demas-theme' ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
