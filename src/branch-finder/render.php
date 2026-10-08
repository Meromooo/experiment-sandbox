<?php
/**
 * Server-rendered markup for the Branch Finder (AMM-169): the Contact page's
 * fifteen branches as a list, a map, and a card for the chosen one.
 *
 * The map (AMM-189) is a store locator with service areas: the Kingdom on
 * its real outline, divided into one area per branch, each area the part of
 * the Kingdom nearer to that branch than to any other (a Voronoi diagram),
 * so a visitor finds their branch by finding where they are. The land is the
 * footer key plan's (inc/key-plan-land.php, Natural Earth, in the projection
 * of demas_theme_branch_plan_point()), drawn the same way on the dark green:
 * water-lines along the coasts, the neighbours a shade off the background,
 * a north arrow and a 500 km scale bar. The chosen branch's area is filled
 * and its dot ringed and named; pointing at an area, a dot or a city in the
 * list lights that area and names it. The map is a pointer-only twin of the
 * list, hidden from assistive technology like the footer plan: the list is
 * the control.
 *
 * Every city is a link to its card (#branch-jed), and so is every area and
 * dot on the map. That is the whole mechanism without JavaScript — the card a
 * link targets is the one shown (:target), the default branch's otherwise —
 * so the page works, and deep links work, before or without view.ts. With
 * it, the card changes in place and the URL follows.
 *
 * The default branch is the main one, or the one named by ?branch= (the
 * homepage Branch Desk and the cards' "Send a request" link carry it).
 *
 * Each card: a photo (chosen per branch in the block's sidebar, the drawn
 * placeholder until then), who answers there, the address, the opening
 * hours with an "Open now" status the browser works out in Riyadh time, and
 * directions — the branch's Google Maps link. Branch phone numbers are not
 * shown by decision (head office's is, where hours are not listed); staff
 * email addresses never appear anywhere.
 *
 * @param array    $attributes Block attributes: photos (branch code → attachment ID).
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_branches' ) || ! function_exists( 'demas_theme_branch_plan_point' ) || ! function_exists( 'demas_theme_get_key_plan_land' ) ) {
	return;
}

$demas_branches = demas_theme_get_branches();

if ( ! $demas_branches ) {
	return;
}

$demas_main = (string) key( $demas_branches );
$demas_list = wp_list_filter( $demas_branches, array( 'main' => true ) );

if ( $demas_list ) {
	$demas_main = (string) key( $demas_list );
}

$demas_asked  = isset( $_GET['branch'] ) ? sanitize_key( wp_unslash( $_GET['branch'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display parameter.
$demas_chosen = isset( $demas_branches[ $demas_asked ] ) ? $demas_asked : $demas_main;
$demas_photos = (array) ( $attributes['photos'] ?? array() );

/*
 * The map's frame and land are the footer key plan's. Sizes meant in pixels
 * are multiplied by $demas_u, the plan units in a pixel where the map is
 * widest (598px, beside the intro at 1440); CSS gets the same number as --_u.
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
$demas_u      = $demas_width / 598;

$demas_x = static function ( float $lon ): float {
	return demas_theme_branch_plan_point( 32, $lon )[0];
};
$demas_y = static function ( float $lat ): float {
	return demas_theme_branch_plan_point( $lat, 36 )[1];
};
$demas_n = static function ( float $value ): string {
	return number_format( $value, 1, '.', '' );
};

// Ids for the land, the Kingdom's clip, the edge fades and the frame.
$demas_id = wp_unique_id( 'dh-ct-map-' );

$demas_points = array();
foreach ( $demas_branches as $demas_code => $demas_branch ) {
	$demas_points[ $demas_code ] = demas_theme_branch_plan_point( (float) $demas_branch['lat'], (float) $demas_branch['lon'] );
}

/*
 * Each branch's area: start from the frame and, for every other branch, keep
 * only the half nearer to this one — the side of the line halfway between
 * the two where a x + b y <= c. What is left is convex, so cutting it is one
 * pass round its corners. The Kingdom's outline clips the areas when drawn.
 */
$demas_keep = static function ( array $poly, float $a, float $b, float $c ): array {
	$kept = array();
	$last = end( $poly );
	foreach ( $poly as $point ) {
		$in      = $a * $point[0] + $b * $point[1] - $c;
		$was     = $a * $last[0] + $b * $last[1] - $c;
		$crosses = ( $in <= 0 ) !== ( $was <= 0 );
		if ( $crosses ) {
			$t      = $was / ( $was - $in );
			$kept[] = array( $last[0] + ( $point[0] - $last[0] ) * $t, $last[1] + ( $point[1] - $last[1] ) * $t );
		}
		if ( $in <= 0 ) {
			$kept[] = $point;
		}
		$last = $point;
	}
	return $kept;
};

$demas_zones = array();
foreach ( $demas_points as $demas_code => $demas_site ) {
	$demas_poly = array( array( $demas_x0, $demas_y0 ), array( $demas_x1, $demas_y0 ), array( $demas_x1, $demas_y1 ), array( $demas_x0, $demas_y1 ) );
	foreach ( $demas_points as $demas_other => $demas_near ) {
		if ( $demas_other !== $demas_code ) {
			$demas_poly = $demas_keep(
				$demas_poly,
				$demas_near[0] - $demas_site[0],
				$demas_near[1] - $demas_site[1],
				( $demas_near[0] ** 2 + $demas_near[1] ** 2 - $demas_site[0] ** 2 - $demas_site[1] ** 2 ) / 2
			);
		}
	}
	$demas_zones[ $demas_code ] = 'M' . implode( 'L', array_map( static fn( $p ) => $demas_n( $p[0] ) . ' ' . $demas_n( $p[1] ), $demas_poly ) ) . 'Z';
}

// The areas and dots come on outward from the main branch: nearest first.
$demas_hub  = $demas_points[ $demas_main ];
$demas_dist = array();
foreach ( $demas_points as $demas_code => $demas_point ) {
	$demas_dist[ $demas_code ] = hypot( $demas_point[0] - $demas_hub[0], $demas_point[1] - $demas_hub[1] );
}
asort( $demas_dist );
$demas_order = array_flip( array_keys( $demas_dist ) );

/*
 * Where a branch's code and name sit beside its dot: to the right, unless
 * placed here so none collide: Buraidah and Unaizah are 28 km apart, and
 * the Gulf's name is to Dammam's right, Taif to Jeddah's. A new branch
 * starts on the right.
 */
$demas_sides = array(
	'dam' => 'left',
	'jed' => 'left',
	'bur' => 'above',
	'una' => 'below',
	'saj' => 'below',
	'alk' => 'below',
);

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

// The water-lines, as on the key plan: the band's number and its distance
// from the coast, in plan units.
$demas_waves = array(
	1 => 14,
	2 => 8.5,
	3 => 4,
);

// Where a point sits in the drawing, as a percentage, for the HTML names.
$demas_pct = static function ( float $value, float $origin, float $size ): string {
	return round( ( $value - $origin ) / $size * 100, 2 ) . '%';
};

// Hovering a city in the list, or an area or dot on the map, lights that area and names it.
$demas_css = '';
foreach ( array_keys( $demas_branches ) as $demas_code ) {
	$demas_code = sanitize_key( $demas_code );
	$demas_css .= sprintf(
		'.dh-ct-finder:has([data-branch-link="%1$s"]:is(:hover,:focus-visible),.dh-ct-plan__head[data-code="%1$s"]:hover) :is(.dh-ct-plan__head,.dh-ct-plan__label)[data-code="%1$s"]{--_on:1}',
		$demas_code
	);
}
wp_add_inline_style( generate_block_asset_handle( 'demas-theme/branch-finder', 'style' ), $demas_css );

$demas_strings = array(
	/* translators: %s: closing time, e.g. 13:00 */
	'open'     => __( 'Open now · closes %s', 'demas-theme' ),
	/* translators: %s: opening time later today, e.g. 16:00 */
	'later'    => __( 'Closed now · opens %s', 'demas-theme' ),
	/* translators: 1: short weekday, 2: opening time, e.g. Sat 8:00 */
	'day'      => __( 'Closed now · opens %1$s %2$s', 'demas-theme' ),
	'days'     => array(
		_x( 'Sun', 'short weekday', 'demas-theme' ),
		_x( 'Mon', 'short weekday', 'demas-theme' ),
		_x( 'Tue', 'short weekday', 'demas-theme' ),
		_x( 'Wed', 'short weekday', 'demas-theme' ),
		_x( 'Thu', 'short weekday', 'demas-theme' ),
		_x( 'Fri', 'short weekday', 'demas-theme' ),
		_x( 'Sat', 'short weekday', 'demas-theme' ),
	),
	/* translators: %s: city */
	'announce' => __( '%s branch', 'demas-theme' ),
);

$demas_pick_id = wp_unique_id( 'dh-ct-pick-' );
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-ct-finder' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-branch-finder data-default="<?php echo esc_attr( $demas_chosen ); ?>" data-strings="<?php echo esc_attr( wp_json_encode( $demas_strings ) ); ?>">
	<nav class="dh-ct-pick" aria-labelledby="<?php echo esc_attr( $demas_pick_id ); ?>" data-reveal="rise">
		<p class="dh-ct-pick__label" id="<?php echo esc_attr( $demas_pick_id ); ?>">
			<?php esc_html_e( 'Pick a branch', 'demas-theme' ); ?>
		</p>
		<ul class="dh-ct-pick__list">
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<li>
					<a class="dh-ct-city" href="#branch-<?php echo esc_attr( $demas_code ); ?>" data-branch-link="<?php echo esc_attr( $demas_code ); ?>"<?php echo $demas_chosen === $demas_code ? ' aria-current="true"' : ''; ?>>
						<span class="dh-ct-city__code" aria-hidden="true"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span>
						<span class="dh-ct-city__name"><?php echo esc_html( $demas_branch['city'] ); ?></span>
						<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
							<span class="dh-ct-city__tag"><?php esc_html_e( 'Main', 'demas-theme' ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="dh-ct-stage">
		<figure class="dh-ct-plan dh-card dh-card--canopy" data-reveal="seed" aria-hidden="true">
			<div class="dh-ct-plan__draw">
				<svg class="dh-ct-plan__svg" xmlns="http://www.w3.org/2000/svg" viewBox="<?php echo esc_attr( $demas_n( $demas_x0 ) . ' ' . $demas_n( $demas_y0 ) . ' ' . $demas_n( $demas_width ) . ' ' . $demas_n( $demas_height ) ); ?>" style="--_u:<?php echo esc_attr( number_format( $demas_u, 3, '.', '' ) ); ?>" focusable="false">
					<defs>
						<path id="<?php echo esc_attr( $demas_id ); ?>-kingdom" d="<?php echo esc_attr( $demas_land['kingdom'] ); ?>" />
						<path id="<?php echo esc_attr( $demas_id ); ?>-neighbours" d="<?php echo esc_attr( $demas_land['neighbours'] ); ?>" />
						<clipPath id="<?php echo esc_attr( $demas_id ); ?>-inside">
							<use href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" />
						</clipPath>
						<clipPath id="<?php echo esc_attr( $demas_id ); ?>-frame">
							<rect x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>" />
						</clipPath>
						<linearGradient id="<?php echo esc_attr( $demas_id ); ?>-fx" gradientUnits="userSpaceOnUse" x1="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" x2="<?php echo esc_attr( $demas_n( $demas_x1 ) ); ?>" y1="0" y2="0">
							<stop offset="0" stop-color="#000" /><stop offset=".08" stop-color="#fff" /><stop offset=".92" stop-color="#fff" /><stop offset="1" stop-color="#000" />
						</linearGradient>
						<linearGradient id="<?php echo esc_attr( $demas_id ); ?>-fy" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" y2="<?php echo esc_attr( $demas_n( $demas_y1 ) ); ?>">
							<stop offset="0" stop-color="#000" /><stop offset=".08" stop-color="#fff" /><stop offset=".92" stop-color="#fff" /><stop offset="1" stop-color="#000" />
						</linearGradient>
						<?php foreach ( array( 'x', 'y' ) as $demas_axis ) : ?>
							<mask id="<?php echo esc_attr( $demas_id . '-m' . $demas_axis ); ?>" maskUnits="userSpaceOnUse" x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>">
								<rect x="<?php echo esc_attr( $demas_n( $demas_x0 ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_y0 ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_width ) ); ?>" height="<?php echo esc_attr( $demas_n( $demas_height ) ); ?>" fill="url(#<?php echo esc_attr( $demas_id . '-f' . $demas_axis ); ?>)" />
							</mask>
						<?php endforeach; ?>
					</defs>

					<g clip-path="url(#<?php echo esc_attr( $demas_id ); ?>-frame)">
						<g mask="url(#<?php echo esc_attr( $demas_id ); ?>-mx)">
							<g mask="url(#<?php echo esc_attr( $demas_id ); ?>-my)">
								<?php foreach ( $demas_waves as $demas_wave => $demas_far ) : ?>
									<g class="dh-ct-plan__wave dh-ct-plan__wave--<?php echo (int) $demas_wave; ?>" stroke-width="<?php echo esc_attr( $demas_n( 2 * $demas_far + 1.15 * $demas_u ) ); ?>"><use href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" /><use href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" /></g>
									<g class="dh-ct-plan__wave-gap" stroke-width="<?php echo esc_attr( $demas_n( 2 * $demas_far - 1.15 * $demas_u ) ); ?>"><use href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" /><use href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" /></g>
								<?php endforeach; ?>
								<use class="dh-ct-plan__neighbours" href="#<?php echo esc_attr( $demas_id ); ?>-neighbours" />
							</g>
						</g>
						<use class="dh-ct-plan__kingdom" href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" />

						<g class="dh-ct-plan__zones" clip-path="url(#<?php echo esc_attr( $demas_id ); ?>-inside)">
							<?php foreach ( $demas_zones as $demas_code => $demas_zone ) : ?>
								<a class="dh-ct-plan__head dh-ct-plan__zone<?php echo $demas_chosen === $demas_code ? ' is-current' : ''; ?>" href="#branch-<?php echo esc_attr( $demas_code ); ?>" tabindex="-1" data-code="<?php echo esc_attr( $demas_code ); ?>" style="--i:<?php echo (int) $demas_order[ $demas_code ]; ?>"><path d="<?php echo esc_attr( $demas_zone ); ?>" /></a>
							<?php endforeach; ?>
						</g>
						<g class="dh-ct-plan__borders" clip-path="url(#<?php echo esc_attr( $demas_id ); ?>-inside)">
							<?php foreach ( $demas_zones as $demas_zone ) : ?>
								<path d="<?php echo esc_attr( $demas_zone ); ?>" />
							<?php endforeach; ?>
						</g>
						<use class="dh-ct-plan__outline" href="#<?php echo esc_attr( $demas_id ); ?>-kingdom" />
					</g>

					<text class="dh-ct-plan__sea" text-anchor="middle" transform="translate(<?php echo esc_attr( $demas_n( $demas_x( 38.15 ) ) . ' ' . $demas_n( $demas_y( 20.6 ) ) ); ?>) rotate(60)"><?php esc_html_e( 'Red Sea', 'demas-theme' ); ?></text>
					<text class="dh-ct-plan__sea" text-anchor="middle" transform="translate(<?php echo esc_attr( $demas_n( $demas_x( 50.75 ) ) . ' ' . $demas_n( $demas_y( 27.55 ) ) ); ?>) rotate(35)"><?php esc_html_e( 'Arabian Gulf', 'demas-theme' ); ?></text>

					<g class="dh-ct-plan__scale">
						<path d="<?php echo esc_attr( $demas_scale ); ?>" />
						<rect x="<?php echo esc_attr( $demas_n( $demas_bx ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 1.5 * $demas_u ) ); ?>" width="<?php echo esc_attr( $demas_n( $demas_bar / 2 ) ); ?>" height="<?php echo esc_attr( $demas_n( 3 * $demas_u ) ); ?>" />
						<text x="<?php echo esc_attr( $demas_n( $demas_bx ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 6 * $demas_u ) ); ?>">0</text>
						<text x="<?php echo esc_attr( $demas_n( $demas_bx + $demas_bar ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_by - 6 * $demas_u ) ); ?>" text-anchor="end"><?php esc_html_e( '500 km', 'demas-theme' ); ?></text>
					</g>

					<g class="dh-ct-plan__north">
						<path d="<?php echo esc_attr( $demas_arrow( 1 ) ); ?>" />
						<path class="dh-ct-plan__north-half" d="<?php echo esc_attr( $demas_arrow( -1 ) ); ?>" />
						<text x="<?php echo esc_attr( $demas_n( $demas_ax ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_ay - 16 * $demas_u ) ); ?>" text-anchor="middle"><?php echo esc_html_x( 'N', 'north, on the map', 'demas-theme' ); ?></text>
					</g>

					<g class="dh-ct-plan__sites">
						<?php foreach ( $demas_points as $demas_code => $demas_point ) : ?>
							<?php
							$demas_side   = $demas_sides[ $demas_code ] ?? 'right';
							$demas_tx     = $demas_point[0];
							$demas_ty     = $demas_point[1] + 3.6 * $demas_u;
							$demas_anchor = 'middle';
							if ( 'above' === $demas_side || 'below' === $demas_side ) {
								$demas_ty = $demas_point[1] + ( 'above' === $demas_side ? -7 : 14 ) * $demas_u;
							} else {
								$demas_tx     = $demas_point[0] + ( 'right' === $demas_side ? 7 : -7 ) * $demas_u;
								$demas_anchor = 'right' === $demas_side ? 'start' : 'end';
							}
							?>
							<a class="dh-ct-plan__head dh-ct-plan__site<?php echo $demas_chosen === $demas_code ? ' is-current' : ''; ?>" href="#branch-<?php echo esc_attr( $demas_code ); ?>" tabindex="-1" data-code="<?php echo esc_attr( $demas_code ); ?>" style="--i:<?php echo (int) $demas_order[ $demas_code ]; ?>">
								<circle class="dh-ct-plan__ring" cx="<?php echo esc_attr( $demas_n( $demas_point[0] ) ); ?>" cy="<?php echo esc_attr( $demas_n( $demas_point[1] ) ); ?>" r="<?php echo esc_attr( $demas_n( 8 * $demas_u ) ); ?>" />
								<circle class="dh-ct-plan__dot" cx="<?php echo esc_attr( $demas_n( $demas_point[0] ) ); ?>" cy="<?php echo esc_attr( $demas_n( $demas_point[1] ) ); ?>" r="<?php echo esc_attr( $demas_n( 3.4 * $demas_u ) ); ?>" />
								<text x="<?php echo esc_attr( $demas_n( $demas_tx ) ); ?>" y="<?php echo esc_attr( $demas_n( $demas_ty ) ); ?>" text-anchor="<?php echo esc_attr( $demas_anchor ); ?>"><?php echo esc_html( strtoupper( $demas_code ) ); ?></text>
							</a>
						<?php endforeach; ?>
					</g>
				</svg>

				<div class="dh-ct-plan__labels">
					<?php foreach ( $demas_points as $demas_code => $demas_point ) : ?>
						<?php $demas_side = $demas_sides[ $demas_code ] ?? 'right'; ?>
						<span class="dh-ct-plan__label is-<?php echo esc_attr( $demas_side ); ?><?php echo $demas_chosen === $demas_code ? ' is-current' : ''; ?>" data-code="<?php echo esc_attr( $demas_code ); ?>" style="--x:<?php echo esc_attr( $demas_pct( $demas_point[0], $demas_x0, $demas_width ) ); ?>;--y:<?php echo esc_attr( $demas_pct( $demas_point[1], $demas_y0, $demas_height ) ); ?>"><b><?php echo esc_html( strtoupper( $demas_code ) ); ?></b> <?php echo esc_html( $demas_branches[ $demas_code ]['city'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>

			<figcaption class="dh-ct-plan__caption"><?php esc_html_e( 'Each area is closest to the branch inside it.', 'demas-theme' ); ?></figcaption>
		</figure>

		<div class="dh-ct-cards" data-reveal="rise">
			<p class="screen-reader-text" aria-live="polite" data-announce></p>
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<?php
				$demas_hours    = demas_theme_branch_hours( (string) ( $demas_branch['hours'] ?? '' ) );
				$demas_photo_id = absint( $demas_photos[ $demas_code ] ?? 0 );
				$demas_photo    = $demas_photo_id ? wp_get_attachment_image(
					$demas_photo_id,
					'large',
					false,
					array(
						'class'   => 'dh-ct-card__img',
						'loading' => 'lazy',
						'sizes'   => '(min-width: 56rem) 34rem, 100vw',
					)
				) : '';
				$demas_classes  = 'dh-ct-card dh-card dh-card--sand' . ( $demas_chosen === $demas_code ? ' is-default' : '' );
				$demas_title_id = 'dh-ct-city-' . $demas_code;
				?>
				<article class="<?php echo esc_attr( $demas_classes ); ?>" id="branch-<?php echo esc_attr( $demas_code ); ?>" data-code="<?php echo esc_attr( $demas_code ); ?>" aria-labelledby="<?php echo esc_attr( $demas_title_id ); ?>"<?php echo $demas_hours['week'] ? ' data-hours="' . esc_attr( wp_json_encode( (object) $demas_hours['week'] ) ) . '"' : ''; ?>>
					<div class="dh-ct-card__photo">
						<?php if ( $demas_photo ) : ?>
							<?php echo $demas_photo; // phpcs:ignore WordPress.Security.EscapeOutput -- core image markup. ?>
						<?php else : ?>
							<div class="dh-svc-placeholder dh-ct-card__placeholder">
								<p class="dh-svc-placeholder__chip">
									<span class="dh-svc-placeholder__label"><?php esc_html_e( 'Photo to come', 'demas-theme' ); ?></span>
									<?php
									/* translators: %s: city */
									printf( esc_html__( 'The %s branch', 'demas-theme' ), esc_html( $demas_branch['city'] ) );
									?>
								</p>
							</div>
						<?php endif; ?>
					</div>

					<div class="dh-ct-card__body">
						<p class="dh-ct-card__meta">
							<span class="dh-mono"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span>
							<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
								<?php esc_html_e( 'Main branch, head office', 'demas-theme' ); ?>
							<?php endif; ?>
						</p>
						<h2 class="dh-ct-card__city" id="<?php echo esc_attr( $demas_title_id ); ?>"><?php echo esc_html( $demas_branch['city'] ); ?></h2>

						<dl class="dh-ct-card__facts">
							<?php if ( ! empty( $demas_branch['person'] ) ) : ?>
								<div class="dh-ct-card__fact">
									<dt><?php esc_html_e( 'Ask for', 'demas-theme' ); ?></dt>
									<dd class="dh-ct-card__person"><?php echo esc_html( $demas_branch['person'] ); ?></dd>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $demas_branch['address'] ) ) : ?>
								<div class="dh-ct-card__fact">
									<dt><?php esc_html_e( 'Address', 'demas-theme' ); ?></dt>
									<dd><?php echo esc_html( $demas_branch['address'] ); ?></dd>
								</div>
							<?php endif; ?>
							<div class="dh-ct-card__fact">
								<dt><?php esc_html_e( 'Hours', 'demas-theme' ); ?></dt>
								<dd>
									<?php if ( $demas_hours['lines'] ) : ?>
										<ul class="dh-ct-hours">
											<?php foreach ( $demas_hours['lines'] as $demas_line ) : ?>
												<li>
													<span class="dh-ct-hours__days"><?php echo esc_html( $demas_line['days'] ); ?></span>
													<span class="dh-ct-hours__times">
														<?php if ( $demas_line['times'] ) : ?>
															<?php
															foreach ( $demas_line['times'] as $demas_t => $demas_range ) {
																echo ( $demas_t ? ', ' : '' ) . '<span class="dh-ct-hours__range">' . esc_html( $demas_range ) . '</span>';
															}
															?>
														<?php else : ?>
															<?php esc_html_e( 'Closed', 'demas-theme' ); ?>
														<?php endif; ?>
													</span>
												</li>
											<?php endforeach; ?>
										</ul>
										<p class="dh-ct-card__status" data-status hidden></p>
									<?php else : ?>
										<?php esc_html_e( 'Not listed yet. Head office can tell you:', 'demas-theme' ); ?>
										<a href="tel:+966114634102">011 463 4102</a>
									<?php endif; ?>
								</dd>
							</div>
						</dl>

						<div class="dh-ct-card__actions">
							<a class="dh-pill dh-pill--solid" href="<?php echo esc_url( '?branch=' . $demas_code . '#request' ); ?>" data-request="<?php echo esc_attr( $demas_code ); ?>">
								<?php
								/* translators: %s: city */
								printf( esc_html__( 'Send a request to %s', 'demas-theme' ), esc_html( $demas_branch['city'] ) );
								?>
							</a>
							<?php if ( ! empty( $demas_branch['map'] ) ) : ?>
								<a class="dh-pill dh-pill--outline" href="<?php echo esc_url( $demas_branch['map'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Get directions', 'demas-theme' ); ?>
									<span class="screen-reader-text"><?php esc_html_e( '(Google Maps, opens in a new tab)', 'demas-theme' ); ?></span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8.5 7H17v8.5"/></svg>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</div>
