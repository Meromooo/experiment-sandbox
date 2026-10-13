<?php
/**
 * The viewport tag, with viewport-fit=cover (AMM-193, 2026-10-13).
 *
 * WordPress prints a fixed viewport tag for block themes
 * (_block_template_viewport_meta_tag(), added to wp_head at priority 0 while
 * the template is resolved) and offers no filter for it. Without
 * viewport-fit=cover a phone letterboxes the page inside its safe area and
 * env(safe-area-inset-*) reads 0, so the quote list's bottom sheet can't keep
 * clear of the home bar. This swaps core's tag for the same one with
 * viewport-fit=cover; style.css pads the page back out of the notch.
 *
 * Zoom is never disabled (no maximum-scale, no user-scalable=no).
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'demas_theme_viewport_meta_tag', -1 );

/**
 * Prints the viewport tag in place of core's.
 *
 * Runs just before core's priority-0 callback and removes it, so the page has
 * exactly one viewport tag. On a page that isn't a block template, core never
 * added its tag and the removal does nothing.
 */
function demas_theme_viewport_meta_tag(): void {
	remove_action( 'wp_head', '_block_template_viewport_meta_tag', 0 );
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />' . "\n";
}
