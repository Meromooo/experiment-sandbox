<?php
/**
 * Title: Not found — the ways back in
 * Slug: demas-theme/not-found
 * Categories: demas
 * Inserter: no
 * Description: Head of the 404 page. Says what happened without apologising, then offers the catalogue and a branch; the template adds the stage index below.
 *
 * @package Demas_Theme
 */

$demas_catalogue = function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' );
?>
<!-- wp:group {"className":"dh-404__head","layout":{"type":"default"}} -->
<div class="wp-block-group dh-404__head">
	<!-- wp:paragraph {"className":"dh-404__code dh-eyebrow"} -->
	<p class="dh-404__code dh-eyebrow"><?php esc_html_e( '404 · Not found', 'demas-theme' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":1,"className":"dh-404__title"} -->
	<h1 class="wp-block-heading dh-404__title"><?php esc_html_e( 'Nothing at this address.', 'demas-theme' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"dh-404__lede"} -->
	<p class="dh-404__lede"><?php esc_html_e( 'If you followed an old link, the page may have moved when the site was rebuilt. Search for the part by name or number, find it by where it sits in its system below, or ask a branch to find it for you.', 'demas-theme' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:html -->
	<?php echo function_exists( 'demas_theme_search_form' ) ? demas_theme_search_form( array( 'class' => 'dh-404__search' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside the helper. ?>
	<!-- /wp:html -->

	<!-- wp:buttons {"className":"dh-404__actions"} -->
	<div class="wp-block-buttons dh-404__actions">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $demas_catalogue ); ?>"><?php esc_html_e( 'Browse the catalogue', 'demas-theme' ); ?></a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Ask a branch', 'demas-theme' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
