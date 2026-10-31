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
    AMM-153 that page holds the homepage's own blocks, no Kadence). Page photos get a `sizes` for the 46rem reading column (not on the
    homepage or a Designed page, which have none; AMM-239) and all but the
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
    and, since AMM-189, the Contact page's map (`src/branch-finder/render.php`).

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
  `DEMAS-Certificates.pdf` already in the media library; LinkedIn is the company page. The
  closing line's first link is the privacy page (`get_the_privacy_policy_link( '<li>', '</li>' )`,
  AMM-162: the page chosen in Settings → Privacy, titled as that page; nothing until one is
  chosen). **AMM-153:** all six

## `parts/`

- `parts/` — template parts referenced by `templates/*.html`. `header.html` carries the
  **logo** (the `header-brand` pattern, 2026-10-31: `assets/images/demas-logo-white.svg`, the
  whole logo in paper, linking home with the alt text "Demas, home"; 3.5rem tall from 48rem,
  the band's own content height, so the band stays 80px, and 2.75rem below, its 44px tap
  target; its two text lines are too small to read at that size, Ammar's option A. A theme
  file, not the Site Logo setting, which is saved content and still holds the old site's
  logo. It replaced the Site Title block), the mega-menu block, the navigation block, the finder block (the header search) and
  the quote-drawer block (which hosts the quote store — don't remove it); `footer.html` only places the `footer` pattern (the part
  renders the `<footer>` landmark itself). The navigation block carries
  its own two links (Services, Contact → `/contact-us/`, the rebuilt Contact page since AMM-169)
  — left empty it falls back to the cloned site's only navigation post, a Page List of every
  page (AMM-145). WooCommerce
  would also block-hook a customer-account icon and a mini-cart after the navigation block;
  both are removed in `inc/woocommerce.php` (`hooked_block_types`). Keep these
  thin — push real content into patterns, not directly into the part.
- **The header is a canopy band (AMM-198, 2026-10-16).** `.dh-header__bar` is a rounded canopy
  panel with paper type, inset from the screen edges by `--dh-gutter` (`style.css`, header
  section: spacing-20 on phones, spacing-40 from 48rem). The homepage hero frame uses the same
  gutter and sits that far under the band (`.wp-site-blocks > .dh-home` has no top margin; the
  hero's top padding is the gutter); every other page keeps main's own top margin. Controls in
  the band are paper; hover, the open menu and the current page are field with ink text. Their
  focus rings turn paper through an explicit `:is()` list of the band's own controls, not a
  descendant rule, so the finder and quote dialogs and the open hamburger overlay keep the
  default ring. Core's skip link sits outside the header but opens over the band, so it is a
  second selector on that rule (AMM-204). From 56.0625rem the header part is **sticky** under the admin bar
  (`top: var(--wp-admin--admin-bar--height, 0px)`). **`--dh-header-h`** (AMM-231, on `html`
  at the same width: the admin bar plus 5rem, the band's measured 80px; a `shortcut:` note)
  is how much of the screen it covers. `html` takes it as `scroll-padding-top` (plus
  spacing-20), so keyboard focus, anchor jumps and `scrollIntoView` land below the band
  instead of under it (WCAG 2.4.11). On catalogue pages it also adds **`--dh-toolbar-h`**
  (AMM-233), the stuck toolbar's height, which wraps (71 to 129px), so `main.js` section 9
  sets it on `html` from one `ResizeObserver`; without JS the padding clears the header
  only. Every other sticky element offsets from it: the
  catalogue toolbar at `var(--dh-header-h)`, and the product photo, Contact's branch list
  and request copy at `calc(var(--dh-header-h) + spacing-50)`. A new sticky element does
  the same, and a change to the header's height changes the 5rem. Then the band gains its shadow over the
  first 8rem of scroll (a scroll-driven animation, only where supported). The shadow is on a
  `::after` layer of the band and only its `opacity` animates, so the band isn't repainted on
  every scroll frame (AMM-199); with reduced motion the shadow switches on without a fade
  (`step-end`) once 8rem have scrolled, Ammar's call. Below 56rem the bar is a wrapping flex row: the logo (its end margin `auto`), the links, finder, quote and
  hamburger at the end, and the product menu on its own full-width line beneath (`order: 1`;
  the nav wrapper is `display: contents`). The mega menu's closed panel must collapse to zero
  height there (AMM-159) or the header grows ~530px.
- **The product menu (the mega menu) is a disclosure navigation menu with a top-level link
  (AMM-198).** "Products" is a link to the catalogue (`demas_theme_catalogue_url()`); the
  chevron button beside it shows and hides the panel for touch and keyboard (Escape closes and
  returns focus to it); a mouse opens it by hovering. The floating panel sits 0.6rem below the
  band, and a **hover bridge** (`.dh-mega-menu.is-open::after`, in `style.css`'s 56.0625rem
  block) covers that gap so the pointer can travel down into the panel without leaving the
  nav. The bridge hangs on the nav, not the panel, because the panel's `clip-path` would clip
  it. Below 56rem the chevron button is 44px tall itself rather than through the tap-area
  list: at 320px with 200% text it wraps under the link, where a stretched tap area would
  cover "Products". There is no close delay; add one only if closing feels twitchy. The
  panel opens and closes in 250ms (`motion.fast`, AMM-199), then its columns fade in 70ms
  apart; with reduced motion the panel only fades (no clip, no columns' lift, AMM-194); opened or closed from the keyboard it appears at once: while the chevron is
  `:focus-visible` (which the browser sets only after keyboard use) a `:has()` rule turns the
  panel's and columns' transitions off.

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
  the eyebrow). The Contact page also uses the Designed page template: see `quote-and-contact.md`.

  **The Services redesign, first two parts (AMM-192, 2026-10-13)**, after four mockup rounds
  and the taste audit. The hero is on paper with no eyebrow: one weight of Archivo throughout
  (700, the display width), the first line canopy and the second, the **Bold format** on a
  line of its own, ink with "kept running." (full stop included) on a sky-blue pill; the
  opening sentence; the **figures**, a list whose numbers are Bold, each number on sky
  blue ("46 years in business", "5 year warranty"); the two buttons a full 44px with a
  direction mark each (`::after` masks: an arrow across, a chevron down; the outline one on
  white), the selector two classes deep (`.dh-svc-hero .wp-block-button > …`) because the pill
  rule in section 4 is three. The crew photo is 4:5 with a canopy-tinted shadow, and the offer
  ("Survey & design / Free site visit / Anywhere in the Kingdom.") is a **card over the
  photo's lower edge** (`.dh-svc-offer`, three paragraphs; it rises in after the photo grows),
  not the notch tab. Phones: the second line runs on (three lines, not four) and the buttons
  come before the figures (flex `order`), so both buttons make a 320x568 first screen. Under
  the hero, the **trust row** (`.dh-svc-trust`: the branches and the certificates, each list
  item's class giving its mark) and the **services band** (`.dh-svc-steps`: an ordered list,
  not numbered, each title a link to its section and the whole step its target) replace the
  numbered stage line. The survey section is the visit (`.dh-svc-visit`): title, lead, button
  and photo (hidden under 56rem) beside the four visit steps drawn as the **route** shared
  with Contact (`.dh-svc-route`; see `quote-and-contact.md`), the checks a list nested in the
  third step (`.dh-svc-route__checks`, placed in the step's text column). The checks strip,
  "What we check" label and outcome line are gone, and so are their rules. Page 822 takes the
  new markup by Ammar's paste in the Code editor (the pattern and the page must match for the
  cutover).

  **The survey as a folder (AMM-195, 2026-10-13)** replaced AMM-192's services band and
  side-by-side visit, after three mockup rounds and the taste audit (Ammar: "messy, zero
  styling"). The trust row stays under the hero. `#survey` (`.dh-svc--folder`) is a folder:
  its **tabs** (`.dh-svc-tabs`, a core list, in-page links styled as a folder's tabs, not a
  tabs widget, so no `tablist` role) are the open one, Survey & design, plain text in the
  board's own canopy with a field line on top, continuing the board's left edge and joined to
  its top by the notch's fillet; and Installation and Maintenance behind it, darker, each a
  link down the page with a chevron that nudges on hover (hover only on devices that hover).
  On press the tab dips (0.97 from its bottom edge, a fade under reduced motion), not its
  link: a transform on the link would make it the containing block of its `::before`, which
  covers the tab, so the tap area would shrink mid-press.
  The **board** (`.dh-svc-board`, canopy, top-left corner square under the open tab) holds the
  heading ("visit" in the Highlight format, swept in sky blue as on Contact's request
  frame), the opening text, the paper "Request a site visit" button and the route on the left,
  and the site-survey photo (media 2898, AMM-239) full height on the right, its left edge fading into the
  board. The route is the shared one with paper tabs; each step is its name in the Bold format
  and one line, so on Services a step is a block with the tab in its margin rather than the
  shared two-column grid. The steps' copy is Ammar's as saved on page 822 (2026-10-14: "&"
  and "!" opening steps 1 and 4, and step 3 a name only; the five checks and their rules are
  gone). The tabs rise in turn
  through the site's reveal classes (`dh-reveal-group`, `dh-reveal--rise` on the list items).
  The board needs `.dh-svc-folder > .dh-svc-board { margin-block: 0 }`: the folder is a flow
  layout, whose `* + *` gap otherwise opens a strip between the tabs and the board. The board
  trims its photo with `overflow: clip`, not `hidden` (AMM-194): `hidden` makes it a scroll
  container, which the route's view timeline tracks instead of the page, so the steps' line
  froze part drawn. Any box around a scroll-driven animation clips the same way. Under
  56rem the tabs stack, the open one last, on the board. Known for the next sections: the
  hero, this board and Installation are three image-and-text splits in a row (the taste skill
  allows two), and the page has two dark blocks (this board and the closing call to action).

## No `single.html`; `template-parts/`; `woocommerce/`

  There is no `single.html`: the site has
  no blog posts, and `index.html` covers the fallback.
- `template-parts/` — **not yet created** (as of 2026-07-28 audit). Once it exists: smaller
  reusable template fragments organized by concern (`header/`, `product/`, `navigation/`), for
  pieces that are shared across templates but aren't full parts.
- `woocommerce/` — classic WooCommerce template overrides, used only as a last resort (see
  Forbidden Patterns below).
