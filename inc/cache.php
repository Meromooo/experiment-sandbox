<?php
/**
 * Page cache: cleared after every deploy (AMM-164).
 *
 * The sandbox runs the LiteSpeed Cache plugin, which keeps each finished
 * page on the server, and the Hostinger CDN keeps copies of those pages
 * too. A cached page still links the stylesheets and scripts of the deploy
 * it was made under, while the files themselves are replaced in place by the
 * next deploy, so after a deploy old pages could pair with new files.
 *
 * A deploy shows as a newer modification time somewhere in the theme's files
 * (Git rewrites only what changed; a deleted file still touches its folder).
 * The first request PHP runs after one (any page the cache misses: a
 * search, the finder, a 404, the admin) clears every cached page through
 * LiteSpeed's documented hook. Without the plugin this does nothing and
 * stores nothing.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The newest modification time among the theme's files and folders, skipping
 * dot-entries (a host's .git folder changes on every fetch).
 */
function demas_theme_deploy_stamp(): string {
	// Ponytail: ~200 file stats per uncached request (about a millisecond); a
	// stamp written by the deploy job would replace it if the theme grows a lot.
	$folders = new RecursiveCallbackFilterIterator(
		new RecursiveDirectoryIterator( DEMAS_THEME_DIR, FilesystemIterator::SKIP_DOTS ),
		static fn( SplFileInfo $entry ) => '.' !== $entry->getFilename()[0]
	);

	$latest = 0;
	foreach ( new RecursiveIteratorIterator( $folders, RecursiveIteratorIterator::SELF_FIRST ) as $entry ) {
		$latest = max( $latest, $entry->getMTime() );
	}

	return (string) $latest;
}

add_action(
	'wp_loaded',
	static function () {
		if ( ! has_action( 'litespeed_purge_all' ) ) {
			return;
		}

		$stamp = demas_theme_deploy_stamp();

		if ( get_option( 'demas_theme_deploy_stamp' ) === $stamp ) {
			return;
		}

		update_option( 'demas_theme_deploy_stamp', $stamp, false );
		do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache's own documented hook.
	}
);
