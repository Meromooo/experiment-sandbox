<?php
/**
 * Nav menu registration and the mega-menu's category icon set.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'demas-theme' ),
		'footer'  => __( 'Footer Menu', 'demas-theme' ),
	) );
} );

add_action( 'init', function () {
	$build_path = DEMAS_THEME_DIR . '/build/mega-menu';

	if ( file_exists( $build_path . '/block.json' ) ) {
		register_block_type( $build_path );
	}
} );

/**
 * Inline icon for a product_cat slug, drawn as an engineering schematic rather
 * than a generic pictogram: valves use the apex-to-apex gate-valve symbol,
 * dosing pumps the circle-and-triangle pump symbol, cutting tools a rhombic
 * turning insert. Uniform 1.5px stroke, squared joins — the same orthogonal
 * grammar as the Demas monogram.
 *
 * Drawn in-house from primitives (no icon library, no plugin) so the set can
 * stay consistent as categories change. Keys are the live-site subcategory
 * slugs; see demas-mega-menu-content-spec.md.
 *
 * @param string $slug product_cat slug.
 * @return string Inline SVG markup. Safe to echo: static author-controlled markup.
 */
function demas_theme_get_category_icon( $slug ) {
	static $paths = array(

		/* Irrigation Products ------------------------------------------- */

		// Pipe run with a union collar.
		'pipes'
			=> '<path d="M2 9h20M2 15h20"/><path d="M14 7.5v9M17 7.5v9"/>',

		// Elbow, drawn as two pipe walls turning through 90 degrees.
		'fittings'
			=> '<path d="M3.5 21.5V8.5a5 5 0 0 1 5-5h13"/><path d="M9.5 21.5v-6a2 2 0 0 1 2-2h10"/>',

		// Funnel with a screen line across the throat.
		'filtration'
			=> '<path d="M3 4h18l-7 8v7l-4 2v-9L3 4Z"/><path d="M7.5 8h9"/>',

		// Hex nut with a bore — loose hardware.
		'cp-accessories'
			=> '<path d="M12 2.5 20 7v10l-8 4.5L4 17V7l8-4.5Z"/><circle cx="12" cy="12" r="3.5"/>',

		// Electrofusion coupler: body, two terminal pins, heating coil.
		'electro-fusion-fittings'
			=> '<rect x="2.5" y="8.5" width="19" height="7" rx="2"/><path d="M8 8.5V5M16 8.5V5"/>'
			   . '<path d="M6 12h2l1.4-2 1.4 4 1.4-4 1.4 4 1.4-2H18"/>',

		/* Landscape ------------------------------------------------------ */

		// Rotor head on a riser, throwing two arcs.
		'rotors'
			=> '<path d="M12 21.5v-4"/><circle cx="12" cy="15.5" r="1.8"/>'
			   . '<path d="M5 13a9 9 0 0 1 14 0"/><path d="M8.5 9.2a5 5 0 0 1 7 0"/>',

		// Controller enclosure: dial left, programme lines right.
		'controllers'
			=> '<rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="9" cy="12" r="3"/>'
			   . '<path d="M9 12v-2"/><path d="M15 9h3M15 12h3M15 15h3"/>',

		// Gate valve, ISO symbol: two triangles apex to apex, stem, handwheel.
		'landscape-valves'
			=> '<path d="M4 7v10l8-5-8-5Z"/><path d="M20 7v10l-8-5 8-5Z"/><path d="M12 12V6"/><path d="M8 5.5h8"/>',

		// Valve box in plan: body, lid seam, pull slot.
		'valve-boxes-fittings'
			=> '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/><path d="M10 8h4"/>',

		/* Fog Systems ---------------------------------------------------- */

		// Pump, ISO symbol: circle with flow triangle.
		'controllers-dosingpumps-electromagneticvalves'
			=> '<circle cx="12" cy="12" r="8"/><path d="M9.5 8l7 4-7 4V8Z"/>',

		// Tee junction — run with a branch rising from it.
		'tecnocooling-fittings'
			=> '<path d="M2 16.5h20"/><path d="M2 10.5h6M16 10.5h6"/><path d="M8 10.5V4.5M16 10.5V4.5"/>',

		// Nozzle body over a mist cone.
		'nozzles-and-extensions'
			=> '<path d="M10 3.5h4v4l-2 3-2-3v-4Z"/>'
			   . '<path d="M12 12.5v1.5M8.8 15v1.4M15.2 15v1.4M6.4 18.4v1.6M12 17.6v1.6M17.6 18.4v1.6"/>',

		// Treated water: droplet with a process band.
		'water-treatment'
			=> '<path d="M12 3s6 6.4 6 10.4a6 6 0 0 1-12 0C6 9.4 12 3 12 3Z"/><path d="M7 14h10"/>',

		/* Industrial Tool Services --------------------------------------- */

		// Bandsaw blade: band with a toothed edge.
		'band-saw-accessories'
			=> '<path d="M2.5 6.5h19v6h-19z"/><path d="M2.5 12.5l2.4 3 2.4-3 2.4 3 2.4-3 2.4 3 2.4-3 2.4 3 2.4-3"/>',

		// Rhombic turning insert with its clamping bore.
		'cutting-tools'
			=> '<path d="M12 2.5 21.5 12 12 21.5 2.5 12 12 2.5Z"/><circle cx="12" cy="12" r="2.2"/>',

		// Electrode striking an arc.
		'welding-machines'
			=> '<path d="M3.5 3.5 12 12"/><path d="M13 11.5l-3.2 4.6h3.6L10.6 21.5"/>'
			   . '<path d="M17.5 8.5 20.5 5.5M18 12.5h3.5M16.5 16l2.6 2.6"/>',

		// Horseshoe magnet with pole faces.
		'magnetic-drills'
			=> '<path d="M4 21v-9a8 8 0 0 1 16 0v9"/><path d="M9 21v-9a3 3 0 0 1 6 0v9"/><path d="M4 17h5M15 17h5"/>',

		/* Fallbacks ------------------------------------------------------ */

		// Outbound link.
		'__external'
			=> '<path d="M7 17 17 7"/><path d="M8.5 7H17v8.5"/>',

		// Unmapped category: a plain plan-view node.
		'__default'
			=> '<rect x="4.5" y="4.5" width="15" height="15" rx="2"/><path d="M4.5 12h15M12 4.5v15"/>',
	);

	$key = isset( $paths[ $slug ] ) ? $slug : '__default';

	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"'
		. ' fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"'
		. ' stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. $paths[ $key ]
		. '</svg>';
}
