<?php
/**
 * Title: Product categories — the catalogue gateway
 * Slug: demas-theme/categories
 * Categories: demas
 * Description: Five category cards linking into the WooCommerce catalogue, product counts socketed into each corner.
 */

// Order and descriptors follow demas-homepage-brief.md; the fifth card is the
// Non-Woven sister site, as in the mega menu, since Swimming Pool is not part
// of the locked category tree. Counts are the brief's verified figures and are
// editable text — update them when the real catalogue lands.
$demas_cats = array(
	array( 'fog-systems',               'Fog Systems',              '267', 'High-pressure misting and cooling for outdoor commercial spaces.', 'nozzles-and-extensions', true ),
	array( 'landscape',                 'Landscape',                '166', 'Equipment and materials for large-scale landscape engineering.',  'rotors',                 true ),
	array( 'irrigation',                'Irrigation Products',      '122', 'Drip, spray, and rotor systems with filtration and control.',      'pipes',                  false ),
	array( 'industrial-tools-services', 'Industrial Tool Services', '53',  'Professional tools and servicing for site teams.',                 'cutting-tools',          false ),
);
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-cats","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-cats">
<!-- wp:html -->
<div class="dh-section__head">
	<p class="dh-eyebrow">Product categories</p>
	<h2 class="dh-section__title">Five specialisms. One supplier.</h2>
	<p class="dh-section__lead">Around 700 professional-grade products — pipes, fittings, filtration, valves, controllers, drippers, rotors, valve boxes, and fog systems — specified for commercial performance and Saudi conditions.</p>
</div>
<ul class="dh-cats__grid" data-reveal-group>
	<?php foreach ( $demas_cats as list( $demas_slug, $demas_name, $demas_count, $demas_desc, $demas_icon, $demas_wide ) ) : ?>
	<li class="dh-cat dh-notch dh-card dh-card--sand<?php echo $demas_wide ? ' dh-cat--wide' : ''; ?>" data-reveal="sliver">
		<a class="dh-cat__link" href="<?php echo esc_url( home_url( '/product-category/' . $demas_slug . '/' ) ); ?>">
			<span class="dh-cat__icon" aria-hidden="true"><?php echo demas_theme_get_category_icon( $demas_icon ); // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?></span>
			<span class="dh-cat__body">
				<span class="dh-cat__name"><?php echo esc_html( $demas_name ); ?></span>
				<span class="dh-cat__desc"><?php echo esc_html( $demas_desc ); ?></span>
			</span>
			<span class="dh-cat__arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
		</a>
		<span class="dh-cat__count dh-notch__tab" data-notch="top-start"><span class="dh-mono"><?php echo esc_html( $demas_count ); ?></span> products</span>
	</li>
	<?php endforeach; ?>
	<li class="dh-cat dh-notch dh-card dh-card--sand" data-reveal="sliver">
		<a class="dh-cat__link" href="https://demasnonwoven.com/" target="_blank" rel="noopener noreferrer">
			<span class="dh-cat__icon" aria-hidden="true"><?php echo demas_theme_get_category_icon( '__external' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static author-controlled SVG. ?></span>
			<span class="dh-cat__body">
				<span class="dh-cat__name">Non-Woven</span>
				<span class="dh-cat__desc">Non-woven materials from DM Non-Wovens, our sister company.</span>
				<span class="screen-reader-text">(opens in a new tab)</span>
			</span>
			<span class="dh-cat__arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8.5 7H17v8.5"/></svg></span>
		</a>
		<span class="dh-cat__count dh-notch__tab" data-notch="top-start">Sister site</span>
	</li>
</ul>
<!-- /wp:html -->
</section>
<!-- /wp:group -->
