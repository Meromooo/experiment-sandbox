<?php
/**
 * The specification, reshaped into a spec sheet at render time.
 *
 * A product's description is whatever was pasted into it on the old site:
 * headings, paragraphs, lists and tables, in a dozen habits. This file reads
 * that HTML and draws it as a sheet in the "Plate" style:
 *
 *  - every heading starts a section, drawn as a white card under a canopy band;
 *  - a table is drawn by its shape: one column is a size grid, two columns a
 *    list of label and value pairs, two columns of part codes against comma
 *    lists a set of model tags, and anything wider a table that scrolls
 *    sideways inside its own box with the first column pinned;
 *  - a paragraph with a PDF link becomes a row of the Documents card;
 *  - a dash between numbers reads "to", and no en or em dash reaches the page.
 *
 * It runs on the server on every render and never writes to the database, so
 * the page has no layout jump, works without JavaScript, and the stored
 * descriptions stay as they are. When the HTML can't be parsed, the filtered
 * description comes back as it was.
 *
 * Everything taken from the description is either text, escaped here, or
 * markup that has already passed wp_kses_post(). Every class, label and
 * attribute added here is the theme's own.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- PHP's DOM API names its properties in camelCase (textContent, childNodes).

/**
 * The mark for a table cell with nothing in it.
 *
 * @return string Safe markup.
 */
function demas_theme_spec_none(): string {
	return '<span class="dh-spec__none">' . esc_html__( 'n/a', 'demas-theme' ) . '</span><span class="dh-spec__sr"> ' . esc_html__( 'not available', 'demas-theme' ) . '</span>';
}

/**
 * Copy rules for visible text: a dash between numbers reads "to", a spaced
 * dash reads as a comma, and any dash left over becomes a hyphen.
 *
 * @param string $text A run of text.
 * @return string
 */
function demas_theme_spec_tidy( string $text ): string {
	$text = preg_replace( '/(\d[\x{00B0}\x{2033}"%]?)\s*[\x{2013}\x{2014}]\s*(?=\d)/u', '$1 to ', $text ) ?? $text;
	$text = preg_replace( '/\s[\x{2013}\x{2014}]\s/u', ', ', $text ) ?? $text;

	return preg_replace( '/[\x{2013}\x{2014}]/u', '-', $text ) ?? $text;
}

/**
 * A node's text with its white space folded to single spaces.
 *
 * @param DOMNode $node Any node.
 * @return string
 */
function demas_theme_spec_text( DOMNode $node ): string {
	return trim( (string) preg_replace( '/[\s\x{00A0}]+/u', ' ', $node->textContent ) );
}

/**
 * The HTML inside a node.
 *
 * @param DOMNode $node Any node.
 * @return string
 */
function demas_theme_spec_inner( DOMNode $node ): string {
	$html = '';

	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}

	return trim( $html );
}

/**
 * A table cell's HTML, or the "n/a" mark when it is empty or only a dash.
 *
 * @param DOMNode $cell A td or th.
 * @return string
 */
function demas_theme_spec_cell( DOMNode $cell ): string {
	$text = demas_theme_spec_text( $cell );

	if ( '' === $text || 1 === preg_match( '/^[-\x{2013}\x{2014}]$/u', $text ) ) {
		return demas_theme_spec_none();
	}

	return demas_theme_spec_inner( $cell );
}

/**
 * The td and th children of a table row.
 *
 * @param DOMElement $row A tr.
 * @return DOMElement[]
 */
function demas_theme_spec_cells( DOMElement $row ): array {
	$cells = array();

	foreach ( $row->childNodes as $child ) {
		if ( $child instanceof DOMElement && in_array( strtolower( $child->nodeName ), array( 'td', 'th' ), true ) ) {
			$cells[] = $child;
		}
	}

	return $cells;
}

/**
 * A cell's rowspan or colspan, held between 1 and 30 so a wild value can't
 * inflate the column count.
 *
 * @param DOMElement $cell      A td or th.
 * @param string     $attribute rowspan or colspan.
 * @return int
 */
function demas_theme_spec_span( DOMElement $cell, string $attribute ): int {
	return min( 30, max( 1, (int) $cell->getAttribute( $attribute ) ) );
}

/**
 * Read a table and say what shape it is.
 *
 * Without merged cells, one column is a size grid and two columns are label
 * and value pairs. Anything else stays a table; its td cells that are empty
 * or a lone dash get the "n/a" mark.
 *
 * @param DOMElement $table A table.
 * @return array The block: its kind, content and row count.
 */
function demas_theme_spec_classify( DOMElement $table ): array {
	$head_rows = array();
	$body_rows = array();
	$width     = 0;
	$spans     = false;

	foreach ( $table->getElementsByTagName( 'tr' ) as $row ) {
		$cells = demas_theme_spec_cells( $row );
		$total = 0;

		foreach ( $cells as $cell ) {
			$colspan = demas_theme_spec_span( $cell, 'colspan' );

			$total += $colspan;

			if ( $colspan > 1 || demas_theme_spec_span( $cell, 'rowspan' ) > 1 ) {
				$spans = true;
			}
		}

		$width = max( $width, $total );

		if ( 'thead' === strtolower( $row->parentNode->nodeName ) ) {
			$head_rows[] = $cells;
		} elseif ( $cells ) {
			$body_rows[] = $cells;
		}
	}

	$cols = 0;

	foreach ( $body_rows as $cells ) {
		$cols = max( $cols, count( $cells ) );
	}

	$head = array();

	if ( 1 === count( $head_rows ) ) {
		foreach ( $head_rows[0] as $cell ) {
			$head[] = demas_theme_spec_text( $cell );
		}
	}

	if ( ! $spans && 1 === $cols ) {
		$items = array();

		foreach ( $body_rows as $cells ) {
			$items[] = demas_theme_spec_cell( $cells[0] );
		}

		return array(
			'kind'  => 'sizes',
			'label' => $head[0] ?? '',
			'items' => $items,
			'count' => count( $items ),
		);
	}

	if ( ! $spans && 2 === $cols ) {
		$rows  = array();
		$codes = 0;

		foreach ( $body_rows as $cells ) {
			$label  = demas_theme_spec_cell( $cells[0] );
			$value  = isset( $cells[1] ) ? demas_theme_spec_cell( $cells[1] ) : demas_theme_spec_none();
			$rows[] = array( $label, $value, isset( $cells[1] ) ? demas_theme_spec_text( $cells[1] ) : '' );

			if ( 1 === preg_match( '/^[A-Z0-9][A-Z0-9.\/\- ]{2,}$/D', demas_theme_plain_text( $label ) ) ) {
				++$codes;
			}
		}

		// A head row that only says "Specification / Value" tells the reader nothing.
		$named   = 2 === count( $head );
		$generic = $named && 1 === preg_match( '/^specification$/i', $head[0] ) && 1 === preg_match( '/^value$/i', $head[1] );

		return array(
			'kind'  => 'pairs',
			'head'  => $named && ! $generic ? $head : array(),
			'rows'  => $rows,
			'code'  => $codes > count( $rows ) * 0.6,
			'count' => count( $rows ),
		);
	}

	foreach ( iterator_to_array( $table->getElementsByTagName( 'td' ) ) as $cell ) {
		if ( demas_theme_spec_none() !== demas_theme_spec_cell( $cell ) ) {
			continue;
		}

		while ( $cell->firstChild ) {
			$cell->removeChild( $cell->firstChild );
		}

		$mark = $table->ownerDocument->createDocumentFragment();
		$mark->appendXML( demas_theme_spec_none() );
		$cell->appendChild( $mark );
	}

	return array(
		'kind'  => 'wide',
		'html'  => $table->ownerDocument->saveHTML( $table ),
		'cols'  => max( $width, $cols ),
		'count' => count( $body_rows ),
	);
}

/**
 * Split a model list into what every model shares and what sets each apart.
 *
 * Returns null unless every model's text is a comma list of three or more
 * phrases. A phrase the product's title already says is dropped.
 *
 * @param array  $rows  The pairs rows: code HTML, text HTML, plain text.
 * @param string $title The product's title as plain text.
 * @return array|null "shared" (phrases) and "rest" (phrases per model).
 */
function demas_theme_spec_models( array $rows, string $title ): ?array {
	$title = mb_strtolower( $title );
	$kept  = array();

	foreach ( $rows as $row ) {
		$parts = preg_split( '/\s*,\s*/u', $row[2] );

		if ( false === $parts ) {
			return null;
		}

		$parts = array_values( array_filter( $parts, 'strlen' ) );

		if ( count( $parts ) < 3 ) {
			return null;
		}

		$kept[] = array_values(
			array_filter(
				$parts,
				function ( $phrase ) use ( $title ) {
					return ! str_contains( $title, mb_strtolower( $phrase ) );
				}
			)
		);
	}

	if ( ! $kept ) {
		return null;
	}

	$shared = array();

	foreach ( $kept[0] as $phrase ) {
		$key = mb_strtolower( $phrase );

		foreach ( $kept as $phrases ) {
			if ( ! in_array( $key, array_map( 'mb_strtolower', $phrases ), true ) ) {
				continue 2;
			}
		}

		$shared[ $key ] = $phrase;
	}

	$rest = array();

	foreach ( $kept as $phrases ) {
		$rest[] = array_values(
			array_filter(
				$phrases,
				function ( $phrase ) use ( $shared ) {
					return ! isset( $shared[ mb_strtolower( $phrase ) ] );
				}
			)
		);
	}

	return array(
		'shared' => array_values( $shared ),
		'rest'   => $rest,
	);
}

/**
 * A row of feature tags.
 *
 * @param string[] $tags   The phrases, as plain text.
 * @param array    $common Lower-case phrases most models have; drawn faded.
 * @return string Safe markup.
 */
function demas_theme_spec_tags( array $tags, array $common ): string {
	$out = '<ul class="dh-spec__tags" role="list">';

	foreach ( $tags as $tag ) {
		$out .= isset( $common[ mb_strtolower( $tag ) ] ) ? '<li class="is-common">' : '<li>';
		$out .= esc_html( $tag ) . '</li>';
	}

	return $out . '</ul>';
}

/**
 * A description list of label and value pairs.
 *
 * @param array  $rows      Each row: label HTML, then value HTML.
 * @param string $modifiers Extra classes, each with a leading space.
 * @return string Safe markup.
 */
function demas_theme_spec_dl( array $rows, string $modifiers ): string {
	$out = '<dl class="dh-spec__pairs' . $modifiers . '">';

	foreach ( $rows as $row ) {
		$out .= '<div class="dh-spec__pair"><dt>' . $row[0] . '</dt><dd>' . $row[1] . '</dd></div>';
	}

	return $out . '</dl>';
}

/**
 * A model list: tags per model under one "Every model" strip, or the full
 * text stacked when the lists don't split into tags.
 *
 * @param array  $block The pairs block.
 * @param string $title The product's title as plain text.
 * @return string Safe markup.
 */
function demas_theme_spec_render_models( array $block, string $title ): string {
	$models = demas_theme_spec_models( $block['rows'], $title );

	if ( null === $models ) {
		return '<div class="dh-spec__block">' . demas_theme_spec_dl( $block['rows'], ' is-code is-stack' ) . '</div>';
	}

	$uses = array();

	foreach ( $models['rest'] as $phrases ) {
		foreach ( $phrases as $phrase ) {
			$key          = mb_strtolower( $phrase );
			$uses[ $key ] = ( $uses[ $key ] ?? 0 ) + 1;
		}
	}

	$common = array();

	foreach ( $uses as $key => $count ) {
		if ( $count >= count( $block['rows'] ) * 0.6 ) {
			$common[ $key ] = true;
		}
	}

	$pairs = array();

	foreach ( $block['rows'] as $i => $row ) {
		$tags = $models['rest'][ $i ];

		$pairs[] = array(
			$row[0],
			$tags ? demas_theme_spec_tags( $tags, $common ) : '<span class="dh-spec__none">' . esc_html__( 'Base model', 'demas-theme' ) . '</span>',
		);
	}

	$shared = '';

	if ( $models['shared'] ) {
		$shared = '<div class="dh-spec__shared"><p>' . esc_html__( 'Every model', 'demas-theme' ) . '</p>' . demas_theme_spec_tags( $models['shared'], array() ) . '</div>';
	}

	return '<div class="dh-spec__block">' . $shared . demas_theme_spec_dl( $pairs, ' is-code is-stack is-tags' ) . '</div>';
}

/**
 * Draw one block of a section.
 *
 * @param array  $block The block, from demas_theme_shape_spec().
 * @param string $name  The section's title, which names a scrolling table.
 * @param string $title The product's title as plain text.
 * @return string Safe markup.
 */
function demas_theme_spec_render_block( array $block, string $name, string $title ): string {
	$kind = $block['kind'];

	if ( 'html' === $kind ) {
		return '<div class="dh-spec__block dh-spec__block--text">' . $block['html'] . '</div>';
	}

	if ( 'list' === $kind ) {
		return '<div class="dh-spec__block"><ul><li>' . implode( '</li><li>', $block['items'] ) . '</li></ul></div>';
	}

	if ( 'sizes' === $kind ) {
		$label = '';
		$aria  = '';

		if ( '' !== $block['label'] ) {
			$label = '<p class="dh-spec__sizes-label">' . esc_html( $block['label'] ) . '</p>';
			$aria  = ' aria-label="' . esc_attr( __( 'Sizes', 'demas-theme' ) . ', ' . $block['label'] ) . '"';
		}

		return '<div class="dh-spec__block">' . $label . '<ul class="dh-spec__sizes" role="list"' . $aria . '><li>' . implode( '</li><li>', $block['items'] ) . '</li></ul></div>';
	}

	if ( 'pairs' === $kind && $block['code'] ) {
		return demas_theme_spec_render_models( $block, $title );
	}

	if ( 'pairs' === $kind ) {
		$head = '';

		if ( $block['head'] ) {
			$head = '<div class="dh-spec__pairs-head" aria-hidden="true"><span>' . esc_html( $block['head'][0] ) . '</span><span>' . esc_html( $block['head'][1] ) . '</span></div>';
		}

		return '<div class="dh-spec__block">' . $head . demas_theme_spec_dl( $block['rows'], '' ) . '</div>';
	}

	// A wide table. From five columns it also says it scrolls; CSS shows that only on narrow boxes.
	$hint = '';

	if ( $block['cols'] >= 5 ) {
		/* translators: %d: number of columns in the table. */
		$hint = '<p class="dh-spec__hint"><svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' . esc_html( sprintf( __( 'Scroll sideways for all %d columns', 'demas-theme' ), $block['cols'] ) ) . '</p>';
	}

	$label = $name . ', ' . __( 'table, scrolls sideways', 'demas-theme' );

	return '<div class="dh-spec__block"><div class="dh-spec__scroll" tabindex="0" role="region" aria-label="' . esc_attr( $label ) . '">' . $block['html'] . '</div>' . $hint . '</div>';
}

/**
 * Draw one section: a card, under a canopy band when it has a title.
 *
 * @param array  $section Its title ('' for the text before any heading) and blocks.
 * @param string $title   The product's title as plain text.
 * @return string Safe markup.
 */
function demas_theme_spec_render_section( array $section, string $title ): string {
	$count  = 0;
	$leader = null;

	foreach ( $section['blocks'] as $block ) {
		$count += $block['count'];

		if ( null === $leader && $block['count'] > 0 ) {
			$leader = $block;
		}
	}

	$name = $section['title'];
	$unit = 'items';

	if ( 1 === preg_match( '/model/i', $name ) ) {
		$unit = 'models';
	} elseif ( $leader && 'sizes' === $leader['kind'] ) {
		$unit = 'sizes';
	} elseif ( $leader && 'wide' === $leader['kind'] ) {
		$unit = 'rows';
	} elseif ( $leader && 'pairs' === $leader['kind'] && ! $leader['code'] ) {
		$unit = 'specs';
	}

	$out = '<section class="dh-spec__card">';

	if ( '' !== $name ) {
		$out .= '<div class="dh-spec__band"><h3 class="dh-spec__heading">' . esc_html( $name ) . '</h3>';
		$out .= $count >= 6 ? '<span class="dh-spec__count">' . esc_html( $count . ' ' . $unit ) . '</span>' : '';
		$out .= '</div>';
	}

	foreach ( $section['blocks'] as $block ) {
		$out .= demas_theme_spec_render_block( $block, '' !== $name ? $name : __( 'Specification', 'demas-theme' ), $title );
	}

	return $out . '</section>';
}

/**
 * The Documents card: each PDF link as a 44px row that says what it opens.
 *
 * @param array $docs Each: href, text.
 * @return string Safe markup, or '' when no link survives esc_url().
 */
function demas_theme_spec_render_docs( array $docs ): string {
	$links = '';
	$icon  = '<svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l5 5v12a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';

	foreach ( $docs as $doc ) {
		$href = esc_url( $doc['href'], array( 'http', 'https' ) );

		if ( '' === $href ) {
			continue;
		}

		$text = '' !== $doc['text'] ? $doc['text'] : __( 'Datasheet', 'demas-theme' );

		$links .= '<a class="dh-spec__doc" href="' . $href . '" target="_blank" rel="noopener noreferrer">' . $icon . '<span class="dh-spec__doc-text">' . esc_html( $text ) . ' <span class="dh-spec__doc-note">' . esc_html__( 'PDF, opens in a new tab', 'demas-theme' ) . '</span></span></a>';
	}

	if ( '' === $links ) {
		return '';
	}

	return '<section class="dh-spec__card"><div class="dh-spec__band"><h3 class="dh-spec__heading">' . esc_html__( 'Documents', 'demas-theme' ) . '</h3></div><div class="dh-spec__docs">' . $links . '</div></section>';
}

/**
 * The specification, drawn as a sheet.
 *
 * Takes the cleaned description (demas_theme_clean_description()), filters it
 * with wp_kses_post(), then reads it with DOMDocument and draws it section by
 * section. Without DOMDocument, or when the HTML can't be read, the filtered
 * HTML comes back unchanged.
 *
 * @param string $html  The cleaned description.
 * @param string $title The product's title.
 * @return string Safe markup; '' when there is nothing to show.
 */
function demas_theme_shape_spec( string $html, string $title ): string {
	$html = wp_kses_post( $html );

	if ( '' === trim( $html ) || ! class_exists( 'DOMDocument' ) ) {
		return trim( $html );
	}

	// The meta tag makes libxml read the fragment as UTF-8 instead of Latin-1.
	$doc      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $doc->loadHTML( '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $html . '</body></html>', LIBXML_NONET );

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	$body = $loaded ? $doc->getElementsByTagName( 'body' )->item( 0 ) : null;

	if ( ! $body instanceof DOMElement ) {
		return $html;
	}

	$xpath = new DOMXPath( $doc );

	foreach ( $xpath->query( '//body//text()' ) as $node ) {
		if ( 1 === preg_match( '/[\x{2013}\x{2014}]/u', $node->nodeValue ) ) {
			$node->data = demas_theme_spec_tidy( $node->nodeValue );
		}
	}

	$title    = demas_theme_plain_text( $title );
	$sections = array();
	$docs     = array();
	$current  = array(
		'title'  => '',
		'blocks' => array(),
	);

	foreach ( iterator_to_array( $body->childNodes ) as $node ) {
		if ( $node instanceof DOMText && '' !== demas_theme_spec_text( $node ) ) {
			$current['blocks'][] = array(
				'kind'  => 'html',
				'html'  => '<p>' . esc_html( demas_theme_spec_text( $node ) ) . '</p>',
				'count' => 0,
			);
		}

		if ( ! $node instanceof DOMElement ) {
			continue;
		}

		$tag = strtolower( $node->nodeName );

		// A heading starts the next section; a title with nothing under it is dropped.
		if ( 1 === preg_match( '/^h[1-6]$/', $tag ) ) {
			if ( $current['blocks'] ) {
				$sections[] = $current;
			}

			$current = array(
				'title'  => demas_theme_spec_text( $node ),
				'blocks' => array(),
			);

			continue;
		}

		$table = 'table' === $tag ? $node : $node->getElementsByTagName( 'table' )->item( 0 );

		if ( $table instanceof DOMElement ) {
			$current['blocks'][] = demas_theme_spec_classify( $table );

			continue;
		}

		if ( 'ul' === $tag || 'ol' === $tag ) {
			$items = array();

			foreach ( $node->childNodes as $item ) {
				if ( $item instanceof DOMElement && 'li' === strtolower( $item->nodeName ) ) {
					$items[] = demas_theme_spec_inner( $item );
				}
			}

			$current['blocks'][] = array(
				'kind'  => 'list',
				'items' => $items,
				'count' => count( $items ),
			);

			continue;
		}

		// A paragraph holding a PDF link becomes a Documents row; its other words go.
		$pdf = $xpath->query( 'descendant-or-self::a[contains(translate(@href, "PDF", "pdf"), ".pdf")]', $node )->item( 0 );

		if ( $pdf instanceof DOMElement ) {
			$docs[] = array(
				'href' => $pdf->getAttribute( 'href' ),
				'text' => trim( (string) preg_replace( '/\s*\(PDF\)\s*$/i', '', demas_theme_spec_text( $pdf ) ) ),
			);

			continue;
		}

		$current['blocks'][] = array(
			'kind'  => 'html',
			'html'  => $doc->saveHTML( $node ),
			'count' => 0,
		);
	}

	if ( $current['blocks'] ) {
		$sections[] = $current;
	}

	$out = '';

	foreach ( $sections as $section ) {
		$out .= demas_theme_spec_render_section( $section, $title );
	}

	return $out . demas_theme_spec_render_docs( $docs );
}

// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
