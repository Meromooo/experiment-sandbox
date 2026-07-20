# Demas Theme (Sandbox) — CLAUDE.md

This is a custom, no-page-builder WooCommerce **block theme**. It runs on a sandbox WordPress
install (`cornflowerblue-fish-235112.hostingersite.com`) that shares a Hostinger hosting account
with the live demas-group.com site, but is otherwise fully isolated — no live content, database,
or files are ever touched from work on this theme. See the repo `README.md` for the one-line
summary and a pointer to the research doc behind this direction.

Deploys via Git auto-deploy: pushing to `main` on this repo lands directly in
`wp-content/themes/demas-theme` on the sandbox site. There is no build step — files in this repo
are the files WordPress reads.

## Version targets

- WordPress: 6.5+
- WooCommerce: current stable, block-compatible (declared via `before_woocommerce_init` in
  `inc/woocommerce.php`)
- PHP: 8.1+

## Folder purposes

- `assets/css/`, `assets/js/`, `assets/images/` — hand-written front-end assets, enqueued via
  `inc/enqueue.php`. No build tooling (webpack/vite/etc.) — if one becomes necessary, that's a
  deliberate architectural decision to make explicitly, not to slip in.
- `inc/` — PHP includes, one concern per file, all required from `functions.php`:
  - `setup.php` — theme support flags only (title-tag, thumbnails, WooCommerce support, etc.)
  - `enqueue.php` — front-end script/style registration only
  - `navigation.php` — nav menu registration
  - `woocommerce.php` — WooCommerce compatibility declarations and any theme-side WC integration
  - `catalog-filters.php` — product catalog filtering behavior (category/attribute sidebar) —
    intended to eventually replace the live site's custom `wp:html` sidebar filter script
  - `structured-data.php` — JSON-LD / schema.org output for products and organization
- `patterns/` — registered block patterns (PHP files with pattern header comments), for reusable
  content blocks editors can insert — this is where reusable marketing/content blocks belong, not
  hardcoded into templates.
- `parts/` — template parts referenced by `templates/*.html` (header, footer). Keep these thin;
  push real content into patterns or template-parts, not directly into `parts/header.html`.
- `templates/` — top-level block templates (`index.html` is the only one WordPress strictly
  requires to activate; `single-product.html` and `archive-product.html` are WooCommerce-specific).
- `template-parts/` — smaller reusable template fragments organized by concern (`header/`,
  `product/`, `navigation/`), for pieces that are shared across templates but aren't full parts.
- `woocommerce/` — classic WooCommerce template overrides, used only as a last resort (see
  Forbidden Patterns below).

## Forbidden patterns

- **No page-builder shortcodes or markup** (Elementor, WPBakery, etc., or their leftover shortcode
  syntax). This theme's entire purpose is to prove a no-page-builder architecture works.
- **No hardcoded content that belongs in a pattern.** If it's marketing copy, a repeated layout,
  or anything an editor would reasonably want to change without touching code, it belongs in
  `patterns/`, not baked into a template or template-part.
- **No `!important` in CSS without a comment explaining the specificity conflict it resolves.**
  If you can't name the conflict, the `!important` is masking a specificity problem to fix instead.
- **No classic WooCommerce template overrides in `woocommerce/` unless a WooCommerce block
  genuinely cannot achieve the needed markup or behavior.** Block-theme-native WooCommerce blocks
  (`woocommerce/single-product`, `woocommerce/product-image-gallery`, etc.) are the default; only
  drop to a classic override as a last resort, and document why in the override file itself.
- **No direct database queries** (`$wpdb` writes, raw SQL) from theme code. Use WordPress/WooCommerce
  APIs. `functions.php` in particular must stay a thin loader — no risky logic lives there directly.
- **No assumptions about plugins beyond WooCommerce.** Don't reference or depend on Kadence,
  page-builder plugins, or anything not explicitly installed on this sandbox.
- **No copying live-site content, credentials, or database rows into this repo or the sandbox
  site.** Dummy/placeholder content only — see the product-seeding step in the scaffold brief for
  the naming convention used for fake catalog data.

## Working agreement

- Keep `functions.php` a pure loader — one `require_once` per concern file in `inc/`, nothing else.
- Prefer WordPress core / WooCommerce blocks over custom PHP whenever a block can do the job.
- Every new top-level concern gets its own file in `inc/`, not bolted onto an existing one.
- This file should stay current — when the architecture changes, update this file in the same
  commit, not as an afterthought.
