<?php
/**
 * The fifteen Demas branches — one list for everywhere the site names them.
 *
 * Each branch is keyed by the three-letter code the homepage Branch Desk
 * already shows (RIY, JED …). The branch-routed request form (AMM-140) will
 * post one of these codes, and the server-side code → address map lives
 * outside version control; no email address ever belongs in this file.
 *
 * Order is the company's own (the live site's), main branch first. The
 * coordinates are each city's centre, rounded to two decimals — enough for
 * the footer's key plan, which plots positions, not addresses.
 *
 * The person is who answers for that branch, shown on the homepage Branch
 * Desk (src/branch-desk, AMM-153). Names only: staff email addresses never
 * belong in the repo.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every branch, by code.
 *
 * @return array<string, array{city: string, person: string, lat: float, lon: float, main: bool}>
 */
function demas_theme_get_branches(): array {
	return (array) apply_filters(
		'demas_theme_branches',
		array(
			'riy' => array(
				'city'   => __( 'Riyadh', 'demas-theme' ),
				'person' => 'Odai Assolie',
				'lat'    => 24.71,
				'lon'    => 46.68,
				'main'   => true,
			),
			'jed' => array(
				'city'   => __( 'Jeddah', 'demas-theme' ),
				'person' => 'Awni Sadi',
				'lat'    => 21.54,
				'lon'    => 39.17,
				'main'   => false,
			),
			'dam' => array(
				'city'   => __( 'Dammam', 'demas-theme' ),
				'person' => 'Mohammed Hamaideh',
				'lat'    => 26.43,
				'lon'    => 50.10,
				'main'   => false,
			),
			'mad' => array(
				'city'   => __( 'Madinah', 'demas-theme' ),
				'person' => 'Malaz Nabil',
				'lat'    => 24.47,
				'lon'    => 39.61,
				'main'   => false,
			),
			'naj' => array(
				'city'   => __( 'Najran', 'demas-theme' ),
				'person' => 'Osama Manzalgi',
				'lat'    => 17.49,
				'lon'    => 44.13,
				'main'   => false,
			),
			'hai' => array(
				'city'   => __( 'Hail', 'demas-theme' ),
				'person' => 'Osama Safar',
				'lat'    => 27.52,
				'lon'    => 41.69,
				'main'   => false,
			),
			'alk' => array(
				'city'   => __( 'Alkharj', 'demas-theme' ),
				'person' => 'Osama Fayad',
				'lat'    => 24.15,
				'lon'    => 47.31,
				'main'   => false,
			),
			'bur' => array(
				'city'   => __( 'Buraidah', 'demas-theme' ),
				'person' => 'Ward Hakmi',
				'lat'    => 26.33,
				'lon'    => 43.97,
				'main'   => false,
			),
			'una' => array(
				'city'   => __( 'Unaizah', 'demas-theme' ),
				'person' => 'Ahmad Rhayel',
				'lat'    => 26.08,
				'lon'    => 43.99,
				'main'   => false,
			),
			'tai' => array(
				'city'   => __( 'Taif', 'demas-theme' ),
				'person' => 'Mazen Alo',
				'lat'    => 21.27,
				'lon'    => 40.42,
				'main'   => false,
			),
			'zul' => array(
				'city'   => __( 'Zulfi', 'demas-theme' ),
				'person' => 'Ammar Abdullatif',
				'lat'    => 26.30,
				'lon'    => 44.80,
				'main'   => false,
			),
			'tab' => array(
				'city'   => __( 'Tabarjal', 'demas-theme' ),
				'person' => 'Mohannad Dwaghra',
				'lat'    => 30.50,
				'lon'    => 38.22,
				'main'   => false,
			),
			'saj' => array(
				'city'   => __( 'Sajer', 'demas-theme' ),
				'person' => 'Amjad Shura',
				'lat'    => 25.18,
				'lon'    => 44.60,
				'main'   => false,
			),
			'kha' => array(
				'city'   => __( 'Khamis Mushait', 'demas-theme' ),
				'person' => 'Fakhir Alostwani',
				'lat'    => 18.31,
				'lon'    => 42.73,
				'main'   => false,
			),
			'ska' => array(
				'city'   => __( 'Skaka', 'demas-theme' ),
				'person' => 'Mustafa Fshehs',
				'lat'    => 29.97,
				'lon'    => 40.21,
				'main'   => false,
			),
		)
	);
}

/**
 * Where a branch link goes: the homepage Branch Desk with that city selected
 * (assets/js/main.js reads the #branch-xxx hash). Without JavaScript the hash
 * still lands on the city's button in the Desk.
 *
 * @param string $code A branch code from demas_theme_get_branches(), e.g. "jed".
 */
function demas_theme_branch_url( string $code ): string {
	return home_url( '/#branch-' . sanitize_key( $code ) );
}
