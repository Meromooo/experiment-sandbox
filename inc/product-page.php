<?php
/**
 * The single product page: the product-summary block, and the helpers it
 * needs to describe one part — its deepest category, its brand and series,
 * the stage it sits at on its system line, and a cleaned copy of its
 * description.
 *
 * The page is a datasheet, not a shop page. Every price in the catalogue is
 * zero, so there is no price, no cart and no tabs: the buyer arrives to
 * confirm the part and leaves by asking what it costs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$build_path = DEMAS_THEME_DIR . '/build/product-summary';

	if ( file_exists( $build_path . '/block.json' ) ) {
		register_block_type( $build_path );
	}
} );

/**
 * A product's category chain from its deepest term up to the top level.
 *
 * Products are filed on several terms at once (a leaf and one or more of its
 * ancestors — "Compression Fittings" and "Fittings"), and the order WordPress
 * returns them in says nothing about depth. The deepest one is the most
 * specific description of the part, so it leads.
 *
 * @return WP_Term[] Leaf first, top-level last. Empty when uncategorised.
 */
function demas_theme_get_product_term_chain( int $product_id ): array {
	$terms = get_the_terms( $product_id, 'product_cat' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	$leaf  = null;
	$depth = -1;

	foreach ( $terms as $term ) {
		$d = count( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) );

		if ( $d > $depth ) {
			$leaf  = $term;
			$depth = $d;
		}
	}

	$chain = array( $leaf );

	foreach ( get_ancestors( $leaf->term_id, 'product_cat', 'taxonomy' ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, 'product_cat' );

		if ( $ancestor instanceof WP_Term ) {
			$chain[] = $ancestor;
		}
	}

	return $chain;
}

/**
 * Brand and series, read from the category chain rather than stored anywhere:
 * the tree files branded parts as "Valves / Irritrol / 2400 Series", so the
 * brand is whichever ancestor is a manufacturer and the series is the leaf
 * beneath it. 191 of the 643 products have one.
 *
 * @param WP_Term[] $chain From demas_theme_get_product_term_chain().
 * @return array{brand:?WP_Term, series:?WP_Term}
 */
function demas_theme_get_product_make( array $chain ): array {
	$make = array(
		'brand'  => null,
		'series' => null,
	);

	foreach ( $chain as $term ) {
		$kind = demas_theme_get_term_kind( $term );

		if ( 'series' === $kind && ! $make['series'] ) {
			$make['series'] = $term;
		} elseif ( 'brand' === $kind ) {
			$make['brand'] = $term;
			break;
		}
	}

	return $make;
}

/**
 * Where a part sits on its system line.
 *
 * Walks the chain from the leaf upward and takes the first stage that lists
 * any of those terms. The leaf wins over its ancestors on purpose: a bubbler
 * is filed under Fittings, but it sits at Deliver, and saying so is the whole
 * reason the system map exists.
 *
 * @param WP_Term[] $chain From demas_theme_get_product_term_chain().
 * @return array{system:array, index:int}|null Null for parts on no line (Swimming Pool).
 */
function demas_theme_get_product_stage( array $chain ): ?array {
	if ( ! $chain || ! function_exists( 'demas_theme_get_systems_for_term' ) ) {
		return null;
	}

	$systems = demas_theme_get_systems_for_term( $chain[0] );

	if ( ! $systems ) {
		return null;
	}

	$system = reset( $systems );

	foreach ( $chain as $term ) {
		foreach ( $system['stages'] as $i => $stage ) {
			if ( in_array( $term->slug, $stage['terms'], true ) ) {
				return array(
					'system' => $system,
					'index'  => $i,
				);
			}
		}
	}

	return null;
}

/**
 * Plain text of an HTML fragment, for comparing two descriptions.
 */
function demas_theme_plain_text( string $html ): string {
	$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	return trim( preg_replace( '/\s+/u', ' ', $text ) ?? $text );
}

/**
 * Whether a run of text is a stylesheet rather than prose.
 *
 * 53 products — most of Industrial Tools — open their description with the
 * old live site's per-product CSS pasted in as a paragraph (".postid-2769
 * .woocommerce div.product div.images{background:#ffffff;…}"), which renders
 * as a wall of code. Two or more "selector{property:value;}" rules is not
 * something a product description says on purpose.
 */
function demas_theme_looks_like_css( string $text ): bool {
	return preg_match_all( '/[^{}]+\{[^{}]*:[^{}]*;?[^{}]*\}/', $text ) >= 2;
}

/**
 * The description, made safe to set in this theme's type.
 *
 * The descriptions came over from the live site as whatever was pasted into
 * them over the years — the old site's CSS as visible text, 3,500 inline
 * style attributes (colours, borders, absolute positioning), Elementor and
 * chat-tool wrapper markup. None of that is changed in the database; this
 * only decides what reaches the page:
 *
 *  - paragraphs that are really stylesheets are dropped;
 *  - presentational attributes are removed, so the theme's type and rules
 *    apply instead of the old site's navy and grey;
 *  - wrapper <div>s are unwrapped and typed "•" bullets inside list items
 *    are dropped;
 *  - tables get a scrolling wrapper, so a wide spec table scrolls inside the
 *    column on a phone instead of pushing the page sideways;
 *  - empty paragraphs left behind are removed.
 */
function demas_theme_clean_description( string $html ): string {
	if ( '' === trim( $html ) ) {
		return '';
	}

	// Each pass keeps the previous result if its pattern fails (a null from
	// preg_* on malformed input), so a bad description degrades to less
	// cleaning rather than to an empty specification.
	$html = preg_replace_callback(
		'#<p\b[^>]*>(.*?)</p>#is',
		function ( $m ) {
			return demas_theme_looks_like_css( demas_theme_plain_text( $m[1] ) ) ? '' : $m[0];
		},
		$html
	) ?? $html;

	$html = preg_replace( '#<(style|script)\b[^>]*>.*?</\1>#is', '', $html ) ?? $html;

	$presentational = array( 'style', 'align', 'valign', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'color', 'face' );
	$processor      = new WP_HTML_Tag_Processor( $html );

	while ( $processor->next_tag() ) {
		foreach ( $presentational as $attribute ) {
			$processor->remove_attribute( $attribute );
		}

		// Images keep width/height so the browser can reserve their space.
		if ( 'IMG' !== $processor->get_tag() ) {
			$processor->remove_attribute( 'width' );
			$processor->remove_attribute( 'height' );
		}
	}

	$html = $processor->get_updated_html();

	// Every <div> in these descriptions is a styled box from the old site or a
	// page-builder wrapper. With the styles gone they only nest each section
	// one level deep, which makes every heading a first child and collapses
	// the space above it. Unwrapping leaves headings, paragraphs, lists and
	// tables as siblings — the shape the spec styles are written for.
	$html = preg_replace( '#</?div\b[^>]*>#i', "\n", $html ) ?? $html;

	// 311 list items (all 53 tool products) start with a typed "•" as well as
	// being in a list, which would print two bullets.
	$html = preg_replace( '#(<li\b[^>]*>)\s*(?:&\#8226;|&bull;|•|·)\s*#u', '$1', $html ) ?? $html;

	$html = preg_replace( '#<table\b#i', '<div class="dh-spec__table"><table', $html ) ?? $html;
	$html = preg_replace( '#</table>#i', '</table></div>', $html ) ?? $html;

	$html = preg_replace( '#<p>(?:\s|&nbsp;|<br\s*/?>)*</p>#i', '', $html ) ?? $html;

	return trim( $html );
}

/**
 * Title size tier, set from the length of the name.
 *
 * Product names are data, not headlines: the median is 37 characters, one
 * in ten runs past 72, and the longest is 110 ("Irritrol 2713APRDK-MF 1-1/2″
 * Angle Solenoid Valve with Decoder and Adjustable Pressure Regulator —
 * Medium Flow"). Set at one size in stretched Archivo, the long ones become a
 * five-line block. Three tiers keep every name to about three lines.
 *
 * @return string Modifier class, or '' for the default (short) size.
 */
function demas_theme_title_tier( string $title ): string {
	$length = mb_strlen( demas_theme_plain_text( $title ) );

	if ( $length > 70 ) {
		return 'is-long';
	}

	return $length > 40 ? 'is-medium' : '';
}

/**
 * Give the product's own photo an alt text when the media library left it
 * empty. The photo is the part itself, so an empty alt would hide the most
 * identifying thing on the page from a screen reader.
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attributes, $attachment ) {
		if ( ! is_singular( 'product' ) || '' !== trim( (string) ( $attributes['alt'] ?? '' ) ) ) {
			return $attributes;
		}

		$product_id = get_queried_object_id();

		if ( (int) get_post_thumbnail_id( $product_id ) === (int) $attachment->ID ) {
			$attributes['alt'] = wp_strip_all_tags( get_the_title( $product_id ) );
		}

		return $attributes;
	},
	10,
	2
);
