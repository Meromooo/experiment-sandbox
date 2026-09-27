<?php
/**
 * The site footer (AMM-144): registers its two server-rendered blocks.
 *
 * The footer is drawn as an engineering drawing's title block — a ruled
 * panel of labelled cells — and lives in patterns/footer.php, which
 * parts/footer.html places. Its copy (company details, links) is in that
 * pattern so an editor can change it; the parts that are data are blocks:
 *
 *  - demas-theme/catalogue-index — the product groups and subcategories,
 *    from the same list as the mega menu (inc/navigation.php);
 *  - demas-theme/branch-plan — the fifteen branches (inc/branches.php) and
 *    the key plan that plots them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	foreach ( array( 'catalogue-index', 'branch-plan' ) as $block ) {
		$build_path = DEMAS_THEME_DIR . '/build/' . $block;

		if ( file_exists( $build_path . '/block.json' ) ) {
			register_block_type( $build_path );
		}
	}
} );
