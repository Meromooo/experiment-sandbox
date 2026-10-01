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
// AI-generated and flagged for replacement (AMM-149). The survey's drawn
// frame is a placeholder: delete it and insert an Image block in its place.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-svc-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-svc-hero"><!-- wp:group {"className":"dh-svc-hero__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-hero__grid"><!-- wp:group {"className":"dh-svc-hero__copy dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-hero__copy dh-reveal-group"><!-- wp:paragraph {"className":"dh-eyebrow dh-reveal--rise"} -->
<p class="dh-eyebrow dh-reveal--rise">Services</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"dh-svc-hero__title dh-reveal--rise"} -->
<h1 class="wp-block-heading dh-svc-hero__title dh-reveal--rise">Designed, installed, and <span class="dh-highlight" data-reveal="wipe">kept running</span>.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-lead dh-reveal--rise"} -->
<p class="dh-svc-lead dh-reveal--rise">We design, install and maintain complete irrigation systems across Saudi Arabia — for villas, commercial landscapes and farms. Forty-six years, thousands of projects, and a five-year warranty on what we install.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"dh-svc-actions dh-reveal-group"} -->
<div class="wp-block-buttons dh-svc-actions dh-reveal-group"><!-- wp:button {"className":"is-style-dh-pill-solid dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-solid dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="/#find-your-branch">Request a site visit</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-outline dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="#install">What we install</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-notch dh-svc-hero__media dh-reveal--seed","layout":{"type":"default"}} -->
<div class="wp-block-group dh-notch dh-svc-hero__media dh-reveal--seed"><!-- wp:image {"id":1164,"sizeSlug":"large","linkDestination":"none","className":"dh-svc-photo"} -->
<figure class="wp-block-image size-large dh-svc-photo"><img src="/wp-content/uploads/2026/03/1000750832-1-768x1024.jpg" alt="A Demas crew laying irrigation lines across a desert site, mountains behind" class="wp-image-1164"/></figure>
<!-- /wp:image -->

<!-- wp:group {"className":"dh-notch__tab dh-notch__tab--bottom-start","layout":{"type":"default"}} -->
<div class="wp-block-group dh-notch__tab dh-notch__tab--bottom-start"><!-- wp:paragraph {"className":"dh-notch__title"} -->
<p class="dh-notch__title">Free site visit</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"dh-notch__copy"} -->
<p class="dh-notch__copy">Anywhere in the Kingdom.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-stages","layout":{"type":"constrained"}} -->
<div class="wp-block-group dh-svc-stages"><!-- wp:list {"ordered":true,"className":"dh-svc-stages__list"} -->
<ol class="wp-block-list dh-svc-stages__list"><!-- wp:list-item -->
<li><a href="#survey"><strong>01</strong> Survey &amp; design</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="#install"><strong>02</strong> Installation</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="#maintain"><strong>03</strong> Maintenance &amp; repairs</a></li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"survey","className":"dh-section dh-svc","layout":{"type":"constrained"}} -->
<section id="survey" class="wp-block-group dh-section dh-svc"><!-- wp:group {"className":"dh-svc__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__grid"><!-- wp:group {"className":"dh-svc__copy","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__copy"><!-- wp:paragraph {"className":"dh-eyebrow"} -->
<p class="dh-eyebrow"><strong>01</strong> Survey &amp; design</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">It starts with a visit to your site.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc-lead"} -->
<p class="dh-svc-lead">A Demas engineer visits for free, looks at what you have, and recommends the system that suits your villa, landscape or farm.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"dh-svc__label"} -->
<h3 class="wp-block-heading dh-svc__label">What we check</h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"dh-svc-checks"} -->
<ul class="wp-block-list dh-svc-checks"><!-- wp:list-item -->
<li><strong>Water source</strong> Well, network or tank</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Pressure &amp; flow</strong> Measured at the source</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Soil &amp; slope</strong> How water moves on your ground</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Sun &amp; shade</strong> What each area needs</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Zones</strong> Areas grouped by how much they drink</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"dh-svc-outcome"} -->
<p class="dh-svc-outcome">You get a clear recommendation and a quotation — typically within two business days.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-svc-placeholder dh-reveal--sliver","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-placeholder dh-reveal--sliver"><!-- wp:group {"className":"dh-svc-placeholder__chip","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc-placeholder__chip"><!-- wp:paragraph {"className":"dh-svc-placeholder__label"} -->
<p class="dh-svc-placeholder__label">Photo to come</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>An engineer on a site visit</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
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
<div class="wp-block-group dh-svc__copy"><!-- wp:paragraph {"className":"dh-eyebrow"} -->
<p class="dh-eyebrow"><strong>02</strong> Installation</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">Everything that goes in the ground.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc__text"} -->
<p class="dh-svc__text">We install the complete system — sprinklers, drip, valves, pipes, filtration and controllers — then program it to run on its own and waste less water. Many jobs are installed the same week, by expert technicians, with premium brands.</p>
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
<section id="maintain" class="wp-block-group dh-section dh-svc"><!-- wp:group {"className":"dh-svc__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__grid"><!-- wp:group {"className":"dh-svc__copy","layout":{"type":"default"}} -->
<div class="wp-block-group dh-svc__copy"><!-- wp:paragraph {"className":"dh-eyebrow"} -->
<p class="dh-eyebrow"><strong>03</strong> Maintenance &amp; repairs</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-svc__title"} -->
<h2 class="wp-block-heading dh-svc__title">Small leaks don’t stay small.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-svc__text"} -->
<p class="dh-svc__text">Most irrigation problems start small — a leak, a clogged emitter, a drop in pressure. We handle routine maintenance and emergency repairs, tune controllers, check the parts that wear, and stay with you after installation so the system stays reliable and the landscape healthy.</p>
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
<div class="wp-block-button is-style-dh-pill-paper"><a class="wp-block-button__link wp-element-button" href="/#find-your-branch">Find your branch</a></div>
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
