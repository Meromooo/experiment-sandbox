<?php
/**
 * The Contact page (AMM-169): registers its server-rendered blocks.
 *
 * The page is a page on the "Designed page" template whose content is the
 * contact pattern (patterns/contact.php): core blocks for its copy, and two
 * theme blocks:
 *
 *  - demas-theme/branch-finder — the fifteen branches as a list, the chosen
 *    one's card (who answers there, address, hours, directions) and the
 *    branches drawn as an irrigation layout plan. All of it comes from the
 *    branch list in inc/branches.php; a photo per branch is the block's one
 *    setting, chosen in its sidebar;
 *  - demas-theme/request-form — the request: what the buyer needs, the
 *    branch (kept in step with the finder), their details and message, and
 *    their quote list. Front end only: AMM-140 connects sending; until then
 *    Send says so, and nothing typed leaves the browser.
 *
 * It lives at /contact-us/, the live site's URL. That page was retired while
 * it was the cloned page publishing staff email addresses, and comes back
 * once it uses the Designed page template (inc/pages.php).
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	function () {
		foreach ( array( 'branch-finder', 'request-form' ) as $block ) {
			$build_path = DEMAS_THEME_DIR . '/build/' . $block;

			if ( file_exists( $build_path . '/block.json' ) ) {
				register_block_type( $build_path );
			}
		}
	}
);

// The sidebar lists every branch for its photo; the editor script reads them here.
add_action(
	'enqueue_block_editor_assets',
	function () {
		if ( ! function_exists( 'demas_theme_get_branches' ) ) {
			return;
		}

		$branches = array();
		foreach ( demas_theme_get_branches() as $code => $branch ) {
			$branches[] = array(
				'code' => (string) $code,
				'city' => (string) $branch['city'],
			);
		}

		wp_add_inline_script(
			generate_block_asset_handle( 'demas-theme/branch-finder', 'editorScript' ),
			'window.demasThemeBranches = ' . wp_json_encode( $branches ) . ';',
			'before'
		);
	}
);
