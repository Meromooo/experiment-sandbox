<?php
/**
 * Server-rendered markup for one Process Step: a list item holding its
 * title (a core Heading) and text (a core Paragraph). Its numeral is drawn
 * by CSS from the step's position in the list.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    The rendered title and text.
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
<li <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal="rise">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- core blocks, escaped when saved. ?>
</li>
