<?php
/**
 * Demas Theme (Sandbox) bootstrap.
 *
 * See CLAUDE.md for architecture, conventions, and forbidden patterns
 * before adding anything here.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEMAS_THEME_VERSION', '0.4.0' );
define( 'DEMAS_THEME_DIR', get_template_directory() );

$demas_theme_includes = array(
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/performance.php',
	'inc/navigation.php',
	'inc/patterns.php',
	'inc/woocommerce.php',
	'inc/system-map.php',
	'inc/catalog-filters.php',
	'inc/product-page.php',
	'inc/quote.php',
	'inc/pages.php',
	'inc/branches.php',
	'inc/footer.php',
	'inc/search.php',
	'inc/sheet-view.php',
	'inc/structured-data.php',
	'inc/homepage.php',
);

foreach ( $demas_theme_includes as $demas_theme_include ) {
	$demas_theme_path = DEMAS_THEME_DIR . '/' . $demas_theme_include;

	if ( file_exists( $demas_theme_path ) ) {
		require_once $demas_theme_path;
	}
}
unset( $demas_theme_includes, $demas_theme_include, $demas_theme_path );
