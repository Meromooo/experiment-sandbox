<?php
/**
 * Product search (AMM-143): part numbers match, results come in a useful
 * order, and the page says why each result is there.
 *
 *  - Every front-end search is a product search. The catalogue is the site's
 *    content; the few content pages are not what anyone searches for.
 *  - WordPress's search matches titles and content only. A trade buyer
 *    mostly types a part number, so the main search query is widened to
 *    the union of the normal title/content matches and a partial SKU match
 *    (wc_get_products( 'sku' ) — WooCommerce's own API, no theme SQL).
 *  - Unless the buyer chose a sort, results are ranked: exact part number,
 *    then part numbers that start with the query, then names containing it,
 *    then everything else.
 *  - On the results page the matched fragment is highlighted in each title
 *    and part number, and an exact part-number hit is tagged.
 *
 * Also holds the search form every entry point uses (toolbar, 404).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The results page's empty state (server-rendered, from build/).
add_action( 'init', function () {
	$build_path = DEMAS_THEME_DIR . '/build/search-empty';

	if ( file_exists( $build_path . '/block.json' ) ) {
		register_block_type( $build_path );
	}
} );

/**
 * The search as the site reads it, once per request.
 *
 * @return array{raw: string, words: string[], skus: string[]}
 *     raw   — the trimmed query;
 *     words — its words of two or more characters (for highlighting);
 *     skus  — forms to try against part numbers: as typed, and with spaces
 *             and underscores turned into hyphens ("tcn ft 062" → "tcn-ft-062").
 */
function demas_theme_search_terms(): array {
	static $terms = null;

	if ( null !== $terms ) {
		return $terms;
	}

	$raw   = trim( (string) get_query_var( 's' ) );
	$words = array_values(
		array_unique(
			array_filter(
				preg_split( '/\s+/u', $raw ) ?: array(),
				static fn( $word ) => mb_strlen( $word ) >= 2
			)
		)
	);

	$skus = array();
	if ( mb_strlen( $raw ) >= 2 ) {
		$skus[]     = $raw;
		$hyphenated = trim( (string) preg_replace( '/[\s_]+/u', '-', $raw ), '-' );
		if ( $hyphenated !== $raw ) {
			$skus[] = $hyphenated;
		}
	}

	$terms = array(
		'raw'   => $raw,
		'words' => $words,
		'skus'  => $skus,
	);

	return $terms;
}

/**
 * Is this the main front-end product search?
 */
function demas_theme_is_product_search( WP_Query $query ): bool {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return false;
	}

	$post_type = $query->get( 'post_type' );

	return 'product' === $post_type || ( is_array( $post_type ) && array( 'product' ) === array_values( $post_type ) );
}

/*
 * Every front-end search is a product search, so WooCommerce serves it with
 * the Product Search Results template (templates/product-search-results.html).
 * `request` runs before the query is parsed, so the conditionals agree.
 */
add_filter(
	'request',
	function ( $vars ) {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $vars;
		}

		if ( isset( $vars['s'] ) && '' !== trim( (string) $vars['s'] ) && empty( $vars['post_type'] ) ) {
			$vars['post_type'] = 'product';
		}

		return $vars;
	}
);

/*
 * Widen the match to part numbers and rank the results. Runs after
 * WooCommerce's own pre_get_posts (priority 10), which would otherwise set
 * its "relevance" order over ours.
 */
add_action(
	'pre_get_posts',
	function ( WP_Query $query ) {
		if ( ! demas_theme_is_product_search( $query ) ) {
			return;
		}

		$terms = demas_theme_search_terms();

		if ( '' === $terms['raw'] ) {
			return;
		}

		// Names and descriptions, as WordPress would have matched them.
		$ids = get_posts(
			array(
				'post_type'        => 'product',
				'post_status'      => 'publish',
				's'                => $terms['raw'],
				'fields'           => 'ids',
				'posts_per_page'   => -1,
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);

		// Part numbers, partial match, as typed and hyphenated.
		foreach ( $terms['skus'] as $sku ) {
			$ids = array_merge(
				$ids,
				wc_get_products(
					array(
						'sku'    => $sku,
						'status' => 'publish',
						'limit'  => -1,
						'return' => 'ids',
					)
				)
			);
		}

		$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );

		// The union replaces WordPress's own search clause (see posts_search
		// below); an empty union must still return nothing.
		$query->set( 'post__in', $ids ? $ids : array( 0 ) );
		$query->set( 'demas_search', true );

		$chosen = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter.

		if ( '' !== $chosen || ! $ids ) {
			return;
		}

		// Best match first.
		update_meta_cache( 'post', $ids );
		_prime_post_caches( $ids, false, false );

		$raw    = mb_strtolower( $terms['raw'] );
		$skus   = array_map( 'mb_strtolower', $terms['skus'] );
		$scored = array();

		foreach ( $ids as $id ) {
			$sku   = mb_strtolower( (string) get_post_meta( $id, '_sku', true ) );
			$title = mb_strtolower( get_the_title( $id ) );
			$score = 3;

			if ( '' !== $sku && in_array( $sku, $skus, true ) ) {
				$score = 0;
			} elseif ( '' !== $sku && array_filter( $skus, static fn( $s ) => str_starts_with( $sku, $s ) ) ) {
				$score = 1;
			} elseif ( str_contains( $title, $raw ) ) {
				$score = 2;
			}

			$scored[] = array( $score, $title, $id );
		}

		usort(
			$scored,
			static fn( $a, $b ) => array( $a[0], $a[1] ) <=> array( $b[0], $b[1] )
		);

		$query->set( 'post__in', array_column( $scored, 2 ) );
		$query->set( 'orderby', 'post__in' );
		$query->set( 'order', 'ASC' );
	},
	20
);

/*
 * WooCommerce jumps straight to the product when a search has one result,
 * which is nearly every exact part number. The results page stays — the
 * hit is tagged "Exact match" there — so a search URL always shows what
 * was searched; the instant jump belongs to AMM-142's finder (Enter).
 */
add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

// The union above already did the matching; WordPress's own LIKE clause
// would drop every result that matched on its part number alone.
add_filter(
	'posts_search',
	function ( $search, WP_Query $query ) {
		return $query->get( 'demas_search' ) ? '' : $search;
	},
	10,
	2
);

/**
 * Wrap each occurrence of the needles in <mark class="dh-hit">, in text only —
 * never inside a tag or an attribute. Case-insensitive, longest needle first.
 *
 * @param string   $html    Rendered HTML.
 * @param string[] $needles Plain-text fragments to mark.
 */
function demas_theme_highlight( string $html, array $needles ): string {
	$needles = array_filter( array_map( 'trim', $needles ), 'strlen' );

	if ( ! $needles ) {
		return $html;
	}

	usort( $needles, static fn( $a, $b ) => mb_strlen( $b ) <=> mb_strlen( $a ) );

	$pattern = '/(' . implode(
		'|',
		array_map( static fn( $needle ) => preg_quote( esc_html( $needle ), '/' ), $needles )
	) . ')/iu';

	$parts = preg_split( '/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( false === $parts ) {
		return $html;
	}

	foreach ( $parts as $i => $part ) {
		if ( '' === $part || '<' === $part[0] ) {
			continue;
		}

		$parts[ $i ] = preg_replace( $pattern, '<mark class="dh-hit">$1</mark>', $part ) ?? $part;
	}

	return implode( '', $parts );
}

/*
 * Results page: the headline is the query itself, in quotes — not
 * "Search results for: …".
 */
add_filter(
	'render_block_core/query-title',
	function ( $content, $block ) {
		if ( ! is_search() || 'search' !== ( $block['attrs']['type'] ?? '' ) ) {
			return $content;
		}

		$query = esc_html( demas_theme_search_terms()['raw'] );

		return (string) preg_replace(
			'#(<h[1-6][^>]*>).*?(</h[1-6]>)#s',
			'$1<span class="dh-search__quote" aria-hidden="true">“</span>' . $query . '<span class="dh-search__quote" aria-hidden="true">”</span>$2',
			$content,
			1
		);
	},
	10,
	2
);

// Results page: mark the matched words in each product name.
add_filter(
	'render_block_core/post-title',
	function ( $content, $block, $instance ) {
		if ( ! is_search() || ! $instance instanceof WP_Block || 'product' !== ( $instance->context['postType'] ?? '' ) ) {
			return $content;
		}

		$terms = demas_theme_search_terms();

		return demas_theme_highlight( $content, array_merge( array( $terms['raw'] ), $terms['words'] ) );
	},
	10,
	3
);

/*
 * Results page: each card's part number, with the matched part marked and an
 * exact hit tagged. Replaces WooCommerce's "SKU:" line outright, so the card
 * reads like the rest of the catalogue's data (mono, no label).
 */
add_filter(
	'render_block_woocommerce/product-sku',
	function ( $content, $block, $instance ) {
		if ( ! is_search() || ! $instance instanceof WP_Block ) {
			return $content;
		}

		$product_id = (int) ( $instance->context['postId'] ?? 0 );
		$sku        = $product_id ? (string) get_post_meta( $product_id, '_sku', true ) : '';

		if ( '' === $sku ) {
			return '';
		}

		$terms = demas_theme_search_terms();
		$exact = in_array( mb_strtolower( $sku ), array_map( 'mb_strtolower', $terms['skus'] ), true );
		$class = trim( 'dh-pcard__sku dh-mono ' . ( $block['attrs']['className'] ?? '' ) );

		return sprintf(
			'<p class="%1$s">%2$s%3$s</p>',
			esc_attr( $class ),
			demas_theme_highlight( esc_html( $sku ), array_merge( $terms['skus'], $terms['words'] ) ),
			$exact ? '<span class="dh-pcard__exact">' . esc_html__( 'Exact match', 'demas-theme' ) . '</span>' : ''
		);
	},
	10,
	3
);

/**
 * The search form. One component for every place a buyer can search from;
 * AMM-142's finder will enhance these same forms rather than replace them.
 * Works without JavaScript: a GET to /?s=… which the request filter above
 * turns into a product search.
 *
 * @param array $args {
 *     @type string $class Extra class on the form.
 * }
 * @return string Form markup.
 */
function demas_theme_search_form( array $args = array() ): string {
	$id    = wp_unique_id( 'dh-search-' );
	$count = wp_count_posts( 'product' );
	$total = isset( $count->publish ) ? (int) $count->publish : 0;

	$placeholder = $total
		/* translators: %s: number of products in the catalogue. */
		? sprintf( __( 'Search %s parts or part numbers', 'demas-theme' ), number_format_i18n( $total ) )
		: __( 'Search parts or part numbers', 'demas-theme' );

	ob_start();
	?>
	<form class="<?php echo esc_attr( trim( 'dh-search ' . ( $args['class'] ?? '' ) ) ); ?>" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="dh-sr" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Search parts and part numbers', 'demas-theme' ); ?></label>
		<input
			class="dh-search__input"
			id="<?php echo esc_attr( $id ); ?>"
			type="search"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
			autocomplete="off"
			spellcheck="false"
			enterkeyhint="search"
		/>
		<input type="hidden" name="post_type" value="product" />
		<button class="dh-search__submit" type="submit">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg>
			<span class="dh-sr"><?php esc_html_e( 'Search', 'demas-theme' ); ?></span>
		</button>
	</form>
	<?php
	return (string) ob_get_clean();
}
