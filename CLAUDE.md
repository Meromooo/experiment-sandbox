# Demas Theme (Sandbox) — CLAUDE.md

This is a custom, no-page-builder WooCommerce **block theme**. It runs on a sandbox WordPress
install (`cornflowerblue-fish-235112.hostingersite.com`) that shares a Hostinger hosting account
with the live demas-group.com site, but is otherwise fully isolated — no live content, database,
or files are ever touched from work on this theme. See the repo `README.md` for the one-line
summary and a pointer to the research doc behind this direction.

Deploys via Git auto-deploy: pushing to `main` on this repo lands directly in
`wp-content/themes/demas-theme` on the sandbox site. There is no server-side build step — the
files in this repo are the files WordPress reads, which is why compiled block output is
committed (see ADR-001 under Working agreement).

## Version targets

- WordPress: 6.5+
- WooCommerce: current stable, block-compatible (declared via `before_woocommerce_init` in
  `inc/woocommerce.php`)
- PHP: 8.1+

## Folder purposes

- `assets/css/`, `assets/js/` — hand-written front-end assets, enqueued via `inc/enqueue.php`.
  No build tooling for these — they are plain files. `style.css` holds the shape grammar
  (cards, pills, the concave notch), marquee, and reveal motion; `main.js` is the small
  dependency-free script that drives reveals and the marquee loop.
- `assets/fonts/` — self-hosted woff2 subsets (Archivo variable; IBM Plex Sans, Plex Sans
  Arabic, Plex Mono), SIL OFL. Registered through `theme.json` `fontFace` — never via a
  third-party font CDN. Fetched from the Google Fonts API on 2026-09-14; the fetch script
  is not kept, the files are.
- `assets/images/` — **not yet created.** Reserved for hand-placed theme imagery (logo,
  icons); photography for content goes through the Media Library, not this folder.
- `tools/` — one-off operational scripts run by a human on the host, never by the theme at
  runtime (currently: `create-product-categories.sh`, the WP-CLI script that builds the
  locked category tree). Nothing here is loaded by `functions.php`.
- `inc/` — PHP includes, one concern per file, all required from `functions.php`:
  - `setup.php` — theme support flags only (title-tag, thumbnails, WooCommerce support, etc.)
  - `enqueue.php` — front-end script/style registration only
  - `navigation.php` — nav menu registration
  - `woocommerce.php` — WooCommerce compatibility declarations and any theme-side WC integration
  - `catalog-filters.php` — product catalog filtering behavior (category/attribute sidebar) —
    intended to eventually replace the live site's custom `wp:html` sidebar filter script
  - `structured-data.php` — JSON-LD / schema.org output for products and organization
- `patterns/` — **not yet created** (as of 2026-07-28 audit — build in Phase 6). Once it exists:
  registered block patterns (PHP files with pattern header comments), for reusable content blocks
  editors can insert — this is where reusable marketing/content blocks belong, not hardcoded into
  templates.
- `parts/` — template parts referenced by `templates/*.html` (header, footer). Exists today, but
  is currently thin/unconnected (mega-menu block not yet placed in `parts/header.html`). Keep
  these thin once wired up; push real content into patterns or template-parts, not directly into
  `parts/header.html`.
- `templates/` — top-level block templates (`index.html` is the only one WordPress strictly
  requires to activate; `single-product.html` and `archive-product.html` are WooCommerce-specific).
  `front-page.html` does not exist yet — the live site currently falls back to the default blog
  index (Phase 6).
- `template-parts/` — **not yet created** (as of 2026-07-28 audit). Once it exists: smaller
  reusable template fragments organized by concern (`header/`, `product/`, `navigation/`), for
  pieces that are shared across templates but aren't full parts.
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
  **Documented exception:** `hostinger-reach` (a Hostinger subscription/marketing block plugin) is
  active on the sandbox and is intentional, confirmed 2026-07-28 — not a stray default install.
  Don't build anything that depends on it working, but no need to flag or remove it.
- **No copying live-site credentials, database rows, or *design* (Kadence markup, theme options,
  CSS, its palette or typefaces) into this repo or the sandbox.** The live site's visual design is
  being replaced wholesale and nothing from it carries over.
  **Content is different — reuse is authorized (2026-09-14):** company copy, the category tree,
  service descriptions, and branch cities with staff names may be used as-is. Staff **email
  addresses never appear in the repo, in markup, or in JavaScript** — the branch→address map
  lives server-side outside version control, and the contact form posts a branch ID, not an
  address. The 14 "Sandbox …" products remain dummy data until real catalogue data lands.

## Design & content reference docs

These live in the repo root, are not code, and should be treated as **standing reference
material for every session** — not one-time `/spec` input to read once and discard. Consult
them for any front-end, visual, or content work on this theme.

**Direction change, 2026-09-14.** The visual direction is now the reference recording analysed
in `demas-motion-reference.md` (card composition, concave notch, grow-from-seed motion,
marquee) re-skinned in Demas's own register — *not* the navy/gold/serif system the older docs
describe. Until `demas-design-direction.md` is rewritten, **`theme.json` is the single source
of truth for tokens** (palette: paper / sand / field / canopy / ink / water; type: Archivo
display, IBM Plex Sans + Plex Sans Arabic body, Plex Mono data; radii, notch, motion under
`settings.custom`). Locked facts: 46 years of operation; 15 branches with named staff; a
branch-routed contact form replaces published email addresses.

- `demas-homepage-brief.md` — homepage content/copy/section brief. **Content and section
  order remain valid; its visual notes (navy/gold, serif, hero treatment) are superseded** —
  see the banner at the top of the file.
- `demas-mega-menu-content-spec.md` — locked mega-menu category/subcategory content, sourced
  directly from the live demas-group.com site. Category/subcategory names, structure, and depth
  (two levels) here are authoritative — don't invent or alter them.
- `demas-design-direction.md` — **superseded 2026-09-14** (banner at top). Its pattern
  decisions (sticky condensing header, stat counters, trust-strip hover, no testimonials
  without real ones) still hold; its visual system does not. Rewrite pending.
- `demas-motion-reference.md` — motion mechanics (primitives, durations, easing) analysed from the
  reference recording in `references/`, with an adopt/adapt/skip list. Supplies the "how" behind the
  reveal/counter/sticky-header patterns `demas-design-direction.md` locks in; not a design to copy.

## Working agreement

- Keep `functions.php` a pure loader — one `require_once` per concern file in `inc/`, nothing else.
- Prefer WordPress core / WooCommerce blocks over custom PHP whenever a block can do the job.
- Every new top-level concern gets its own file in `inc/`, not bolted onto an existing one.
- This file should stay current — when the architecture changes, update this file in the same
  commit, not as an afterthought.
- **Build step (ADR-001):** any custom block using TypeScript/React (`@wordpress/scripts`)
  must be compiled with `npm run build` before committing. The compiled `build/` output is
  committed to git alongside source — Hostinger's git auto-deploy has no build step of its
  own, so a stale or missing `build/` folder means the change isn't actually live. Always
  run `npm run build` and confirm `build/` is staged before every commit that touches a block.
- **`three`, `@react-three/fiber`, `@react-three/drei` in `package.json`** are intentionally
  pre-installed, unused as of 2026-07-28. They're reserved for a planned phase-2 scroll-driven
  pipe/particle-flow scene (see the system design doc's "Future ideas" section) — not scope creep,
  don't remove them, but also don't treat their presence as a green light to start that work before
  it's actually scheduled.
