<?php
/**
 * Server-rendered markup for the Credentials block: the belt of certificate
 * and partner plates under the hero (AMM-178). Each plate is a
 * demas-theme/credential inside it.
 *
 * The belt is a named region. It runs the marquee's loop (style.css, the
 * credentials section; main.js copies the plates once, hidden and inert), the
 * other way from the supply line above it. main.js also stamps the plates in
 * when the belt first comes into view, slows it to a stop under the pointer,
 * and stops it on keyboard focus with the focused plate in view. With reduced
 * motion the plates stand still as a wall.
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

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-belt' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> role="region" aria-label="<?php esc_attr_e( 'Certificates and approvals', 'demas-theme' ); ?>">
	<ul class="dh-belt__track">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- inner blocks, each escaped in its own render.php. ?>
	</ul>
</div>
