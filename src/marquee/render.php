<?php
/**
 * Server-rendered markup for the Marquee: a line of words scrolling across
 * the page (style.css section 6). main.js copies the words once, hidden from
 * screen readers, so the loop has no seam.
 *
 * The words are either the branch cities, from demas_theme_get_branches()
 * (inc/branches.php) in its order, or the editor's own list.
 *
 * The marquee is a named region that takes keyboard focus: focus pauses the
 * moving line, as the pointer does (WCAG 2.2.2), and with reduced motion,
 * where the line stands still and scrolls sideways instead, the arrow keys
 * scroll it.
 *
 * @param array    $attributes Block attributes: label, branches, items, reverse.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $attributes['branches'] ) && function_exists( 'demas_theme_get_branches' ) ) {
	$demas_items = wp_list_pluck( demas_theme_get_branches(), 'city' );
} else {
	$demas_items = (array) ( $attributes['items'] ?? array() );
}

$demas_items = array_values( array_filter( array_map( 'trim', array_map( 'strval', $demas_items ) ) ) );

if ( ! $demas_items ) {
	return;
}

$demas_label   = trim( (string) ( $attributes['label'] ?? '' ) );
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-marquee' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> role="region" tabindex="0"<?php echo '' === $demas_label ? '' : ' aria-label="' . esc_attr( $demas_label ) . '"'; ?><?php echo empty( $attributes['reverse'] ) ? '' : ' data-direction="reverse"'; ?>>
	<ul class="dh-marquee__track">
		<?php foreach ( $demas_items as $demas_item ) : ?>
			<li class="dh-marquee__item"><?php echo esc_html( $demas_item ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
