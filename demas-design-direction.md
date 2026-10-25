# Demas Sandbox — Design Direction

**Status:** current. Rewritten 2026-10-06 (AMM-157) around what was actually built, and brought
up to date on 2026-10-25 with the logo palette (AMM-203), the sticky header (AMM-198), the
category index (2026-10-08), the spec sheet (AMM-202) and the hero carousel (AMM-196). It
replaces the 2026-09-14 navy/gold/serif version, which stays in git history (`git log --follow`
on this file).

**What this file is for:** the *reasons* behind the visual system. The *values* live elsewhere:
the tokens (named colours, sizes, radii and timings that everything reads) in `theme.json`, and
the rules that use them in `assets/css/style.css` and each block's `src/<block>/style.scss`.
When a value changes, change it there; when a reason changes, change it here.

**Read with:**
- `demas-motion-reference.md`, the analysis of the reference recording the direction comes from.
- `demas-homepage-brief.md`, the homepage's content and section order.
- `CLAUDE.md`, the architecture.
- `.claude/skills/name-that-ui/`, the standard name of every component mentioned below.

---

## 1. The design read

**Trust-first B2B, at a smaller scale than the references.** The buyers are contractors,
municipalities, developers and facility managers in Saudi Arabia, ordering from a catalogue of
about 700 parts. They come to check facts (a part number, a branch, a certificate) and to ask
for a quotation. The site has to look like an engineering supplier with 46 years behind it, not
a lifestyle brand.

Since 2026-09-14 the direction has been the reference recording analysed in
`demas-motion-reference.md`: cards, a concave notch, containers that grow from a seed, a
marquee, and one restrained motion vocabulary reused everywhere. It is re-skinned in Demas's own
register: the colours of the Demas logo on warm paper, and the language of engineering drawings
and datasheets. Nothing from the old demas-group.com design carries over.

Three principles run through everything below:

1. **Every element carries information.** No decoration that doesn't say something true: a
   count is live, a plan is drawn at real coordinates, a certificate links to the certificate.
2. **One vocabulary, reused.** The same few shapes, motions and numerals on every page, so that
   consistency itself reads as engineering rigour (motion reference, "Adopt" 1).
3. **Smaller than the references.** Each pattern was picked on its own merits, not adopted
   wholesale. Ammar's principle from the first direction still holds.

The theme is **light-only** until Ammar decides otherwise.

## 2. Palette

Six colours plus white, in `theme.json` (`settings.color.palette`), and an alert colour in
`settings.custom.alert`. WordPress's default palette, gradients and duotones are off, so editors
pick only from these. The idea: **the Demas logo's indigo and blue on warm paper, with water
where water flows.**

Since 2026-10-17 (AMM-203) the colours are measured from the logo. The slugs (`canopy`, `field`,
`water`) are older than that and stay, because saved page content uses them, so a slug no
longer describes its colour; the editor shows the display name in quotes below.

| Token | Hex | Job | Why it exists |
|---|---|---|---|
| `paper` | `#FAF9F6` | The page | A warm off-white, the drawing sheet everything sits on. Pure white is kept for cards, so they lift off the page. Also the `theme-color` and the icon tiles. |
| `sand` | `#E8DFCE` | The second surface: a plate set on the page | A warm ground. Where content is a *set* rather than prose: the Branch Desk, the branch cards and request form on Contact, the category cards, the footer's title block, the ground behind product photos, the Services installation plate. Since the Branch Desk and the footer use it, on this site it also reads as "branches and contact". |
| `field` "Sky" | `#6BB3DE` | The marker | A light tint of the logo blue. Mostly a **marker**: the eyebrow dot, the marquee dot, the highlighted word's tint (38% on paper), list markers, ticks, a chosen chip's icon. On the header band it is also the hover and current-page fill, under ink text, and on indigo it is the focus ring (see the `water` rule). |
| `canopy` "Indigo" | `#232D8E` | The brand's dark | The logo's indigo. The primary pill, links (underlined), and the dark surfaces: the header band (AMM-198), the closing call to action (AMM-170), the Services folder board, Contact's request section and layout plan. |
| `ink` | `#0F1433` | Text | An indigo-tinted near-black rather than pure black, so text belongs to the palette. Also link hover, the solid pill's hover and the hang tags' ground. |
| `water` "Cerulean" | `#1A79AB` | Water, and what moves toward Demas | The logo's blue (about `#1B7EB2`), deepened to pass 4.5:1 on paper and white. See the rule below. |
| `white` | `#FFFFFF` | Cards | The `dh-card` surface, lifted by the `card` shadow. |
| `custom.alert` | `#A3361F` | Errors | The request form's errors (5.1:1 on sand). Always paired with words, never colour alone. |

**Contrast** (WCAG ratio, computed from `theme.json` on 2026-10-25; 4.5:1 is the floor for
body text, 3:1 for large text, icons and focus rings):

| On → | paper | sand | white | canopy | ink |
|---|---|---|---|---|---|
| `ink` | 17.07 | 13.59 | 17.98 | — | — |
| `canopy` | 10.86 | 8.64 | 11.43 | — | 1.57 |
| `water` | 4.57 | 3.64 | 4.81 | **2.38** | 3.74 |
| `field` | **2.19** | **1.74** | **2.30** | 4.97 | 7.81 |
| `alert` | 6.42 | 5.11 | 6.76 | — | — |
| `paper` | — | 1.26 | — | 10.86 | 17.07 |

What follows from it:

- **`field` is never text on a light surface** (2.19:1 on paper). As text it appears only on
  `ink` (the hang tags' "View certificate" link) or as a decorative glyph hidden from screen
  readers (the search title's quote marks). The open numbers band switches its prefix colour
  for this reason.
- **`water` as text needs paper or white** (4.57 and 4.81:1); on sand it is large text or
  icons only.
- **Text on canopy is paper**, and **a focus ring on canopy is `field`** (4.97:1), because
  `water` there is 2.38:1.

### The `water` rule

`water` marks three things, and only these three:

1. **The quote flow**, the buyer's path toward a request: the "Add to quote" button
   (`src/quote-button`), the "Your quote" control and list (`src/quote-drawer`, a modal
   dialog), and the footer's quote link (`.dh-foot__quote`).
2. **The focus ring**, the outline that shows which control the keyboard is on: 3px `water`,
   offset 3px, rounded to the small radius (`style.css` section 1, `:focus-visible`, through
   `--dh-ring`). One ring shape everywhere teaches keyboard users what to look for. **On
   indigo it turns `field`** (AMM-204): the surfaces in the `--dh-ring` list in `style.css`
   section 3 (the canopy card, the Services folder board, Contact's request copy) set it, and a
   new dark surface with controls joins that list. The header band's own controls take a paper
   ring instead (AMM-198), so its dialogs keep `water`.
3. **Water in drawings**: the pipes of the irrigation layout plan on Contact (`src/branch-finder`)
   and the hero film. The film is still graded to the old teal `water` (`#2E7D8C`,
   `tools/hero-film.sh`); re-grading it to Cerulean needs the source clips and a Media Library
   upload (open since AMM-203).

It is not used for decoration, headings or generic links (those are `canopy`). A new use must
fit one of the three.

The three contrast gaps found during the first rewrite are closed: the 404's `field` ring went
and link hover turned `ink` (AMM-182), and the ring on indigo became `field` (AMM-204).

## 3. Type

Three families, all self-hosted woff2 under the SIL Open Font License (`assets/fonts/`,
registered in `theme.json`, never from a font CDN):

- **Archivo, wide, for display** (`display`). The variable width axis set to 125%
  (`custom.display.stretch`, applied in `style.css` section 2 because `theme.json` can't express
  `font-stretch`), weight 600, tracking −0.02em, leading 0.98. The width is what turns a neutral
  grotesque into the site's voice: broad, low and steady, like lettering on a plan.
- **IBM Plex Sans, for body and interface** (`body`), weights 400 to 600, with **IBM Plex Sans
  Arabic** in the same stack, chosen because the two were drawn as one family. This matters for
  the Arabic version that is still to come (ADR-002, correction 5).
- **IBM Plex Mono, for data** (`mono`): part numbers, counts, line numbers, branch codes, cell
  labels. See section 5.

Rules that carry reasons:

- **Arabic headings don't stretch.** Plex Sans Arabic has no width axis, so `:lang(ar)` headings
  reset `font-stretch`, letter-spacing and leading (1.25).
- **Size-matched stand-in fonts** ("Archivo Fallback", "IBM Plex Sans Fallback": Arial scaled to
  the web fonts' measured widths) sit after the web fonts in every stack, so a line breaks in the
  same place before and after the fonts load. Without them the homepage hero jumped 84px when
  the fonts arrived (CLS, the layout-shift score, 0.103 → 0, AMM-154). Change a face and these
  must be re-measured.
- **Headings balance their lines** (`text-wrap: balance`); paragraphs take the body's 1.55
  everywhere (never set through a block style in `theme.json`; see CLAUDE.md, AMM-153).
- **The eyebrow** (`.dh-eyebrow`, a small label above a heading) is Plex Sans, xs, uppercase,
  tracked 0.08em, with a `field` dot. At most one per three sections (the taste rules, section 9).
- **The highlight** (`.dh-highlight`, the editor's Highlight format) puts one word on a soft
  `field` pill and sweeps it in (the `wipe` reveal). At most one per headline.

The type scale (`xs` 0.75rem to `display` 5rem) and the spacing scale (`10` to `80`, the two
largest fluid) are in `theme.json`. Content width is 1120px, wide 1360px.

## 4. Shape grammar

A small set of shapes, reused so the whole site reads as one object.

### Radii

`custom.radius`: `sm` 4px (focus rings, tags, small chips), `md` 10px (the highlight, photos
inside cards), `lg` 18px (cards, bands, the footer's top corners), `pill` 999px (buttons and
chips). A new component picks one of the four. (Two 2px corners on tiny drawn details are the
only other values.)

### Surfaces (cards)

`style.css` section 3. A **card** (the standard name) is a surface that groups one thing:

- `.dh-card`: white, `lg` radius, the `card` shadow (two layers tinted with ink, not grey, so
  the shadow belongs to the palette). The `raised` shadow is for surfaces that sit above
  others: the mega menu's panel, the Service Record, the chosen branch card.
- `.dh-card--sand`: sand, no shadow. A plate on the page (see `sand` above).
- `.dh-card--canopy`: indigo with paper text and the `field` focus ring. The closing call to
  action, Contact's layout plan.
- `.dh-card--flush`: a white card without its shadow.

### Pills

`style.css` section 4. Every button is a **pill button**: the pill radius, Plex Sans sm/500,
0.6em × 1.15em padding. Editors pick them as Button block styles (`inc/homepage.php`, AMM-153):

| Style | Look | Use |
|---|---|---|
| `dh-pill-solid` | canopy fill, paper text; ink on hover | The one primary action in a group |
| `dh-pill-outline` | ink hairline at 24%; full ink on hover | The secondary action |
| `dh-pill-paper` | paper fill on a dark card; white on hover | Primary action on canopy |
| `dh-pill-outline-paper` | paper hairline on a dark card | Secondary action on canopy |

Plus two tag forms in CSS only: `.dh-pill--glass` (translucent, over photography) and
`.dh-pill--field` (field tint). WooCommerce's own buttons take the pill shape too, so the
catalogue matches.

**Every pill and header control takes taps over at least 44px** (AMM-172) while keeping its
drawn size, through a transparent `::before` that reaches above and below it. A new pill style
or header control joins that `:is()` list, and a wrapped row of pills gets `spacing-30` between
rows so tap areas don't overlap. Any other new control is 44px itself.

**One label per intent.** The same action has the same words everywhere ("Request a site
visit", "Get directions"), so a buyer never wonders whether two buttons do different things.

### The concave notch

`style.css` section 5. The site's signature shape, taken from the reference recording: **a label
socketed into a card's corner, with the card's edge curving in around it.** The tab is painted
in the surface colour behind the card (paper by default, `--dh-notch-bg` on sand) and its two
pseudo-elements draw the concave fillets, at the `custom.notch` radius (18px, the same as the
card radius `lg`).

Why it's here: it lets a fact sit *in* a card without a box of its own, the way a stamp or a
label sits on a drawing. It is used where a card carries one short fact:

- **Category cards:** the live product count in the corner.
- **The numbers band** (AMM-179): an outlined card whose notch holds the "Est. 1979, Riyadh"
  tab. The outline draws itself round from the notch on first view.
- **Services** (AMM-167): photo cards with a notched label, built from core blocks through
  the `.dh-notch__tab--bottom-start` class.

Variants: top-start (default) and bottom-start; the fillets mirror for right-to-left text.
**One notch per card, holding one short fact.**

### The rule

`custom.rule`: a 1px hairline of ink at 12%. The divider between rows (lists, the footer's
cells, the parts sheet). Lines, not boxes, separate things inside a card.

## 5. The numeral device

**Numbers are set in Plex Mono with tabular figures, zero-padded to a fixed width.** Step
numbers are two digits (`01`, `02`); counts and line numbers are three (`024`, `001`).

| Where | Format | Source |
|---|---|---|
| The process steps (homepage) | `01`… | CSS counter, `decimal-leading-zero` (`style.css` section 9) |
| "What happens next" (Contact) | `01`… | CSS counter (`style.css` section 15) |
| Catalogue toolbar count; the printed sheet's line range | `024`; `lines 001–024` | `str_pad` in `src/catalog-toolbar/render.php` |
| Parts sheet line numbers, continued across pages | `025` | `src/product-meta/render.php` |
| Quote list lines and count, printed sheet, copied text | `001` | `pad()` in `src/quote-drawer/view.ts` |
| Finder category counts | `012` | `pad()` in `src/finder/view.ts` |

Why:

- **It is how datasheets and drawings number things.** A parts list numbered `001` reads as a
  document you can quote from, which is what the buyer is doing.
- **Fixed width keeps columns aligned.** Tabular figures plus padding mean a column of counts or
  line numbers lines up without any table markup, and doesn't shift as a number changes.
- **One look for "this is data".** Anywhere a buyer sees mono numerals, the value is exact and
  comes from the catalogue, not from copy.

Counted numerals come from CSS counters or the data, never typed, so adding or reordering a step
renumbers itself. **Exception:** the homepage's headline figures (46 years, 15 branches) are
display numerals in Archivo that count up (section 6), because they are claims to read at a
glance, not data to look up.

## 6. Motion vocabulary

`style.css` section 7 and `assets/js/main.js`. The reference recording's lesson was discipline:
**a few primitives, reused everywhere.**

**The idea: a container grows from a seed, then its content fades in.** Nothing slides in from
off screen, so layout never shifts. Everything decelerates; there is no bounce or overshoot.

### The six reveals

Set as `data-reveal="…"`, or on core blocks as a `dh-reveal--…` class that `inc/homepage.php`
turns into the attribute. One block adds a motion of its own on the same trigger: `plot` on the
footer's key plan (the branch dots appear outward from Riyadh).

| Reveal | Motion | For | Used on |
|---|---|---|---|
| `dot` | a circle opens into the pill | buttons | hero, Services and Contact buttons |
| `sliver` | a thin bar widens from the start edge | thumbnails, rows | category cards, Services photos |
| `bar` | a thin bar opens to full height from the middle | bands, dark cards | the closing call to action, Services bands |
| `seed` | a small square (12%) grows to full size from the top-start corner | hero media | the schematic, the Branch Desk, the Branch Finder, Services hero photo |
| `rise` | 12px lift and fade | everything else | headings, leads, steps, cards |
| `wipe` | the highlight sweeps in behind a word | `.dh-highlight` | the headline's highlighted word |

**Timing** (`custom.motion`): `fast` 250ms (hovers, content fade), `base` 400ms (a reveal),
`slow` 550ms, `stagger` 70ms between siblings; easing `cubic-bezier(0.2, 0.7, 0.2, 1)`. Entrances
land in 0.3 to 0.6 seconds, as in the reference.

**How they combine:**

- **Groups stagger.** A `data-reveal-group` numbers its own reveals (`--i`), and each waits one
  stagger step after the last. A highlighted word inside a revealing heading sweeps two steps
  after the heading lands.
- **Container, then content.** `data-reveal-content` inside a reveal fades in after its
  container has landed.
- **A reveal steps aside once it lands** (AMM-166). It only *removes* its starting state, and
  once its transitions finish `main.js` marks it `.is-settled`, which drops the reveal's clip and
  transition. After that the component's own hover lift, focus ring and tooltips apply as if the
  reveal had never been there.

### The hero opens; it doesn't scroll in

The hero is on screen at first paint, so it plays as the page opens, on **CSS keyframes
alone**, with no JavaScript gate (AMM-168). On the homepage (AMM-196) the photo frame opens out
from its middle while the first photo settles; then the headline, lead and the word's sweep, and
the buttons in their socket; then, from 56rem up where they share the first screen, the Branch
Desk and the schematic. The Services and Contact heroes open the same way. Before AMM-196 the
headline was the LCP element (the largest thing painted first), about 160ms after first paint;
AMM-196's Linear report has the traces since.

### Other moving parts

- **The hero carousel** (AMM-196, Ammar's exception beside the two moving strips): the
  homepage's photo frame changes photo every 7.9s, the next growing up over the last in strips.
  The tab's segments are the timer, with a pause button; it holds off screen and while the
  button has keyboard focus.
- **Counters:** whole numbers in the numbers band count up from zero when scrolled into view.
  The markup already holds the final value.
- **The marquee** (one per page): the supply list under the hero, looping. A focusable region,
  so keyboard focus pauses it.
- **The certificates belt** (AMM-178): the homepage's second moving strip, by Ammar's standing
  exception, looping the other way. It stamps in on first view, eases to a stop under the
  pointer, and on keyboard focus stops and glides the focused plate to the middle.
- **The finale:** on the homepage the closing call to action stays pinned while the footer
  slides over it (CSS `position: sticky`; `main.js` only measures).
- **The hero film** (AMM-175): plays once when its card is half in view, stops on the finished
  garden, with a Pause / Play / Replay button (WCAG 2.2.2).
- **Gallery | Sheet** (a segmented control): switches in place with View Transitions.

### Constraints, without exception

- **Reduced motion gets the final state at once.** Every reveal, keyframe and loop has a
  `prefers-reduced-motion: reduce` branch: the marquee becomes a strip you scroll sideways, the
  belt a still wall, the film its last frame, and the carousel starts paused and fades.
- **No JavaScript means final state.** Every hidden starting state is gated on the `.js` class
  that `main.js` adds; without it nothing is hidden. The hero's keyframes end on the element's
  own styles.
- **Motion never gates product data.** The catalogue, the datasheet, the parts sheet and search
  results don't reveal; they render at full opacity on first paint.

## 7. The drawing register

The motif that ties the pages together: **the site is drawn like an engineering drawing.** It
fits a company that designs and installs irrigation systems, and every drawing is real data.

| Component | What it draws | Source |
|---|---|---|
| The schematic (homepage hero) | An irrigation line drawing itself; or the site-plan film | `src/schematic` |
| The footer's title block | The corner block of a drawing: ruled cells, mono labels, the company plate (CR and VAT numbers) | `patterns/footer.php` |
| The key plan (footer) | One dot per branch at its real latitude and longitude on a graticule | `src/branch-plan` |
| The irrigation layout plan (Contact) | Riyadh as the pump, a mainline, a lateral to each branch's real position; choosing a branch runs water to it | `src/branch-finder` |
| The product nameplate | The datasheet's plate: part number, brand, series | `src/product-summary` (`.dh-plate`) |
| The spec sheet (product page) | The imported description redrawn as a sheet: a white card per heading under a hatched canopy band, size grids, label and value pairs, model tags, a Documents card | `inc/spec-sheet.php` (the "Plate" style, AMM-202) |
| The parts sheet | The catalogue as a numbered parts list; also what prints | `src/product-meta`, `inc/sheet-view.php` |
| The Service Record (Services) | A job card | `patterns/services.php` |
| The category icons | One per subcategory, drawn by hand, uniform 1.5px stroke | `demas_theme_get_category_icon()` in `inc/navigation.php` |

The hand-drawn icon set stays: the taste skill's "never hand-roll SVG icons" rule is overridden
here (CLAUDE.md).

## 8. Photography

Photography is Demas's own or nothing: no stock or placeholder photos. The homepage's hero frame
(AMM-196) and the Services page show Demas's photos of their work; elsewhere, until more arrive
(AMM-149), a drawn placeholder or the drawing register stands in. Product photos are studio shots on white, so
their tiles sit on `sand` to give the cut-out something to read against, with
`object-fit: contain` so no part of the component is cropped (`style.css` section 10).

## 9. Rules for visible site copy

Ammar adopted the taste skill's rules for anything a visitor reads (they don't apply to repo
docs, code comments or Linear):

- **No em dash or en dash.**
- **Hero subtext 20 words or fewer.**
- **At most one eyebrow per three sections.**
- **At most one middle dot per line.**
- **One label per intent** (see Pills).
- **One marquee per page.** Exception: the homepage's certificates belt.

The full list and how each is measured are in CLAUDE.md, under `design-taste-frontend`.

## 10. Pattern decisions from the first direction

The 2026-09-14 direction's pattern decisions, with where each stands:

| Decision | Status |
|---|---|
| **Scroll-triggered reveals** | Built: section 6. |
| **Animated stat counters** | Built: `data-count`, `main.js` job 3. The band now holds two numbers (AMM-179). |
| **Trust strip with hover detail** | Built as the certificates belt (AMM-178): each plate's hang tag gives detail, issuer and a link to the certificate, on hover or keyboard focus. |
| **No testimonials without real ones** | Holds. None exist; none are invented. |
| **A call to action at each natural section break**, not after every subsection | Holds: the homepage asks in the hero and at the close (Services adds its own call to action). One label per intent. |
| **Sticky header that condenses on scroll** | **Half built.** Sticky from 56.0625rem since AMM-198 (`style.css`, header section), gaining its shadow over the first 8rem of scroll; on phones it scrolls away. Condensing it is not built: AMM-160. |
| **Photo-overlay captions** (numbered phrases on category photos) | **Open, not built.** Needs real photography (AMM-149); the category cards carry the notched count instead. |
| **Floating tag clusters** (real taxonomy terms as small pills) | **Open, not built.** Would sit beside a feature blurb as plain styled pills. |
| **Before/after comparison card** | **Open, not built.** Only with a real number on Demas's side (the quotation turnaround), never an invented competitor figure. |

## 11. Declined

| What | Why |
|---|---|
| A preloader (the reference's 2.2s assembly) | It blocks the largest paint for seconds; a conversion tax on a catalogue. |
| A smooth-scroll library | 15 to 20 KB that breaks native scrolling, anchors and find-in-page. |
| A pinned, scroll-jacked carousel | Fights the catalogue's job of getting buyers to parts. |
| Dark mode | Light-only until Ammar decides otherwise. |
| Mock app or dashboard visuals | Demas has no app; there would be nothing real behind them. |
| Navy, gold and serif headlines (the first direction) | Replaced on 2026-09-14 by this direction. |
| Anything from the old site's design (its palette, typefaces, Kadence markup) | Its content is reused; its design is not (CLAUDE.md, Forbidden patterns). |

## 12. Where things live

| Thing | Values | Rules |
|---|---|---|
| Colours, type, spacing, shadows | `theme.json` `settings` | — |
| Radii, notch, rule, motion, display axis, alert | `theme.json` `settings.custom` | — |
| Foundations, focus ring | — | `style.css` section 1 |
| Type, eyebrow, mono, highlight | — | `style.css` section 2 |
| Cards, pills, notch, marquee | — | `style.css` sections 3–6 |
| Reveals, hero keyframes, reduced motion | — | `style.css` section 7; `main.js` |
| Each block | `src/<block>/block.json` | `src/<block>/style.scss` (compiled to `build/`, ADR-001) |
| Component names | — | `.claude/skills/name-that-ui/` |
