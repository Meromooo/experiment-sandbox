---
paths:
  - "assets/**"
  - "theme.json"
  - "style.css"
  - "functions.php"
  - "inc/{setup,enqueue,performance,cache,site-icons}.php"
---

# Assets, enqueues, performance, cache, icons and theme.json

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

No jQuery and no classic WooCommerce assets on any page (AMM-154): after any enqueue change, run a network trace on home, catalogue and product pages and compare with AMM-154's table.

## `assets/` — CSS, JS, fonts, images

- `assets/css/`, `assets/js/` — hand-written front-end assets, enqueued via `inc/enqueue.php`.
  No build tooling for these — they are plain files. `editor.js` is editor-only (the Highlight
  format, AMM-153). `style.css` holds the shape grammar
  (cards, pills, the concave notch), marquee, and reveal motion; `main.js` is the small
  dependency-free script that drives reveals, the marquee loop and the certificates belt
  (AMM-178), counters (which mark their stat `.is-counted` so its drawing runs, AMM-184), the Branch Desk (including `#branch-xxx` deep links), the homepage
  footer slide-over and the catalogue's Gallery / Sheet switch.
- `assets/fonts/` — self-hosted woff2 subsets (Archivo variable; IBM Plex Sans, Plex Sans
  Arabic, Plex Mono), SIL OFL. Registered through `theme.json` `fontFace` — never via a
  third-party font CDN. Fetched from the Google Fonts API on 2026-09-14; the fetch script
  is not kept, the files are. IBM Plex Sans is one variable file per subset
  (`ibm-plex-sans-latin-400-600.woff2`, `…-latin-ext-400-600.woff2`) declared once for weights
  400–600; the API had served the same file for each weight and it was downloaded three times
  (AMM-154). `style.css` section 2 holds **metric-matched stand-in faces** ("Archivo Fallback",
  "IBM Plex Sans Fallback": Arial scaled to the web fonts' measured widths), listed after the
  web fonts in `theme.json`'s stacks, so text wraps the same before and after the fonts swap
  in. Change a face or its width axis and those values must be re-measured.
- `assets/images/` — hand-placed theme imagery (logo, icons); photography for content goes
  through the Media Library, not this folder. Since 2026-10-05 it holds the **icon set**, made
  by `tools/make-icons.py` from two rectangle-only masters of the Demas "dm" mark (blue
  `#1B7EB2`, indigo `#232D8E`, the bowl's ground white, as in the logo): `demas-mark.svg` (the
  full mark, measured from the logo PNG Ammar supplied) and `favicon.svg` (a tab-size
  simplification on a 16px grid — the full mark is too tall and thin to read at 16px; shape from
  a ChatGPT sketch Ammar approved, redrawn so every bar is two whole pixels). Plus
  `favicon.ico` (16/32/48), `apple-touch-icon.png` (180), `icon-192.png`, `icon-512.png`,
  `icon-maskable-512.png` (all on a `paper` tile) and `site.webmanifest`. Change an icon by
  editing the masters in the script and re-running it, never by hand-editing a PNG.

## `inc/setup.php`, `inc/enqueue.php`, `inc/performance.php`

  - `setup.php` — theme support flags only (title-tag, thumbnails, WooCommerce support, etc.),
    plus `add_editor_style( 'assets/css/style.css' )` so the editor shows the homepage sections
    as the site does (AMM-153)
  - `enqueue.php` — front-end script/style registration only
  - `performance.php` — what the front end does **not** load (AMM-154, 2026-09-28):
    WooCommerce's classic stylesheets (`woocommerce_enqueue_styles`, plus
    `woocommerce-blocktheme`) and classic scripts (woocommerce, add-to-cart, blockUI,
    js-cookie), which takes jQuery with them; order attribution (sourcebuster, `sbjs_*`
    cookies — there is no checkout to attribute); the emoji script. WooCommerce **block**
    styles stay. Fonts are deliberately not preloaded: measured, it delayed first paint
    150–200 ms on a slow phone connection. No setting is changed — drop the file from
    `functions.php` and it all comes back.

## `inc/cache.php`, `inc/site-icons.php`

  - `cache.php` — the page cache's deploy purge (AMM-164, 2026-10-05). The sandbox runs the
    **LiteSpeed Cache** plugin (installed by Ammar; only its page cache is on, every
    "optimisation" off, no QUIC.cloud), and the Hostinger CDN now keeps copies of the cached
    pages too (`x-litespeed-cache: hit`, `x-hcdn-cache-status: HIT`). A cached page links the
    CSS/JS of the deploy it was made under, so on `wp_loaded` the newest modification time among
    the theme's files (dot-entries skipped) is compared with the `demas_theme_deploy_stamp`
    option; when it moved, every page is purged through `do_action( 'litespeed_purge_all' )`.
    That happens on the first request PHP runs after a deploy (a search, the finder, a 404, the
    admin), not at the deploy itself. Without the plugin it does nothing. Search results stay
    uncached (the plugin's default; each query is its own URL and rarely repeats). Nothing in
    a page may vary by visitor: no cookies, nonces or user data in the HTML, which the theme
    never had (per-visitor state lives in `localStorage` and the browser).
  - `site-icons.php` — the browser-tab and home-screen icons (2026-10-05). Replaces WordPress's
    Site Icon tags on the front end (`remove_action( 'wp_head', 'wp_site_icon', 99 )`) with the
    theme's set in `assets/images/`: `favicon.ico` (`sizes="32x32"`), `favicon.svg`,
    `apple-touch-icon.png`, `site.webmanifest`, each with its file time as `?ver=`, and a
    `theme-color` read from the `theme.json` palette (`paper`; the manifest repeats `#FAF9F6`, so
    change both together). Without the files it falls back to WordPress's tags. The Site Icon
    setting stays for the admin and login screens. No `/favicon.ico` at the domain root yet:
    that would be server work for Ammar, and browsers follow the link tags. Rules:
    `.claude/skills/favicon-cheat-sheet`.

## `theme.json` — block-level styles

    **Don't set a block-level style in `theme.json` that reaches beyond the block** (like
    `core/paragraph` → `lineHeight`, whose rule is `:root :where(p)`): WordPress prints it
    only on pages where that block renders, so the same `<p>` — even the header's site title
    — got 1.55 on one page and 1.6 on another. Removed 2026-09-30; paragraphs take the
    body's 1.55 everywhere.
