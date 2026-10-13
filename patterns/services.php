<?php
/**
 * Title: Services page
 * Slug: demas-theme/services
 * Categories: demas
 * Keywords: services, site visit, installation, maintenance
 * Post Types: page
 * Description: The Services page: survey, installation and maintenance, with the Service Record job card and the site-visit call to action. Use it on a page set to the "Designed page" template.
 *
 * @package Demas_Theme
 */

// AMM-167. Every word is an editable block; layout and motion live in
// style.css section 15. Photos are media-library images by id (relative
// paths, so they resolve on any domain); the installation photo is
// AI-generated and flagged for replacement (AMM-149). AMM-171: the sections
// alternate side by side and stacked, and only the call to action carries an
// eyebrow. AMM-192: the hero's second line is the Bold format and its figures
// a list with Bold numbers; the offer is a card over the photo's edge (no
// notch); the trust row sits under the hero. AMM-195: the survey section is a
// folder: its tabs a list (the open one, Survey & design, plain text; the
// other two links down the page), its board the visit copy, the route (each
// step's name in Bold, the checks a nested list) and the crew photo.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-svc-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-svc-hero"><!-- wp:group {"className":"dh-svc-hero__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-hero__grid"><!-- wp:group {"className":"dh-svc-hero__copy dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-hero__copy dh-reveal-group"><!-- wp:heading {"level":1,"className":"dh-svc-hero__title dh-reveal--rise"} -->
<h1 class="wp-block-heading dh-svc-hero__title dh-reveal--rise">Designed, installed, <strong>and <span class="dh-highlight" data-reveal="wipe">kept running.</span></strong></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-lead dh-reveal--rise"} -->
<p class="dh-svc-lead dh-reveal--rise">We design, install and maintain irrigation for villas, landscapes and farms across Saudi Arabia.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"dh-svc-proof dh-reveal--rise"} -->
<ul class="wp-block-list dh-svc-proof dh-reveal--rise"><!-- wp:list-item -->
<li><strong>46</strong> years in business</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>5</strong> year warranty</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:buttons {"className":"dh-svc-actions dh-reveal-group"} -->
<div class="wp-block-buttons dh-svc-actions dh-reveal-group"><!-- wp:button {"className":"is-style-dh-pill-solid dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-solid dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="/contact-us/?need=survey#request">Request a site visit</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-outline dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="#install">What we install</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-hero__media dh-reveal--seed","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-hero__media dh-reveal--seed"><!-- wp:image {"id":1164,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-photo"} -->
<figure class="wp-block-image size-large dh-svc-photo"><img src="/wp-content/uploads/2026/03/1000750832-1-768x1024.jpg" alt="A Demas crew laying irrigation lines across a desert site, mountains behind" class="wp-image-1164"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"dh-svc-offer","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-offer"><!-- wp:paragraph {"className":"dh-svc-offer__badge"} -->
<p class="dh-svc-offer__badge">Survey &amp; design</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"dh-svc-offer__title"} -->
<p class="dh-svc-offer__title">Free site visit</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"dh-svc-offer__copy"} -->
<p class="dh-svc-offer__copy">Anywhere in the Kingdom.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-stages","layout":{"type":"constrained"}} -->
<div class="wp-block-group dh-svc-stages"><!-- wp:list {"className":"dh-svc-trust"} -->
<ul class="wp-block-list dh-svc-trust"><!-- wp:list-item {"className":"dh-svc-trust__branches"} -->
<li class="dh-svc-trust__branches">15 branches across the Kingdom</li>
<!-- /wp:list-item -->

<!-- wp:list-item {"className":"dh-svc-trust__certified"} -->
<li class="dh-svc-trust__certified">ISO 9001 and SASO certified</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"survey","className":"dh-section dh-svc dh-svc--folder","layout":{"type":"constrained"}} -->
<section id="survey" class="wp-block-group dh-section dh-svc dh-svc--folder"><!-- wp:group {"className":"dh-svc-folder","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-folder"><!-- wp:list {"className":"dh-svc-tabs dh-reveal-group"} -->
<ul class="wp-block-list dh-svc-tabs dh-reveal-group"><!-- wp:list-item {"className":"is-current dh-reveal--rise"} -->
<li class="is-current dh-reveal--rise">Survey &amp; design</li>
<!-- /wp:list-item -->

<!-- wp:list-item {"className":"dh-reveal--rise"} -->
<li class="dh-reveal--rise"><a href="#install">Installation</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item {"className":"dh-reveal--rise"} -->
<li class="dh-reveal--rise"><a href="#maintain">Maintenance &amp; repairs</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"dh-svc-board","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-board"><!-- wp:group {"className":"dh-svc-board__copy","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-board__copy"><!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">It starts with a <span class="dh-highlight" data-reveal="wipe">visit</span> to your site.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-lead"} -->
<p class="dh-svc-lead">A Demas engineer visits for free, looks at what you have, and recommends the system that suits your villa, landscape or farm.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"dh-svc-actions"} -->
<div class="wp-block-buttons dh-svc-actions"><!-- wp:button {"className":"is-style-dh-pill-paper"} -->
<div class="wp-block-button is-style-dh-pill-paper"><a class="wp-block-button__link wp-element-button" href="/contact-us/?need=survey#request">Request a site visit</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:list {"ordered":true,"className":"dh-svc-route"} -->
<ol class="wp-block-list dh-svc-route"><!-- wp:list-item -->
<li><strong>Tell us about your site</strong> Pick your nearest branch.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>An engineer visits</strong> Free, anywhere in the Kingdom.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>We check your site</strong><!-- wp:list {"className":"dh-svc-route__checks"} -->
<ul class="wp-block-list dh-svc-route__checks"><!-- wp:list-item -->
<li>Water source</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Pressure &amp; flow</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Soil &amp; slope</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Sun &amp; shade</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Zones</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>You get a quotation</strong> Usually within two business days.</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:image {"id":1161,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-board__photo"} -->
<figure class="wp-block-image size-large dh-svc-board__photo"><img src="/wp-content/uploads/2026/03/1000239887-1024x576.jpg" alt="Technicians in hard hats kneeling to fit drip lines in the sand" class="wp-image-1161"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"install","className":"dh-section dh-svc dh-svc--plate","layout":{"type":"constrained"}} -->
<section id="install" class="wp-block-group dh-section dh-svc dh-svc--plate"><!-- wp:group {"className":"dh-svc-plate","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-plate"><!-- wp:group {"className":"dh-svc__grid dh-svc__grid--flip","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__grid dh-svc__grid--flip"><!-- wp:image {"id":840,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-media dh-reveal--sliver"} -->
<figure class="wp-block-image size-large dh-svc-media dh-reveal--sliver"><img src="/wp-content/uploads/2026/02/metal-equipment-aids-growth-rural-farm-generated-by-ai-1024x585.jpg" alt="Drip lines and emitters running along rows of young seedlings in a greenhouse" class="wp-image-840"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"dh-svc__copy","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__copy"><!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">Everything that goes in the ground.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc__text"} -->
<p class="dh-svc__text">We install the whole system: sprinklers, drip, valves, pipes, filtration and controllers. Then we program it to run on its own and waste less water.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-parts","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-parts"><!-- wp:group {"className":"dh-svc-parts__head","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-parts__head"><!-- wp:heading {"level":3,"className":"dh-svc-parts__title"} -->
<h3 class="wp-block-heading dh-svc-parts__title">What we install</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-parts__all"} -->
<p class="dh-svc-parts__all"><a href="/products/">Browse all parts</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:demas-theme/category-cards {"cards":[{"category":"rotors"},{"category":"landscape-valves"},{"category":"controllers"},{"category":"pipes"},{"category":"fittings"},{"category":"filtration"}],"className":"is-style-list"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"maintain","className":"dh-section dh-svc","layout":{"type":"constrained"}} -->
<section id="maintain" class="wp-block-group dh-section dh-svc"><!-- wp:group {"className":"dh-svc__head","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__head"><!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">Small leaks don’t stay small.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc__text"} -->
<p class="dh-svc__text">Most problems start small: a leak, a clogged emitter, a drop in pressure. We catch them early and keep the system reliable.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"dh-svc-two","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-two"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Routine maintenance</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Scheduled visits that catch problems early.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Emergency repairs</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Leaks, breaks and failures, fixed and tested.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-record-stage","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-record-stage"><!-- wp:image {"id":841,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-record__photo dh-reveal--sliver"} -->
<figure class="wp-block-image size-large dh-svc-record__photo dh-reveal--sliver"><img src="/wp-content/uploads/2026/02/pexels-connorscottmcmanus-14823389-683x1024.jpg" alt="A pop-up sprinkler spraying over a hedge and planted bed" class="wp-image-841"/></figure>
<!-- /wp:image -->

<!-- wp:group {"tagName":"article","className":"dh-svc-record dh-reveal--rise","layout":{"type":"default"}} -->
<article class="wp-block-group dh-svc-record dh-reveal--rise"><!-- wp:group {"className":"dh-svc-record__head","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-record__head"><!-- wp:heading {"level":3,"className":"dh-svc-record__title"} -->
<h3 class="wp-block-heading dh-svc-record__title">Service record</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-record__brand"} -->
<p class="dh-svc-record__brand">Demas Group</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:list {"className":"dh-svc-record__fields"} -->
<ul class="wp-block-list dh-svc-record__fields"><!-- wp:list-item -->
<li><strong>Site</strong> Villa, landscape or farm</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Branch</strong> Your nearest of 15</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Visit</strong> Scheduled or emergency</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:list {"className":"dh-svc-record__checks"} -->
<ul class="wp-block-list dh-svc-record__checks"><!-- wp:list-item -->
<li>Pressure and flow test</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Flush lines and emitters</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Check and clean filters</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Inspect valves and fittings</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Tune the controller program</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Fix leaks, clogs and weak spots</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:group {"className":"dh-svc-record__sign","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-record__sign"><!-- wp:paragraph -->
<p>Technician</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Customer</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"dh-svc-stamp"} -->
<p class="dh-svc-stamp"><strong>5</strong> Year warranty</p>
<!-- /wp:paragraph --></article>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"dh-section dh-svc-cta","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-svc-cta"><!-- wp:group {"className":"dh-band dh-card dh-card--canopy dh-svc-band dh-reveal--bar","layout":{"type":"default"}} -->
<div class="wp-block-group dh-band dh-card dh-card--canopy dh-svc-band dh-reveal--bar"><!-- wp:group {"className":"dh-svc-band__copy dh-reveal-content","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-band__copy dh-reveal-content"><!-- wp:paragraph {"className":"dh-eyebrow dh-eyebrow--on-dark"} -->
<p class="dh-eyebrow dh-eyebrow--on-dark">Free consultation &amp; site assessment</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-band__title"} -->
<h2 class="wp-block-heading dh-band__title">Request a site visit. We cover all of Saudi Arabia.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-band__lead"} -->
<p class="dh-band__lead">Tell us what you need. We’ll visit, assess the system, and give you clear recommendations and a quotation. Many jobs are installed the same week, with a five-year warranty.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-dh-pill-paper"} -->
<div class="wp-block-button is-style-dh-pill-paper"><a class="wp-block-button__link wp-element-button" href="/contact-us/?need=survey#request">Request a site visit</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline-paper"} -->
<div class="wp-block-button is-style-dh-pill-outline-paper"><a class="wp-block-button__link wp-element-button" href="tel:+966114634102">Call 011 463 4102</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"dh-svc-band__meta"} -->
<p class="dh-svc-band__meta">15 branches · head office 8018 King Abdulaziz Rd, Riyadh</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-collage dh-reveal-content","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-collage dh-reveal-content"><!-- wp:image {"id":825,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-collage__a"} -->
<figure class="wp-block-image size-large dh-svc-collage__a"><img src="/wp-content/uploads/2026/02/pexels-sam-mccool-1923523643-34031016-1024x576.jpg" alt="Aerial view of irrigated fields and centre-pivot circles beside a river" class="wp-image-825"/></figure>
<!-- /wp:image -->

<!-- wp:image {"id":1161,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-collage__b"} -->
<figure class="wp-block-image size-large dh-svc-collage__b"><img src="/wp-content/uploads/2026/03/1000239887-1024x576.jpg" alt="Technicians in hard hats kneeling to fit drip lines in the sand" class="wp-image-1161"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
