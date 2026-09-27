<?php
/**
 * Generic pages: the legacy markup they carry, and the pages retired in the
 * redesign (AMM-146).
 *
 * Every content page that came over in the live-site clone — Services,
 * Contact Us, the old Homepage — is built from Kadence Blocks. Rendered as-is,
 * the Kadence plugin dresses them in the old site's design: its colours, row
 * dividers, button styles and scroll animations. CLAUDE.md is explicit that
 * none of that design carries over; the *content* may. So Kadence blocks keep
 * their text, images and links and lose everything that styled them, at
 * render only — the database is untouched, and pages rebuilt later with core
 * blocks and theme patterns are not affected.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence's class prefixes. Anything else — core's wp-image-123 (needed for
 * responsive images), wp-block-button__link, has-text-align-center — stays.
 */
const DEMAS_THEME_KADENCE_CLASS = '/^(?:kb-|kt-|kb_|kt_|kadence|wp-block-kadence|reveal-on-scroll|aos)/i';

/**
 * One Kadence block's rendered HTML, with the Kadence design removed.
 *
 *  - per-block <style> tags go;
 *  - row separators go entirely — they are full-width SVG shapes sized only
 *    by Kadence's CSS, and without it they stretch into dark wedges;
 *  - Kadence classes, inline styles and Kadence data attributes go;
 *  - an <h1> becomes an <h2>, because the page template's title is the h1.
 *
 * Idempotent: an outer Kadence block (a row) re-processes its already-cleaned
 * children harmlessly. Each pass keeps its input if the pattern fails.
 */
function demas_theme_strip_kadence_markup( string $html ): string {
	if ( '' === trim( $html ) ) {
		return $html;
	}

	$html = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', $html ) ?? $html;
	$html = preg_replace( '#<div\b[^>]*class="[^"]*\bkt-row-layout-(?:top|bottom)-sep\b[^"]*"[^>]*>.*?</div>#is', '', $html ) ?? $html;
	$html = preg_replace( '#<(/?)h1\b#i', '<$1h2', $html ) ?? $html;

	$processor = new WP_HTML_Tag_Processor( $html );

	while ( $processor->next_tag() ) {
		$processor->remove_attribute( 'style' );

		foreach ( (array) $processor->get_attribute_names_with_prefix( 'data-' ) as $attribute ) {
			if ( 0 === strpos( $attribute, 'data-kb' ) || 0 === strpos( $attribute, 'data-aos' ) ) {
				$processor->remove_attribute( $attribute );
			}
		}

		$classes = array();
		foreach ( $processor->class_list() as $class ) {
			$classes[] = $class;
		}

		foreach ( $classes as $class ) {
			if ( preg_match( DEMAS_THEME_KADENCE_CLASS, $class ) ) {
				$processor->remove_class( $class );
			}
		}

		if ( '' === trim( (string) $processor->get_attribute( 'class' ) ) ) {
			$processor->remove_attribute( 'class' );
		}
	}

	return $processor->get_updated_html();
}

add_filter(
	'render_block',
	function ( $block_content, $block ) {
		$name = (string) ( $block['blockName'] ?? '' );

		if ( 0 !== strpos( $name, 'kadence/' ) ) {
			return $block_content;
		}

		return demas_theme_strip_kadence_markup( (string) $block_content );
	},
	10,
	2
);

/*
 * Kadence forms post to Kadence's own handler, and the site's contact route
 * is the branch-routed form (AMM-140). They render nothing.
 */
add_filter(
	'pre_render_block',
	function ( $pre_render, $parsed_block ) {
		if ( null !== $pre_render ) {
			return $pre_render;
		}

		$name = (string) ( $parsed_block['blockName'] ?? '' );

		return in_array( $name, array( 'kadence/form', 'kadence/advanced-form' ), true ) ? '' : null;
	},
	10,
	2
);

/**
 * Pages retired in the redesign, by slug, and where their visitors go.
 *
 * Contact Us publishes fifteen staff email addresses and a Kadence form — the
 * two things the branch-routed contact form replaces — so its visitors go to
 * the homepage's Branch Desk instead. Its "About us" copy is worth keeping and
 * belongs in a rebuilt About section, not this page.
 *
 * Filterable, so a retirement can be reversed without a theme change.
 *
 * @return array<string, string> Page slug => destination URL.
 */
function demas_theme_retired_pages(): array {
	return (array) apply_filters(
		'demas_theme_retired_pages',
		array(
			'contact-us' => home_url( '/#find-your-branch' ),
		)
	);
}

// 302 for now, like cart and checkout: this is a content decision that may be
// revisited before launch, and a 301 would be cached by browsers. Switch to
// 301 at cutover.
add_action(
	'template_redirect',
	function () {
		if ( ! is_page() || is_front_page() ) {
			return;
		}

		$page = get_queried_object();

		if ( ! $page instanceof WP_Post ) {
			return;
		}

		$retired = demas_theme_retired_pages();

		if ( isset( $retired[ $page->post_name ] ) ) {
			wp_safe_redirect( $retired[ $page->post_name ], 302 );
			exit;
		}
	}
);
