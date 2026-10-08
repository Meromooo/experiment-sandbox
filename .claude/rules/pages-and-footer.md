---
paths:
  - "inc/{pages,footer,key-plan-land}.php"
  - "patterns/{services,footer,not-found}.php"
  - "templates/**"
  - "parts/**"
  - "src/{catalogue-index,branch-plan}/**"
  - "woocommerce/**"
---

# Pages, templates, parts, the footer, Services and the 404

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

## `inc/pages.php`

  - `pages.php` — generic pages (AMM-146, 2026-09-27). Every cloned content page (Services,
    Contact Us, the old Homepage) is built from **Kadence Blocks**; a `render_block` filter strips
    Kadence classes, inline styles, per-block `<style>`, row-separator SVGs and data attributes
    at render and demotes their `<h1>`s, so the content shows in this theme's type and none of
    the old design survives. Database untouched; core-block pages unaffected. Kadence forms
    render nothing. Kadence's front-end **assets** are dropped too (AMM-154): its stylesheets,
    per-block CSS, slider/form scripts and its Google Fonts `<link>` to the old site's
    typeface (Trykker) — the plugin parsed page 17's content even on the homepage (since
    AMM-153 that page holds the homepage's own blocks, no Kadence). Page photos get a `sizes` for the 46rem reading column and all but the
    first load lazily. Also holds `demas_theme_retired_pages()`: Contact Us (publishes 15 staff
    emails) redirects to the homepage Branch Desk, and the empty "All Products" page (`/shop/`)
    to the catalogue at `/products/` (AMM-147) — both 302 until cutover. A retired page comes
    back once it is set to the Designed page template (choosing the template is the switch, so
    the old content is never served in between) — Contact Us does, as the Contact page
    (AMM-169). Live and sandbox share
    the same URL structure (`/products/`, `/product/…`, `/product-category/…`), so product and
    category URLs survive cutover unchanged.

## `inc/footer.php`

  - `footer.php` — registers the footer's two server-rendered blocks (AMM-144, 2026-09-28):
    `demas-theme/catalogue-index` (every group and subcategory, from the shared catalogue
    structure) and `demas-theme/branch-plan` (the branch links beside a **key plan**, a
    **locator map**: one dot per branch at its real latitude/longitude; hovering a branch
    lights its dot via generated `:has()` CSS; the dots reveal outward from Riyadh. **Since
    AMM-188** it draws the real land from `inc/key-plan-land.php`: the Kingdom as a paper
    sheet with a canopy outline, the neighbours a shade darker than sand, three water-lines
    along the coasts (the land's outline stroked in widening bands, water then sand, under the
    land itself, so the sand band must match the footer's background), the surroundings
    fading out at the frame's edges; a graticule, a north arrow and a 500 km scale bar. The
    frame holds the whole Kingdom (34.2-56.0°E, 15.9-32.6°N), so the plan is 288 x 242 at its
    18rem width; sizes meant in pixels are multiplied by `--_u`, set on the SVG).

## `inc/key-plan-land.php`

  - `key-plan-land.php` — **generated** by `tools/make-key-plan.py` (AMM-188):
    `demas_theme_get_key_plan_land()`, the footer key plan's frame and its land (the Kingdom
    and its neighbours) as SVG path data in the plan projection. Read by the branch-plan block
    only.

## `patterns/` — the set, the footer and the 404

- `patterns/` — registered block patterns (PHP files with pattern header comments), filed under
  the "Demas" category declared in `inc/patterns.php`. This is where marketing/content sections
  live — never hardcoded into templates. Current set (homepage, 2026-09-15): `hero`,
  `credentials`, `numbers`, `categories`, `process`, `closing-cta`; plus `not-found` (the 404
  head, core blocks, not inserter-visible) and `footer` (AMM-144 — the site footer drawn as an
  engineering drawing's **title block**: catalogue index, branches + key plan, the company
  plate, the closing line; not inserter-visible). The plate's registered name (English, plus
  the Arabic of the CR certificate as a `lang="ar"` line), CR number (1010028038) and VAT
  number (300054069400003) come from the certificates PDF (AMM-162); "Our certificates" links the
  `DEMAS-Certificates.pdf` already in the media library; LinkedIn is the company page. **AMM-153:** all six

## `parts/`

- `parts/` — template parts referenced by `templates/*.html`. `header.html` carries the site
  title, the mega-menu block, the navigation block, the finder block (the header search) and
  the quote-drawer block (which hosts the quote store — don't remove it); `footer.html` only places the `footer` pattern (the part
  renders the `<footer>` landmark itself). The navigation block carries
  its own two links (Services, Contact → `/contact-us/`, the rebuilt Contact page since AMM-169)
  — left empty it falls back to the cloned site's only navigation post, a Page List of every
  page (AMM-145). WooCommerce
  would also block-hook a customer-account icon and a mini-cart after the navigation block;
  both are removed in `inc/woocommerce.php` (`hooked_block_types`). Below 56rem the header is a
  two-row grid in `style.css` (title + controls, then the full-width product menu); the mega
  menu's closed panel must collapse to zero height there (AMM-159) or the header grows ~530px. Keep these
  thin — push real content into patterns, not directly into the part.

## `templates/`

- `templates/` — top-level block templates (`index.html` is the only one WordPress strictly
  requires to activate; `single-product.html` and `archive-product.html` are WooCommerce-specific).
  `single-product.html` (AMM-138, 2026-09-24) is a datasheet: `core/post-featured-image` for the
  photo (not WooCommerce's gallery — 641 of 643 products have one image, and the gallery pulls
  in jQuery, flexslider and photoswipe), the product-summary block, and a related-parts
  `product-collection`. No price, no add-to-cart, no tabs.

## `templates/` — page, 404, Designed page, Services

  `page.html` (title + content, readable measure) and `404.html` (the `not-found` pattern plus
  the stage index for every system) added 2026-09-27. `designed-page.html` (AMM-167,
  2026-10-01) is the **"Designed page"** custom template (`theme.json` `customTemplates`),
  picked per page in the editor: post-content full width, no automatic title, no reading
  column (and `inc/pages.php` leaves its photo `sizes` alone). The Services page uses it with
  the `services` pattern (`patterns/services.php`: core blocks, the Service Record job card,
  the category-cards **"List"** style for "What we install"; CSS in `style.css` section 15,
  `dh-svc-` classes); its hero opens on CSS like the homepage's (the AMM-168 rules, scoped to
  `.page-template-designed-page .dh-svc-hero`). Version 0.4.0 for the new pattern file. It
  passed the taste skill's audit in AMM-171 (2026-10-03): side-by-side and stacked sections
  alternate (hero, survey stacked with its checks as one ruled inspection strip, installation,
  maintenance stacked with the Service Record over its photo across the page, call to action);
  two eyebrows (hero, call to action), the stage line under the hero numbering the services; a
  headline sized to its column (`cqi`, two lines from 768px up, both buttons on a 320x568
  phone); one label per intent ("Request a site visit"). The record's overhang below its photo
  needs a two-class selector: WordPress zeroes a flow layout's last child's end margin with
  `:root :where(.is-layout-flow) > :last-child`, which outweighs one class. The 404 got the
  same audit: a 19-word lead, a title that takes two lines on a phone, and its category index
  headed by each top-level category's name in the display face (no eyebrow dot or capitals;
  `.dh-404 .dh-line__eyebrow` in `style.css` section 12; the catalogue and an empty search keep

## No `single.html`; `template-parts/`; `woocommerce/`

  homepage hero (page 17) and Services (page 822) buttons were re-pointed in the editor. There is no `single.html`: the site has
  no blog posts, and `index.html` covers the fallback.
- `template-parts/` — **not yet created** (as of 2026-07-28 audit). Once it exists: smaller
  reusable template fragments organized by concern (`header/`, `product/`, `navigation/`), for
  pieces that are shared across templates but aren't full parts.
- `woocommerce/` — classic WooCommerce template overrides, used only as a last resort (see
  Forbidden Patterns below).
