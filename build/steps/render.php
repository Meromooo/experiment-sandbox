<?php
/**
 * Server-rendered markup for the Process Steps block: an ordered list, each
 * step a demas-theme/step inside it. The numerals and the pipeline line
 * joining them are drawn by CSS (style.css, "Process"), so adding, removing
 * or reordering a step renumbers the rest. The list numbers its steps for a
 * staggered reveal (data-reveal-group, main.js).
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
<ol <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal-group>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- inner blocks, each escaped in its own render.php. ?>
</ol>
