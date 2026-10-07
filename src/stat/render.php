<?php
/**
 * Server-rendered markup for one Number: what it counts (the term) and the
 * number (the definition). The number is shown first by CSS (order in
 * style.css, "Bands"), so the list reads correctly as well as looking right.
 *
 * A whole number counts up from zero as it scrolls into view: it carries
 * data-count, which main.js reads. Anything else — "24/7", "1.5" — is shown
 * as written. Without JavaScript, or with reduced motion, the final number
 * is simply there.
 *
 * Two optional extras (AMM-184), both hidden from screen readers, which hear
 * the term and the number:
 *  - a unit word beside the number ("Years"). Where it is set, the homepage
 *    band shows it instead of the term, which stays in the list, visually
 *    hidden (style.css, "Bands").
 *  - a drawing under a whole number: a drafting scale with a tick a year
 *    ("scale", from the year given as since to today), or the branch network
 *    ("network": head office as the pump, a sprinkler head on a lateral for
 *    each other branch). main.js marks the number's stat .is-counted as its
 *    count starts, and CSS runs the drawing in step with the count; each head
 *    carries the moment the count reaches it as --d. Without JavaScript, or
 *    with reduced motion, it is drawn finished.
 *
 * @param array    $attributes Block attributes: value, prefix, label, unit, drawing, since.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Value and label are rich text with every format turned off: plain text,
// which may hold entities. The prefix and unit are plain text fields.
$demas_value  = trim( wp_strip_all_tags( (string) ( $attributes['value'] ?? '' ) ) );
$demas_label  = trim( wp_strip_all_tags( (string) ( $attributes['label'] ?? '' ) ) );
$demas_prefix = trim( wp_strip_all_tags( (string) ( $attributes['prefix'] ?? '' ) ) );
$demas_unit   = trim( wp_strip_all_tags( (string) ( $attributes['unit'] ?? '' ) ) );
$demas_since  = trim( (string) ( $attributes['since'] ?? '' ) );

if ( '' === $demas_value ) {
	return;
}

$demas_count = str_replace( ',', '', $demas_value );
$demas_count = ctype_digit( $demas_count ) ? $demas_count : '';
$demas_since = ( ctype_digit( $demas_since ) && strlen( $demas_since ) <= 4 ) ? (int) $demas_since : 0;

// The drawing needs a whole number to draw, and a sensible one: a tick a year,
// a head a branch.
$demas_n       = '' === $demas_count ? 0 : (int) $demas_count;
$demas_drawing = (string) ( $attributes['drawing'] ?? '' );
$demas_svg     = '';

if ( 'scale' === $demas_drawing && $demas_n >= 1 && $demas_n <= 200 ) {
	$demas_svg = '<line class="dh-stat__rail" x1="1.5" y1="18" x2="358.5" y2="18"/>';
	for ( $demas_i = 0; $demas_i <= $demas_n; $demas_i++ ) {
		$demas_major = 0 === $demas_i || $demas_n === $demas_i || 0 === ( $demas_since + $demas_i ) % 10;
		$demas_svg  .= sprintf(
			'<line class="dh-scale__tick%1$s" x1="%2$.1F" y1="%3$d" x2="%2$.1F" y2="16"/>',
			$demas_major ? ' dh-scale__tick--major' : '',
			1.5 + $demas_i * 357 / $demas_n,
			$demas_major ? 7 : 12
		);
	}
	$demas_svg .= '<line class="dh-stat__run" x1="1.5" y1="18" x2="358.5" y2="18"/><circle class="dh-scale__now" cx="358.5" cy="18" r="4.5"/>';
	$demas_svg .= ( $demas_since ? '<text x="0" y="36">' . $demas_since . '</text>' : '' ) . '<text x="360" y="36" text-anchor="end">' . esc_html__( 'Today', 'demas-theme' ) . '</text>';
	$demas_svg  = '<svg class="dh-stat__draw dh-stat__draw--scale" viewBox="0 0 360 40" aria-hidden="true" focusable="false">' . $demas_svg . '</svg>';
} elseif ( 'network' === $demas_drawing && $demas_n >= 2 && $demas_n <= 60 ) {
	// The pump is head office, named from the branch list; every other branch
	// is a head, on laterals that alternate up and down the mainline.
	$demas_hq = '';
	if ( function_exists( 'demas_theme_get_branches' ) ) {
		foreach ( demas_theme_get_branches() as $demas_branch ) {
			if ( ! empty( $demas_branch['main'] ) ) {
				$demas_hq = (string) $demas_branch['city'];
				break;
			}
		}
	}

	$demas_svg   = '<line class="dh-stat__rail" x1="10" y1="30" x2="354" y2="30"/><line class="dh-stat__run dh-net__run" x1="10" y1="30" x2="354" y2="30"/>';
	$demas_heads = $demas_n - 1;
	for ( $demas_k = 1; $demas_k <= $demas_heads; $demas_k++ ) {
		$demas_x  = $demas_heads > 1 ? 34 + ( $demas_k - 1 ) * 320 / ( $demas_heads - 1 ) : 354;
		$demas_up = 1 === $demas_k % 2;
		$demas_y  = $demas_up ? 15 : 45;
		$demas_s  = $demas_up ? -1 : 1;

		// When the count reaches this head: main.js counts for 900ms, easing out
		// (1 - (1 - t)^3), so a share p of the line is reached at 1 - cbrt(1 - p).
		$demas_d    = (int) round( ( 1 - pow( 1 - min( 0.999, ( $demas_x - 10 ) / 344 ), 1 / 3 ) ) * 900 );
		$demas_svg .= sprintf(
			'<g class="dh-net__branch" style="--d:%1$dms"><line class="dh-net__lateral" x1="%2$.1F" y1="30" x2="%2$.1F" y2="%3$d"/><path class="dh-net__spray%4$s" d="M%5$.1F %6$d Q%2$.1F %7$d %8$.1F %6$d M%9$.1F %10$d Q%2$.1F %11$d %12$.1F %10$d"/><circle class="dh-net__head" cx="%2$.1F" cy="%3$d" r="3.8"/></g>',
			$demas_d,
			$demas_x,
			$demas_y,
			$demas_up ? '' : ' dh-net__spray--down',
			$demas_x - 8,
			$demas_y + 3 * $demas_s,
			$demas_y + 15 * $demas_s,
			$demas_x + 8,
			$demas_x - 4.5,
			$demas_y + 5 * $demas_s,
			$demas_y + 11 * $demas_s,
			$demas_x + 4.5
		);
	}
	$demas_svg .= '<circle class="dh-net__ring" cx="10" cy="30" r="10"/><circle class="dh-net__pump" cx="10" cy="30" r="6"/>';
	$demas_svg .= '' === $demas_hq ? '' : '<text x="0" y="64">' . esc_html( $demas_hq ) . '</text>';
	$demas_svg  = '<svg class="dh-stat__draw dh-stat__draw--network" viewBox="0 0 360 68" aria-hidden="true" focusable="false">' . $demas_svg . '</svg>';
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-stat' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<dt class="dh-stat__label"><?php echo wp_kses( $demas_label, array() ); ?></dt>
	<dd class="dh-stat__value"><span class="dh-stat__figure"><?php echo '' === $demas_prefix ? '' : '<span class="dh-stat__prefix">' . esc_html( $demas_prefix ) . '</span>'; ?><span<?php echo '' === $demas_count ? '' : ' data-count="' . esc_attr( $demas_count ) . '"'; ?>><?php echo wp_kses( $demas_value, array() ); ?></span><?php echo '' === $demas_unit ? '' : '<span class="dh-stat__unit" aria-hidden="true">' . esc_html( $demas_unit ) . '</span>'; ?></span><?php echo $demas_svg; // phpcs:ignore WordPress.Security.EscapeOutput -- built in this file from numbers and escaped text. ?></dd>
</div>
