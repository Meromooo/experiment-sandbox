<?php
/**
 * Server-rendered markup for the Category Cards block: the homepage's
 * gateway into the catalogue.
 *
 * Each card names a product group by its product_cat slug. Its link and live
 * product count come from the catalogue itself: the same terms the mega menu
 * and the footer's index read (demas_theme_get_catalogue_columns()), counted
 * the way the catalogue pages count (demas_theme_term_product_count(), which
 * includes subcategories). A group listed with an outbound link in
 * demas_theme_get_catalogue_external_links() (Non-Woven, the sister site)
 * links there instead, in a new tab. A group whose term is missing, or has
 * no link, is left out.
 *
 * The name and description are the editor's (block attributes, edited in the
 * sidebar); the icon and the wide flag are part of the card's layout.
 *
 * A group or a subcategory can be listed: the mega menu's columns are read
 * with their children. The "List" block style (is-style-list, AMM-167) draws
 * compact rows instead of cards — icon, name, live count — for a page that
 * points into the catalogue, such as Services' "What we install".
 *
 * @param array    $attributes Block attributes: cards (category, name, description, icon, wide).
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_catalogue_columns' ) || ! function_exists( 'demas_theme_term_product_count' ) ) {
	return;
}

$demas_external = demas_theme_get_catalogue_external_links();
$demas_terms    = array();
$demas_cards    = array();
$demas_is_list  = false !== strpos( (string) ( $attributes['className'] ?? '' ), 'is-style-list' );

foreach ( demas_theme_get_catalogue_columns() as $demas_column ) {
	$demas_terms[ $demas_column['term']->slug ] = $demas_column['term'];

	foreach ( $demas_column['children'] as $demas_child ) {
		$demas_terms[ $demas_child->slug ] = $demas_child;
	}
}

foreach ( (array) ( $attributes['cards'] ?? array() ) as $demas_card ) {
	$demas_slug = sanitize_title( (string) ( $demas_card['category'] ?? '' ) );
	$demas_link = $demas_external[ $demas_slug ][0] ?? null;
	$demas_term = $demas_terms[ $demas_slug ] ?? null;

	$demas_url = $demas_link ? $demas_link['url'] : ( $demas_term ? get_term_link( $demas_term ) : '' );

	if ( ! is_string( $demas_url ) || '' === $demas_url ) {
		continue;
	}

	$demas_name = trim( (string) ( $demas_card['name'] ?? '' ) );

	$demas_cards[] = array(
		'name'  => '' !== $demas_name ? $demas_name : ( $demas_term ? $demas_term->name : '' ),
		'desc'  => trim( (string) ( $demas_card['description'] ?? '' ) ),
		'icon'  => $demas_link ? '__external' : (string) ( $demas_card['icon'] ?? $demas_slug ),
		'wide'  => ! empty( $demas_card['wide'] ),
		'url'   => $demas_url,
		'count' => $demas_link ? null : demas_theme_term_product_count( $demas_term ),
	);
}

if ( ! $demas_cards ) {
	return;
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-cats__grid' ) );
?>
<ul <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal-group>
	<?php foreach ( $demas_cards as $demas_card ) : ?>
		<?php $demas_outbound = null === $demas_card['count']; ?>
		<?php if ( $demas_is_list ) : ?>
	<li class="dh-cat-row" data-reveal="rise">
		<a class="dh-cat-row__link" href="<?php echo esc_url( $demas_card['url'] ); ?>"<?php echo $demas_outbound ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
			<span class="dh-cat-row__icon" aria-hidden="true"><?php echo demas_theme_get_category_icon( $demas_card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?></span>
			<span class="dh-cat-row__body">
				<span class="dh-cat-row__name"><?php echo esc_html( $demas_card['name'] ); ?></span>
				<?php if ( $demas_outbound ) : ?>
					<span class="dh-cat-row__count"><?php esc_html_e( 'Sister site', 'demas-theme' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'demas-theme' ); ?></span></span>
				<?php else : ?>
					<span class="dh-cat-row__count"><b><?php echo esc_html( number_format_i18n( $demas_card['count'] ) ); ?></b> <?php echo esc_html( _n( 'product', 'products', $demas_card['count'], 'demas-theme' ) ); ?></span>
				<?php endif; ?>
			</span>
			<span class="dh-cat-row__arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $demas_outbound ? 'M7 17 17 7M8.5 7H17v8.5' : 'M5 12h14M13 6l6 6-6 6'; ?>"/></svg></span>
		</a>
	</li>
		<?php else : ?>
	<li class="dh-cat dh-notch dh-card dh-card--sand<?php echo $demas_card['wide'] ? ' dh-cat--wide' : ''; ?>" data-reveal="sliver">
		<a class="dh-cat__link" href="<?php echo esc_url( $demas_card['url'] ); ?>"<?php echo $demas_outbound ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
			<span class="dh-cat__icon" aria-hidden="true"><?php echo demas_theme_get_category_icon( $demas_card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?></span>
			<span class="dh-cat__body">
				<span class="dh-cat__name"><?php echo esc_html( $demas_card['name'] ); ?></span>
				<?php if ( '' !== $demas_card['desc'] ) : ?>
					<span class="dh-cat__desc"><?php echo esc_html( $demas_card['desc'] ); ?></span>
				<?php endif; ?>
				<?php if ( $demas_outbound ) : ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'demas-theme' ); ?></span>
				<?php endif; ?>
			</span>
			<span class="dh-cat__arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $demas_outbound ? 'M7 17 17 7M8.5 7H17v8.5' : 'M5 12h14M13 6l6 6-6 6'; ?>"/></svg></span>
		</a>
		<?php if ( $demas_outbound ) : ?>
			<span class="dh-cat__count dh-notch__tab" data-notch="top-start"><?php esc_html_e( 'Sister site', 'demas-theme' ); ?></span>
		<?php else : ?>
			<span class="dh-cat__count dh-notch__tab" data-notch="top-start"><span class="dh-mono"><?php echo esc_html( number_format_i18n( $demas_card['count'] ) ); ?></span> <?php echo esc_html( _n( 'product', 'products', $demas_card['count'], 'demas-theme' ) ); ?></span>
		<?php endif; ?>
	</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
