---
paths:
  - "inc/homepage.php"
  - "inc/category-drawings.php"
  - "inc/patterns.php"
  - "patterns/{hero,credentials,numbers,categories,process,closing-cta}.php"
  - "src/{stats,stat,steps,step,category-cards,credentials,credential,schematic,marquee,pivot-field,branch-desk}/**"
  - "templates/front-page.html"
  - "assets/js/main.js"
  - "assets/js/editor.js"
---

# Homepage: sections, blocks, reveals, the hero

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

The homepage's sections are the content of Pages → Homepage (page 17); the patterns are starting points. Change a section's copy in both, or the cutover paste (AMM-158) brings the old copy back.

## `inc/patterns.php`

  - `patterns.php` — block pattern category registration only

## `inc/homepage.php` — blocks, reveal bridge, hero

  - `homepage.php` — the homepage's editable sections (AMM-153, from 2026-09-30). Registers the
    small theme blocks core blocks can't replace — `demas-theme/credentials` + `/credential` (the
    **certificates belt** under the hero, AMM-178, 2026-10-05: each credential is an approval
    plate with a logo from the Media Library (a file from `tools/credential-logos.py`), drawn in
    `canopy` at rest as a CSS mask and in its own colours on hover or focus, plus a hang tag:
    detail, issuer and, with a link, "View certificate"; without a logo the plate shows the
    name. The belt is full width and loops the other way from the supply strip over three sets
    (copies, plates, copies; `main.js` adds the copies, `aria-hidden` and `inert`), stamps in
    the first time it comes into view, eases to a stop under the pointer and, on keyboard
    focus, stops and glides the focused plate to mid belt; with reduced motion it is a still
    wall. Only a credential with a tag or a link takes focus), `/stats` +
    `/stat` (the numbers band: a `<dl>`, label as the term and the number shown above it by CSS
    `order`; a whole number counts up via `data-count`. Since AMM-184 a stat can carry a **unit**
    word beside the number ("Years"; on the homepage band it replaces the visible label, which
    stays in the `<dl>` visually hidden, via `:has(.dh-stat__unit)`) and a **drawing** under it,
    server-rendered and `aria-hidden`: `scale` (a drafting scale, a tick a year from `since` to
    today) or `network` (the branch network: head office from `demas_theme_get_branches()` as the
    pump, a sprinkler head per other branch). `main.js`'s count-up marks the stat `.is-counted`
    and CSS runs the drawing in step with it (900ms, ease-out; each head's moment is `--d`,
    computed in `render.php`); finished without JavaScript or with reduced motion) and `/steps` + `/step` (the process: an
    `<ol>` whose numerals are a CSS counter in a canopy tab, each step a locked heading only, its
    name, under a canopy rule with an arrowhead that `step/render.php` adds; since AMM-187 the row
    plays left to right as it comes up the screen, rule, tab and the field highlight behind each
    name in turn, as a CSS scroll-driven animation on the list's own `view-timeline`, no pinning,
    no script, rewinding on scroll back; with reduced motion or no scroll-timeline support it is
    finished from the start; the section is its title and the steps, no lead, copy or closing
    line) and
    `/category-cards` (the catalogue gateway: one server-rendered block whose `cards`
    attribute lists product_cat slugs with the editor's name and description, edited in the
    sidebar; link and **live** product count come from `demas_theme_get_catalogue_columns()`
    and `demas_theme_term_product_count()`, and a slug with an outbound link in
    `demas_theme_get_catalogue_external_links()` — Non-Woven — becomes the sister-site card;
    the editor canvas shows it through ServerSideRender. **Since AMM-186** a card whose slug
    has a drawing in `inc/category-drawings.php` shows that **spot illustration** in place of
    its icon (`dh-cat--drawn`, `.dh-cat__art`, `aria-hidden`): the category's signature product
    drawn whole on one 260 x 144 sheet, the same scale on every card (sized from a third of the
    grid, a container query on `.dh-cats__grid`), its supply line running off the card edge;
    the product starts up once as the card comes into view and keeps working while the card is
    hovered or focused (loops only switch between paused and running, so nothing jumps).
    Finished and still without JavaScript or with reduced motion. A slug without a drawing keeps
    the icon; the "List" style never shows drawings), `/branch-desk` (every branch as
    a button with who answers there, all from `demas_theme_get_branches()`; eyebrow and title
    edited in the sidebar), `/schematic` (the self-drawing irrigation line; its corner note
    edited in the sidebar; **film mode, AMM-175:** with a film chosen in its "Film" panel it
    plays the site-plan film instead, as a paper sheet laid on the card with the tab under it,
    in the flow. Four media-library files made by `tools/hero-film.sh`: AV1 MP4 (not WebM: the
    host serves `.webm` as `text/plain`), H.264 MP4,
    the first frame (an `<img>` under the video, so it paints at once and is the desktop LCP
    candidate) and the last frame (the still for reduced motion, Save-Data, a refused play and
    `<noscript>`). `src/schematic/view.ts` plays it once when the card is half in view and its
    reveal has landed (`.is-settled`, 3 s fallback), leaves it on the finished garden, and runs
    the Pause / Play / Replay button (WCAG 2.2.2), placed top-left, clear of the picture's
    corners. The video has `preload="none"`, so it is only fetched when it plays. The sheet
    takes the film's exact shape (1200 / 950): a browser renders video colour a shade off the
    page's, and a band of CSS paper round the picture showed the join. With no film file it
    draws the schematic as before, which is what live shows until the cutover (AMM-158)
    re-uploads the files there) and `/marquee` (the scrolling lines under the hero: the branch
    cities or an editor's list; a focusable `role="region"`, so keyboard focus pauses it and,
    with reduced motion, scrolls it) and `/pivot-field` (AMM-177, 2026-10-06: the hero's
    **background illustration**, decorative and `aria-hidden`: centre-pivot fields seen from
    above in a staggered grid, the main field planted in sectors with its crop rows and wheel
    tracks, its arm turning once a minute with the crop it has just wetted behind it. No
    settings; the last block in the hero head, which `style.css` section 9 makes a card (sand
    42% into paper, the content width). Two SVGs in units of a field radius, sized and placed
    by CSS from one `--_r` that shrinks with the card so the main field clears the lead's 36rem
    measure; below 56rem the field sits in the card's bottom corner. The arm's `<svg>` sits in a
    `<span>` that turns, because Chrome won't run a transform animation on an `<svg>` on the
    compositor (a trace showed `compositeFailed`); with reduced motion it is parked at the same angle
    it starts from. A global `max-inline-size: 100%` on SVGs had to be lifted for it). Enqueues `assets/js/editor.js`, the **Highlight**
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

## `inc/category-drawings.php`

  - `category-drawings.php` — **generated** by `tools/make-category-drawings.py` (AMM-186):
    `demas_theme_get_category_drawing( $slug )`, the product drawing for a product_cat slug as
    SVG markup (empty when there is none). Read by the category cards block only. Its ids start
    `dh-cd-`, which `render.php` swaps for a unique prefix; its classes start `dh-ca-` (paint
    and motion in `style.css`, "The product drawing").

## `patterns/` — the homepage set and its history

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
  homepage patterns are editable blocks (core blocks + the theme blocks in `inc/homepage.php`),
  pixel-identical to the old HTML at 320–1440 apart from deliberate fixes (live category counts,
  Branch Desk code contrast, the pills' focus ring). Five of them are now the content of Pages →
  Homepage (see `templates/` below), so for the homepage they are starting points, not the
  source; `closing-cta` is still placed by the template. **AMM-170 (2026-10-02):** the homepage
  passes the taste skill's pre-flight — two eyebrows (hero, Product categories), one marquee
  (the supply list; since AMM-178 the certificates belt is a second moving strip, by Ammar's
  exception), no dashes, one middle dot per line, one label per CTA intent ("Request a
  site visit"), the numbers band open on the page (`.dh-band--open`, so the dark canopy card
  appears once, at the close). **AMM-179 (2026-10-05):** the numbers band holds two numbers (46
  years, 15 branches; ~700 products and the ISO count went, as the categories lead and the
  certificates belt carry them) stacked beside the copy, in an outlined card with the site's
  concave notch (`.dh-band--notched`, `style.css` after the open band): the notch's socket is a
  group holding an editable "Est. 1979, Riyadh" paragraph drawn as a canopy tab, and draws the
  notch's edge as gradient lines (the outline is an inset shadow, so it isn't snapped to device
  pixels and matches them). On first view the outline draws round from the notch (a conic mask
  turned by a registered `@property` angle) and the tab presses in; static without JavaScript
  or with reduced motion. **AMM-177 (2026-10-06):** the hero lost its eyebrow ("Since 1979 ·
  15 branches"), so the homepage has one (Product categories); its head is a card over the
  pivot field drawing (`demas-theme/pivot-field`, last in the head). **AMM-184 (2026-10-07):** the
  numbers band is in the film card's frame (canopy card, a `spacing-50` frame round a sand
  sheet, the tab's socket cut into the frame), its two numbers on riveted paper plates edged in
  2px field green, each with a canopy unit word and its drawing (46: the scale from 1979; 15:
  the network), and "irrigation" in its heading highlighted. **AMM-186 (2026-10-07):** each
  category card shows its product drawing in place of the icon (fog line and gauge, pop-up
  rotor, pipe with tee and valve, drill in a plate, fabric roll with a loupe); the cards are
  ~57px taller at desktop. Nothing in Page 17 changed: the drawings follow the cards' slugs.
  The patterns and Page 17 were changed together; when a homepage
  section's copy changes, change both, or the cutover paste (AMM-158) brings the old copy back.

## `templates/front-page.html`

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
