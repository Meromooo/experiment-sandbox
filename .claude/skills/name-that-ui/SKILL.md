---
name: name-that-ui
description: Standard names for UI components (from namethatui.com, the visual dictionary of UI) and the Demas theme's own components mapped to them. Use when planning, briefing, writing a Linear issue, commit, CLAUDE.md entry or explanation that names a UI component; when Ammar describes a component loosely ("the box that slides up", "the row of buttons where one stays selected"); when naming a new block or CSS class; when choosing the ARIA pattern for a new control; or when two names compete (tabs or segmented control, dialog or drawer, tooltip or popover).
---

# Name That UI, for the Demas theme

Source: [namethatui.com](https://namethatui.com) (free): 49 web entries at `/web/<slug>`, 19 "commonly confused" pairs at `/vs/<pair>`, design styles at `/styles/<slug>`. Each entry gives the standard name and aliases, an anatomy with every part named, the ARIA role of each part, and a paste-ready prompt. Read an entry with `curl -sL https://namethatui.com/web/<slug>` (or the built-in browser) when the glossary below isn't enough.

## Rules

1. **First mention uses the standard name.** In plans, Linear issues, commits and CLAUDE.md, name a component by its standard name the first time, with our own name beside it when they differ: "the quote list (a modal dialog)". Precise names give precise briefs and precise results.
2. **Translate loose descriptions.** When Ammar describes a component in his own words, answer with the standard name, one line on what it is, and the entry link. He is learning; the name is the lesson.
3. **When two names compete, check the `/vs/` page.** The test is usually the job, not the look: tabs swap content, a segmented control re-shows the same content; a drawer slides from a side, a sheet from the bottom, a modal dialog sits centred.
4. **ARIA comes from the anatomy.** For a new control, take the roles from the entry's anatomy, cross-check with the WAI-ARIA Authoring Practices pattern of the same name, then verify with Chrome DevTools `take_snapshot`.
5. **Names in code.** New blocks and classes follow the standard name where the choice is ours (`.dh-…-chip`, not `.dh-…-pill-thing`), still namespaced per component (working agreement). Don't rename existing code only to match; note the standard name in its doc comment instead.

## Our components, by their standard names

| Ours (where) | Standard name | Entry | Note |
|---|---|---|---|
| Header finder (`demas-theme/finder`) | **Command palette** holding a **combobox** | `/web/command-palette`, `/web/combobox` | Modal `<dialog>` opened by its control or `/`; `role="combobox"` + `role="listbox"`. |
| "Your quote" list (`demas-theme/quote-drawer`) | **Modal dialog** (anchored top-right); rises from the bottom like a **sheet** on small screens | `/web/dialog-drawer-sheet` | Not a drawer: a drawer slides in from a side edge. The block keeps its name. |
| Dialog backdrop | **Scrim** | `/web/scrim` | |
| Gallery \| Sheet switch (catalogue toolbar) | **Segmented control** (toggle group) | `/web/toggle-group`, `/vs/tabs-vs-segmented-control` | Same parts, shown another way, so segmented control is right. Built as links with `aria-current`. |
| Child-category rail (catalogue toolbar) | **Chips** (link chips) | `/web/badge-chip-pill` | |
| "Exact match" tag (search results) | **Badge** | `/web/badge-chip-pill` | Badge = status label; chip = something you act on. |
| Pills (Button block styles) | **Pill buttons** | `/web/badge-chip-pill` | "Pill" names the shape; ours are buttons. |
| Parts sheet (Sheet view) | **Data table** | `/web/data-table` | Columns and numbered lines reflowed by CSS. |
| Category cards | **Card**, each with a **spot illustration** (its product drawing, AMM-186) | `/web/card` | The drawing is decorative (`aria-hidden`); it moves on hover and on keyboard focus alike. |
| Category index (`demas-theme/system-index`, above every listing) | **Nested link list** in a `nav` landmark, sitemap style | none on the site | Not a tree view: nothing expands or collapses. Names and parents exactly as the store has them; position marked with `aria-current`. |
| Breadcrumb trail | **Breadcrumbs** | `/web/breadcrumbs` | |
| Catalogue page links | **Pagination** | `/web/pagination` | |
| Credential marks with a detail | **Tooltip** | `/web/popover-dropdown-tooltip`, `/vs/tooltip-vs-hover-card` | If it ever holds links or rich content it becomes a hover card or popover. |
| Process (`demas-theme/steps`) | **Steps** | `/web/steps` | Static ordered steps, not a stepper (wizard) control. |
| Contact page "What happens next" (`.dh-ct-next`) and the Services visit (`.dh-svc-route`) | **Steps**, drawn as a vertical **timeline** | `/web/steps` | AMM-191, shared since AMM-192. An ordered list; number tabs on one track that fills as it scrolls in; decorative numbers hidden from screen readers. The Services one sits on the folder board since AMM-195. |
| Services hero figures (`.dh-svc-proof`) | **Stats**, shown as number **badges** | `/web/badge-chip-pill` | AMM-192. A list; each number (Bold) on field green, its words beside it. Not chips: nothing to press. |
| Services hero offer (`.dh-svc-offer`) | **Card** overlapping the photo | `/web/card` | AMM-192. A label, not a control; the button beside it is the action. |
| Services trust row (`.dh-svc-trust`) | **Trust bar** (a short list of credentials) | none on the site | AMM-192. Under the hero, not inside it (taste rule); each item's mark is a CSS mask. |
| Services folder tabs (`.dh-svc-tabs`) | **Navigation links styled as tabs** (a folder's tabs), not a tabs widget | `/vs/tabs-vs-segmented-control` | AMM-195. A list: the open tab (Survey & design) is plain text, the board under it is its content; the other two are in-page links down the page. No `tablist` role: nothing swaps in place. |
| Services folder board (`.dh-svc-board`) | **Card** (a panel) holding the copy, the steps and a photo | `/web/card` | AMM-195. Canopy; joined to the open tab by the notch's fillet. |
| Lines under the hero | **Marquee** | `/web/marquee`, `/vs/carousel-vs-marquee` | One per page (taste rule). |
| Numbers band (`demas-theme/stats` + `/stat`, homepage) | **Stats section**; each number a **stat card** (our riveted plate) | none on the site | A `<dl>`: term = what it counts (visually hidden when a unit word shows), definition = the number; its drawing is `aria-hidden`. |
| Pivot field drawing (`demas-theme/pivot-field`, homepage hero) | **Background illustration** (decorative image) of the **hero section** | none on the site | `aria-hidden`; its one motion (the arm) is parked under reduced motion. Unused since AMM-196, still registered. |
| Hero photo frame (`.dh-hero-slides`, homepage hero) | **Carousel** (an auto-rotating slideshow) with a **pause button** and a segmented **progress indicator** | `/vs/carousel-vs-marquee` | AMM-196. WAI-ARIA carousel: the group and each photo carry `aria-roledescription` (carousel, slide), the pause button comes first; it holds off screen and on keyboard focus (not on hover: the frame fills most of the first screen), and starts paused with reduced motion. No previous/next buttons. Its caption tab and button socket are the notch again. |
| Key plan (footer, `demas-theme/branch-plan`) | **Locator map** | none on the site | Real land from Natural Earth (AMM-188), north arrow, scale bar; `aria-hidden`, the branch list beside it is the control. |
| Branch Desk city buttons (homepage) | By job, **tabs**: each city swaps one detail panel | `/vs/tabs-vs-segmented-control` | Built as toggle buttons (`aria-pressed`) plus a live region. Worth a look in the next accessibility pass; not a known bug. |
| Contact page city list + branch card | **List-detail** (master-detail) | none on the site | In-page links; CSS `:target` shows the card without JavaScript. |
| Contact page map (`demas-theme/branch-finder`) | **Store locator map** with **service areas** (a Voronoi diagram) | none on the site | AMM-189. Each area is the part of the Kingdom nearest its branch; `aria-hidden`, a pointer twin of the city list, which is the control. |
| Request form | **Form fields**; the "what you need" pills are a **radio group** | `/web/form-field`, `/web/switch-checkbox-radio` | Looks like a segmented control, behaves as radios: keep the radio semantics. |
| Form error summary | **Alert / callout** | `/web/alert-callout-banner`, `/vs/toast-vs-alert` | Not a toast: it stays until fixed and takes focus. |
| Sheet-row − / + quantity | **Number stepper** | `/vs/stepper-vs-slider` | |
| Empty search / empty listing | **Empty state** | `/web/empty-state`, `/vs/empty-state-vs-skeleton` | |
| Header canopy band (`.dh-header__bar`) | **Sticky header** (a navbar) | `/web/sticky-fixed`, `/web/header-navbar` | AMM-198. Sticky from 56.0625rem only, under the admin bar; gains its shadow as the page scrolls. On phones it scrolls away. |
| Product menu (`demas-theme/mega-menu`) | **Mega menu**, built as a **disclosure navigation menu with a top-level link** | none on the site | AMM-198. "Products" is a link to `/products/`; the chevron is a separate button (`aria-expanded`, `aria-controls`) that shows and hides the panel; a mouse opens it by hovering, and a hover bridge spans the gap down to the panel. Not `role="menu"`: it is site navigation. |
| Keyboard focus outline | **Focus ring** | `/web/focus-ring-web` | |
| Reveal timing | **Easing** | `/web/easing`, `/vs/easing-vs-spring` | |

When a new component is built, add its row here in the same commit.
