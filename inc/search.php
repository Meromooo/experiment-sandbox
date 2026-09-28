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
 * Also holds the search form every entry point uses (toolbar, 404), and the
 * header's Jump to part finder (AMM-142): its block, and the REST route it
 * reads — the same matching and ranking, so the finder and the results page
 * never disagree about what a query finds.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The results page's empty state, and the header finder (both from build/).
add_action( 'init', function () {
	foreach ( array( 'search-empty', 'finder' ) as $block ) {
		$build_path = DEMAS_THEME_DIR . '/build/' . $block;

		if ( file_exists( $build_path . '/block.json' ) ) {
			register_block_type( $build_path );
		}
	}
} );

/**
 * A query as the site reads it.
 *
 * @return array{raw: string, words: string[], skus: string[]}
 *     raw   — the trimmed query;
 *     words — its words of two or more characters (for highlighting);
 *     skus  — forms to try against part numbers: as typed, and with spaces
 *             and underscores turned into hyphens ("tcn ft 062" → "tcn-ft-062").
 */
function demas_theme_parse_search( string $raw ): array {
	$raw   = trim( $raw );
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

	return array(
		'raw'   => $raw,
		'words' => $words,
		'skus'  => $skus,
	);
}

/**
 * The current page's search, parsed once per request.
 *
 * @return array{raw: string, words: string[], skus: string[]}
 */
function demas_theme_search_terms(): array {
	static $terms = null;

	if ( null === $terms ) {
		$terms = demas_theme_parse_search( (string) get_query_var( 's' ) );
	}

	return $terms;
}

/**
 * Every published product a query matches: names and descriptions, as
 * WordPress's own search would find them, and part numbers, partial match,
 * as typed and hyphenated. Both through WordPress and WooCommerce APIs.
 *
 * Ranked unless asked not to: exact part number, part numbers starting with
 * the query (in part-number order), then names containing it, then the rest
 * (by name).
 *
 * @param string $raw  The query.
 * @param bool   $rank Whether to rank; the order is arbitrary otherwise.
 * @return int[] Product IDs.
 */
function demas_theme_find_product_ids( string $raw, bool $rank = true ): array {
	$terms = demas_theme_parse_search( $raw );

	if ( '' === $terms['raw'] ) {
		return array();
	}

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

	if ( ! $rank || ! $ids ) {
		return $ids;
	}

	update_meta_cache( 'post', $ids );
	_prime_post_caches( $ids, false, false );

	$query  = mb_strtolower( $terms['raw'] );
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
		} elseif ( str_contains( $title, $query ) ) {
			$score = 2;
		}

		// Part-number hits read in part-number order; the rest by name.
		$scored[] = array( $score, $score <= 1 ? $sku : $title, $id );
	}

	usort(
		$scored,
		static fn( $a, $b ) => array( $a[0], $a[1] ) <=> array( $b[0], $b[1] )
	);

	return array_column( $scored, 2 );
}

/**
 * Categories whose names match: the whole query, else the first of its
 * words (three characters or more) that matches anything. Empty categories
 * are left out.
 *
 * @return WP_Term[]
 */
function demas_theme_find_categories( string $raw, int $limit = 12 ): array {
	$raw = trim( $raw );

	if ( '' === $raw ) {
		return array();
	}

	foreach ( array_merge( array( $raw ), preg_split( '/\s+/u', $raw ) ?: array() ) as $needle ) {
		if ( mb_strlen( $needle ) < 3 ) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'name__like' => $needle,
				'hide_empty' => true,
				'number'     => $limit,
			)
		);

		if ( ! is_wp_error( $terms ) && $terms ) {
			return $terms;
		}
	}

	return array();
}

/**
 * A category's place in the tree, top first: "Fog Systems / Fittings".
 */
function demas_theme_term_path( WP_Term $term ): string {
	$names = array( $term->name );

	foreach ( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, 'product_cat' );

		if ( $ancestor instanceof WP_Term ) {
			array_unshift( $names, $ancestor->name );
		}
	}

	// Plain text: term names are stored HTML-escaped (Fittings &amp; …).
	return html_entity_decode( implode( ' / ', $names ), ENT_QUOTES, 'UTF-8' );
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

		$chosen = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only sort parameter.
		$ids    = demas_theme_find_product_ids( $terms['raw'], '' === $chosen );

		// The union replaces WordPress's own search clause (see posts_search
		// below); an empty union must still return nothing.
		$query->set( 'post__in', $ids ? $ids : array( 0 ) );
		$query->set( 'demas_search', true );

		// Best match first, unless the buyer picked a sort.
		if ( '' === $chosen && $ids ) {
			$query->set( 'orderby', 'post__in' );
			$query->set( 'order', 'ASC' );
		}
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

// Each card's part number — marked and tagged on a search — is rendered by
// the Part Details block (src/product-meta), which calls the helpers above.

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
			maxlength="80"
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

/*
 * The header finder's data: GET /wp-json/demas-theme/v1/find?q=…
 *
 * Public and read-only — published catalogue data a visitor could read on
 * the site anyway. The same matching and ranking as the results page, cut
 * to what a suggestion row shows. Short-cached: the catalogue changes by
 * hand, not by the minute.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'demas-theme/v1',
			'/find',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'args'                => array(
					'q' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static fn( $value ) => is_string( $value ) && mb_strlen( $value ) <= 80,
					),
				),
				'callback'            => 'demas_theme_rest_find',
			)
		);
	}
);

/**
 * Up to five categories and eight parts for a query, plus the total and the
 * results page URL.
 */
function demas_theme_rest_find( WP_REST_Request $request ): WP_REST_Response {
	$raw   = trim( (string) $request->get_param( 'q' ) );
	$terms = demas_theme_parse_search( $raw );

	$categories = array();
	foreach ( demas_theme_find_categories( $raw, 5 ) as $term ) {
		$categories[] = array(
			'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
			'path'  => demas_theme_term_path( $term ),
			'count' => function_exists( 'demas_theme_term_product_count' ) ? demas_theme_term_product_count( $term ) : (int) $term->count,
			'url'   => get_term_link( $term ),
		);
	}

	$ids   = mb_strlen( $raw ) >= 2 ? demas_theme_find_product_ids( $raw ) : array();
	$parts = array();
	$skus  = array_map( 'mb_strtolower', $terms['skus'] );

	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );

		// Products hidden from search stay hidden here too.
		if ( ! $product || ! in_array( $product->get_catalog_visibility(), array( 'visible', 'search' ), true ) ) {
			continue;
		}

		$chain = function_exists( 'demas_theme_get_product_term_chain' ) ? demas_theme_get_product_term_chain( $id ) : array();
		$sku   = (string) $product->get_sku();

		$parts[] = array(
			'name'  => html_entity_decode( $product->get_name(), ENT_QUOTES, 'UTF-8' ),
			'sku'   => $sku,
			'path'  => $chain ? demas_theme_term_path( $chain[0] ) : '',
			'url'   => get_permalink( $id ),
			'exact' => '' !== $sku && in_array( mb_strtolower( $sku ), $skus, true ),
		);

		if ( count( $parts ) >= 8 ) {
			break;
		}
	}

	$response = new WP_REST_Response(
		array(
			'query'      => $raw,
			'needles'    => array_values( array_unique( array_merge( array( $raw ), $terms['skus'], $terms['words'] ) ) ),
			'total'      => count( $ids ),
			'categories' => $categories,
			'parts'      => $parts,
			'resultsUrl' => add_query_arg(
				array(
					's'         => rawurlencode( $raw ),
					'post_type' => 'product',
				),
				home_url( '/' )
			),
		)
	);

	$response->header( 'Cache-Control', 'public, max-age=300' );

	return $response;
}
