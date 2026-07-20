<?php
/**
 * Nav menu registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'demas-theme' ),
		'footer'  => __( 'Footer Menu', 'demas-theme' ),
	) );
} );
