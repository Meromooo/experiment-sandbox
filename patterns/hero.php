<?php
/**
 * Title: Hero — Water, engineered
 * Slug: demas-theme/hero
 * Categories: demas
 * Description: Headline, the Branch Desk, the irrigation schematic card, and the city marquee.
 *
 * @package Demas_Theme
 */

// The Branch Desk's cities and people come from inc/branches.php, as do the
// first marquee's cities; the headline's green word is the Highlight format.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-hero"><!-- wp:group {"className":"dh-hero__head dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-hero__head dh-reveal-group"><!-- wp:paragraph {"className":"dh-eyebrow dh-reveal--rise"} -->
<p class="dh-eyebrow dh-reveal--rise">Since 1979 · Riyadh · 15 branches</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"dh-hero__title dh-reveal--rise"} -->
<h1 class="wp-block-heading dh-hero__title dh-reveal--rise">Water, <span class="dh-highlight" data-reveal="wipe">engineered</span><br>for the Kingdom.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-hero__lead dh-reveal--rise"} -->
<p class="dh-hero__lead dh-reveal--rise">From buried pipelines to intelligent control systems, Demas Company for Trading &amp; Contracting designs, supplies, and supports complete irrigation and landscape infrastructure across Saudi Arabia. Forty-six years. ISO-certified. A free site visit, and a five-year warranty on what we install.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"dh-hero__actions dh-reveal-group"} -->
<div class="wp-block-buttons dh-hero__actions dh-reveal-group"><!-- wp:button {"className":"is-style-dh-pill-solid dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-solid dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="#find-your-branch">Request a site visit</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-outline dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="/products/">Browse the catalogue</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"dh-hero__grid dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-hero__grid dh-reveal-group"><!-- wp:demas-theme/branch-desk {"eyebrow":"Find your branch","title":"Fifteen cities. Someone who answers."} /-->

<!-- wp:demas-theme/schematic {"tabTitle":"Five-year warranty","tabCopy":"Installed and maintained by Demas technicians."} /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:demas-theme/marquee {"label":"Branches across Saudi Arabia","branches":true} /-->

<!-- wp:demas-theme/marquee {"label":"What we supply and install","items":["Sprinkler systems","Micro-sprinklers","Drip and drip tape","Nursery irrigation","Dust-suppression mainlines","Commercial turf","Sports fields","Golf courses","Fog systems","Industrial tools"],"reverse":true} /-->
