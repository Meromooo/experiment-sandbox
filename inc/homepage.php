<?php
/**
 * The homepage's editable sections (AMM-153).
 *
 * The homepage patterns were single Custom HTML blocks, which nobody at
 * Demas could edit. They are now core blocks (headings, paragraphs,
 * buttons, groups) plus a few small theme blocks for the parts core blocks
 * cannot express. This file holds what those need:
 *
 *  - the section blocks: demas-theme/credentials and /credential (the marks
 *    under the hero, each with an optional tooltip), /stats and /stat (the
 *    numbers band, counting up), /steps and /step (the numbered process),
 *    /category-cards (the catalogue gateway, with live product counts);
 *  - the pills as Button block styles, so an editor picks "Pill" in the
 *    block's Styles panel instead of typing class names;
 *  - the bridge that turns reveal classes on core blocks into the data
 *    attributes the motion code reads.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		foreach ( array( 'credentials', 'credential', 'stats', 'stat', 'steps', 'step', 'category-cards' ) as $block ) {
			$build_path = DEMAS_THEME_DIR . '/build/' . $block;

			if ( file_exists( $build_path . '/block.json' ) ) {
				register_block_type( $build_path );
			}
		}

		// The rules are in assets/css/style.css, section 4, beside .dh-pill.
		$pills = array(
			'dh-pill-solid'         => __( 'Pill', 'demas-theme' ),
			'dh-pill-outline'       => __( 'Pill outline', 'demas-theme' ),
			'dh-pill-paper'         => __( 'Pill on dark', 'demas-theme' ),
			'dh-pill-outline-paper' => __( 'Pill outline on dark', 'demas-theme' ),
		);

		foreach ( $pills as $name => $label ) {
			register_block_style(
				'core/button',
				array(
					'name'  => $name,
					'label' => $label,
				)
			);
		}
	}
);

/**
 * Scroll reveals for core blocks.
 *
 * The motion code (style.css section 7, main.js) animates elements marked
 * with data attributes. The block editor cannot put a data attribute on a
 * core block, but it can give it a class under Advanced → Additional CSS
 * class(es), so a block with one of these classes
 *
 *   dh-reveal--rise, --seed, --dot, --sliver, --bar, --wipe
 *   dh-reveal-group     numbers the reveals inside it, for a stagger
 *   dh-reveal-content   fades in once its revealing container has landed
 *
 * is rendered with the matching data-reveal, data-reveal-group or
 * data-reveal-content attribute. The browser receives the same markup the
 * hand-written sections had, so the motion code needs nothing new.
 *
 * @param string $html  Rendered block markup.
 * @param array  $block Parsed block.
 * @return string
 */
function demas_theme_reveal_attributes( $html, $block ) {
	$class = (string) ( $block['attrs']['className'] ?? '' );

	if ( '' === $html || false === strpos( $class, 'dh-reveal' ) ) {
		return $html;
	}

	$tags = new WP_HTML_Tag_Processor( $html );

	if ( ! $tags->next_tag() ) {
		return $html;
	}

	foreach ( $tags->class_list() as $name ) {
		if ( 'dh-reveal-group' === $name ) {
			$tags->set_attribute( 'data-reveal-group', true );
		} elseif ( 'dh-reveal-content' === $name ) {
			$tags->set_attribute( 'data-reveal-content', true );
		} elseif ( preg_match( '/^dh-reveal--([a-z]+)$/', $name, $match ) ) {
			$tags->set_attribute( 'data-reveal', $match[1] );
		}
	}

	return $tags->get_updated_html();
}
add_filter( 'render_block', 'demas_theme_reveal_attributes', 10, 2 );
