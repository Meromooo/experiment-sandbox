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
 * The map link, address and opening hours (AMM-169, the Contact page's
 * branch cards) come from each branch's public Google Maps listing — the
 * links the live Contact Us page carries — read on 2026-10-01, for Demas to
 * confirm. The listings' phone numbers are deliberately not copied. Gaps as
 * listed: Taif's link is a bare map pin (no address, no hours); Tabarjal and
 * Skaka list no hours. Two listings were rounded: Alkharj's Sunday close
 * (20:01) and Zulfi's midday close (12:02); Alkharj lists Tuesday as one
 * unbroken 8–20 shift. Addresses use the site's spelling of each city.
 *
 * Hours are written the way a person reads them, one group of days per
 * clause: "Sat-Wed 08:00-13:00 16:00-19:00; Thu 08:00-15:00; Fri closed".
 * An empty string means not listed. demas_theme_branch_hours() reads them.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every branch, by code.
 *
 * @return array<string, array{city: string, person: string, lat: float, lon: float, main: bool, map: string, address: string, hours: string}>
 */
function demas_theme_get_branches(): array {
	return (array) apply_filters(
		'demas_theme_branches',
		array(
			'riy' => array(
				'city'    => __( 'Riyadh', 'demas-theme' ),
				'person'  => 'Odai Assolie',
				'lat'     => 24.71,
				'lon'     => 46.68,
				'main'    => true,
				'map'     => 'https://maps.app.goo.gl/53i9RdD1yu7EiJCe9',
				'address' => '8018 King Abdulaziz Rd, As Sulimaniyah, Riyadh 12245',
				'hours'   => 'Sat-Wed 08:00-13:00 16:00-19:00; Thu 08:00-15:00; Fri closed',
			),
			'jed' => array(
				'city'    => __( 'Jeddah', 'demas-theme' ),
				'person'  => 'Awni Sadi',
				'lat'     => 21.54,
				'lon'     => 39.17,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/RKQ78tPgS1ye5ZPW8',
				'address' => 'Al Baladiyah St, Aziziyah, Jeddah 23334',
				'hours'   => 'Sat-Thu 08:30-13:30 16:30-20:00; Fri closed',
			),
			'dam' => array(
				'city'    => __( 'Dammam', 'demas-theme' ),
				'person'  => 'Mohammed Hamaideh',
				'lat'     => 26.43,
				'lon'     => 50.10,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/r2EMMBskzF5EEW4g9',
				'address' => 'Prince Nayef Bin Abdulaziz Rd, Al Athir, Dammam 32248',
				'hours'   => 'Sat 08:00-12:00 16:00-19:00; Sun-Thu 08:00-20:00; Fri closed',
			),
			'mad' => array(
				'city'    => __( 'Madinah', 'demas-theme' ),
				'person'  => 'Malaz Nabil',
				'lat'     => 24.47,
				'lon'     => 39.61,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/R93wLScXkqQo26vi6',
				'address' => 'Al Barakah, Madinah 42333',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'naj' => array(
				'city'    => __( 'Najran', 'demas-theme' ),
				'person'  => 'Osama Manzalgi',
				'lat'     => 17.49,
				'lon'     => 44.13,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/nn7DCPSwmPdvYHFf7',
				'address' => 'King Abdulaziz Rd, Najran 66234',
				'hours'   => 'Sat-Wed 08:00-12:00 16:00-20:00; Thu 08:00-16:00; Fri closed',
			),
			'hai' => array(
				'city'    => __( 'Hail', 'demas-theme' ),
				'person'  => 'Osama Safar',
				'lat'     => 27.52,
				'lon'     => 41.69,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/wdX6Gz8i9rp5CHnY8',
				'address' => 'King Abdulaziz Rd, Alkhamashiyyah, Hail 55422',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'alk' => array(
				'city'    => __( 'Alkharj', 'demas-theme' ),
				'person'  => 'Osama Fayad',
				'lat'     => 24.15,
				'lon'     => 47.31,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/SbFw2ZudLy9Pqzaf7',
				'address' => 'Al Safa, Alkharj 16259',
				'hours'   => 'Sat-Mon 08:00-12:00 16:00-20:00; Tue 08:00-20:00; Wed-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'bur' => array(
				'city'    => __( 'Buraidah', 'demas-theme' ),
				'person'  => 'Ward Hakmi',
				'lat'     => 26.33,
				'lon'     => 43.97,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/zbS6A7KYnU3z2XC48',
				'address' => 'King Abdulaziz Rd, Sultanah, Buraidah 52375',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-19:00; Fri closed',
			),
			'una' => array(
				'city'    => __( 'Unaizah', 'demas-theme' ),
				'person'  => 'Ahmad Rhayel',
				'lat'     => 26.08,
				'lon'     => 43.99,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/ryEh1YrN7gqqqsJ39',
				'address' => 'King Abdulaziz Rd, Sultanah, Unaizah 56434',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-21:00; Fri closed',
			),
			'tai' => array(
				'city'    => __( 'Taif', 'demas-theme' ),
				'person'  => 'Mazen Alo',
				'lat'     => 21.27,
				'lon'     => 40.42,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/6fJYNwtM5NtnzLTw5',
				'address' => '',
				'hours'   => '',
			),
			'zul' => array(
				'city'    => __( 'Zulfi', 'demas-theme' ),
				'person'  => 'Ammar Abdullatif',
				'lat'     => 26.30,
				'lon'     => 44.80,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/7gi5smb2sPzR1baN9',
				'address' => 'King Fahd Rd, Al Faisaliyah, Zulfi 15931',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'tab' => array(
				'city'    => __( 'Tabarjal', 'demas-theme' ),
				'person'  => 'Mohannad Dwaghra',
				'lat'     => 30.50,
				'lon'     => 38.22,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/G2HA6oaV2WbXHHxi7',
				'address' => 'Wholesale Market, Tabarjal 74766',
				'hours'   => '',
			),
			'saj' => array(
				'city'    => __( 'Sajer', 'demas-theme' ),
				'person'  => 'Amjad Shura',
				'lat'     => 25.18,
				'lon'     => 44.60,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/y9FiVASLoMWmbv2F6',
				'address' => 'King Abdulaziz Rd, Sajer 10235',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'kha' => array(
				'city'    => __( 'Khamis Mushait', 'demas-theme' ),
				'person'  => 'Fakhir Alostwani',
				'lat'     => 18.31,
				'lon'     => 42.73,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/H1WTxKcMKNSoG2rf6',
				'address' => 'Omar Bin Al Khattab St, Al Jazirah, Khamis Mushait 62458',
				'hours'   => 'Sat-Thu 08:00-12:00 16:00-20:00; Fri closed',
			),
			'ska' => array(
				'city'    => __( 'Skaka', 'demas-theme' ),
				'person'  => 'Mustafa Fshehs',
				'lat'     => 29.97,
				'lon'     => 40.21,
				'main'    => false,
				'map'     => 'https://maps.app.goo.gl/m3yrRnQ8oYjp85FU7',
				'address' => 'King Fahd Bin Abdulaziz Rd, Al Sina'iyah, Skaka 72341',
				'hours'   => '',
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

/**
 * A branch's opening hours, read from the written form in its "hours" field.
 *
 * Returns the lines to show, one per clause as written ("Sat–Wed",
 * "8:00–13:00 · 16:00–19:00"), and the week as minutes from midnight per
 * day, keyed 0 = Sunday … 6 = Saturday like JavaScript's getDay(), for the
 * "Open now" status the Contact page works out in the browser. A day that is
 * not written down is not in the week; a closed day is an empty list.
 *
 * @param string $spec The hours as written, e.g. "Sat-Thu 08:00-12:00 16:00-20:00; Fri closed".
 * @return array{lines: list<array{days: string, times: string}>, week: array<int, list<array{0: int, 1: int}>>}
 */
function demas_theme_branch_hours( string $spec ): array {
	$order = array( 'sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri' );
	$index = array(
		'sun' => 0,
		'mon' => 1,
		'tue' => 2,
		'wed' => 3,
		'thu' => 4,
		'fri' => 5,
		'sat' => 6,
	);
	$names = array(
		'sat' => _x( 'Sat', 'short weekday', 'demas-theme' ),
		'sun' => _x( 'Sun', 'short weekday', 'demas-theme' ),
		'mon' => _x( 'Mon', 'short weekday', 'demas-theme' ),
		'tue' => _x( 'Tue', 'short weekday', 'demas-theme' ),
		'wed' => _x( 'Wed', 'short weekday', 'demas-theme' ),
		'thu' => _x( 'Thu', 'short weekday', 'demas-theme' ),
		'fri' => _x( 'Fri', 'short weekday', 'demas-theme' ),
	);
	$clock = static function ( int $minutes ): string {
		return sprintf( '%d:%02d', intdiv( $minutes, 60 ), $minutes % 60 );
	};
	$lines = array();
	$week  = array();

	foreach ( array_filter( array_map( 'trim', explode( ';', $spec ) ) ) as $clause ) {
		$parts = preg_split( '/\s+/', $clause );
		$days  = explode( '-', strtolower( (string) array_shift( $parts ) ) );
		$from  = array_search( $days[0], $order, true );
		$to    = array_search( $days[1] ?? $days[0], $order, true );

		if ( false === $from || false === $to || $to < $from ) {
			continue;
		}

		$ranges = array();
		foreach ( $parts as $part ) {
			if ( preg_match( '/^(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})$/', $part, $time ) ) {
				$ranges[] = array( (int) $time[1] * 60 + (int) $time[2], (int) $time[3] * 60 + (int) $time[4] );
			}
		}

		for ( $day = $from; $day <= $to; $day++ ) {
			$week[ $index[ $order[ $day ] ] ] = $ranges;
		}

		$lines[] = array(
			'days'  => $names[ $order[ $from ] ] . ( $to > $from ? '–' . $names[ $order[ $to ] ] : '' ),
			'times' => $ranges
				? implode( ' · ', array_map( static fn( $range ) => $clock( $range[0] ) . '–' . $clock( $range[1] ), $ranges ) )
				: __( 'Closed', 'demas-theme' ),
		);
	}

	return array(
		'lines' => $lines,
		'week'  => $week,
	);
}

/**
 * Where a branch sits on the theme's plans of the Kingdom: the footer's key
 * plan and the Contact page's layout plan draw from the same projection, so
 * the two never disagree.
 *
 * Equirectangular, longitude scaled by cos(24°) so the Kingdom keeps its
 * proportions at its middle latitude; 20 units per degree of latitude, with
 * 36°E, 32°N at the origin.
 *
 * @param float $lat Latitude, degrees north.
 * @param float $lon Longitude, degrees east.
 * @return array{0: float, 1: float} x and y in plan units.
 */
function demas_theme_branch_plan_point( float $lat, float $lon ): array {
	return array(
		round( ( $lon - 36 ) * 20 * cos( deg2rad( 24 ) ), 1 ),
		round( ( 32 - $lat ) * 20, 1 ),
	);
}
