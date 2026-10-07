<?php
/**
 * Server-rendered markup for one Process Step: a list item holding its name
 * (a core Heading), under a canopy rule that ends in an arrowhead pointing on
 * to the next step (AMM-187). Its number is drawn by CSS from the step's
 * position in the list, and the rule draws itself in as the row comes up the
 * screen (style.css, "Process").
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    The rendered name.
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

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-process__step' ) );
?>
<li <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<span class="dh-process__rule" aria-hidden="true"><svg class="dh-process__arrow" viewBox="0 0 16 16" focusable="false"><path d="M5 2 11 8 5 14"/></svg></span>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks, escaped when saved. ?>
</li>
