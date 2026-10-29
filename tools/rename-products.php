<?php
/**
 * Give products the names Demas chose, so products that share a name can be
 * told apart (AMM-150).
 *
 * Input: product-names.csv next to this file, made from the spreadsheet Demas
 * filled in ("AMM-150 product names.xlsx", column "Final name (Demas)").
 * Columns:
 *
 *   product_id, sku, current_name, new_name
 *
 * A row is applied only when the product still has current_name. If someone
 * renamed it in the admin since the sheet was made, the row is skipped and
 * reported, so a newer edit is never overwritten. Rows with an empty new_name
 * are skipped. A new_name with an em or en dash is refused: product names are
 * visible copy, and the site's copy rules ban both.
 *
 * Only the name changes. The URL slug, SKU, categories and description stay
 * as they are, so every product URL survives (the cutover, AMM-158, depends
 * on that).
 *
 * Idempotent: a row already applied reads as "already named" on a re-run.
 *
 * Run from the SANDBOX WordPress root. Dry run first (the default):
 *
 *   cd ~/domains/cornflowerblue-fish-235112.hostingersite.com/public_html
 *   wp eval-file ~/rename-products.php
 *
 * Then, once the preview reads right:
 *
 *   wp eval-file ~/rename-products.php write
 *
 * tools/ is not deployed (AMM-173): upload this file and product-names.csv
 * together, outside the theme folder (the home folder above), and run that
 * copy. NOT from ~/domains/demas-group.com/public_html. The guard below
 * refuses to run anywhere but the sandbox; do not remove it.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "This file is run with wp eval-file, not loaded by the theme.\n" );
}

$demas_sandbox_host = 'cornflowerblue-fish-235112.hostingersite.com';
$demas_site_url     = (string) get_option( 'siteurl' );

if ( false === strpos( $demas_site_url, $demas_sandbox_host ) ) {
	WP_CLI::error( "REFUSING TO RUN — wrong site.\n  current site : {$demas_site_url}\n  expected     : https://{$demas_sandbox_host}\nThis script writes product names and must never touch the live site." );
}

if ( ! function_exists( 'wc_get_product' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$demas_write = isset( $args[0] ) && 'write' === $args[0];
$demas_csv   = __DIR__ . '/product-names.csv';

if ( ! is_readable( $demas_csv ) ) {
	WP_CLI::error( "Cannot read {$demas_csv} (it is made from Demas's filled-in sheet; upload it next to this file)." );
}

/**
 * Compare names as a person reads them: entities decoded (&#8243; is ″),
 * spaces collapsed.
 *
 * @param string $name A product name, as stored or as typed in the sheet.
 * @return string
 */
function demas_rename_normalise( $name ) {
	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( (string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
}

WP_CLI::log( 'Site: ' . $demas_site_url );
WP_CLI::log( $demas_write ? 'Mode: WRITE' : 'Mode: dry run (pass "write" to apply)' );
WP_CLI::log( '' );

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- a WP-CLI script reading its own CSV; WP_Filesystem adds nothing here.
$demas_handle = fopen( $demas_csv, 'r' );
$demas_header = fgetcsv( $demas_handle );
$demas_stats  = array(
	'renamed' => 0,
	'already' => 0,
	'changed' => 0,
	'refused' => 0,
	'empty'   => 0,
	'missing' => 0,
	'rows'    => 0,
);

// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition -- the standard fgetcsv() loop.
while ( ( $demas_row = fgetcsv( $demas_handle ) ) !== false ) {
	++$demas_stats['rows'];
	$demas_r = array_combine( $demas_header, $demas_row );

	$demas_id      = (int) $demas_r['product_id'];
	$demas_from    = demas_rename_normalise( $demas_r['current_name'] );
	$demas_to      = demas_rename_normalise( $demas_r['new_name'] );
	$demas_product = wc_get_product( $demas_id );

	if ( '' === $demas_to ) {
		++$demas_stats['empty'];
		continue;
	}

	if ( ! $demas_product ) {
		++$demas_stats['missing'];
		WP_CLI::warning( "#{$demas_id} not found — skipped." );
		continue;
	}

	$demas_now   = demas_rename_normalise( $demas_product->get_name() );
	$demas_label = str_pad( "#{$demas_id}", 6 ) . ' ' . str_pad( (string) $demas_product->get_sku(), 14 );

	if ( $demas_now === $demas_to ) {
		++$demas_stats['already'];
		WP_CLI::log( "  done  {$demas_label} already named \"{$demas_to}\"" );
		continue;
	}

	if ( $demas_now !== $demas_from ) {
		++$demas_stats['changed'];
		WP_CLI::warning( "  CHANGED {$demas_label} is now \"{$demas_now}\", the sheet expected \"{$demas_from}\" — skipped." );
		continue;
	}

	if ( preg_match( '/[\x{2013}\x{2014}]/u', $demas_to ) ) {
		++$demas_stats['refused'];
		WP_CLI::warning( "  DASH  {$demas_label} \"{$demas_to}\" has an em or en dash — skipped. Use a comma or \"to\"." );
		continue;
	}

	++$demas_stats['renamed'];
	WP_CLI::log( "  name  {$demas_label} \"{$demas_now}\"" );
	WP_CLI::log( "        {$demas_label} -> \"{$demas_to}\"" );

	if ( $demas_write ) {
		$demas_product->set_name( $demas_to );
		$demas_product->save();
	}
}

fclose( $demas_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- pairs with the fopen() above.

WP_CLI::log( '' );
WP_CLI::log(
	sprintf(
		'%d rows: %d %s, %d already named, %d changed since the sheet (skipped), %d refused (dash), %d with no new name, %d missing.',
		$demas_stats['rows'],
		$demas_stats['renamed'],
		$demas_write ? 'renamed' : 'to rename',
		$demas_stats['already'],
		$demas_stats['changed'],
		$demas_stats['refused'],
		$demas_stats['empty'],
		$demas_stats['missing']
	)
);

if ( $demas_write ) {
	WP_CLI::success( 'Done. Clear the page cache before checking the catalogue.' );
} else {
	WP_CLI::success( 'Dry run complete — nothing written.' );
}
