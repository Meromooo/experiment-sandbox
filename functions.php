<?php
/**
 * Demas Theme (Sandbox) bootstrap.
 *
 * See CLAUDE.md for architecture, conventions, and forbidden patterns
 * before adding anything here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEMAS_THEME_VERSION', '0.1.0' );
define( 'DEMAS_THEME_DIR', get_template_directory() );

$demas_theme_includes = array(
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/navigation.php',
	'inc/patterns.php',
	'inc/woocommerce.php',
	'inc/system-map.php',
	'inc/catalog-filters.php',
	'inc/product-page.php',
	'inc/structured-data.php',
);

foreach ( $demas_theme_includes as $demas_theme_include ) {
	$path = DEMAS_THEME_DIR . '/' . $demas_theme_include;

	if ( file_exists( $path ) ) {
		require_once $path;
	}
}
unset( $demas_theme_includes, $demas_theme_include, $path );
