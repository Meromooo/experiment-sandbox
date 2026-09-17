<?php
/**
 * Server-rendered markup for the Mega Menu block.
 *
 * Renders the five product groups and their subcategories in the exact order
 * of demas-mega-menu-content-spec.md, which mirrors the live demas-group.com
 * menu. Order is declared here rather than sorted from the database because
 * the spec's order is authoritative and is not alphabetical (Irrigation runs
 * Pipes → Fittings → Filtration → Accessories → EF Fittings).
 *
 * Labels come from the WooCommerce terms, so an editor renaming a category
 * renames it here too. A slug in the map with no matching term is skipped, so
 * a partially-built taxonomy degrades to fewer items rather than fatal errors.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner block content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Column order and the subcategory order within each column.
 *
 * @param array $structure Map of parent slug => ordered child slugs.
 */
$demas_structure = apply_filters(
	'demas_theme_mega_menu_structure',
	array(
		'irrigation'                => array( 'pipes', 'fittings', 'filtration', 'cp-accessories', 'electro-fusion-fittings' ),
		'landscape'                 => array( 'rotors', 'controllers', 'landscape-valves', 'valve-boxes-fittings' ),
		'fog-systems'               => array( 'controllers-dosingpumps-electromagneticvalves', 'tecnocooling-fittings', 'nozzles-and-extensions', 'water-treatment' ),
		'industrial-tools-services' => array( 'band-saw-accessories', 'cutting-tools', 'welding-machines', 'magnetic-drills' ),
		// Slug is misspelled on the live site ("non-wooven"); matching it is
		// deliberate, because product URLs must survive the migration.
		'swimming-pool'             => array(),
		'non-wooven'                => array(),
	)
);

/**
 * Outbound links appended to a column. Non-Woven is a link to the sister site
 * on the live menu, not a real subcategory, so it has no term to read.
 *
 * @param array $links Map of parent slug => list of array( label, url ).
 */
$demas_external = apply_filters(
	'demas_theme_mega_menu_external_links',
	array(
		'non-wooven' => array(
			array(
				'label' => __( 'Visit DM Non-Wovens', 'demas-theme' ),
				'url'   => 'https://demasnonwoven.com/',
			),
		),
	)
);

// One query for every term the menu can show.
$demas_slugs = array_merge( array_keys( $demas_structure ), ...array_values( $demas_structure ) );
$demas_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'slug'       => $demas_slugs,
		'hide_empty' => false,
	)
);

if ( is_wp_error( $demas_terms ) || empty( $demas_terms ) ) {
	return;
}

$demas_by_slug = array();
foreach ( $demas_terms as $demas_term ) {
	$demas_by_slug[ $demas_term->slug ] = $demas_term;
}

// Drop columns whose parent term does not exist yet.
$demas_columns = array();
foreach ( $demas_structure as $demas_parent_slug => $demas_child_slugs ) {
	if ( isset( $demas_by_slug[ $demas_parent_slug ] ) ) {
		$demas_columns[ $demas_parent_slug ] = $demas_child_slugs;
	}
}

if ( empty( $demas_columns ) ) {
	return;
}

$demas_panel_id   = wp_unique_id( 'dh-mega-menu-panel-' );
$demas_wrapper    = get_block_wrapper_attributes( array( 'class' => 'dh-mega-menu' ) );
$demas_icon_alert = '<span class="dh-mega-menu__icon" aria-hidden="true">%s</span>';
?>
<nav
	<?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>
	data-wp-interactive="demas-theme/mega-menu"
	<?php echo wp_interactivity_data_wp_context( array( 'isOpen' => false, 'isPinned' => false ) ); ?>
	data-wp-class--is-open="context.isOpen"
	data-wp-on--keydown="actions.onKeydown"
	data-wp-on--focusout="actions.onFocusOut"
	data-wp-on--mouseenter="actions.openOnHover"
	data-wp-on--mouseleave="actions.closeOnHover"
	aria-label="<?php esc_attr_e( 'Product categories', 'demas-theme' ); ?>"
>
	<button
		type="button"
		class="dh-mega-menu__trigger"
		aria-haspopup="true"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $demas_panel_id ); ?>"
		data-wp-on--click="actions.toggle"
		data-wp-bind--aria-expanded="context.isOpen"
	>
		<span><?php esc_html_e( 'Products', 'demas-theme' ); ?></span>
		<svg class="dh-mega-menu__chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6"/></svg>
	</button>

	<div
		class="dh-mega-menu__panel"
		id="<?php echo esc_attr( $demas_panel_id ); ?>"
		data-wp-bind--inert="!context.isOpen"
	>
		<ul class="dh-mega-menu__columns">
			<?php
			$demas_column_index = 0;
			foreach ( $demas_columns as $demas_parent_slug => $demas_child_slugs ) :
				$demas_parent = $demas_by_slug[ $demas_parent_slug ];
				$demas_links  = isset( $demas_external[ $demas_parent_slug ] ) ? $demas_external[ $demas_parent_slug ] : array();
				?>
				<li class="dh-mega-menu__column" style="--i:<?php echo (int) $demas_column_index; ?>">
					<a
						class="dh-mega-menu__heading"
						href="<?php echo esc_url( get_term_link( $demas_parent ) ); ?>"
					>
						<?php echo esc_html( $demas_parent->name ); ?>
					</a>

					<?php if ( ! $demas_child_slugs && ! $demas_links ) : ?>
						<?php // A group with no subcategories (Swimming Pool) still gets one row, so the column reads like its neighbours. ?>
						<ul class="dh-mega-menu__list">
							<li>
								<a class="dh-mega-menu__link" href="<?php echo esc_url( get_term_link( $demas_parent ) ); ?>">
									<?php
									printf(
										$demas_icon_alert, // phpcs:ignore WordPress.Security.EscapeOutput -- static format string.
										demas_theme_get_category_icon( $demas_parent->slug ) // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG.
									);
									?>
									<span class="dh-mega-menu__label">
										<?php
										/* translators: %d: number of products in the group. */
										printf( esc_html__( 'Browse all %d products', 'demas-theme' ), (int) $demas_parent->count );
										?>
									</span>
								</a>
							</li>
						</ul>
					<?php else : ?>
						<ul class="dh-mega-menu__list">
							<?php
							foreach ( $demas_child_slugs as $demas_child_slug ) :
								if ( ! isset( $demas_by_slug[ $demas_child_slug ] ) ) {
									continue;
								}
								$demas_child = $demas_by_slug[ $demas_child_slug ];
								?>
								<li>
									<a class="dh-mega-menu__link" href="<?php echo esc_url( get_term_link( $demas_child ) ); ?>">
										<?php
										printf(
											$demas_icon_alert, // phpcs:ignore WordPress.Security.EscapeOutput -- static format string.
											demas_theme_get_category_icon( $demas_child->slug ) // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG.
										);
										?>
										<span class="dh-mega-menu__label"><?php echo esc_html( $demas_child->name ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>

							<?php foreach ( $demas_links as $demas_link ) : ?>
								<li>
									<a
										class="dh-mega-menu__link dh-mega-menu__link--external"
										href="<?php echo esc_url( $demas_link['url'] ); ?>"
										target="_blank"
										rel="noopener noreferrer"
									>
										<?php
										printf(
											$demas_icon_alert, // phpcs:ignore WordPress.Security.EscapeOutput -- static format string.
											demas_theme_get_category_icon( '__external' ) // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG.
										);
										?>
										<span class="dh-mega-menu__label"><?php echo esc_html( $demas_link['label'] ); ?></span>
										<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'demas-theme' ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
				<?php
				++$demas_column_index;
			endforeach;
			?>
		</ul>
	</div>
</nav>
