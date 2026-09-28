<?php
/**
 * Front-end asset registration.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_enqueue_scripts',
	function () {
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
	}
);

/*
 * The theme's own blocks register their stylesheets from block.json, which
 * carries no version, so WordPress stamps each one ?ver=<WordPress version>.
 * That never changes on a deploy, and browsers kept serving the previous
 * build's CSS. Version them the way style.css is versioned: by the file's
 * modification time, which the git deploy updates whenever the file changes.
 * (Their view scripts are unaffected: those carry a content hash from
 * build/<block>/view.asset.php.)
 */
add_action(
	'init',
	function () {
		$styles = wp_styles();
		$prefix = 'demas-theme/';

		foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type ) {
			if ( 0 !== strpos( $name, $prefix ) ) {
				continue;
			}

			foreach ( (array) $type->style_handles as $handle ) {
				$style = $styles->registered[ $handle ] ?? null;

				if ( ! $style || ! is_string( $style->src ) ) {
					continue;
				}

				$file = DEMAS_THEME_DIR . '/build/' . substr( $name, strlen( $prefix ) ) . '/' . basename( (string) wp_parse_url( $style->src, PHP_URL_PATH ) );

				if ( file_exists( $file ) ) {
					$style->ver = (string) filemtime( $file );
				}
			}
		}
	},
	99
);
