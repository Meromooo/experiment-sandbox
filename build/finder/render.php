<?php
/**
 * Server-rendered markup for the Jump to Part block (AMM-143 / AMM-142).
 *
 * In the header on every page:
 *
 *  - the control. Two of them, identical to look at: a link to the
 *    catalogue (where the toolbar's search field works without JavaScript),
 *    shown until view.ts is running, and a button that opens the finder,
 *    shown once it is;
 *  - the finder: a native modal <dialog>, like the quote list, so the
 *    browser keeps focus inside, makes the page behind inert, closes on
 *    Escape and returns focus to the control. Inside it, the same search
 *    form as everywhere else (a GET to /?s=), enhanced by view.ts into an
 *    ARIA combobox whose suggestions come from the demas-theme/v1/find
 *    route (inc/search.php).
 *
 * Before anything is typed the finder offers the catalogue's groups. The
 * suggestion rows are drawn by view.ts; every string it needs is passed
 * here so it goes through translation.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demas_catalogue = function_exists( 'demas_theme_catalogue_url' ) ? demas_theme_catalogue_url() : home_url( '/' );
$demas_groups    = function_exists( 'demas_theme_get_catalogue_columns' ) ? demas_theme_get_catalogue_columns() : array();
$demas_label     = __( 'Search parts or part numbers', 'demas-theme' );

$demas_strings = array(
	'categories' => __( 'Categories', 'demas-theme' ),
	'parts'      => __( 'Parts', 'demas-theme' ),
	/* translators: %s: the search query. */
	'none'       => __( 'Nothing matches “%s”.', 'demas-theme' ),
	'ask'        => __( 'Ask a branch to source it', 'demas-theme' ),
	'error'      => __( 'Suggestions are unavailable right now. Press Enter to search the catalogue.', 'demas-theme' ),
	/* translators: %s: number of results. */
	'all'        => __( 'See all %s results', 'demas-theme' ),
	'allOne'     => __( 'See the result', 'demas-theme' ),
	/* translators: %s: number of results. */
	'status'     => __( '%s results. Use the arrow keys to choose one.', 'demas-theme' ),
	'statusOne'  => __( '1 result. Use the arrow keys to choose it.', 'demas-theme' ),
	'exact'      => __( 'Exact match', 'demas-theme' ),
);

$demas_search_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg>';

$demas_wrapper = get_block_wrapper_attributes(
	array(
		'class'         => 'dh-finder',
		'data-endpoint' => esc_url_raw( rest_url( 'demas-theme/v1/find' ) ),
		'data-branch'   => esc_url_raw( home_url( '/#find-your-branch' ) ),
		'data-strings'  => wp_json_encode( $demas_strings ),
	)
);
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<a class="dh-finder__trigger dh-finder__trigger--fallback" href="<?php echo esc_url( $demas_catalogue ); ?>">
		<?php echo $demas_search_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?>
		<span class="dh-finder__label"><?php echo esc_html( $demas_label ); ?></span>
	</a>

	<button
		type="button"
		class="dh-finder__trigger dh-finder__trigger--open"
		aria-haspopup="dialog"
		aria-controls="dh-finder-dialog"
		aria-keyshortcuts="/"
		hidden
	>
		<?php echo $demas_search_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?>
		<span class="dh-finder__label"><?php echo esc_html( $demas_label ); ?></span>
		<kbd class="dh-finder__key" aria-hidden="true">/</kbd>
	</button>

	<dialog id="dh-finder-dialog" class="dh-finder__dialog" aria-label="<?php esc_attr_e( 'Find a part', 'demas-theme' ); ?>">
		<div class="dh-finder__panel">
			<form class="dh-finder__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo $demas_search_icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?>
				<label class="dh-sr" for="dh-finder-input"><?php esc_html_e( 'Search parts and part numbers', 'demas-theme' ); ?></label>
				<input
					id="dh-finder-input"
					class="dh-finder__input"
					type="search"
					name="s"
					role="combobox"
					aria-expanded="false"
					aria-controls="dh-finder-list"
					aria-autocomplete="list"
					aria-describedby="dh-finder-hint"
					placeholder="<?php echo esc_attr( $demas_label ); ?>"
					maxlength="80"
					autocomplete="off"
					autocapitalize="off"
					spellcheck="false"
					enterkeyhint="search"
				/>
				<input type="hidden" name="post_type" value="product" />
				<button type="button" class="dh-finder__close" aria-label="<?php esc_attr_e( 'Close search', 'demas-theme' ); ?>">
					<span class="dh-finder__esc" aria-hidden="true">Esc</span>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
			</form>

			<div class="dh-finder__body">
				<div class="dh-finder__start">
					<p id="dh-finder-hint" class="dh-finder__hint">
						<?php esc_html_e( 'Type a name or a part number, like', 'demas-theme' ); ?>
						<span class="dh-mono">DMS-FIL-009</span>.
					</p>

					<?php if ( $demas_groups ) : ?>
						<p class="dh-finder__group"><?php esc_html_e( 'Or start from a group', 'demas-theme' ); ?></p>
						<ul class="dh-finder__groups">
							<?php foreach ( $demas_groups as $demas_group ) : ?>
								<li><a class="dh-finder__group-link" href="<?php echo esc_url( get_term_link( $demas_group['term'] ) ); ?>"><?php echo esc_html( $demas_group['term']->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div id="dh-finder-list" class="dh-finder__list" role="listbox" aria-label="<?php esc_attr_e( 'Suggestions', 'demas-theme' ); ?>" hidden></div>

				<div class="dh-finder__empty" hidden></div>
			</div>

			<div class="dh-finder__foot" hidden>
				<a class="dh-finder__all" href="<?php echo esc_url( $demas_catalogue ); ?>"></a>
				<p class="dh-finder__keys" aria-hidden="true">
					<kbd>↑</kbd><kbd>↓</kbd> <?php esc_html_e( 'choose', 'demas-theme' ); ?>
					<kbd>↵</kbd> <?php esc_html_e( 'open', 'demas-theme' ); ?>
					<kbd>esc</kbd> <?php esc_html_e( 'close', 'demas-theme' ); ?>
				</p>
			</div>

			<p class="dh-sr" role="status" aria-live="polite" data-finder-status></p>
		</div>
	</dialog>
</div>
