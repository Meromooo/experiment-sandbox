<?php
/**
 * Server-rendered markup for the Branches and Key Plan block.
 *
 * Two parts, side by side in the footer's title block:
 *
 *  - The key plan: the small locator map an engineering drawing carries in
 *    its title block. Each branch is a dot at its city's real latitude and
 *    longitude, on the real land (AMM-188): the Kingdom as a paper sheet with
 *    a canopy outline, its neighbours a shade darker than sand, the seas
 *    drawn with three water-lines along the coast (offset curves, made by
 *    stroking the land in widening bands under the land itself), and the
 *    surroundings fading out at the frame's edges. The outlines come from
 *    Natural Earth's public-domain map data (inc/key-plan-land.php, made by
 *    tools/make-key-plan.py), not drawn by hand, so they are right
 *    everywhere. A graticule, a north arrow and a 500 km scale bar (true at
 *    24°N) finish it. Decorative, so hidden from assistive technology: the
 *    list says the same thing in words.
 *  - The branch list: every branch as a link to the Contact page with that
 *    branch chosen (demas_theme_branch_url()).
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

if ( ! function_exists( 'demas_theme_get_branches' ) || ! function_exists( 'demas_theme_get_key_plan_land' ) ) {
	return;
}

$demas_branches = demas_theme_get_branches();

if ( ! $demas_branches ) {
	return;
}

/*
 * Projection: demas_theme_branch_plan_point() (inc/branches.php), shared with
 * the Contact page's map. The frame, the whole Kingdom with a margin,
 * comes with the land (inc/key-plan-land.php). Sizes meant in pixels are
 * multiplied by $demas_u, the plan units in a pixel at the plan's full width
 * (18rem); CSS gets the same number as --_u.
 */
$demas_land   = demas_theme_get_key_plan_land();
$demas_frame  = $demas_land['frame'];
$demas_nw     = demas_theme_branch_plan_point( $demas_frame[3], $demas_frame[0] );
$demas_se     = demas_theme_branch_plan_point( $demas_frame[2], $demas_frame[1] );
$demas_x0     = $demas_nw[0];
$demas_y0     = $demas_nw[1];
$demas_x1     = $demas_se[0];
$demas_y1     = $demas_se[1];
$demas_width  = $demas_x1 - $demas_x0;
$demas_height = $demas_y1 - $demas_y0;
$demas_u      = $demas_width / 288;

$demas_x = static function ( float $lon ): float {
	return demas_theme_branch_plan_point( 32, $lon )[0];
};
$demas_y = static function ( float $lat ): float {
	return demas_theme_branch_plan_point( $lat, 36 )[1];
};
$demas_n = static function ( float $value ): string {
	return number_format( $value, 1, '.', '' );
};

// Ids for the land, the edge fades and the frame.
$demas_id = wp_unique_id( 'dh-plan-' );

// The scale bar, bottom right: 500 km where the projection is true (24°N).
$demas_bar   = 500 * 20 / 111.32;
$demas_bx    = $demas_x( 51.0 );
$demas_by    = $demas_y( 17.15 );
$demas_scale = sprintf(
	'M%1$s %2$sh%3$sM%1$s %4$sv%5$sM%6$s %7$sv%8$sM%9$s %4$sv%5$s',
	$demas_n( $demas_bx ),
	$demas_n( $demas_by ),
	$demas_n( $demas_bar ),
	$demas_n( $demas_by - 3 * $demas_u ),
	$demas_n( 6 * $demas_u ),
	$demas_n( $demas_bx + $demas_bar / 2 ),
	$demas_n( $demas_by - 2 * $demas_u ),
	$demas_n( 4 * $demas_u ),
	$demas_n( $demas_bx + $demas_bar )
);

// The north arrow, top right: a needle, its west half outlined.
$demas_ax    = $demas_x( 55.0 );
$demas_ay    = $demas_y( 30.9 );
$demas_arrow = static function ( float $side ) use ( $demas_n, $demas_ax, $demas_ay, $demas_u ): string {
	return sprintf(
		'M%1$s %2$sL%3$s %4$sL%1$s %5$sZ',
		$demas_n( $demas_ax ),
		$demas_n( $demas_ay - 13 * $demas_u ),
		$demas_n( $demas_ax + $side * 4.5 * $demas_u ),
		$demas_n( $demas_ay + 6 * $demas_u ),
		$demas_n( $demas_ay + 2.5 * $demas_u )
	);
};

// The water-lines: each a band of water colour with a band of sand inside
// it, both the land's outline stroked wide, outermost first; the land is
// painted over them, so only the sea side shows. The band's number and its
// distance from the coast, in plan units.
$demas_waves = array(
	1 => 14,
	2 => 8.5,
	3 => 4,
);

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

		<svg class="dh-plan__svg" xmlns="http://www.w3.org/2000/svg" viewBox="<?php echo esc_attr( $demas_n( $demas_x0 ) . ' ' . $demas_n( $demas_y0 ) . ' ' . $demas_n( $demas_width ) . ' ' . $demas_n( $demas_height ) ); ?>" style="--_u:<?php echo esc_attr( number_format( $demas_u, 3, '.', '' ) ); ?>" focusable="false">
			<defs>
				<path id="<?php echo esc_attr( $demas_id ); ?>-kingdom" d="<?php echo esc_attr( $demas_land['kingdom'] ); ?>" />
				<path id="<?php echo esc_attr( $demas_id ); ?>-neighbours" d="<?php echo esc_attr( $demas_land['neighbours'] ); ?>" />
				<linearGradient id="<?php echo esc_attr( $demas_id ); ?>-fx" gradientUnits="userSpaceOnUse" x1="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" x2="<?php echo esc_attr( $demas_n( $demas_x1 ) ); ?>" y1="0" y2="0">
					<stop offset="0" stop-color="#000" /><stop offset=".09" stop-color="#fff" /><stop offset=".91" stop-color="#fff" /><stop offset="1" stop-color="#000" />
				</linearGradient>
				<linearGradient id="<?php echo esc_attr( $demas_id ); ?>-fy" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" y2="<?php echo esc_attr( $demas_n( $demas_y1 ) ); ?>">
					<stop offset="0" stop-color="#000" /><stop offset=".09" stop-color="#fff" /><stop offset=".91" stop-color="#fff" /><stop offset="1" stop-color="#000" />
				</linearGradient>
				<?php foreach ( array( 'x', 'y' ) as $demas_axis ) : ?>
					<mask id="<?php echo esc_attr( $demas_id . '-m' . $demas_axis ); ?>" maskUnits="userSpaceOnUse" x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>">
						<rect x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>" fill="url(#<?php echo esc_attr( $demas_id . '-f' . $demas_axis ); ?>)" />
					</mask>
				<?php endforeach; ?>
				<clipPath id="<?php echo esc_attr( $demas_id ); ?>-frame">
					<rect x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>" />
				</clipPath>
			</defs>

			<g clip-path="url(#<?php echo esc_attr( $demas_id ); ?>-frame)">
				<g mask="url(#<?php echo esc_attr( $demas_id ); ?>-mx)">
					<g mask="url(#<?php echo esc_attr( $demas_id ); ?>-my)">
						<?php foreach ( $demas_waves as $demas_wave => $demas_far ) : ?>
							<g class="dh-plan__wave dh-plan__wave--<?php echo (int) $demas_wave; ?>" stroke-width="<?php echo esc_attr( $demas_n( 2 * $demas_far + 1.15 * $demas_u ) ); ?>"><use href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" /><use href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" /></g>
							<g class="dh-plan__wave-gap" stroke-width="<?php echo esc_attr( $demas_n( 2 * $demas_far - 1.15 * $demas_u ) ); ?>"><use href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" /><use href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" /></g>
						<?php endforeach; ?>
						<use class="dh-plan__neighbours" href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" />
					</g>
				</g>
				<use class="dh-plan__kingdom" href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" />

				<g class="dh-plan__grid">
					<?php foreach ( array( 20, 25, 30 ) as $demas_lat ) : ?>
						<line x1="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" x2="<?php echo esc_attr( $demas_n( $demas_x1 ) ); ?>" y1="<?php echo esc_attr( $demas_n( $demas_y( $demas_lat ) ) ); ?>" y2="<?php echo esc_attr( $demas_n( $demas_y( $demas_lat ) ) ); ?>" />
					<?php endforeach; ?>
					<?php foreach ( array( 35, 40, 45, 50, 55 ) as $demas_lon ) : ?>
						<line y1="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" y2="<?php echo esc_attr( $demas_n( $demas_y1 ) ); ?>" x1="<?php echo esc_attr( $demas_n( $demas_x( $demas_lon ) ) ); ?>" x2="<?php echo esc_attr( $demas_n( $demas_x( $demas_lon ) ) ); ?>" />
					<?php endforeach; ?>
				</g>
			</g>

			<?php foreach ( array( 20, 25, 30 ) as $demas_lat ) : ?>
				<text class="dh-plan__tick" x="<?php echo esc_attr( $demas_n( $demas_x0 + 3 * $demas_u ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y( $demas_lat ) - 4 * $demas_u ) ); ?>"><?php echo (int) $demas_lat; ?>°N</text>
			<?php endforeach; ?>
			<?php foreach ( array( 40, 45, 50 ) as $demas_lon ) : ?>
				<text class="dh-plan__tick" x="<?php echo esc_attr( $demas_n( $demas_x( $demas_lon ) + 3 * $demas_u ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y1 - 4 * $demas_u ) ); ?>"><?php echo (int) $demas_lon; ?>°E</text>
			<?php endforeach; ?>

			<text class="dh-plan__sea" text-anchor="middle" transform="translate(<?php echo esc_attr( $demas_n( $demas_x( 38.15 ) ) . ' ' . $demas_n( $demas_y( 20.6 ) ) ); ?>) rotate(60)"><?php esc_html_e( 'Red Sea', 'demas-theme' ); ?></text>
			<text class="dh-plan__sea" text-anchor="middle" transform="translate(<?php echo esc_attr( $demas_n( $demas_x( 50.75 ) ) . ' ' . $demas_n( $demas_y( 27.55 ) ) ); ?>) rotate(35)"><?php esc_html_e( 'Arabian Gulf', 'demas-theme' ); ?></text>

			<g class="dh-plan__scale">
				<path d="<?php echo esc_attr( $demas_scale ); ?>" />
				<rect x="<?php echo esc_attr( $demas_n( $demas_bx ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 1.5 * $demas_u ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_bar / 2 ) ); ?>" height="<?php echo esc_attr( $demas_n( 3 * $demas_u ) ); ?>" />
				<text x="<?php echo esc_attr( $demas_n( $demas_bx ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 6 * $demas_u ) ); ?>">0</text>
				<text x="<?php echo esc_attr( $demas_n( $demas_bx + $demas_bar ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 6 * $demas_u ) ); ?>" text-anchor="end"><?php esc_html_e( '500 km', 'demas-theme' ); ?></text>
			</g>

			<g class="dh-plan__north">
				<path d="<?php echo esc_attr( $demas_arrow( 1 ) ); ?>" />
				<path class="dh-plan__north-half" d="<?php echo esc_attr( $demas_arrow( -1 ) ); ?>" />
				<text x="<?php echo esc_attr( $demas_n( $demas_ax ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_ay - 16 * $demas_u ) ); ?>" text-anchor="middle"><?php echo esc_html_x( 'N', 'north, on the map', 'demas-theme' ); ?></text>
			</g>

			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<?php
				$demas_cx   = $demas_x( (float) $demas_branch['lon'] );
				$demas_cy   = $demas_y( (float) $demas_branch['lat'] );
				$demas_east = $demas_cx > $demas_x0 + $demas_width * 0.75;
				?>
				<g class="dh-plan__site<?php echo empty( $demas_branch['main'] ) ? '' : ' is-main'; ?>" data-code="<?php echo esc_attr( sanitize_key( $demas_code ) ); ?>" style="--i:<?php echo (int) $demas_order[ $demas_code ]; ?>">
					<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
						<circle class="dh-plan__ring" cx="<?php echo esc_attr( $demas_cx ); ?>" cy="<?php echo esc_attr( $demas_cy ); ?>" r="<?php echo esc_attr( $demas_n( 8 * $demas_u ) ); ?>" />
					<?php endif; ?>
					<circle class="dh-plan__dot" cx="<?php echo esc_attr( $demas_cx ); ?>" cy="<?php echo esc_attr( $demas_cy ); ?>" r="<?php echo esc_attr( $demas_n( 3.4 * $demas_u ) ); ?>" />
					<text class="dh-plan__code" x="<?php echo esc_attr( $demas_n( $demas_east ? $demas_cx - 12 * $demas_u : $demas_cx + 12 * $demas_u ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_cy + 4.5 * $demas_u ) ); ?>" text-anchor="<?php echo $demas_east ? 'end' : 'start'; ?>"><?php echo esc_html( strtoupper( $demas_code ) ); ?></text>
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
