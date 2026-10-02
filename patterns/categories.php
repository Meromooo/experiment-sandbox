<?php
/**
 * Title: Product categories — the catalogue gateway
 * Slug: demas-theme/categories
 * Categories: demas
 * Description: Five category cards linking into the WooCommerce catalogue, live product counts socketed into each corner.
 *
 * @package Demas_Theme
 */

// Order and descriptors follow demas-homepage-brief.md; the fifth card is the
// Non-Woven sister site, as in the mega menu. Counts are live from the
// catalogue (src/category-cards/render.php); names and descriptions are
// edited in the block's sidebar.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-cats","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-cats"><!-- wp:group {"className":"dh-section__head","layout":{"type":"default"}} -->
<div class="wp-block-group dh-section__head"><!-- wp:paragraph {"className":"dh-eyebrow"} -->
<p class="dh-eyebrow">Product categories</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-section__title"} -->
<h2 class="wp-block-heading dh-section__title">Five specialisms. One supplier.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-section__lead"} -->
<p class="dh-section__lead">Around 700 professional-grade products, from pipes and valves to controllers and fog systems, specified for commercial performance and Saudi conditions.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:demas-theme/category-cards {"cards":[{"category":"fog-systems","name":"Fog Systems","description":"High-pressure misting and cooling for outdoor commercial spaces.","icon":"nozzles-and-extensions","wide":true},{"category":"landscape","name":"Landscape","description":"Equipment and materials for large-scale landscape engineering.","icon":"rotors","wide":true},{"category":"irrigation","name":"Irrigation Products","description":"Drip, spray, and rotor systems with filtration and control.","icon":"pipes"},{"category":"industrial-tools-services","name":"Industrial Tool Services","description":"Professional tools and servicing for site teams.","icon":"cutting-tools"},{"category":"non-wooven","name":"Non-Woven","description":"Non-woven materials from DM Non-Wovens, our sister company."}]} /--></section>
<!-- /wp:group -->
