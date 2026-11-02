<?php
/**
 * Title: Hero — Water, engineered
 * Slug: demas-theme/hero
 * Categories: demas
 * Description: Headline and buttons in a frame of irrigation photos that change on their own, the Branch Desk, the irrigation schematic card, and the supply marquee.
 *
 * @package Demas_Theme
 */

// The frame (AMM-196) is a carousel of photos: one Image block each, its alt
// text and caption edited here, in the editor (the caption is read out to
// screen readers, not shown). The first five are free stock (Pexels and
// Unsplash licences; Ammar's exception for the hero, 2026-11-02; sources in
// tools/grade-photos.py), the misting system is Demas's own. main.js adds
// the tab with a segment per photo and the pause button; without it the
// first photo stays. The image ids are the sandbox's Media Library; a paste on
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
<div class="wp-block-group dh-hero-slides"><!-- wp:image {"id":2914,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/11/demas-hero-pivot-field-1.jpg" alt="A centre-pivot irrigator watering a field of young crops under a clear sky" class="wp-image-2914"/><figcaption class="wp-element-caption">Pivot irrigation</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2915,"sizeSlug":"full","linkDestination":"none","className":"dh-focus--left"} -->
<figure class="wp-block-image size-full dh-focus--left"><img src="/wp-content/uploads/2026/11/demas-hero-pivot-rainbow-1.jpg" alt="A pivot irrigator spraying a green field, a small rainbow in the spray" class="wp-image-2915"/><figcaption class="wp-element-caption">Pivot in action</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2913,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/11/demas-hero-pivot-aerial-1.jpg" alt="Circular fields watered by centre pivots, seen from the air beside a river" class="wp-image-2913"/><figcaption class="wp-element-caption">Fields from above</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2911,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/11/demas-hero-field-sprinklers-1.jpg" alt="Sprinklers watering long rows of green crops" class="wp-image-2911"/><figcaption class="wp-element-caption">Field sprinklers</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":2916,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="/wp-content/uploads/2026/11/demas-hero-impact-sprinkler.jpg" alt="A single impact sprinkler spraying over a grass field in evening light" class="wp-image-2916"/><figcaption class="wp-element-caption">Impact sprinkler</figcaption></figure>
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
