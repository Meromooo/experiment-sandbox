---
paths:
  - "inc/{navigation,system-map,catalog-filters,product-page,woocommerce,sheet-view,structured-data,typography}.php"
  - "src/{catalog-toolbar,system-index,product-meta,product-summary,mega-menu}/**"
  - "templates/{single-product,archive-product,taxonomy-product_cat}.html"
  - "demas-mega-menu-content-spec.md"
---

# Catalogue: categories, product pages, sheet view, no-cart, JSON-LD

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

The product category tree is locked: make it easier to navigate, never re-parent or relabel it in the theme.

## `inc/navigation.php`

  - `navigation.php` — nav menu registration, the mega-menu icon set, and the catalogue
    structure both the mega menu and the footer read: `demas_theme_get_catalogue_structure()`
    (group → subcategory order, filter `demas_theme_mega_menu_structure`),
    `demas_theme_get_catalogue_external_links()` and `demas_theme_get_catalogue_columns()`
    (resolved to terms, one query)

## `inc/woocommerce.php` (compatibility)

  - `woocommerce.php` — WooCommerce compatibility declarations and any theme-side WC integration

## `inc/system-map.php`, `inc/catalog-filters.php`, `inc/product-page.php`

  - `system-map.php` — how the catalogue's categories are read (the file keeps its old name):
    `demas_theme_term_product_count()` (inclusive counts), `demas_theme_get_term_kind()`
    (a part type, a brand or a model series) and the brand lists behind it.
    `demas_theme_get_product_cat_index()` reads every product category once per request (by
    slug, and children by parent in the store's category order, with their term meta): look
    terms up there rather than with `get_term_by()` or a `get_terms( parent )` per term — one
    query each, which made the category index ~105 queries a page until AMM-164.
    **Category names are shown exactly as the store (and the live site) has them, under their
    own parents.** Until 2026-10-08 this file drew the catalogue as the stages of physical
    systems ("Source, Carry, Join, Control, Deliver"; fog "Treat, Pump… Move air"; workshop
    "Cut, Drill, Saw, Weld"), which put invented names over the real ones and showed
    Irrigation's pipes and fittings on the Landscape page. Ammar found the names meaningless;
    the map is gone. Don't regroup or relabel categories in the theme again: a different
    grouping is a change to the category tree, made in the store (and on live at cutover).
  - `catalog-filters.php` — registers the `demas-theme/catalog-toolbar` block (count, child-category
    rail with a "By brand / By type / By series" label, sort links; server-rendered, no JS) and
    the `demas-theme/system-index` block — the **category index** (block title "Category
    Index"; the old block name stays because templates place it): an eyebrow with the
    top-level category, then one column per sub-category, each with its own sub-categories
    beneath, names and order as the store has them, the buyer's position marked with
    `aria-current`. A category page shows its own top-level category (Landscape: Controllers,
    Valves, Rotors); the shop root, the 404 and an empty search show every top-level category
    with sub-categories (not Swimming Pool). Empty categories are left out. It
    also adds SKU ordering via a `posts_clauses` join. The toolbar also carries the search field;
    on a search its rail lists categories whose names match and its default sort is "Best
    match". There are no product attributes in the
    catalogue — specs live in description HTML — so there is nothing to facet by; category,
    brand-as-category, SKU and name are the only real axes.
  - `product-page.php` — registers the `demas-theme/product-summary` block (the single
    product page's datasheet: category, name, nameplate, quote action, specification) and its
    helpers: the product's deepest-first category chain, brand and series read from that
    chain, a title-length tier, and
    `demas_theme_clean_description()`. That last one matters: the imported descriptions carry
    the old site's CSS pasted in as visible text (53 products), ~3,500 inline style attributes
    and Elementor/chat-tool wrapper markup. It is cleaned **at render only** — the database is
    untouched. Brand children count as "series" only under Hunter, Rain Bird and Irritrol
    (`demas_theme_get_series_brand_slugs()` in `system-map.php`); under tool brands they are
    types.

## `inc/woocommerce.php` — no cart, no checkout

  - `woocommerce.php` also holds **no cart, no checkout** (decided 2026-09-24): every product is
    SAR 0.00, so `woocommerce_is_purchasable` is false, `/cart` and `/checkout` 302 to the
    catalogue, and the mini-cart plus Cart/Checkout menu links are stopped in
    `pre_render_block`. Nothing is deleted — remove the filters and WooCommerce's cart comes
    back. Link to the catalogue with `demas_theme_catalogue_url()` — the product archive, which
    is `/products/` because WooCommerce's shop page is the cloned "Products" page. `/shop/` is
    an unrelated empty "All Products" page, retired to `/products/` (AMM-147).

## `inc/sheet-view.php`

  - `sheet-view.php` — **Sheet view** (AMM-141, 2026-09-28): every catalogue page (archive,
    categories, search) shows its cards as Gallery or as a numbered **parts sheet** from one
    set of markup, reflowed by CSS (`src/product-meta/style.scss`; the card wrappers become
    `display: contents` cells). `?view=sheet` is honoured server-side (`.is-sheet` on the
    `.dh-grid--catalogue` product-collection); a remembered choice (`localStorage`
    `demas-theme/view`) is applied by an inline `<head>` script before paint
    (`.dh-view-sheet` on `<html>`) — not a cookie, because a CDN-cached page would ignore it.
    Registers `demas-theme/product-meta` (line number continued across pages, part number —
    marked on searches — and category path, each with a screen-reader label). Its stylesheet
    loads only when a card renders, so anything that must hold on an **empty** listing lives
    in `style.css` instead — the column headings' base `display: none` does (they showed as raw
    text on a search with no results). 24 parts a page
    (`loop_shop_per_page`). The toolbar carries the Gallery | Sheet links and a print-only
    heading; `main.js` switches in place with View Transitions. Printing any catalogue page
    gives the sheet. Sheet rows carry a quantity field (in the compact quote button) that adds
    that quantity, and edits the quote once the part is in it.

## `inc/structured-data.php`

  - `structured-data.php` — JSON-LD (AMM-155, 2026-09-28), built on the plugins' own generators
    and adjusted only through their documented filters. **One breadcrumb trail per page:** on
    WooCommerce pages (product, category, catalogue, search) it is WooCommerce's — the same
    trail the page shows, from the category tree, which is identical to the live site's —
    with names fully decoded (`20″`, not `20&amp;#8243;`); Yoast's breadcrumb piece and its
    WebPage reference are dropped there (`wpseo_schema_graph_pieces`, `wpseo_schema_webpage`).
    Yoast keeps WebSite, Organization and WebPage. **No Product markup while there are no
    prices:** WooCommerce's generator refuses a product without an offer, rating or review
    (Google's rule too), and writing our own would have filled Search Console with ~643
    invalid items. The generator is started on the datasheet template and its filter is ready
    — the day a product has a real price or reviews, WooCommerce writes its Product with
    clean names, the cleaned description, the category path, brand, sku, and mpn only for
    manufacturer numbers (never DMS-); a zero price is never published. Branch locations in
    the Organization wait for real addresses (AMM-148).

## `inc/typography.php`

  - `typography.php` — hyphens stay hyphens (2026-10-08). WordPress runs `wptexturize()` over
    every block template, which turned the spaced hyphen in "Clamp Saddle - PN6, PN16" (stored
    and shown on live with a hyphen) into an en dash in its heading, breadcrumbs, the category
    index, the toolbar and every card's path, and gave 22 product names dashes nobody typed.
    `wptexturize()` takes those dashes from WordPress's translations ("en dash", "em dash"),
    so a `gettext_with_context_default` filter answers both with `-`: only the inserted dashes
    go; curly quotes, ellipses and inch marks stay, and a dash someone typed stays as typed.

## `templates/single-product.html`

  `single-product.html` (AMM-138, 2026-09-24) is a datasheet: `core/post-featured-image` for the
  photo (not WooCommerce's gallery — 641 of 643 products have one image, and the gallery pulls
  in jQuery, flexslider and photoswipe), the product-summary block, and a related-parts
  `product-collection`. No price, no add-to-cart, no tabs.

## Mega menu content spec

- `demas-mega-menu-content-spec.md` — locked mega-menu category/subcategory content, sourced
  directly from the live demas-group.com site. Category/subcategory names, structure, and depth
  (two levels) here are authoritative — don't invent or alter them. **Implemented 2026-09-15**:
  column and item order live in `demas_theme_get_catalogue_structure()` in
  `inc/navigation.php` (filterable via `demas_theme_mega_menu_structure`; the footer's
  catalogue index reads the same list), the Non-Woven outbound link via
  `demas_theme_mega_menu_external_links`, and one schematic icon per subcategory slug in
  `demas_theme_get_category_icon()`. **Amended 2026-09-17:** Swimming Pool (59 products, no
  subcategories) is a sixth column by Ammar's decision; the Non-Woven slug is `non-wooven`
  (misspelled on live, matched deliberately). See the amendment note at the top of the spec.
