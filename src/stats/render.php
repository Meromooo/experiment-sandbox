<?php
/**
 * Server-rendered markup for the Numbers block: a description list of hard
 * numbers, each a demas-theme/stat inside it.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    The rendered numbers.
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

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-stats' ) );
?>
<dl <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- inner blocks, each escaped in its own render.php. ?>
</dl>
