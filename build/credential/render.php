<?php
/**
 * Server-rendered markup for one Credential: a mark in the credentials row
 * and, when it has one, its line of detail as a tooltip. The mark takes
 * keyboard focus only when there is a tooltip to show, and the tooltip is
 * tied to it with aria-describedby, so a screen reader reads both.
 *
 * @param array    $attributes Block attributes: name, detail.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The name is edited as rich text with every format turned off: plain text,
// which may hold entities such as &amp;. The detail is a plain text field.
$demas_name   = trim( wp_strip_all_tags( (string) ( $attributes['name'] ?? '' ) ) );
$demas_detail = trim( wp_strip_all_tags( (string) ( $attributes['detail'] ?? '' ) ) );

if ( '' === $demas_name ) {
	return;
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-creds__item' ) );
$demas_tip_id  = '' === $demas_detail ? '' : wp_unique_id( 'dh-cred-' );
?>
<li <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal="dot">
	<?php if ( '' === $demas_tip_id ) : ?>
		<span class="dh-creds__mark"><?php echo wp_kses( $demas_name, array() ); ?></span>
	<?php else : ?>
		<span class="dh-creds__mark" tabindex="0" aria-describedby="<?php echo esc_attr( $demas_tip_id ); ?>"><?php echo wp_kses( $demas_name, array() ); ?></span>
		<span class="dh-creds__detail" id="<?php echo esc_attr( $demas_tip_id ); ?>" role="tooltip"><?php echo esc_html( $demas_detail ); ?></span>
	<?php endif; ?>
</li>
