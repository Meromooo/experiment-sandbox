<?php
/**
 * Structured data (JSON-LD) for a quote-led catalogue (AMM-155).
 *
 * Built on the plugins' own generators, not a second one of ours:
 *
 *  - WooCommerce writes the BreadcrumbList, and would write the Product
 *    markup — from each product's own WooCommerce fields, so a new product
 *    needs no extra step. Its product generator only runs from the classic
 *    single-product summary hook, which the datasheet template
 *    (templates/single-product.html) does not use, so it is started here.
 *  - Yoast SEO writes WebSite (with the part-number search box), Organization
 *    and WebPage. On WooCommerce pages its own BreadcrumbList is dropped: it
 *    read "Home › Products › part", with no category, beside WooCommerce's
 *    "Home › Fog Systems › Water Treatment › part". One trail per page — the
 *    one the page shows, built from the same category tree as the live site.
 *
 * No Product markup while there are no prices (decided 2026-09-28).
 * WooCommerce's generator writes Product only when a product has an offer, a
 * rating or a review — Google's own rule for product results — and every
 * product here has an empty price and is priced by a branch on request. The
 * alternative, writing Product ourselves, would have put ~643 "invalid items"
 * in Search Console for no gain. So today no product page carries Product
 * markup. The day a product gets a real price or reviews, WooCommerce writes
 * it, adjusted here through its documented filter:
 *
 *  - a zero price is never published: its offer is dropped, and with no
 *    rating or review left the whole Product is withheld;
 *  - names decoded: the imported titles carry "&#8243;" (″), and WooCommerce's
 *    breadcrumb double-encodes it to "&amp;#8243;" (that fix is live now);
 *  - the description comes from the cleaned description (inc/product-page.php):
 *    53 imported descriptions open with the old site's CSS as visible text;
 *  - category is the product's full path in the category tree ("Irrigation >
 *    Fittings > Barbed Fittings"), brand comes from that tree, and mpn is set
 *    only for manufacturer part numbers — never for the DMS- house references
 *    (tools/assign-house-skus.php), which stay as the sku.
 *
 * No settings changed, and nothing here depends on Yoast: without it, its
 * two filters simply never run.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Text as it should read in JSON-LD: entities decoded — repeatedly, because
 * WooCommerce's breadcrumb encodes an already-encoded title — and tags gone.
 */
function demas_theme_schema_text( string $text ): string {
	for ( $i = 0; $i < 3; $i++ ) {
		$decoded = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		if ( $decoded === $text ) {
			break;
		}

		$text = $decoded;
	}

	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) ?? $text );
}

/**
 * A product's description as plain text for structured data: the cleaned
 * description, with a space where each paragraph, list item or table cell
 * ended (so a spec table reads "Pressure 16 bar", not "Pressure16 bar"),
 * trimmed to a search-result length. Falls back to the short description.
 */
function demas_theme_schema_description( WC_Product $product ): string {
	$html = (string) $product->get_description();

	if ( function_exists( 'demas_theme_clean_description' ) ) {
		$html = demas_theme_clean_description( $html );
	}

	if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
		$html = (string) $product->get_short_description();
	}

	$html = preg_replace( '#<(?:br|/?(?:p|div|li|ul|ol|tr|td|th|table|h[1-6]))\b[^>]*>#i', ' ', $html ) ?? $html;
	$text = demas_theme_schema_text( $html );

	return '' === $text ? '' : wp_html_excerpt( $text, 500, '…' );
}

/*
 * Start WooCommerce's product generator on the datasheet template. It prints
 * with WooCommerce's other structured data at wp_footer priority 10.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() || did_action( 'woocommerce_single_product_summary' ) ) {
			return;
		}

		$product = wc_get_product( get_queried_object_id() );

		if ( $product && isset( WC()->structured_data ) ) {
			WC()->structured_data->generate_product_data( $product );
		}
	},
	5
);

add_filter(
	'woocommerce_structured_data_product',
	function ( $markup, $product ) {
		if ( ! is_array( $markup ) || ! $product instanceof WC_Product ) {
			return $markup;
		}

		// A zero price is never published. Without an offer, a Product needs a
		// rating or a review to be valid; with neither, returning no @type makes
		// WooCommerce's set_data() drop it altogether.
		if ( (float) $product->get_price() <= 0 ) {
			unset( $markup['offers'] );

			if ( empty( $markup['aggregateRating'] ) && empty( $markup['review'] ) ) {
				return array();
			}
		}

		$markup['name'] = demas_theme_schema_text( wptexturize( (string) $product->get_name() ) );

		$description = demas_theme_schema_description( $product );

		if ( '' !== $description ) {
			$markup['description'] = $description;
		} else {
			unset( $markup['description'] );
		}

		$chain = function_exists( 'demas_theme_get_product_term_chain' ) ? demas_theme_get_product_term_chain( $product->get_id() ) : array();

		if ( $chain ) {
			$markup['category'] = implode(
				' > ',
				array_map(
					fn( $term ) => demas_theme_schema_text( wptexturize( $term->name ) ),
					array_reverse( $chain )
				)
			);

			$make = function_exists( 'demas_theme_get_product_make' ) ? demas_theme_get_product_make( $chain ) : array( 'brand' => null );

			if ( $make['brand'] instanceof WP_Term ) {
				$markup['brand'] = array(
					'@type' => 'Brand',
					'name'  => demas_theme_schema_text( $make['brand']->name ),
				);
			}
		}

		$sku = (string) $product->get_sku();

		if ( '' !== $sku ) {
			$markup['sku'] = $sku;

			if ( 0 !== stripos( $sku, 'DMS-' ) ) {
				$markup['mpn'] = $sku;
			}
		}

		return $markup;
	},
	10,
	2
);

add_filter(
	'woocommerce_structured_data_breadcrumblist',
	function ( $markup ) {
		if ( ! is_array( $markup ) || empty( $markup['itemListElement'] ) || ! is_array( $markup['itemListElement'] ) ) {
			return $markup;
		}

		// wptexturize first, as the page's own breadcrumb does: category names
		// are stored with a plain hyphen ("Clamp Saddle - PN6, PN16") that the
		// page, and the live site, show as an en dash.
		foreach ( $markup['itemListElement'] as $i => $element ) {
			if ( isset( $element['item']['name'] ) ) {
				$markup['itemListElement'][ $i ]['item']['name'] = demas_theme_schema_text( wptexturize( (string) $element['item']['name'] ) );
			}

			if ( isset( $element['name'] ) ) {
				$markup['itemListElement'][ $i ]['name'] = demas_theme_schema_text( wptexturize( (string) $element['name'] ) );
			}
		}

		return $markup;
	}
);

/**
 * Whether WooCommerce outputs this page's breadcrumb trail: product,
 * category, catalogue and product-search pages all carry the breadcrumbs
 * block (templates/single-product.html, archive-product.html,
 * product-search-results.html).
 */
function demas_theme_woocommerce_owns_breadcrumbs(): bool {
	return function_exists( 'is_woocommerce' ) && is_woocommerce();
}

// Yoast: drop its Breadcrumb piece where WooCommerce has one…
add_filter(
	'wpseo_schema_graph_pieces',
	function ( $pieces ) {
		if ( ! is_array( $pieces ) || ! demas_theme_woocommerce_owns_breadcrumbs() ) {
			return $pieces;
		}

		return array_filter(
			$pieces,
			fn( $piece ) => ! $piece instanceof \Yoast\WP\SEO\Generators\Schema\Breadcrumb
		);
	},
	11
);

// …and the WebPage's reference to it, so nothing points at a missing node.
add_filter(
	'wpseo_schema_webpage',
	function ( $data ) {
		if ( is_array( $data ) && demas_theme_woocommerce_owns_breadcrumbs() ) {
			unset( $data['breadcrumb'] );
		}

		return $data;
	},
	11
);
