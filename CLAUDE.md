# Demas Theme (Sandbox) — CLAUDE.md

This is a custom, no-page-builder WooCommerce **block theme**. It runs on a sandbox WordPress
install (`cornflowerblue-fish-235112.hostingersite.com`) that shares a Hostinger hosting account
with the live demas-group.com site, but is otherwise fully isolated — no live content, database,
or files are ever touched from work on this theme. See the repo `README.md` for the one-line
summary and a pointer to the research doc behind this direction.

Deploys via Git auto-deploy, **gated by CI** (AMM-156, 2026-09-28): a push to `main` runs
`.github/workflows/checks.yml` — `php -l` on every PHP file (PHP 8.3, as the sandbox), WordPress
Coding Standards (`phpcs.xml.dist`, zero violations and blocking), `build/` must match `src/`,
`theme.json`/`block.json` must parse, and no email address anywhere in the repo. Only when all
pass does the `deploy` job fast-forward the **`deploy` branch**, which is what Hostinger deploys
into `wp-content/themes/demas-theme` on the sandbox (the sandbox site's Git setting was switched
from `main` to `deploy` on 2026-09-29, with Ammar's OK, via the Hostinger API — sandbox domain
only). Proven end to end the same day: a stale `build/` pushed to `main` was blocked and never
reached the sandbox; the rebuild passed, moved `deploy`, and Hostinger deployed it on its own. A failing push never reaches the sandbox;
GitHub emails the owner. Never push to `deploy` by hand. Results are readable without signing
in through the commit's check runs (`/commits/<sha>/check-runs` in GitHub's API); the PHPCS
result is posted as its own "PHPCS report" check with one annotation per violation. There is no
server-side build step — the files in this repo are the files WordPress reads, which is why
compiled block output is committed (see ADR-001 under Working agreement).

## Skills to use in this repo

Ammar's standing instruction (2026-09-27): these three are always on for this project, and
`ponytail` is added for debugging and fixing (2026-09-29).

**Invoke the two front-end skills at the start of every front-end task — every plan, every
build, and every fix or audit-fix pass — each time.** A skill loaded for an earlier task does
not count. (A 2026-09-29 check of the transcript found them skipped on six front-end tasks —
AMM-143, AMM-142, AMM-141, AMM-163 and two fix passes — and AMM-141 shipped 27px touch targets
and low-contrast text that frontend-ui-engineering's checklist exists to catch.)

- **`frontend-design:frontend-design`** — invoke it before any front-end, visual, template,
  pattern, CSS or block work (`templates/`, `parts/`, `patterns/`, `src/*`, `assets/css/`,
  `theme.json`). It is not needed for data scripts, server or security work, redirects, docs or
  Linear updates — its "take an aesthetic risk" brief is noise there.
- **`agent-skills:frontend-ui-engineering`** — invoke it alongside frontend-design for the same
  front-end work. frontend-design sets the direction; this one holds the engineering floor:
  accessibility (keyboard, focus, labels, live regions, 44px touch targets), responsive checks at
  320 / 768 / 1024 / 1440, empty and error states, and the design-system tokens in `theme.json`.
  Its checklist is how front-end work is verified before it goes to In Review.
- **`caveman:caveman`** (full intensity) — terse replies in chat. Code, commit messages, PR
  descriptions, repo docs (this file, ADRs, READMEs) and Linear issues stay in normal prose;
  security warnings, irreversible actions, step-by-step instructions and explanations of new
  terms drop back to plain language. The caveman plugin's hooks already switch it on each
  session; this line keeps it on if they ever don't.
- **`ponytail`** — invoke **only when debugging or fixing code**: a bug, a failing CI check, an
  audit finding, a broken behaviour. Not for new features, plans, docs or Linear. It governs the
  fix: read and trace the real flow first, fix the root cause where every caller routes through
  rather than the one symptom reported, reuse what the codebase already has, smallest correct
  diff, never cutting security, accessibility or error handling. Two adjustments for this repo:
  its "code first, three short lines" output rule does not apply to reports, Linear comments or
  explanations for Ammar (he is learning — those are requested); and its "leave one runnable
  check" means the CI checks plus a verification on the sandbox, not new test files in the
  theme (they would deploy to the site). Installed 2026-09-29 as the skill file only
  (`~/.claude/skills/ponytail/SKILL.md`, from github.com/DietrichGebert/ponytail, MIT) — not the
  plugin, whose hooks would switch it on for every session.
- **`design-taste-frontend`** (the "taste skill", added 2026-10-02 at Ammar's request) — a
  strict design critic for **marketing surfaces only**: the homepage, Services, Contact, the
  404 and the footer. Not for the catalogue, sheet view, product datasheet, search, finder or
  quote list (the skill itself excludes data tables and product UI), and never for debugging,
  PHP or performance (ponytail). It is expensive (~35k tokens), so:
  1. **Invoke it once per marketing-page task**, at the plan or audit step, alongside the two
     front-end skills — not on every turn of the build.
  2. **Start with its one-line "design read".** For Demas: trust-first B2B (contractors,
     municipalities, facility managers), not premium consumer — which keeps its consumer-only
     rules (the cream/brass palette ban) from firing.
  3. **Use its "redesign — preserve" mode** (its section 11): URLs, nav labels and content stay
     stable, which the cutover (AMM-158) depends on.
  4. **Run its final pre-flight checklist (its section 14) as an audit**; findings go to
     Linear, and fixes follow the usual plan → go-ahead → build flow.

  **Its rules apply to the site's visible copy and layout** — Ammar likes them, including the
  hero subtext ≤ 20 words, max one eyebrow per three sections, max one middle dot per line,
  and **no em dash or en dash in anything a visitor reads**. (Repo docs, code comments, commit
  messages and Linear are not site copy; the dash ban does not reach them.)

  **It does not override decisions already locked here:** the stack stays a WordPress block
  theme with plain CSS and `theme.json` tokens (install none of the React / Tailwind / Motion /
  design-system packages it names); the hand-drawn schematic icon set stays (its "never
  hand-roll SVG icons" rule is overridden); the theme is light-only until Ammar decides
  otherwise (its mandatory dark mode is not applied); no stock or picsum photography on the
  site (real photos wait on AMM-149); the branch-city marquee is real content, not a "locale
  strip". Installed as a project skill in the main checkout's `.claude/skills/` (from
  github.com/leonxlnx/taste-skill, commit ce26fc2, MIT), kept out of git by
  `.git/info/exclude` so it never deploys into the theme folder.

## Version targets

- WordPress: 6.5+
- WooCommerce: current stable, block-compatible (declared via `before_woocommerce_init` in
  `inc/woocommerce.php`)
- PHP: 8.1+

## Folder purposes

- `assets/css/`, `assets/js/` — hand-written front-end assets, enqueued via `inc/enqueue.php`.
  No build tooling for these — they are plain files. `editor.js` is editor-only (the Highlight
  format, AMM-153). `style.css` holds the shape grammar
  (cards, pills, the concave notch), marquee, and reveal motion; `main.js` is the small
  dependency-free script that drives reveals, the marquee loop, counters, the Branch Desk
  (including `#branch-xxx` deep links), the homepage footer slide-over and the catalogue's
  Gallery / Sheet switch.
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
- `assets/images/` — **not yet created.** Reserved for hand-placed theme imagery (logo,
  icons); photography for content goes through the Media Library, not this folder.
- `tools/` — one-off operational scripts run by a human on the host, never by the theme at
  runtime. `create-product-categories.sh` (WP-CLI, builds the locked category tree; superseded
  by the 2026-09-17 live clone). `assign-house-skus.php` + `house-skus.csv` (run with
  `wp eval-file`, dry-run by default): issues `DMS-<SEG>-<NNN>` house references to the 172
  products that had no manufacturer SKU and files the 23 uncategorised ones — see the file
  header for the grammar and why `DMS-` sits in the brand slot. Nine rows are flagged
  `DUPLICATE` and get a category but no number; they are for Demas to delete. Nothing here is
  loaded by `functions.php`.
- `docs/adr/` — architecture decision records: why a hard-to-reverse choice was made, what was
  rejected, what it costs. See `docs/adr/README.md` for the index. **ADR-002** records the
  platform/theme decision (WordPress + custom block theme over a page builder or headless) and
  lists five corrections the current build still needs — read it before proposing a change of
  stack, hosting, or editing model.
- `inc/` — PHP includes, one concern per file, all required from `functions.php`:
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
  - `navigation.php` — nav menu registration, the mega-menu icon set, and the catalogue
    structure both the mega menu and the footer read: `demas_theme_get_catalogue_structure()`
    (group → subcategory order, filter `demas_theme_mega_menu_structure`),
    `demas_theme_get_catalogue_external_links()` and `demas_theme_get_catalogue_columns()`
    (resolved to terms, one query)
  - `patterns.php` — block pattern category registration only
  - `woocommerce.php` — WooCommerce compatibility declarations and any theme-side WC integration
  - `system-map.php` — the catalogue read as physical systems: `demas_theme_get_system_map()`
    lists the stages of the irrigation, fog and workshop lines and which existing `product_cat`
    slugs sit at each; `demas_theme_get_term_kind()` says whether a term is a part type, a
    brand or a model series. Pure data + helpers, filterable, **re-parents nothing** — the tree is
    locked; this only decides where a term is shown. Swimming Pool has no children and no entry.
  - `catalog-filters.php` — registers the `demas-theme/catalog-toolbar` block (count, child-category
    rail with a "By brand / By type / By series" label, sort links; server-rendered, no JS) and
    the `demas-theme/system-index` block (the stage line rendered from `system-map.php`), and
    adds SKU ordering via a `posts_clauses` join. The toolbar also carries the search field;
    on a search its rail lists categories whose names match and its default sort is "Best
    match". There are no product attributes in the
    catalogue — specs live in description HTML — so there is nothing to facet by; category,
    brand-as-category, SKU and name are the only real axes.
  - `product-page.php` — registers the `demas-theme/product-summary` block (the single
    product page's datasheet: category, name, nameplate, quote action, specification) and its
    helpers: the product's deepest-first category chain, brand and series read from that
    chain, the stage it sits at on its system line, a title-length tier, and
    `demas_theme_clean_description()`. That last one matters: the imported descriptions carry
    the old site's CSS pasted in as visible text (53 products), ~3,500 inline style attributes
    and Elementor/chat-tool wrapper markup. It is cleaned **at render only** — the database is
    untouched. Brand children count as "series" only under Hunter, Rain Bird and Irritrol
    (`demas_theme_get_series_brand_slugs()` in `system-map.php`); under tool brands they are
    types.
  - `quote.php` — registers the quote list's two blocks (AMM-139, 2026-09-24):
    `demas-theme/quote-button` ("Add to quote" — full on the product page, compact on catalogue
    cards) and `demas-theme/quote-drawer` (the header's "Your quote" control and the list, a
    native modal `<dialog>`). They share one Interactivity API store, `demas-theme/quote`,
    defined in `src/quote-drawer/view.ts` — so the drawer must stay in `parts/header.html` or
    every quote button stops working. The list lives in the buyer's `localStorage` as a snapshot
    per part; nothing is sent anywhere until AMM-140. **Print / Copy list / Share** (AMM-163,
    2026-09-28): the dialog foot prints the list as a parts sheet (built as DOM on `<body>` only
    while printing, under `html.dh-print-quote`, so it prints from any page; Ctrl+P while the
    list is open does the same), and copies it as plain text — or opens the phone's share sheet
    where `navigator.share` exists. The sheet's contact line is filterable
    (`demas_theme_quote_sheet_contact`); it carries the company phone and the Branch Desk, never
    a staff email.
  - `woocommerce.php` also holds **no cart, no checkout** (decided 2026-09-24): every product is
    SAR 0.00, so `woocommerce_is_purchasable` is false, `/cart` and `/checkout` 302 to the
    catalogue, and the mini-cart plus Cart/Checkout menu links are stopped in
    `pre_render_block`. Nothing is deleted — remove the filters and WooCommerce's cart comes
    back. Link to the catalogue with `demas_theme_catalogue_url()` — the product archive, which
    is `/products/` because WooCommerce's shop page is the cloned "Products" page. `/shop/` is
    an unrelated empty "All Products" page, retired to `/products/` (AMM-147).
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
  - `branches.php` — the 15 branches as one list, keyed by the Branch Desk's three-letter
    codes (`demas_theme_get_branches()`: city, lat/lon, main) and `demas_theme_branch_url()`
    (`/#branch-jed`, which selects that city on the homepage Desk). AMM-140 will route by these
    codes. No email address belongs here. Each branch also carries its `person` (who answers
    there), shown on the homepage Branch Desk (AMM-153) — names only, never an address. Since
    AMM-169 each also has `map` (its Google Maps link, from the live Contact Us page), `address`
    and `hours`, read from those public listings on 2026-10-01 for Demas to confirm; the
    listings' phone numbers are deliberately not copied. Hours are written as people read them
    (`Sat-Thu 08:00-12:00 16:00-20:00; Fri closed`; empty = not listed) and parsed by
    `demas_theme_branch_hours()` into display lines and a week of minutes.
    `demas_theme_branch_plan_point()` is the one lat/lon → plan projection, used by the
    footer's key plan and the Contact page's layout plan.
  - `contact.php` — the Contact page (AMM-169): registers `demas-theme/branch-finder` (the
    branches as a list of in-page links, the chosen branch's card — photo, who answers there,
    address, hours with an "Open now" status worked out in Riyadh time in the browser,
    directions — and the branches drawn as an **irrigation layout plan**: Riyadh head office
    as the pump, a mainline along its latitude, a lateral to each branch's real position;
    choosing a branch runs water to it and the head sprays). Works without JavaScript: every
    city and plan head links to its card (`#branch-jed`) and CSS shows the `:target` one; the
    view module (`src/branch-finder/view.ts`) swaps cards in place and announces choices as a
    `demas-theme:branch` event for the request form. A photo per branch is the block's only
    setting (its sidebar; the drawn placeholder until then), so the editor gets the branch
    list as `window.demasThemeBranches`. The block's wrapper is `display: contents`; the
    page's hero grid (`style.css` section 15) places its list and its stage (card over plan).
  - `footer.php` — registers the footer's two server-rendered blocks (AMM-144, 2026-09-28):
    `demas-theme/catalogue-index` (every group and subcategory, from the shared catalogue
    structure) and `demas-theme/branch-plan` (the branch links beside a **key plan**: one dot
    per branch at its real latitude/longitude on a graticule, no drawn border; hovering a
    branch lights its dot via generated `:has()` CSS; the dots reveal outward from Riyadh).
  - `search.php` — product search (AMM-143, 2026-09-28). A `request` filter makes **every
    front-end search a product search** (content pages are not searchable), so WooCommerce
    serves `templates/product-search-results.html`. The main search is widened to title/content
    matches ∪ `wc_get_products( 'sku' )` (partial part-number match; `tcn ft 062` also tries
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
  - `homepage.php` — the homepage's editable sections (AMM-153, from 2026-09-30). Registers the
    small theme blocks core blocks can't replace — `demas-theme/credentials` + `/credential` (the
    marks under the hero; a mark with a detail gets a keyboard-focusable tooltip), `/stats` +
    `/stat` (the numbers band: a `<dl>`, label as the term and the number shown above it by CSS
    `order`; a whole number counts up via `data-count`) and `/steps` + `/step` (the process: an
    `<ol>` whose numerals are a CSS counter, each step a locked heading + paragraph) and
    `/category-cards` (the catalogue gateway: one server-rendered block whose `cards`
    attribute lists product_cat slugs with the editor's name and description, edited in the
    sidebar; link and **live** product count come from `demas_theme_get_catalogue_columns()`
    and `demas_theme_term_product_count()`, and a slug with an outbound link in
    `demas_theme_get_catalogue_external_links()` — Non-Woven — becomes the sister-site card;
    the editor canvas shows it through ServerSideRender), `/branch-desk` (every branch as
    a button with who answers there, all from `demas_theme_get_branches()`; eyebrow and title
    edited in the sidebar), `/schematic` (the self-drawing irrigation line; its corner note
    edited in the sidebar) and `/marquee` (the scrolling lines under the hero: the branch
    cities or an editor's list; a focusable `role="region"`, so keyboard focus pauses it and,
    with reduced motion, scrolls it). Enqueues `assets/js/editor.js`, the **Highlight**
    rich-text format (`<span class="dh-highlight" data-reveal="wipe">`) for the headline's
    green word — a plain editor script, no build step. Registers
    the pills as **Button block styles** (`is-style-dh-pill-solid`, `-outline`, `-paper`,
    `-outline-paper`; rules beside `.dh-pill` in `style.css` section 4). And the **reveal
    bridge**: a `render_block` filter that turns classes on core blocks (`dh-reveal--rise`,
    `--bar`, `--dot` …, `dh-reveal-group`, `dh-reveal-content`) into the `data-reveal*`
    attributes the motion code reads — the browser gets the same markup as before, so
    `main.js` and the motion CSS barely change. A `data-reveal-group` numbers only **its own**
    reveals (`main.js`): a group inside it numbers itself, and a reveal inside another reveal
    (the highlighted word) inherits its container's `--i` and sweeps two beats after it — so no
    inline `--i` is needed. **A reveal steps aside once it lands** (AMM-166): its starting state
    applies only `:not(.is-in)`, so it lands on the element's own values and asserts none of its
    own; once the element's own transitions have finished (`getAnimations()` — at once if none
    ran, e.g. a reveal already in its final state when it comes into view) `main.js` adds
    `.is-settled`, which ends the reveal's transition and drops the clip of `dot`, `sliver` and
    `bar` (with reduced motion there is no clip at all). The clip left in place cut off focus
    rings, the credentials' tooltips, the category cards' hover shadow and the first digit of
    their counts; the reveal's `transform: none` and transition, left in place, outranked the
    cards' own hover lift (it never showed) — so a component styles its own revealed element as
    usual, no special case needed. **The hero is not a scroll reveal** (AMM-168): it is on screen
    from the first paint, and `main.js` loads in the footer, so it switched the reveals on too
    late to hide the hero — the hero never animated. It now plays as the page opens on CSS
    keyframes alone (`style.css` section 7, `.home .dh-hero`, no `.js` gate): eyebrow, headline,
    the word's sweep, lead, buttons, then — above 56rem only, where they share the first screen —
    the Branch Desk and the schematic drawing itself; on narrower screens those two stay scroll
    reveals. `main.js` leaves alone any reveal whose computed `animation-name` is not `none`,
    marking it `.is-in .is-settled` *before* it adds `.js`, so the breakpoint lives in CSS only.
    Cost: the headline (the LCP element) reaches the screen ~160ms after first paint.
    Core blocks inside a group get the block
    layout's margins (the first block's are zeroed), so a rule the old HTML got from browser
    defaults has to be written down — see the section eyebrows in `style.css` section 9.
    **Don't set a block-level style in `theme.json` that reaches beyond the block** (like
    `core/paragraph` → `lineHeight`, whose rule is `:root :where(p)`): WordPress prints it
    only on pages where that block renders, so the same `<p>` — even the header's site title
    — got 1.55 on one page and 1.6 on another. Removed 2026-09-30; paragraphs take the
    body's 1.55 everywhere.
- `patterns/` — registered block patterns (PHP files with pattern header comments), filed under
  the "Demas" category declared in `inc/patterns.php`. This is where marketing/content sections
  live — never hardcoded into templates. Current set (homepage, 2026-09-15): `hero`,
  `credentials`, `numbers`, `categories`, `process`, `closing-cta`; plus `not-found` (the 404
  head, core blocks, not inserter-visible) and `footer` (AMM-144 — the site footer drawn as an
  engineering drawing's **title block**: catalogue index, branches + key plan, the company
  plate, the closing line; not inserter-visible). The footer's CR number, VAT number and
  registered name read "Pending" until Ammar supplies them; "Our certificates" links the
  `DEMAS-Certificates.pdf` already in the media library; LinkedIn is the company page. **AMM-153:** all six
  homepage patterns are editable blocks (core blocks + the theme blocks in `inc/homepage.php`),
  pixel-identical to the old HTML at 320–1440 apart from deliberate fixes (live category counts,
  Branch Desk code contrast, the pills' focus ring). Five of them are now the content of Pages →
  Homepage (see `templates/` below), so for the homepage they are starting points, not the
  source; `closing-cta` is still placed by the template.
- `parts/` — template parts referenced by `templates/*.html`. `header.html` carries the site
  title, the mega-menu block, the navigation block, the finder block (the header search) and
  the quote-drawer block (which hosts the quote store — don't remove it); `footer.html` only places the `footer` pattern (the part
  renders the `<footer>` landmark itself). The navigation block carries
  its own two links (Services, Contact → the homepage Branch Desk) — left empty it falls back
  to the cloned site's only navigation post, a Page List of every page (AMM-145). Contact must
  not point at the cloned Contact Us page: it publishes staff email addresses. WooCommerce
  would also block-hook a customer-account icon and a mini-cart after the navigation block;
  both are removed in `inc/woocommerce.php` (`hooked_block_types`). Below 56rem the header is a
  two-row grid in `style.css` (title + controls, then the full-width product menu); the mega
  menu's closed panel must collapse to zero height there (AMM-159) or the header grows ~530px. Keep these
  thin — push real content into patterns, not directly into the part.
- `templates/` — top-level block templates (`index.html` is the only one WordPress strictly
  requires to activate; `single-product.html` and `archive-product.html` are WooCommerce-specific).
  `single-product.html` (AMM-138, 2026-09-24) is a datasheet: `core/post-featured-image` for the
  photo (not WooCommerce's gallery — 641 of 643 products have one image, and the gallery pulls
  in jQuery, flexslider and photoswipe), the product-summary block, and a related-parts
  `product-collection`. No price, no add-to-cart, no tabs.
  `front-page.html` is used for the front page regardless of the Reading setting. Since
  AMM-153 (2026-10-01) its `<main>` renders **`core/post-content`**: the homepage's sections
  (hero, credentials, numbers, categories, process) are the content of the static front page,
  the cloned "Homepage" page, id 17 — keep it published — and Demas edits them in **Pages →
  Homepage**. Changing a pattern file no longer changes the homepage; edit the page (the
  patterns stay in the inserter under "Demas" to re-insert a section). Its Kadence content was
  replaced on the sandbox by a paste of the block markup; the same paste is a cutover step on
  live (AMM-158). Its closing
  CTA sits **outside `<main>`**, in a `.dh-finale` wrapper with the footer part, so the CTA can
  stay pinned while the footer slides over it (CSS sticky; `main.js` supplies the CTA's
  height); the CTA is its own labelled region instead.
  `product-search-results.html` (AMM-143) is the search results page: the query as the h1,
  the toolbar, the catalogue cards with each product's part number instead of its price, and an
  empty state (`search-empty` block + the system index).
  `page.html` (title + content, readable measure) and `404.html` (the `not-found` pattern plus
  the stage index for every system) added 2026-09-27. `designed-page.html` (AMM-167,
  2026-10-01) is the **"Designed page"** custom template (`theme.json` `customTemplates`),
  picked per page in the editor: post-content full width, no automatic title, no reading
  column (and `inc/pages.php` leaves its photo `sizes` alone). The Services page uses it with
  the `services` pattern (`patterns/services.php`: core blocks, the Service Record job card,
  the category-cards **"List"** style for "What we install"; CSS in `style.css` section 15,
  `dh-svc-` classes); its hero opens on CSS like the homepage's (the AMM-168 rules, scoped to
  `.page-template-designed-page .dh-svc-hero`). Version 0.4.0 for the new pattern file. The
  Contact Us page uses it with the `contact` pattern (`patterns/contact.php`, AMM-169: the
  intro and the Branch Finder as the hero, then the request section; `dh-ct-` classes), its
  hero opening the same way at 56rem and up. Version 0.5.0 for that pattern file. There is no `single.html`: the site has
  no blog posts, and `index.html` covers the fallback.
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
  (two levels) here are authoritative — don't invent or alter them. **Implemented 2026-09-15**:
  column and item order live in `demas_theme_get_catalogue_structure()` in
  `inc/navigation.php` (filterable via `demas_theme_mega_menu_structure`; the footer's
  catalogue index reads the same list), the Non-Woven outbound link via
  `demas_theme_mega_menu_external_links`, and one schematic icon per subcategory slug in
  `demas_theme_get_category_icon()`. **Amended 2026-09-17:** Swimming Pool (59 products, no
  subcategories) is a sixth column by Ammar's decision; the Non-Woven slug is `non-wooven`
  (misspelled on live, matched deliberately). See the amendment note at the top of the spec.
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
- **CSS class names are global — namespace them per component.** A block's stylesheet and
  `assets/css/style.css` all load on the same page, so two components sharing a class name
  style each other. The footer once reused `.dh-plate` (the product page's nameplate) and turned
  it unreadable; it is `.dh-foot-plate` now. Before naming a class, grep the repo for it.
- **No jQuery, no classic WooCommerce assets.** Since AMM-154 no page loads jQuery,
  WooCommerce's classic CSS/JS, order attribution, emoji or Kadence assets. Front-end code is
  dependency-free (plain JS, TypeScript modules, the Interactivity API); don't add anything
  that pulls jQuery back in. After any change to enqueues, run a network trace on home,
  catalogue and product pages and compare with the before/after table in AMM-154.
- **Adding a file to `patterns/` needs a version bump.** WordPress caches a theme's pattern list
  keyed on the `Version:` in the root `style.css`, so a new pattern file is invisible — a
  `wp:pattern` pointing at it renders nothing — until that version changes. Bump `Version:` and
  `DEMAS_THEME_VERSION` in `functions.php` together (0.2.0 on 2026-09-27, for `not-found`;
  0.3.0 on 2026-09-28, for `footer`; 0.4.0 for `services`; 0.5.0 on 2026-10-01, for `contact`).
- **`three`, `@react-three/fiber`, `@react-three/drei` in `package.json`** are intentionally
  pre-installed, unused as of 2026-07-28. They're reserved for a planned phase-2 scroll-driven
  pipe/particle-flow scene (see the system design doc's "Future ideas" section) — not scope creep,
  don't remove them, but also don't treat their presence as a green light to start that work before
  it's actually scheduled.
