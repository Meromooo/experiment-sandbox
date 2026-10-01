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
 *
 * The same goes for the assets the Kadence plugin loads for those blocks —
 * its stylesheets, per-block CSS, slider and form scripts, and a Google Fonts
 * link to the old site's typeface (AMM-154). The plugin stays active; its
 * front-end output is simply not loaded. Nothing here depends on Kadence: with
 * the plugin gone, every step below finds nothing to do.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence's class prefixes, plus the full/wide alignments Kadence rows set
 * (layout choices of the old design, which would break these rows out of the
 * page's reading column). Anything else — core's wp-image-123 (needed for
 * responsive images), wp-block-button__link, has-text-align-center — stays.
 */
const DEMAS_THEME_KADENCE_CLASS = '/^(?:kb-|kt-|kb_|kt_|kadence|wp-block-kadence|reveal-on-scroll|aos|has-theme-palette|alignfull$|alignwide$)/i';

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
 *
 * @param string $html One Kadence block's rendered HTML.
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
 * Whether a registered asset belongs to the Kadence Blocks plugin: by its
 * handle (kadence-*, kadence_blocks_css, kad-splide) or by where it is served
 * from.
 *
 * @param string              $handle Registered handle.
 * @param _WP_Dependency|null $asset  Its registration, if any.
 */
function demas_theme_is_kadence_asset( string $handle, $asset ): bool {
	if ( preg_match( '/^(?:kadence|kad-|kb-)/i', $handle ) ) {
		return true;
	}

	$src = $asset instanceof _WP_Dependency ? $asset->src : '';

	return is_string( $src ) && false !== strpos( $src, '/plugins/kadence-blocks/' );
}

/*
 * Dequeue Kadence's front-end assets. Kadence enqueues as it parses a page's
 * blocks — including on the homepage, whose Kadence content is never shown —
 * so this runs just before each print: the head's styles and scripts, then
 * the footer's.
 */
$demas_theme_drop_kadence_assets = function () {
	foreach ( array( wp_styles(), wp_scripts() ) as $deps ) {
		foreach ( (array) $deps->queue as $handle ) {
			if ( demas_theme_is_kadence_asset( (string) $handle, $deps->registered[ $handle ] ?? null ) ) {
				$deps->dequeue( $handle );
			}
		}
	}
};
add_action( 'wp_print_styles', $demas_theme_drop_kadence_assets, 1 );
add_action( 'wp_print_scripts', $demas_theme_drop_kadence_assets, 1 );
add_action( 'wp_print_footer_scripts', $demas_theme_drop_kadence_assets, 1 );
add_action( 'wp_footer', $demas_theme_drop_kadence_assets, 1 );
unset( $demas_theme_drop_kadence_assets );

// Kadence prints its Google Fonts <link> directly (the old site's Trykker).
add_filter( 'kadence_blocks_print_google_fonts', '__return_false' );

/*
 * A page's content, once it is fully rendered:
 *  - an id used twice keeps only its first use (the Services page carried
 *    Kadence's "jsHeader" twice; duplicate ids break in-page links and
 *    labelling);
 *  - links to a retired page go straight to where that page now sends
 *    visitors, instead of through the redirect;
 *  - photos are sized for the reading column (46rem, see .dh-page__body in
 *    style.css) instead of the full-bleed width their srcset assumed, so a
 *    phone downloads a ~768px file, not a 1536px one; and every photo after
 *    the first loads lazily (AMM-154). WordPress leaves the first three
 *    images eager, which on Services was 865 KB up front. A page on the
 *    "Designed page" template (AMM-167) has no reading column, so its photos
 *    keep WordPress's own sizes; the lazy loading still applies.
 */
add_filter(
	'render_block_core/post-content',
	function ( $content ) {
		if ( ! is_page() || '' === (string) $content ) {
			return $content;
		}

		$retired   = demas_theme_retired_pages();
		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$seen      = array();
		$images    = 0;
		$column    = 'designed-page' !== get_page_template_slug();
		$processor = new WP_HTML_Tag_Processor( (string) $content );

		while ( $processor->next_tag() ) {
			if ( 'IMG' === $processor->get_tag() ) {
				if ( $column && $processor->get_attribute( 'srcset' ) ) {
					$processor->set_attribute( 'sizes', '(max-width: 48rem) calc(100vw - 2rem), 46rem' );
				}

				if ( $images > 0 && ! $processor->get_attribute( 'loading' ) ) {
					$processor->set_attribute( 'loading', 'lazy' );
				}

				++$images;
			}

			$id = $processor->get_attribute( 'id' );

			if ( is_string( $id ) && '' !== $id ) {
				if ( isset( $seen[ $id ] ) ) {
					$processor->remove_attribute( 'id' );
				} else {
					$seen[ $id ] = true;
				}
			}

			if ( 'A' !== $processor->get_tag() ) {
				continue;
			}

			$href = $processor->get_attribute( 'href' );

			if ( ! is_string( $href ) ) {
				continue;
			}

			$host = wp_parse_url( $href, PHP_URL_HOST );
			$path = trim( (string) wp_parse_url( $href, PHP_URL_PATH ), '/' );

			if ( '' !== $path && isset( $retired[ $path ] ) && ( ! $host || $host === $home_host ) ) {
				$processor->set_attribute( 'href', $retired[ $path ] );
			}
		}

		return $processor->get_updated_html();
	}
);

/**
 * Pages retired in the redesign, by slug, and where their visitors go.
 *
 * Contact Us publishes fifteen staff email addresses and a Kadence form — the
 * two things the branch-routed contact form replaces — so its visitors go to
 * the homepage's Branch Desk instead. Its "About us" copy is worth keeping and
 * belongs in a rebuilt About section, not this page.
 *
 * "All Products" (/shop/) is an empty page on both the live site and the
 * sandbox; the catalogue is WooCommerce's shop page, "Products", at
 * /products/. Anyone following an old /shop/ link lands on the catalogue.
 * (AMM-147.)
 *
 * A retired page comes back once it is rebuilt on the "Designed page"
 * template: Contact Us does (AMM-169), its content replaced by the contact
 * pattern. Choosing the template is the switch, so the old content — the
 * email addresses — is never served in between.
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
			'shop'       => function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' ),
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

		if ( isset( $retired[ $page->post_name ] ) && 'designed-page' !== get_page_template_slug( $page ) ) {
			wp_safe_redirect( $retired[ $page->post_name ], 302 );
			exit;
		}
	}
);
