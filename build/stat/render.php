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
 * @param array    $attributes Block attributes: value, prefix, label.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Value and label are rich text with every format turned off: plain text,
// which may hold entities. The prefix is a plain text field.
$demas_value  = trim( wp_strip_all_tags( (string) ( $attributes['value'] ?? '' ) ) );
$demas_label  = trim( wp_strip_all_tags( (string) ( $attributes['label'] ?? '' ) ) );
$demas_prefix = trim( wp_strip_all_tags( (string) ( $attributes['prefix'] ?? '' ) ) );

if ( '' === $demas_value ) {
	return;
}

$demas_count   = str_replace( ',', '', $demas_value );
$demas_count   = ctype_digit( $demas_count ) ? $demas_count : '';
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-stat' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<dt class="dh-stat__label"><?php echo wp_kses( $demas_label, array() ); ?></dt>
	<dd class="dh-stat__value"><?php if ( '' !== $demas_prefix ) : ?><span class="dh-stat__prefix"><?php echo esc_html( $demas_prefix ); ?></span><?php endif; ?><span<?php echo '' === $demas_count ? '' : ' data-count="' . esc_attr( $demas_count ) . '"'; ?>><?php echo wp_kses( $demas_value, array() ); ?></span></dd>
</div>
