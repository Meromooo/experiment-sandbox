<?php
/**
 * Server-rendered markup for the Pivot field drawing (AMM-177): the homepage
 * hero's background illustration (decorative, hidden from screen readers).
 * Centre-pivot fields seen from above, the round irrigated fields Saudi
 * farms are known for.
 *
 * Two SVGs in one wrapper (style.css section 9, Hero, places them):
 *  - the fields: a staggered grid of seven, as pivots are laid out on the
 *    ground. Each is planted in sectors at different stages, with its crop
 *    rows and the darker rings its towers' wheels leave; the rows and rings
 *    are one <g>, drawn once and placed with <use>. Some fields lie fallow.
 *  - the main field's arm: the span, a tower on each wheel track, the end
 *    gun's throw past the tip, the crop it has just wetted behind it, and
 *    the pivot at the centre. It sits in its own <span>, which turns once a
 *    minute: an HTML box, so the turn runs on the compositor (Chrome won't
 *    for an <svg>) and never repaints the fields. With reduced motion it is
 *    parked.
 *
 * A field's radius is 100 units. CSS sizes and places both SVGs from one
 * radius (--_r), so the drawing keeps its shape at every width. Lines don't
 * scale with it (vector-effect), so they stay hairlines.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// %F, not %f: a decimal point whatever the server's locale.
$demas_point = static function ( $angle, $radius ) {
	$rad = deg2rad( $angle );
	return sprintf( '%.1F %.1F', $radius * cos( $rad ), $radius * sin( $rad ) );
};

// A slice of a field, from the pivot, clockwise from $from to $to degrees.
$demas_sector = static function ( $from, $to, $radius = 100 ) use ( $demas_point ) {
	return sprintf( 'M0 0L%sA%d %d 0 %d 1 %sZ', $demas_point( $from, $radius ), $radius, $radius, $to - $from > 180 ? 1 : 0, $demas_point( $to, $radius ) );
};

$demas_rows_id = wp_unique_id( 'dh-pivot-rows-' );

// Crop rows, then the wheel tracks under the four towers, then the edge.
$demas_rows = '';
for ( $demas_r = 97; $demas_r > 6; $demas_r -= 3.6 ) {
	$demas_rows .= sprintf( '<circle r="%.1F" stroke-opacity=".045" stroke-width=".7" vector-effect="non-scaling-stroke"/>', $demas_r );
}
for ( $demas_k = 1; $demas_k <= 4; $demas_k++ ) {
	$demas_rows .= sprintf( '<circle r="%.1F" stroke-opacity=".24" stroke-width="1.1" vector-effect="non-scaling-stroke"/>', 100 * $demas_k / 4.4 );
}
$demas_rows .= '<circle r="100" stroke-opacity=".34" stroke-width="1.2" vector-effect="non-scaling-stroke"/>';

// Centre to centre: two radii and a strip of desert. Each field: where it
// sits (in centre-to-centre steps) and its planted sectors (from, to, how green).
$demas_step   = 208;
$demas_fields = array(
	array( -1, 0, array() ),
	array( -0.5, -0.866, array( array( 180, 360, 0.14 ) ) ),
	array( 0.5, -0.866, array() ),
	array( -0.5, 0.866, array( array( 90, 300, 0.12 ) ) ),
	array( 0.5, 0.866, array() ),
	array( 1, 0, array( array( 0, 180, 0.1 ) ) ),
	array( 0, 0, array( array( 0, 140, 0.17 ), array( 140, 235, 0.07 ), array( 235, 360, 0.13 ) ) ),
);

$demas_svg  = '<svg class="dh-pivot__fields" viewBox="-320 -320 640 640" focusable="false">';
$demas_svg .= '<defs><g id="' . esc_attr( $demas_rows_id ) . '" fill="none" stroke="currentColor">' . $demas_rows . '</g></defs>';
foreach ( $demas_fields as $demas_field ) {
	$demas_svg .= sprintf( '<g transform="translate(%.1F %.1F)">', $demas_field[0] * $demas_step, $demas_field[1] * $demas_step );
	foreach ( $demas_field[2] as $demas_crop ) {
		$demas_svg .= sprintf( '<path class="dh-pivot__crop" d="%s" fill-opacity="%.2F"/>', $demas_sector( $demas_crop[0], $demas_crop[1] ), $demas_crop[2] );
	}
	$demas_svg .= '<use href="#' . esc_attr( $demas_rows_id ) . '"/></g>';
}
$demas_svg .= '</svg>';

// The arm points along 0 degrees and turns clockwise: the wet crop trails it.
$demas_arm = '<span class="dh-pivot__arm"><svg viewBox="-120 -120 240 240" focusable="false">';
for ( $demas_i = 0; $demas_i < 14; $demas_i++ ) {
	$demas_t    = ( $demas_i + 1 ) / 14;
	$demas_arm .= sprintf( '<path class="dh-pivot__wake" d="%s" fill-opacity="%.3F"/>', $demas_sector( -70 + $demas_i * 5, -64.6 + $demas_i * 5 ), 0.13 * $demas_t * $demas_t );
}
$demas_arm .= sprintf( '<path class="dh-pivot__wake" d="%s" fill-opacity=".22"/>', $demas_sector( 0, 2.2 ) );
$demas_arm .= '<path class="dh-pivot__span" d="M0 0H100"/>';
$demas_arm .= '<path class="dh-pivot__towers" d="M22.7 -2.4V2.4M45.5 -2.4V2.4M68.2 -2.4V2.4M90.9 -2.4V2.4"/>';
for ( $demas_j = 0; $demas_j < 5; $demas_j++ ) {
	$demas_arm .= sprintf( '<circle class="dh-pivot__drop" cx="%.1F" cy="%.2F" r="%.2F" fill-opacity="%.2F"/>', 101.8 + $demas_j * 2, 0.6 + $demas_j * 0.54, 0.77 - $demas_j * 0.09, 0.7 - $demas_j * 0.12 );
}
$demas_arm .= '<circle class="dh-pivot__hub" r="2.1"/><circle class="dh-pivot__ring" r="5.4"/></svg></span>';

$demas_wrapper = get_block_wrapper_attributes(
	array(
		'class'       => 'dh-pivot',
		'aria-hidden' => 'true',
	)
);
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>><?php echo $demas_svg . $demas_arm; // phpcs:ignore WordPress.Security.EscapeOutput -- built in this file from numbers and an escaped id. ?></div>
