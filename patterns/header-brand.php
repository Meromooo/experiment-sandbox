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
 * tools/make-logo-white.py, side by side (the mark left of the two lines) so
 * it fills a wide, short slot; the alt text carries the link's name.
 *
 * fetchpriority="auto": WordPress would otherwise give the first large image in
 * the header fetchpriority="high", taking it from the page's real LCP image.
 *
 * @package Demas_Theme
 */

?>
<!-- wp:html -->
<a class="dh-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/demas-logo-white-wide.svg' ) ); ?>" width="3133" height="770" alt="Demas, home" fetchpriority="auto"></a>
<!-- /wp:html -->
