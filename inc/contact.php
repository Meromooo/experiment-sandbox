<?php
/**
 * The Contact page (AMM-169): registers its server-rendered blocks.
 *
 * The page is a page on the "Designed page" template whose content is the
 * contact pattern (patterns/contact.php): core blocks for its copy, and two
 * theme blocks:
 *
 *  - demas-theme/branch-finder — the fifteen branches as a list, the chosen
 *    one's card (who answers there, address, hours, directions) and a map of
 *    the Kingdom with each branch's area (AMM-189). All of it comes from the
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

/**
 * Where the site sends anyone who wants to reach Demas: the Contact page,
 * optionally straight to its request form with a branch and a need chosen
 * (e.g. /contact-us/?branch=jed&need=survey#request). Every "contact" link
 * in the theme goes through here — the header, the footer's branch links,
 * the homepage Branch Desk, the closing call to action, the 404 page, an
 * empty search, the header finder and the quote sheet.
 *
 * @param string $branch A branch code from demas_theme_get_branches(), or ''.
 * @param string $need   parts, survey, repair or other, or ''.
 */
function demas_theme_contact_url( string $branch = '', string $need = '' ): string {
	$url  = home_url( '/contact-us/' );
	$args = array_filter(
		array(
			'branch' => sanitize_key( $branch ),
			'need'   => sanitize_key( $need ),
		)
	);

	return $args ? add_query_arg( $args, $url ) . '#request' : $url;
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
