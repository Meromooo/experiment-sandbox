<?php
/**
 * Server-rendered markup for the Credentials block: the row of marks under
 * the hero. Each mark is a demas-theme/credential inside it; the row numbers
 * them for a staggered reveal (data-reveal-group, main.js).
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    The rendered credentials.
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

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-creds__list' ) );
?>
<ul <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal-group>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- inner blocks, each escaped in its own render.php. ?>
</ul>
