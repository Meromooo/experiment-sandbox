<?php
/**
 * Front-end asset registration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	$style_path = get_theme_file_path( 'assets/css/style.css' );

	if ( file_exists( $style_path ) ) {
		wp_enqueue_style(
			'demas-theme-style',
			get_theme_file_uri( 'assets/css/style.css' ),
			array(),
			filemtime( $style_path )
		);
	}

	$script_path = get_theme_file_path( 'assets/js/main.js' );

	if ( file_exists( $script_path ) ) {
		wp_enqueue_script(
			'demas-theme-main',
			get_theme_file_uri( 'assets/js/main.js' ),
			array(),
			filemtime( $script_path ),
			true
		);
	}
} );
