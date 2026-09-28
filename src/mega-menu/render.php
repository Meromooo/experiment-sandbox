<?php
/**
 * Server-rendered markup for the Mega Menu block.
 *
 * Renders the product groups and their subcategories in the exact order of
 * demas-mega-menu-content-spec.md. The order, the term lookup and the
 * outbound links live in demas_theme_get_catalogue_columns()
 * (inc/navigation.php), which the footer's catalogue index shares.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner block content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Groups, subcategories and outbound links, resolved to terms — shared with
// the footer's catalogue index (inc/navigation.php).
$demas_columns = function_exists( 'demas_theme_get_catalogue_columns' ) ? demas_theme_get_catalogue_columns() : array();

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
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core returns an escaped attribute.
	echo wp_interactivity_data_wp_context(
		array(
			'isOpen'   => false,
			'isPinned' => false,
		)
	);
	?>
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
			foreach ( $demas_columns as $demas_column ) :
				$demas_parent   = $demas_column['term'];
				$demas_children = $demas_column['children'];
				$demas_links    = $demas_column['links'];
				?>
				<li class="dh-mega-menu__column" style="--i:<?php echo (int) $demas_column_index; ?>">
					<a
						class="dh-mega-menu__heading"
						href="<?php echo esc_url( get_term_link( $demas_parent ) ); ?>"
					>
						<?php echo esc_html( $demas_parent->name ); ?>
					</a>

					<?php if ( ! $demas_children && ! $demas_links ) : ?>
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
							foreach ( $demas_children as $demas_child ) :
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
