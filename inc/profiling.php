<?php
/**
 * TEMPORARY request profiler (AMM-164, step 1). Removed in the next commit.
 *
 * Off unless the URL asks for it: with ?demas-timing=1 on a front-end page,
 * an HTML comment is printed after </html> with
 *
 *  - when each stage of the request started and ended (theme loaded, init,
 *    the main query, the template, wp_head, wp_footer), in milliseconds from
 *    the moment PHP received the request, with the database query count;
 *  - per block type: how many rendered, the time and queries including the
 *    blocks inside it, and the time and queries of the block itself.
 *
 * Without the parameter nothing below the guard is hooked, so normal pages
 * are byte-for-byte unchanged. The comment holds block names and numbers
 * only — nothing about the visitor or the server.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a read-only switch; nothing is changed or stored.
if ( is_admin() || ! isset( $_GET['demas-timing'] ) ) {
	return;
}

$GLOBALS['demas_theme_timing'] = array(
	'marks'  => array(),
	'stack'  => array(),
	'blocks' => array(),
	'trace'  => false,
	'last'   => null,
	'hooks'  => array(),
);

/**
 * Records a stage of the request: time since the request began, and queries so far.
 *
 * While the template renders (template_include end → wp_head start), every
 * hook is traced too: the time from one hook firing to the next is booked to
 * the first. Rough — it includes the caller's own work after the hook — but
 * it names where per-card time goes.
 *
 * @param string $label Stage name.
 */
function demas_theme_timing_mark( $label ) {
	global $demas_theme_timing;

	$demas_theme_timing['marks'][] = array( $label, timer_float() * 1000, get_num_queries() );

	if ( 'template_include end' === $label ) {
		$demas_theme_timing['trace'] = true;
	} elseif ( 'wp_head start' === $label ) {
		demas_theme_timing_hook( 'wp_head' );
		$demas_theme_timing['trace'] = false;
	}
}

/**
 * Books the time since the previous traced hook to that hook.
 *
 * @param string $hook The hook firing now.
 */
function demas_theme_timing_hook( $hook ) {
	global $demas_theme_timing;

	if ( ! $demas_theme_timing['trace'] ) {
		return;
	}

	$now = microtime( true );

	if ( null !== $demas_theme_timing['last'] ) {
		$name = $demas_theme_timing['last'][0];
		$row  = $demas_theme_timing['hooks'][ $name ] ?? array( 0, 0.0 );

		$demas_theme_timing['hooks'][ $name ] = array( $row[0] + 1, $row[1] + ( $now - $demas_theme_timing['last'][1] ) * 1000 );
	}

	$demas_theme_timing['last'] = array( (string) $hook, $now );
}
add_action( 'all', 'demas_theme_timing_hook' );

demas_theme_timing_mark( 'theme loaded' );

/*
 * Each stage is marked as it starts (first callback) and as it ends (last
 * callback). template_include is a filter, so the callback hands its value on.
 */
foreach ( array( 'after_setup_theme', 'init', 'wp_loaded', 'wp', 'template_redirect', 'template_include', 'wp_head', 'wp_footer' ) as $demas_theme_hook ) {
	add_filter(
		$demas_theme_hook,
		function ( $value = null ) use ( $demas_theme_hook ) {
			demas_theme_timing_mark( $demas_theme_hook . ' start' );
			return $value;
		},
		PHP_INT_MIN
	);
	add_filter(
		$demas_theme_hook,
		function ( $value = null ) use ( $demas_theme_hook ) {
			demas_theme_timing_mark( $demas_theme_hook . ' end' );
			return $value;
		},
		PHP_INT_MAX
	);
}
unset( $demas_theme_hook );

/*
 * Block timing. A block that another filter short-circuits never renders, so
 * it is not timed. Last priority on both: the time includes every other
 * filter's work on the block (the Kadence clean-up, the reveal bridge, search
 * highlighting).
 */
add_filter(
	'pre_render_block',
	function ( $pre, $parsed_block ) {
		global $demas_theme_timing;

		if ( null === $pre ) {
			$demas_theme_timing['stack'][] = array(
				'name'     => (string) $parsed_block['blockName'],
				'start'    => microtime( true ),
				'queries'  => get_num_queries(),
				'child_ms' => 0.0,
				'child_q'  => 0,
			);
		}

		return $pre;
	},
	PHP_INT_MAX,
	2
);

add_filter(
	'render_block',
	function ( $block_content, $parsed_block ) {
		global $demas_theme_timing;

		$top = end( $demas_theme_timing['stack'] );

		// A block rendered without pre_render_block (a post template's item wrapper) is not ours.
		if ( ! $top || (string) $parsed_block['blockName'] !== $top['name'] ) {
			return $block_content;
		}

		array_pop( $demas_theme_timing['stack'] );

		$ms      = ( microtime( true ) - $top['start'] ) * 1000;
		$queries = get_num_queries() - $top['queries'];
		$name    = '' === $top['name'] ? '(html between blocks)' : $top['name'];
		$row     = $demas_theme_timing['blocks'][ $name ] ?? array( 0, 0.0, 0.0, 0, 0 );

		$demas_theme_timing['blocks'][ $name ] = array(
			$row[0] + 1,
			$row[1] + $ms,
			$row[2] + $ms - $top['child_ms'],
			$row[3] + $queries,
			$row[4] + $queries - $top['child_q'],
		);

		$parent = array_key_last( $demas_theme_timing['stack'] );
		if ( null !== $parent ) {
			$demas_theme_timing['stack'][ $parent ]['child_ms'] += $ms;
			$demas_theme_timing['stack'][ $parent ]['child_q']  += $queries;
		}

		return $block_content;
	},
	PHP_INT_MAX,
	2
);

add_action(
	'shutdown',
	function () {
		global $demas_theme_timing;

		demas_theme_timing_mark( 'shutdown' );

		$lines = array( 'demas-timing (AMM-164, temporary)', '', sprintf( '%-26s %9s %7s', 'stage', 'ms', 'queries' ) );
		foreach ( $demas_theme_timing['marks'] as $mark ) {
			$lines[] = sprintf( '%-26s %9.1f %7d', $mark[0], $mark[1], $mark[2] );
		}

		$blocks = $demas_theme_timing['blocks'];
		uasort(
			$blocks,
			function ( $a, $b ) {
				return $b[2] <=> $a[2];
			}
		);

		$lines[] = '';
		$lines[] = sprintf( '%-40s %5s %9s %9s %7s %7s', 'block (by own time)', 'count', 'incl ms', 'own ms', 'incl q', 'own q' );
		foreach ( $blocks as $name => $row ) {
			$lines[] = sprintf( '%-40s %5d %9.1f %9.1f %7d %7d', $name, $row[0], $row[1], $row[2], $row[3], $row[4] );
		}

		$hooks = $demas_theme_timing['hooks'];
		uasort(
			$hooks,
			function ( $a, $b ) {
				return $b[1] <=> $a[1];
			}
		);

		$lines[] = '';
		$lines[] = sprintf( '%-60s %6s %9s', 'hook during the template (top 40)', 'fired', 'ms after' );
		foreach ( array_slice( $hooks, 0, 40, true ) as $name => $row ) {
			$lines[] = sprintf( '%-60s %6d %9.1f', $name, $row[0], $row[1] );
		}

		echo "\n<!--\n" . esc_html( implode( "\n", $lines ) ) . "\n-->\n";
	},
	PHP_INT_MAX
);
