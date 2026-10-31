<?php
/**
 * Title: Header logo
 * Slug: demas-theme/header-brand
 * Categories: demas
 * Inserter: no
 * Description: The Demas logo in paper on the header's canopy band, linking to the home page.
 *
 * Placed by parts/header.html in place of the Site Title block (2026-10-31).
 * A theme file, not the Site Logo setting: that setting is saved content and
 * still holds the old site's logo. The file is made by
 * tools/make-logo-white.py; its two text lines are too small to read at band
 * height, so the alt text carries the link's name.
 *
 * fetchpriority="auto": WordPress would otherwise give the first large image in
 * the header fetchpriority="high", taking it from the page's real LCP image.
 *
 * @package Demas_Theme
 */

?>
<!-- wp:html -->
<a class="dh-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/demas-logo-white.svg' ) ); ?>" width="1536" height="772" alt="Demas, home" fetchpriority="auto"></a>
<!-- /wp:html -->
