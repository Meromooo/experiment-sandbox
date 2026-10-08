---
paths:
  - "inc/search.php"
  - "src/{finder,search-empty}/**"
  - "templates/product-search-results.html"
---

# Search results and the header finder

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

## `inc/search.php`

  - `search.php` — product search (AMM-143, 2026-09-28). A `request` filter makes **every
    front-end search a product search** (content pages are not searchable), so WooCommerce
    serves `templates/product-search-results.html`. The main search is title/excerpt matches —
    **not full descriptions** (AMM-164, 2026-10-03: scanning them cost 86 ms a search, and the
    stored descriptions still carry the old site's CSS as text, so they mostly added junk
    matches) — ∪ `wc_get_products( 'sku' )` (partial part-number match; `tcn ft 062` also tries
    `tcn-ft-062`), its own LIKE clause emptied via `posts_search`, and — unless the buyer chose a
    sort — ranked exact SKU → SKU prefix → name → rest. On results, `render_block` filters mark
    the matched fragment (`<mark class="dh-hit">`) in titles and SKUs, tag an exact part-number
    hit, and make the query-title just the quoted query. Registers `demas-theme/search-empty`
    (the no-results state). `demas_theme_search_form()` is the one search field — toolbar and
    404 use it. The matching lives in `demas_theme_find_product_ids()` /
    `demas_theme_find_categories()`, shared by the results page and the **header finder**
    (AMM-142, `demas-theme/finder`, registered here): a REST route `demas-theme/v1/find?q=`
    returns up to 5 categories and 8 parts with paths, part numbers and an exact flag, so the
    finder and the results page always agree. The finder is a native modal `<dialog>` with an
    ARIA combobox, opened by its header control or `/`; its view module is plain TypeScript,
    not an Interactivity store (rows mark substrings, which the store's templating can't).
    Without JavaScript the control is a link to the catalogue.

## `templates/product-search-results.html`

  `product-search-results.html` (AMM-143) is the search results page: the query as the h1,
  the toolbar, the catalogue cards with each product's part number instead of its price, and an
  empty state (`search-empty` block + the system index).
