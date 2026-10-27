<?php
/**
 * Title: Hero — Water, engineered
 * Slug: demas-theme/hero
 * Categories: demas
 * Description: Headline and buttons in a frame of Demas photos that change on their own, the Branch Desk, the irrigation schematic card, and the supply marquee.
 *
 * @package Demas_Theme
 */

// The frame (AMM-196) is a carousel of Demas's own photos: one Image block
// each, its alt text and caption edited here, in the editor (the caption is read
// out to screen readers, not shown). main.js adds the tab with a segment
// per photo and the pause button; without it the first
// photo stays. The image ids are the sandbox's Media Library; a paste on
// another site takes that site's ids. A dh-focus--left/right/up/down class
// on an image moves its crop. The Branch Desk's cities and people come from
// inc/branches.php; the headline's green word is the Highlight format. One
// marquee only (AMM-170): the cities are already in the Branch Desk above it
// and the footer's key plan.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-hero"><!-- wp:group {"align":"full","className":"dh-hero__frame","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull dh-hero__frame"><!-- wp:group {"className":"dh-hero__copy","layout":{"type":"default"}} -->
<div class="wp-block-group dh-hero__copy"><!-- wp:heading {"level":1,"className":"dh-hero__title dh-reveal--rise"} -->
<h1 class="wp-block-heading dh-hero__title dh-reveal--rise">Water, <span class="dh-highlight" data-reveal="wipe">engineered</span><br>for the Kingdom.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-hero__lead dh-reveal--rise"} -->
<p class="dh-hero__lead dh-reveal--rise">Irrigation and landscape systems designed, supplied and supported across Saudi Arabia. Free site visit, five-year installation warranty.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons {"className":"dh-hero__actions"} -->
<div class="wp-block-buttons dh-hero__actions"><!-- wp:button {"className":"is-style-dh-pill-solid"} -->
<div class="wp-block-button is-style-dh-pill-solid"><a class="wp-block-button__link wp-element-button" href="/contact-us/?need=survey#request">Request a site visit</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline"} -->
<div class="wp-block-button is-style-dh-pill-outline"><a class="wp-block-button__link wp-element-button" href="/products/">Browse the catalogue</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"className":"dh-hero-slides","layout":{"type":"default"}} -->
<div class="wp-block-group dh-hero-slides"><!-- wp:image {"id":2892,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--left"} -->
<figure class="wp-block-image size-full dh-focus--left"><img src="/wp-content/uploads/2026/10/demas-hero-finished-site.jpg" alt="Young trees on drip lines and a new lawn in front of a glass building, palms and a mountain behind" class="wp-image-2892"/><figcaption class="wp-element-caption">Finished site</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2894,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--down"} -->
<figure class="wp-block-image size-full dh-focus--down"><img src="/wp-content/uploads/2026/10/demas-hero-pipework.jpg" alt="A worker walking a freshly dug pipe trench across the desert" class="wp-image-2894"/><figcaption class="wp-element-caption">Pipework</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2895,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--up"} -->
<figure class="wp-block-image size-full dh-focus--up"><img src="/wp-content/uploads/2026/10/demas-hero-filter-station.jpg" alt="A technician beside a blue disc filter station and valve manifold on a planted site" class="wp-image-2895"/><figcaption class="wp-element-caption">Filter station</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2893,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--up"} -->
<figure class="wp-block-image size-full dh-focus--up"><img src="/wp-content/uploads/2026/10/demas-hero-installation-crew.jpg" alt="A Demas crew digging irrigation lines along a new kerb, jagged mountains behind" class="wp-image-2893"/><figcaption class="wp-element-caption">Installation crew</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2896,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--left dh-focus--up"} -->
<figure class="wp-block-image size-full dh-focus--left dh-focus--up"><img src="/wp-content/uploads/2026/10/demas-hero-misting-system.jpg" alt="Mist drifting between young trees on a paved plaza beside glass buildings" class="wp-image-2896"/><figcaption class="wp-element-caption">Misting system</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-hero__grid dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-hero__grid dh-reveal-group"><!-- wp:demas-theme/branch-desk {"title":"Fifteen cities. Someone who answers."} /-->

<!-- wp:demas-theme/schematic {"tabTitle":"Five-year warranty","tabCopy":"Installed and maintained by Demas technicians."} /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:demas-theme/marquee {"label":"What we supply and install","items":["Sprinkler systems","Micro-sprinklers","Drip and drip tape","Nursery irrigation","Dust-suppression mainlines","Commercial turf","Sports fields","Golf courses","Fog systems","Industrial tools"],"reverse":true} /-->
