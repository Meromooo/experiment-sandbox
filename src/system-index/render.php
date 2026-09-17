<?php
/**
 * Server-rendered markup for the Browse by System block.
 *
 * On the shop root: every system. On a category archive: the system that
 * category belongs to, with the buyer's position marked — the stage they are
 * in and the term they are on. Each stage is a column of links to real
 * product_cat terms; a term whose parent sits in the same stage indents
 * under it, and a term with unlisted children says what they are ("2
 * brands", "9 types") so the next click is never a guess.
 *
 * The map itself is in inc/system-map.php.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_systems_for_term' ) ) {
	return;
}

if ( ! ( is_post_type_archive( 'product' ) || is_tax( get_object_taxonomies( 'product' ) ) ) ) {
	return;
}

$demas_term    = is_tax( 'product_cat' ) ? get_queried_object() : null;
$demas_term    = $demas_term instanceof WP_Term ? $demas_term : null;
$demas_systems = demas_theme_get_systems_for_term( $demas_term );

if ( ! $demas_systems ) {
	return;
}

// The queried term and everything above it count as "where you are".
$demas_here = array();
if ( $demas_term ) {
	$demas_here = array_merge( array( $demas_term->term_id ), get_ancestors( $demas_term->term_id, 'product_cat', 'taxonomy' ) );
}

/*
 * Resolve every stage to live terms first, so empty stages can be dropped
 * and the column count is known before any markup is written.
 */
$demas_resolved = array();

foreach ( $demas_systems as $demas_key => $demas_system ) {
	$demas_stages = array();

	foreach ( $demas_system['stages'] as $demas_stage ) {
		$demas_slugs = $demas_stage['terms'];
		$demas_terms = array();

		foreach ( $demas_slugs as $demas_slug ) {
			$demas_t = get_term_by( 'slug', $demas_slug, 'product_cat' );

			if ( $demas_t instanceof WP_Term && demas_theme_term_product_count( $demas_t ) > 0 ) {
				$demas_terms[] = $demas_t;
			}
		}

		if ( ! $demas_terms ) {
			continue;
		}

		$demas_ids     = wp_list_pluck( $demas_terms, 'term_id' );
		$demas_current = (bool) array_intersect( $demas_ids, $demas_here );

		$demas_stages[] = array(
			'label'   => $demas_stage['label'],
			'terms'   => $demas_terms,
			'ids'     => $demas_ids,
			'current' => $demas_current,
		);
	}

	if ( $demas_stages ) {
		$demas_resolved[ $demas_key ] = array(
			'label'  => $demas_system['label'],
			'kind'   => $demas_system['kind'],
			'stages' => $demas_stages,
		);
	}
}

if ( ! $demas_resolved ) {
	return;
}

$demas_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'dh-lines' . ( count( $demas_resolved ) > 1 ? ' dh-lines--all' : '' ),
	)
);
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php foreach ( $demas_resolved as $demas_key => $demas_system ) : ?>
		<?php
		$demas_eyebrow = 'trade' === $demas_system['kind']
			? __( 'Browse by trade', 'demas-theme' )
			: __( 'Browse by stage', 'demas-theme' );
		?>
		<nav
			class="dh-line dh-line--<?php echo esc_attr( $demas_key ); ?>"
			style="--_n: <?php echo (int) count( $demas_system['stages'] ); ?>"
			aria-label="<?php echo esc_attr( sprintf( '%s — %s', $demas_eyebrow, $demas_system['label'] ) ); ?>"
			data-reveal="rise"
		>
			<p class="dh-line__eyebrow dh-eyebrow">
				<span><?php echo esc_html( $demas_eyebrow ); ?></span>
				<span class="dh-line__system"><?php echo esc_html( $demas_system['label'] ); ?></span>
			</p>

			<ol class="dh-line__stages">
				<?php foreach ( $demas_system['stages'] as $demas_i => $demas_stage ) : ?>
					<li
						class="dh-line__stage<?php echo $demas_stage['current'] ? ' is-current' : ''; ?>"
						style="--i: <?php echo (int) $demas_i; ?>"
					>
						<span class="dh-line__node" aria-hidden="true"></span>
						<h3 class="dh-line__stage-name"><?php echo esc_html( $demas_stage['label'] ); ?></h3>

						<ul class="dh-line__terms">
							<?php foreach ( $demas_stage['terms'] as $demas_t ) : ?>
								<?php
								$demas_is_child = $demas_t->parent && in_array( $demas_t->parent, $demas_stage['ids'], true );
								$demas_is_here  = in_array( $demas_t->term_id, $demas_here, true );
								$demas_children = $demas_is_child ? '' : demas_theme_describe_children( $demas_t );

								// Children already spelled out in this stage need no "9 types" tag.
								if ( $demas_children ) {
									$demas_listed = array_filter( $demas_stage['terms'], fn( $x ) => $x->parent === $demas_t->term_id );
									if ( $demas_listed ) {
										$demas_children = '';
									}
								}
								?>
								<li class="<?php echo $demas_is_child ? 'dh-line__item dh-line__item--child' : 'dh-line__item'; ?>">
									<a
										class="dh-line__term"
										href="<?php echo esc_url( get_term_link( $demas_t ) ); ?>"
										<?php echo ( $demas_term && $demas_t->term_id === $demas_term->term_id ) ? 'aria-current="page"' : ( $demas_is_here ? 'aria-current="true"' : '' ); ?>
									>
										<span class="dh-line__name"><?php echo esc_html( $demas_t->name ); ?></span>
										<span class="dh-line__count dh-mono"><?php echo esc_html( str_pad( (string) demas_theme_term_product_count( $demas_t ), 3, '0', STR_PAD_LEFT ) ); ?></span>
									</a>
									<?php if ( $demas_children ) : ?>
										<span class="dh-line__kids"><?php echo esc_html( $demas_children ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endforeach; ?>
</div>
