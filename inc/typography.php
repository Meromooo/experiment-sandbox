<?php
/**
 * Hyphens stay hyphens (2026-10-08).
 *
 * WordPress runs wptexturize() over every block template it renders, which
 * turns a spaced hyphen into an en dash and a double hyphen into an em dash.
 * So the category "Clamp Saddle - PN6, PN16", stored and shown on the live
 * site with a hyphen, printed as "Clamp Saddle – PN6, PN16" in the heading,
 * the breadcrumbs, the category index, the toolbar and every card's category
 * path, and 22 product names got a dash their owners never typed. A visitor
 * should read no en or em dash anywhere on the site (CLAUDE.md, the taste
 * rules).
 *
 * wptexturize() takes the dashes it inserts from WordPress's translations
 * ("en dash", "em dash"), so answering those two with a hyphen switches off
 * only the dashes. Its curly quotes, apostrophes, ellipses and inch marks
 * are unchanged, and a dash someone actually typed stays as typed.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'gettext_with_context_default', 'demas_theme_keep_hyphens', 10, 3 );

/**
 * The hyphen wptexturize() should print in place of its dashes.
 *
 * @param string $translation The translated text.
 * @param string $text        The original text.
 * @param string $context     The translation context.
 * @return string
 */
function demas_theme_keep_hyphens( $translation, $text, $context ) {
	if ( ( 'en dash' === $context && '&#8211;' === $text ) || ( 'em dash' === $context && '&#8212;' === $text ) ) {
		return '-';
	}

	return $translation;
}
