<?php
/**
 * Server-rendered markup for the Mega Menu block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Inner block content (unused — fully dynamic).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demas_mega_menu_categories = get_terms( array(
	'taxonomy'   => 'product_cat',
	'parent'     => 0,
	'hide_empty' => true,
) );

if ( is_wp_error( $demas_mega_menu_categories ) || empty( $demas_mega_menu_categories ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'dh-mega-menu' ) );
?>
<nav
	<?php echo wp_kses_post( $wrapper_attributes ); ?>
	data-wp-interactive="demas-theme/mega-menu"
	<?php echo wp_interactivity_data_wp_context( array( 'isOpen' => false ) ); ?>
>
	<button
		class="dh-mega-menu__trigger"
		data-wp-on--click="actions.toggle"
		data-wp-on--mouseenter="actions.open"
		data-wp-on--mouseleave="actions.close"
		data-wp-bind--aria-expanded="context.isOpen"
		aria-haspopup="true"
	>
		<?php esc_html_e( 'Products', 'demas-theme' ); ?>
	</button>

	<div
		class="dh-mega-menu__panel"
		data-wp-bind--hidden="!context.isOpen"
		data-wp-on--mouseenter="actions.open"
		data-wp-on--mouseleave="actions.close"
	>
		<ul class="dh-mega-menu__columns">
			<?php foreach ( $demas_mega_menu_categories as $demas_mega_menu_category ) : ?>
				<li class="dh-mega-menu__column">
					<a
						class="dh-mega-menu__category-link"
						href="<?php echo esc_url( get_term_link( $demas_mega_menu_category ) ); ?>"
					>
						<?php echo demas_theme_get_category_icon( $demas_mega_menu_category->slug ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php echo esc_html( $demas_mega_menu_category->name ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
