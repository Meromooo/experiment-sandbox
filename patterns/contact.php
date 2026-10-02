<?php
/**
 * Title: Contact page
 * Slug: demas-theme/contact
 * Categories: demas
 * Keywords: contact, branches, request, directions, opening hours
 * Post Types: page
 * Description: The Contact page: the fifteen branches as an irrigation layout plan with each branch's card, and the request section. Use it on the Contact Us page, set to the "Designed page" template.
 *
 * @package Demas_Theme
 */

// AMM-169. Every word is an editable block except the Branch Finder, whose
// branches, people, addresses and hours come from inc/branches.php (a photo
// per branch is chosen in its sidebar). Layout and motion live in style.css
// section 15, src/branch-finder and src/request-form. The form is front end
// only until AMM-140 connects sending; it says so when a buyer presses Send.
?>
<!-- wp:group {"tagName":"section","className":"dh-section dh-ct-hero","layout":{"type":"constrained"}} -->
<section class="wp-block-group dh-section dh-ct-hero"><!-- wp:group {"className":"dh-ct-hero__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-ct-hero__grid"><!-- wp:group {"className":"dh-ct-hero__intro dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-ct-hero__intro dh-reveal-group"><!-- wp:paragraph {"className":"dh-eyebrow dh-reveal--rise"} -->
<p class="dh-eyebrow dh-reveal--rise">Contact · 15 branches</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"dh-ct-hero__title dh-reveal--rise"} -->
<h1 class="wp-block-heading dh-ct-hero__title dh-reveal--rise">Reach the branch <span class="dh-highlight" data-reveal="wipe">nearest your site</span>.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-ct-lead dh-reveal--rise"} -->
<p class="dh-ct-lead dh-reveal--rise">Pick your city in the list or on the plan. Every branch has a named person who answers for it, its own address and its own opening hours.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"dh-ct-actions dh-reveal-group"} -->
<div class="wp-block-buttons dh-ct-actions dh-reveal-group"><!-- wp:button {"className":"is-style-dh-pill-solid dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-solid dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="#request">Send a request</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-dh-pill-outline dh-reveal--dot"} -->
<div class="wp-block-button is-style-dh-pill-outline dh-reveal--dot"><a class="wp-block-button__link wp-element-button" href="tel:+966114634102">Call 011 463 4102</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:demas-theme/branch-finder /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"request","className":"dh-section dh-ct-request","layout":{"type":"constrained"}} -->
<section id="request" class="wp-block-group dh-section dh-ct-request"><!-- wp:group {"className":"dh-ct-request__grid","layout":{"type":"default"}} -->
<div class="wp-block-group dh-ct-request__grid"><!-- wp:group {"className":"dh-ct-request__copy dh-reveal-group","layout":{"type":"default"}} -->
<div class="wp-block-group dh-ct-request__copy dh-reveal-group"><!-- wp:paragraph {"className":"dh-eyebrow dh-reveal--rise"} -->
<p class="dh-eyebrow dh-reveal--rise">Send a request</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"dh-ct-title dh-reveal--rise"} -->
<h2 class="wp-block-heading dh-ct-title dh-reveal--rise">Tell us what you need.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dh-ct-text dh-reveal--rise"} -->
<p class="dh-ct-text dh-reveal--rise">Parts and prices, a site visit, a repair: your request goes to the branch you pick.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"className":"dh-ct-label dh-reveal--rise"} -->
<h3 class="wp-block-heading dh-ct-label dh-reveal--rise">What happens next</h3>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true,"className":"dh-ct-next dh-reveal--rise"} -->
<ol class="wp-block-list dh-ct-next dh-reveal--rise"><!-- wp:list-item -->
<li>It goes to the branch you picked, and to the person who answers there.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>They call or email you back, usually within two business days.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>You get a recommendation and a quotation.</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:demas-theme/request-form /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
