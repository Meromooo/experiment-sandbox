<?php
/**
 * Assign Demas house references (SKUs) to the products that have none, and
 * file the uncategorised ones under their real category.
 *
 * Input: house-skus.csv next to this file — generated 2026-09-17 from the
 * sandbox Store API and reviewed by hand. Columns:
 *
 *   product_id, sku, add_category_ids, segment, name, note
 *
 * Why these numbers look the way they do: every product that already has a
 * SKU carries a manufacturer's number (TCN-WT-032, HUN-ROT-I90). The blanks
 * are generic goods with no maker's number, so Demas issues its own in the
 * same shape — DMS-<segment>-<3-digit sequence>. DMS- in the first slot says
 * "house reference" the way TCN- says "Tecnocooling". A product with a real
 * manufacturer number must never be overwritten here, and never is: any row
 * whose product already has a SKU is skipped.
 *
 * Rows with an empty sku column are flagged duplicates (see the note column)
 * and only get their category, so they show up beside the original for the
 * store owner to delete.
 *
 * Idempotent: re-running changes nothing already applied. Numbers are never
 * reused — if a product is deleted later, its number retires with it.
 *
 * Run from the SANDBOX WordPress root. Dry run first (the default):
 *
 *   cd ~/domains/cornflowerblue-fish-235112.hostingersite.com/public_html
 *   wp eval-file wp-content/themes/demas-theme/tools/assign-house-skus.php
 *
 * Then, once the preview reads right:
 *
 *   wp eval-file wp-content/themes/demas-theme/tools/assign-house-skus.php write
 *
 * NOT from ~/domains/demas-group.com/public_html. The guard below refuses to
 * run anywhere but the sandbox; do not remove it.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "This file is run with wp eval-file, not loaded by the theme.\n" );
}

$demas_sandbox_host = 'cornflowerblue-fish-235112.hostingersite.com';
$demas_site_url     = (string) get_option( 'siteurl' );

if ( false === strpos( $demas_site_url, $demas_sandbox_host ) ) {
	WP_CLI::error( "REFUSING TO RUN — wrong site.\n  current site : {$demas_site_url}\n  expected     : https://{$demas_sandbox_host}\nThis script writes product SKUs and categories and must never touch the live site." );
}

if ( ! function_exists( 'wc_get_product' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

$demas_write = isset( $args[0] ) && 'write' === $args[0];
$demas_csv   = __DIR__ . '/house-skus.csv';

if ( ! is_readable( $demas_csv ) ) {
	WP_CLI::error( "Cannot read {$demas_csv}" );
}

WP_CLI::log( 'Site: ' . $demas_site_url );
WP_CLI::log( $demas_write ? 'Mode: WRITE' : 'Mode: dry run (pass "write" to apply)' );
WP_CLI::log( '' );

$demas_handle = fopen( $demas_csv, 'r' );
$demas_header = fgetcsv( $demas_handle );
$demas_stats  = array(
	'sku_set'      => 0,
	'sku_kept'     => 0,
	'sku_conflict' => 0,
	'cat_added'    => 0,
	'missing'      => 0,
	'rows'         => 0,
);

while ( ( $demas_row = fgetcsv( $demas_handle ) ) !== false ) {
	$demas_stats['rows']++;
	$demas_r = array_combine( $demas_header, $demas_row );

	$demas_id      = (int) $demas_r['product_id'];
	$demas_sku     = trim( (string) $demas_r['sku'] );
	$demas_cats    = array_filter( array_map( 'intval', explode( '|', (string) $demas_r['add_category_ids'] ) ) );
	$demas_product = wc_get_product( $demas_id );

	if ( ! $demas_product ) {
		$demas_stats['missing']++;
		WP_CLI::warning( "#{$demas_id} not found — skipped." );
		continue;
	}

	$demas_changed = false;
	$demas_label   = str_pad( "#{$demas_id}", 6 ) . ' ' . mb_substr( $demas_product->get_name(), 0, 44 );

	/* SKU ----------------------------------------------------------------- */

	if ( '' !== $demas_sku ) {
		$demas_current = trim( (string) $demas_product->get_sku() );

		if ( '' !== $demas_current ) {
			// Already numbered — possibly a real manufacturer SKU added since. Never overwrite.
			$demas_stats['sku_kept']++;
			WP_CLI::log( "  keep  {$demas_label}  has {$demas_current}" );
		} else {
			$demas_owner = wc_get_product_id_by_sku( $demas_sku );

			if ( $demas_owner && $demas_owner !== $demas_id ) {
				$demas_stats['sku_conflict']++;
				WP_CLI::warning( "  CONFLICT {$demas_label}: {$demas_sku} already belongs to #{$demas_owner}" );
			} else {
				$demas_stats['sku_set']++;
				WP_CLI::log( "  sku   {$demas_label}  <- {$demas_sku}" );

				if ( $demas_write ) {
					$demas_product->set_sku( $demas_sku );
					$demas_product->update_meta_data( '_demas_sku_assigned', '1' );
					$demas_changed = true;
				}
			}
		}
	}

	/* Categories ---------------------------------------------------------- */

	if ( $demas_cats ) {
		$demas_have = array_map( 'intval', $demas_product->get_category_ids() );
		$demas_new  = array_values( array_diff( $demas_cats, $demas_have ) );

		if ( $demas_new ) {
			$demas_stats['cat_added']++;
			$demas_note = '' !== (string) $demas_r['note'] ? '  [' . $demas_r['note'] . ']' : '';
			WP_CLI::log( "  cat   {$demas_label}  += " . implode( ',', $demas_new ) . $demas_note );

			if ( $demas_write ) {
				$demas_product->set_category_ids( array_unique( array_merge( $demas_have, $demas_new ) ) );
				$demas_changed = true;
			}
		}
	}

	if ( $demas_changed ) {
		$demas_product->save();
	}
}

fclose( $demas_handle );

WP_CLI::log( '' );
WP_CLI::log( sprintf(
	'%d rows: %d SKUs %s, %d already numbered (kept), %d conflicts, %d categorised, %d missing.',
	$demas_stats['rows'],
	$demas_stats['sku_set'],
	$demas_write ? 'written' : 'to write',
	$demas_stats['sku_kept'],
	$demas_stats['sku_conflict'],
	$demas_stats['cat_added'],
	$demas_stats['missing']
) );

if ( $demas_write ) {
	// The lookup table drives SKU sort and the Store API; make sure it is current.
	if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
		wc_update_product_lookup_tables();
	}
	WP_CLI::success( 'Done. Clear the page cache before checking the catalogue.' );
} else {
	WP_CLI::success( 'Dry run complete — nothing written.' );
}
