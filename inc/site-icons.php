<?php
/**
 * Browser-tab, bookmark and home-screen icons (2026-10-05).
 *
 * The icons are theme files in assets/images/, cut from two rectangle-only
 * masters of the Demas "dm" mark:
 *
 *  - favicon.svg and favicon.ico (16, 32, 48): a tab-size simplification
 *    drawn on a 16px grid, so every bar is two whole pixels wide at 16px.
 *    The full mark is too tall and thin to read there.
 *  - apple-touch-icon.png (180), icon-192/512.png and icon-maskable-512.png:
 *    the full mark (demas-mark.svg) on an opaque paper tile.
 *
 * WordPress's own Site Icon tags (wp_site_icon(), from the image chosen in
 * Settings → General) are replaced here, so the sandbox and, after cutover,
 * live print the same set from code with no admin step. The Site Icon
 * setting is left alone; it still serves the admin and login screens.
 * The rules behind the set are in .claude/skills/favicon-cheat-sheet.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'wp_head', 'wp_site_icon', 99 );
add_action( 'wp_head', 'demas_theme_site_icons', 99 );

/**
 * Prints the icon links, the manifest link and the toolbar colour.
 *
 * Each URL carries its file's modified time, so a changed icon is fetched
 * again; browsers keep favicons for a long time.
 */
function demas_theme_site_icons(): void {
	$dir = get_template_directory() . '/assets/images/';
	$uri = get_template_directory_uri() . '/assets/images/';

	// Without the files (a partial deploy), fall back to WordPress's own tags.
	if ( ! file_exists( $dir . 'favicon.svg' ) ) {
		wp_site_icon();
		return;
	}

	$url = static function ( string $file ) use ( $dir, $uri ): string {
		return add_query_arg( 'ver', (string) filemtime( $dir . $file ), $uri . $file );
	};

	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( $url( 'favicon.ico' ) ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $url( 'favicon.svg' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $url( 'apple-touch-icon.png' ) ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( $url( 'site.webmanifest' ) ) );

	$paper = demas_theme_palette_color( 'paper' );
	if ( '' !== $paper ) {
		printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $paper ) );
	}
}

/**
 * A colour from the theme.json palette, by slug.
 *
 * Site.webmanifest repeats paper (#FAF9F6) because JSON can't read
 * theme.json; change both together.
 *
 * @param string $slug Palette slug.
 * @return string The hex value, or '' when the slug is missing.
 */
function demas_theme_palette_color( string $slug ): string {
	foreach ( (array) wp_get_global_settings( array( 'color', 'palette', 'theme' ) ) as $color ) {
		if ( isset( $color['slug'], $color['color'] ) && $slug === $color['slug'] ) {
			return (string) $color['color'];
		}
	}

	return '';
}
