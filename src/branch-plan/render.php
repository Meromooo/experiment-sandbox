<?php
/**
 * Server-rendered markup for the Branches and Key Plan block.
 *
 * Two parts, side by side in the footer's title block:
 *
 *  - The key plan: the small locator map an engineering drawing carries in
 *    its title block. Each branch is a dot at its city's real latitude and
 *    longitude on a faint graticule. There is deliberately no coastline or
 *    border — a hand-simplified outline of the Kingdom would be wrong
 *    somewhere, and the fifteen dots already trace it. Decorative, so hidden
 *    from assistive technology: the list says the same thing in words.
 *  - The branch list: every branch as a link to the homepage Branch Desk with
 *    that city selected.
 *
 * Hovering or focusing a branch link lights its dot. That coupling is CSS
 * (:has()), generated below from the same list, so nothing needs JavaScript.
 * On first view the dots light up outward from the main branch — Kingdom-wide
 * supply from Riyadh — via the theme's reveal observer; reduced motion and
 * no-JS both get the finished plan.
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

if ( ! function_exists( 'demas_theme_get_branches' ) ) {
	return;
}

$demas_branches = demas_theme_get_branches();

if ( ! $demas_branches ) {
	return;
}

/*
 * Projection: demas_theme_branch_plan_point() (inc/branches.php), shared with
 * the Contact page's layout plan. This plan shows 36–52°E × 16–32°N.
 */
$demas_width  = round( demas_theme_branch_plan_point( 16, 52 )[0] );
$demas_height = demas_theme_branch_plan_point( 16, 52 )[1];

$demas_x = static function ( float $lon ): float {
	return demas_theme_branch_plan_point( 32, $lon )[0];
};
$demas_y = static function ( float $lat ): float {
	return demas_theme_branch_plan_point( $lat, 36 )[1];
};

// Reveal order: nearest to the main branch first.
$demas_main = array_key_first( array_filter( $demas_branches, static fn( $b ) => ! empty( $b['main'] ) ) ) ?? array_key_first( $demas_branches );
$demas_hub  = $demas_branches[ $demas_main ];
$demas_near = array();
foreach ( $demas_branches as $demas_code => $demas_branch ) {
	$demas_near[ $demas_code ] = hypot( $demas_branch['lat'] - $demas_hub['lat'], $demas_branch['lon'] - $demas_hub['lon'] );
}
asort( $demas_near );
$demas_order = array_flip( array_keys( $demas_near ) );

// Link ↔ dot coupling, one rule per branch.
$demas_css = '';
foreach ( array_keys( $demas_branches ) as $demas_code ) {
	$demas_code = sanitize_key( $demas_code );
	$demas_css .= sprintf(
		'.dh-bplan:has([data-branch-link="%1$s"]:is(:hover,:focus-visible)) .dh-plan__site[data-code="%1$s"]{--_on:1}',
		$demas_code
	);
}
wp_add_inline_style( generate_block_asset_handle( 'demas-theme/branch-plan', 'style' ), $demas_css );

$demas_heading_id = wp_unique_id( 'dh-bplan-' );
$demas_wrapper    = get_block_wrapper_attributes( array( 'class' => 'dh-bplan' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<figure class="dh-plan" data-reveal="plot" aria-hidden="true">
		<figcaption class="dh-foot-label"><?php esc_html_e( 'Key plan', 'demas-theme' ); ?></figcaption>

		<svg class="dh-plan__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 <?php echo (int) $demas_width; ?> <?php echo (int) $demas_height; ?>" focusable="false">
			<g class="dh-plan__grid">
				<?php foreach ( array( 20, 25, 30 ) as $demas_lat ) : ?>
					<line x1="0" x2="<?php echo (int) $demas_width; ?>" y1="<?php echo esc_attr( $demas_y( $demas_lat ) ); ?>" y2="<?php echo esc_attr( $demas_y( $demas_lat ) ); ?>" />
					<text class="dh-plan__tick" x="2" y="<?php echo esc_attr( $demas_y( $demas_lat ) - 4 ); ?>"><?php echo (int) $demas_lat; ?>°N</text>
				<?php endforeach; ?>
				<?php foreach ( array( 40, 45, 50 ) as $demas_lon ) : ?>
					<line y1="0" y2="<?php echo (int) $demas_height; ?>" x1="<?php echo esc_attr( $demas_x( $demas_lon ) ); ?>" x2="<?php echo esc_attr( $demas_x( $demas_lon ) ); ?>" />
					<text class="dh-plan__tick" x="<?php echo esc_attr( $demas_x( $demas_lon ) + 3 ); ?>" y="<?php echo (int) $demas_height - 4; ?>"><?php echo (int) $demas_lon; ?>°E</text>
				<?php endforeach; ?>
			</g>

			<text class="dh-plan__sea" transform="translate(<?php echo esc_attr( $demas_x( 38.3 ) . ' ' . $demas_y( 19.7 ) ); ?>) rotate(55)"><?php esc_html_e( 'Red Sea', 'demas-theme' ); ?></text>
			<text class="dh-plan__sea" transform="translate(<?php echo esc_attr( $demas_x( 48.9 ) . ' ' . $demas_y( 29.6 ) ); ?>) rotate(54)"><?php esc_html_e( 'Arabian Gulf', 'demas-theme' ); ?></text>

			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<?php
				$demas_cx   = $demas_x( (float) $demas_branch['lon'] );
				$demas_cy   = $demas_y( (float) $demas_branch['lat'] );
				$demas_east = $demas_cx > $demas_width * 0.75;
				?>
				<g class="dh-plan__site<?php echo empty( $demas_branch['main'] ) ? '' : ' is-main'; ?>" data-code="<?php echo esc_attr( sanitize_key( $demas_code ) ); ?>" style="--i:<?php echo (int) $demas_order[ $demas_code ]; ?>">
					<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
						<circle class="dh-plan__ring" cx="<?php echo esc_attr( $demas_cx ); ?>" cy="<?php echo esc_attr( $demas_cy ); ?>" r="8" />
					<?php endif; ?>
					<circle class="dh-plan__dot" cx="<?php echo esc_attr( $demas_cx ); ?>" cy="<?php echo esc_attr( $demas_cy ); ?>" r="3.4" />
					<text class="dh-plan__code" x="<?php echo esc_attr( $demas_east ? $demas_cx - 12 : $demas_cx + 12 ); ?>" y="<?php echo esc_attr( $demas_cy + 4.5 ); ?>" text-anchor="<?php echo $demas_east ? 'end' : 'start'; ?>"><?php echo esc_html( strtoupper( $demas_code ) ); ?></text>
				</g>
			<?php endforeach; ?>
		</svg>
	</figure>

	<nav class="dh-bplan__branches" aria-labelledby="<?php echo esc_attr( $demas_heading_id ); ?>">
		<h2 class="dh-foot-label" id="<?php echo esc_attr( $demas_heading_id ); ?>">
			<?php
			/* translators: %d: number of branches. */
			printf( esc_html__( 'Branches · %d', 'demas-theme' ), count( $demas_branches ) );
			?>
		</h2>

		<ul class="dh-bplan__list">
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<li>
					<a class="dh-branch" href="<?php echo esc_url( demas_theme_branch_url( $demas_code ) ); ?>" data-branch-link="<?php echo esc_attr( sanitize_key( $demas_code ) ); ?>">
						<span class="dh-branch__code" aria-hidden="true"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span>
						<span class="dh-branch__name">
							<span class="dh-branch__city"><?php echo esc_html( $demas_branch['city'] ); ?></span>
							<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
								<span class="dh-branch__tag"><?php esc_html_e( 'Main', 'demas-theme' ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</div>
