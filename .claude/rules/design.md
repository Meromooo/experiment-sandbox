---
paths:
  - "assets/css/**"
  - "patterns/**"
  - "templates/**"
  - "parts/**"
  - "theme.json"
  - "src/**/*.{scss,css,tsx}"
  - "demas-*.md"
---

# Front-end design work: skills in detail, the taste audit, reference docs

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

The short trigger table is in CLAUDE.md; this is the detail behind it.

## Skills — the full entries

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
  Its checklist is how front-end work is verified before it goes to In Review — on the sandbox,
  in Chrome DevTools (below), measured rather than eyeballed.
- **`caveman:caveman`** (full intensity) — terse replies in chat. Code, commit messages, PR
  descriptions, repo docs (this file, ADRs, READMEs) and Linear issues stay in normal prose;
  security warnings, irreversible actions, step-by-step instructions and explanations of new
  terms drop back to plain language. The caveman plugin's hooks already switch it on each
  session; this line keeps it on if they ever don't. **Cloud sessions** don't install plugins,
  so the plugin and its hooks aren't there: its main skill is committed as the project skill
  `caveman` (`.claude/skills/caveman/`, MIT, with LICENSE and SOURCE.txt; 2026-10-06), and a
  cloud session invokes it itself at the start.
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
  plugin, whose hooks would switch it on for every session. Since 2026-10-06 the same file is
  also committed at `.claude/skills/ponytail/` (with its LICENSE and SOURCE.txt) so cloud
  sessions, which don't see `~/.claude`, get it too.
- **Chrome DevTools MCP** (added 2026-10-04 at Ammar's request; plugin
  `chrome-devtools-mcp@chrome-devtools-plugins`, user scope, Google's official
  github.com/ChromeDevTools/chrome-devtools-mcp) — **the browser tool for every check on the
  sandbox**, replacing Playwright. Screenshots and widths (`resize_page`, `emulate` for a phone
  with touch), the accessibility tree (`take_snapshot`), `lighthouse_audit`, console and network
  (`list_console_messages`, `list_network_requests`), performance traces with LCP/CLS insights.
  Load its skills as the job needs them: `chrome-devtools-mcp:chrome-devtools` (basics),
  `chrome-devtools-mcp:a11y-debugging`, `chrome-devtools-mcp:debug-optimize-lcp`. Playwright
  stays installed only as the fallback for what DevTools lacks — chiefly `prefers-reduced-motion`
  (its `browser_emulate_media`), which every reveal and the hero's opening motion must be
  checked under. The global rule is `~/.claude/rules/chrome-devtools.md`. Google's usage
  statistics are switched off in `~/.claude/settings.json`; a performance trace still sends the
  traced URL to Google's CrUX API, harmless for the public sandbox and live URLs.
- **Responsively** (added 2026-10-06 at Ammar's request; desktop app v1.18.0 on Ammar's
  machine, installed with `winget install Responsively.ResponsivelyApp`, AGPL-3.0,
  responsively.app) — **Ammar's own review tool, not Claude's.** A browser that shows one page
  at several widths side by side, repeats a scroll, click or navigation in every view, and
  screenshots all of them at once. It is how Ammar looks over a change at **320 / 768 / 1024 /
  1440** in one go (a saved "preview suite" of those four widths) before saying a task is
  done. Claude doesn't drive it and doesn't treat it as evidence: Claude's checks stay
  measured in Chrome DevTools. When Claude hands a front-end change to Ammar for review, say
  which page(s) to open in Responsively and what to look at; when Ammar sends its screenshots
  back, read them as his review. Its views are Chromium windows at those sizes, not real
  phones (its own FAQ says so), so touch and real-device quirks still need a phone.
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
     Linear, and fixes follow the usual plan → go-ahead → build flow. **Run it on the sandbox
     in Chrome DevTools, at 320 / 768 / 1024 / 1440, and measure every box that can be
     measured** — a finding carries the number and a screenshot, not an impression:
     - *No em or en dash:* `evaluate_script` counting `—` and `–` in `document.body.innerText`
       (and in `alt` / `aria-label` values).
     - *Button, form and text contrast:* `chrome-devtools-mcp:a11y-debugging` —
       `lighthouse_audit` (accessibility) plus computed colours for anything it flags.
     - *Hero fits the viewport:* screenshots at 1440×900 and a 375×812 phone; headline line
       count (height ÷ line-height), subtext word count (≤ 20), CTA bottom above the fold.
     - *CTA labels on one line at desktop, nav on one line and ≤ 80px:* element heights at 1440.
     - *Eyebrow count ≤ ceil(sections ÷ 3), one marquee per page:* count `.dh-eyebrow` (and
       other eyebrows) against sections, `.dh-marquee` per page. **Exception (Ammar,
       2026-10-05): the homepage has two moving strips,** the supply-list marquee and the
       certificates belt (`.dh-belt`, AMM-178), counter-flowing; don't flag it.
     - *Mobile collapse:* no `scrollWidth > innerWidth` at 320; 44px tap targets.
     - *Core Web Vitals:* `chrome-devtools-mcp:debug-optimize-lcp` — a reload trace for LCP and
       CLS (INP needs an interaction trace).
     - *Reduced motion:* the Playwright fallback (`browser_emulate_media`) — DevTools can't
       emulate it.
     - Console and network clean on the page.
     The judgement boxes (layout families, copy self-audit, AI tells) stay a read of the page
     and its screenshots.

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
  strip"; the homepage's second moving strip (the certificates belt, AMM-178) is Ammar's
  standing call, as is the use of the certification bodies' and partners' logos on it (they
  are on the live site). Installed as a project skill in the main checkout's `.claude/skills/` (from
  github.com/leonxlnx/taste-skill, commit ce26fc2, MIT). **Committed since 2026-10-06** (with
  ponytail, both with their LICENSE and SOURCE.txt) so cloud sessions get them; neither reaches
  the server, because only the `RUNTIME` files deploy (AMM-173). All five project skills are now
  committed (the three below since 2026-10-05). `.git/info/exclude` still lists `.claude/skills/`,
  so a **new** file there needs `git add -f`; changes to committed ones are tracked as usual.
- **`name-that-ui`** (added 2026-10-05 at Ammar's request; project skill in this repo's
  `.claude/skills/name-that-ui/`, from namethatui.com) — the shared vocabulary for UI
  components. **On** whenever a component is named: every front-end plan, Linear issue, commit
  and CLAUDE.md entry (first mention uses the standard name, ours beside it: "the quote list (a
  modal dialog)"), whenever Ammar describes a component in his own words (answer with the name
  and its entry), when naming a new block or class, and when choosing a new control's ARIA
  pattern. It holds a glossary of **our** components under their standard names (finder =
  command palette + combobox, Gallery | Sheet = segmented control, the parts sheet = data
  table…); a new component gets its row in the same commit. Cheap (~2k tokens), so load it at
  the plan step of any front-end task, beside the two front-end skills.
- **`favicon-cheat-sheet`** (added 2026-10-05; project skill in `.claude/skills/favicon-cheat-sheet/`,
  distilled from github.com/audreyfeldroy/favicon-cheat-sheet, MIT) — the icon rules: the
  modern minimum (`favicon.ico` 32, an SVG icon, a 180px opaque `apple-touch-icon`, a manifest
  with 192 / 512 / maskable icons, `theme-color` from `theme.json`), what the sandbox serves
  today (WordPress's Site Icon only: no `/favicon.ico`, SVG, manifest or `theme-color`; a 300px
  touch icon) and where each piece lives here (`assets/images/`, the `site_icon_meta_tags`
  filter, the domain root as Ammar's server work, the Site Icon as database content). **On**
  for anything touching icons, the logo or brand mark, head tags for icons or the manifest, a
  `/favicon.ico` 404 in a network trace, and the cutover checklist (AMM-158).

## Design & content reference docs

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
