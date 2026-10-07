<?php
/**
 * Server-rendered markup for the Process Steps block: an ordered list, each
 * step a demas-theme/step inside it. The numerals are drawn by CSS
 * (style.css, "Process"), so adding, removing or reordering a step renumbers
 * the rest. The row plays left to right as it comes up the screen, driven by
 * its own scroll position in CSS (AMM-187), so it takes no reveal attributes.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    The rendered steps.
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( '' === trim( $content ) ) {
	return;
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-process__steps' ) );
?>
<ol <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- inner blocks, each escaped in its own render.php. ?>
</ol>
