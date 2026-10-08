# Demas Theme (Sandbox) — CLAUDE.md

A custom, no-page-builder WooCommerce **block theme** for Demas Group, on a sandbox WordPress
install (`cornflowerblue-fish-235112.hostingersite.com`) that shares a Hostinger account with the
current demas-group.com (old site) but is otherwise isolated: no live content, database or files
are touched from work on this theme. `README.md` has the one-line summary and a pointer to the
research doc behind this direction. How Ammar works (plan → "ready" → scoped go-ahead; a question means stop; the live site
is untouchable; server work through him; watching a deploy; cloud sessions open pull requests)
is in `.claude/rules/working-with-ammar.md`, which every session loads.

## Read the topic rule before working on an area

The detail lives in `.claude/rules/`, one file per area. Claude Code loads a rule by itself when
a matching file is read, written or edited, but **not** when files are reached through `git show`,
scratch copies or other shell commands — so **read the matching rule file first** whenever you
plan or change something in its area:

| Area | Rule file |
|---|---|
| Homepage sections, patterns, reveals, hero, `main.js` motion | `.claude/rules/homepage.md` |
| Catalogue, categories, sheet view, datasheet, no-cart, JSON-LD, typography filter | `.claude/rules/catalogue.md` |
| Search results and the header finder | `.claude/rules/search.md` |
| Quote list, Contact page, branches, request form | `.claude/rules/quote-and-contact.md` |
| Generic pages, templates, header/footer parts, footer blocks, Services, 404 | `.claude/rules/pages-and-footer.md` |
| CSS/JS/fonts/images, enqueues, performance, cache, icons, `theme.json` | `.claude/rules/assets-and-performance.md` |
| `tools/` scripts and the Hermes checks | `.claude/rules/tools.md` |
| Front-end design work: skill details, the taste audit, reference docs, tokens | `.claude/rules/design.md` |
| Anything that takes input, renders stored content, or touches the deploy job | `.claude/rules/security.md` |
| CI, the deploy job, the `deploy` branch's history | `.claude/rules/deploy.md` |

When the architecture changes, update the matching rule file (or this file, for rules that
apply everywhere) in the same commit.

## Deploys and CI

- A push to `main` runs `.github/workflows/checks.yml`: `php -l` on every PHP file (PHP 8.3),
  WordPress Coding Standards (`phpcs.xml.dist`, zero violations, blocking; also posted as a
  "PHPCS report" check), `build/` must match `src/`, `theme.json`/`block.json` must parse, and
  no email address anywhere in the repo. Only when all pass does the `deploy` job move the
  **`deploy` branch**, which Hostinger deploys into `wp-content/themes/demas-theme` on the
  sandbox (switched from `main` to `deploy` on 2026-09-29, sandbox only). Check results without
  signing in at `/commits/<sha>/check-runs` in GitHub's API. **Never push to `deploy` by hand.**
- **Only the runtime files deploy (AMM-173).** The job commits only `style.css`,
  `functions.php`, `theme.json`, `screenshot.png`, `inc/`, `build/`, `templates/`, `parts/`,
  `patterns/`, `assets/` and `woocommerce/` (the `RUNTIME` list) on top of the previous `deploy`
  commit, titled `Deploy <main sha>` (how to tell which `main` commit is on the sandbox); an
  older commit's re-run is skipped, never rolled back. **A new top-level file or folder
  WordPress reads (`styles/`, `languages/`) must be added to `RUNTIME`**, or it never reaches the
  server. Everything else here (this file, `.claude/`, `docs/`, `tools/`, `src/`) stays private.
  At cutover (AMM-158) the live theme must come from `deploy`, not `main`.
- There is no server-side build: the files in this repo are the files WordPress reads, which is
  why compiled block output is committed (ADR-001, below).

## Skills to use in this repo

Ammar's standing instruction. **Invoke the front-end skills at the start of every front-end
task — every plan, build, and fix or audit-fix pass — each time; a skill loaded for an earlier
task does not count** (skipping them shipped 27px touch targets and low-contrast text in
AMM-141). Details, provenance and the full taste-audit method are in `.claude/rules/design.md`
and `.claude/rules/security.md`.

| Skill / tool | When | Key rule |
|---|---|---|
| `frontend-design:frontend-design` | Any front-end, visual, template, pattern, CSS, block or `theme.json` work | Not for data scripts, server, security, redirects, docs or Linear |
| `agent-skills:frontend-ui-engineering` | Alongside frontend-design, same work | Its checklist (keyboard, focus, labels, 44px targets, 320/768/1024/1440, empty/error states, tokens) verifies work before In Review, measured on the sandbox |
| `name-that-ui` | Plan step of any front-end task; whenever a component is named or Ammar describes one | First mention uses the standard name, ours beside it; a new component gets a glossary row in the same commit |
| `design-taste-frontend` | **Once per marketing-page task** (homepage, Services, Contact, 404, footer), at the plan or audit step | Not for catalogue/product UI, debugging or PHP. Start with the design read (trust-first B2B), "redesign — preserve" mode, run its §14 pre-flight as a measured audit |
| `ponytail` | **Only** when debugging or fixing code (bug, failing CI, audit finding), planning turn included | Root cause where every caller routes through; smallest correct diff. Its terse-output rule doesn't apply to reports for Ammar; "one runnable check" = CI + a sandbox check, no test files in the theme |
| `security-audit` | Guidance mode on every security-sensitive change (plan step and before In Review); full audit only once before cutover, on Ammar's request | Source review only; never probes live systems or the server |
| `favicon-cheat-sheet` | Icons, logo/brand mark, icon head tags or manifest, a `/favicon.ico` 404, the cutover checklist | Change icons through `tools/make-icons.py`, never by hand |
| `caveman:caveman` (full) | Chat replies, every session (cloud sessions invoke the committed project skill themselves) | Code, commits, PRs, repo docs and Linear stay normal prose; security warnings, irreversible actions, step-by-step instructions and new terms drop back to plain language |
| Chrome DevTools MCP | **Every browser check of the sandbox** | Measure, don't eyeball. Playwright only as the fallback (chiefly `prefers-reduced-motion`; say why). Responsively is Ammar's review tool, not evidence |

All six project skills are committed under `.claude/skills/` (`.git/info/exclude` lists that
folder, so a **new** file there needs `git add -f`). None of it deploys.

**The taste skill's rules are Ammar's rules for visible copy and layout:** hero subtext ≤ 20
words, at most one eyebrow per three sections, at most one middle dot per line, **no em or en
dash in anything a visitor reads** (repo docs, code comments, commits and Linear are exempt). It
does not override what is locked here: block theme with plain CSS and `theme.json` tokens (no
React/Tailwind/Motion packages), the hand-drawn schematic icon set, light-only, no stock
photography, and Ammar's exceptions (the homepage's two moving strips, AMM-178; the certification
and partner logos).

## Version targets

WordPress 6.5+; WooCommerce current stable, block-compatible (declared via
`before_woocommerce_init` in `inc/woocommerce.php`); PHP 8.1+.

## Where things live

- `inc/` — PHP includes, one concern per file, each `require_once`d from `functions.php`.
- `src/` → `build/` — the custom blocks (TypeScript, `@wordpress/scripts`); `build/` is committed.
- `assets/` — hand-written CSS and JS (no build step), self-hosted fonts, theme imagery and icons.
- `patterns/` — marketing and content sections (the "Demas" category). The homepage's sections
  are the content of **Pages → Homepage (page 17)**: change a homepage section's copy in both the
  pattern and page 17, or the cutover paste (AMM-158) brings the old copy back.
- `templates/`, `parts/` — block templates and parts; keep parts thin.
- `tools/` — operational scripts run by a person, never by the theme; not deployed.
- `docs/adr/` — architecture decision records (why a hard-to-reverse choice was made, what was
  rejected, what it costs; index in `docs/adr/README.md`). **Read ADR-002**
  (the platform decision, and five corrections the build still needs) before proposing a change
  of stack, hosting or editing model.
- `woocommerce/` — classic overrides, last resort only.
- Reference docs in the repo root (`demas-homepage-brief.md`, `demas-mega-menu-content-spec.md`,
  `demas-design-direction.md` (superseded, rewrite pending), `demas-motion-reference.md`): see
  `.claude/rules/design.md`. **`theme.json` is the single source of truth for tokens.**
- **Every contact link goes through `demas_theme_contact_url( $branch, $need )`**
  (`inc/contact.php`); the catalogue link through `demas_theme_catalogue_url()` (`/products/`).

## Forbidden patterns

- **No page-builder shortcodes or markup** (Elementor, WPBakery, …). The theme exists to prove a
  no-page-builder architecture works.
- **No hardcoded content that belongs in a pattern** — marketing copy, repeated layouts, anything
  an editor would change goes in `patterns/`, not a template or part.
- **No `!important` without a comment naming the specificity conflict it resolves.** If you
  can't name it, the `!important` is masking a specificity problem to fix instead.
- **No classic WooCommerce overrides in `woocommerce/`** unless a block genuinely can't do it.
  Block-theme-native WooCommerce blocks (`woocommerce/single-product`,
  `woocommerce/product-image-gallery`, …) are the default; document why in any override file.
- **No direct database queries** (`$wpdb` writes, raw SQL) from theme code; use the APIs.
  `functions.php` stays a pure loader.
- **No dependencies on plugins beyond WooCommerce** — not Kadence, page builders, or anything not
  installed on the sandbox. Documented exceptions: `hostinger-reach`
  (intentional; don't depend on it, don't remove it) and **LiteSpeed Cache** (page cache only;
  `inc/cache.php` calls its `litespeed_purge_all` hook and does nothing without it; nothing else
  may depend on it).
- **No live-site credentials, database rows or design** (Kadence markup, theme options, CSS,
  palette, typefaces) copied here. **Content reuse is authorized** (company copy, the category
  tree, service descriptions, branch cities with staff names). **Staff email addresses never
  appear in the repo, in markup or in JavaScript**: the branch → address map lives server-side
  outside version control, and the contact form posts a branch ID. The 14 "Sandbox …" products
  are dummy data until real catalogue data lands.

## Working agreement

- Keep `functions.php` a pure loader; every new top-level concern gets its own file in `inc/`.
- Prefer WordPress core / WooCommerce blocks over custom PHP whenever a block can do the job.
- **Build step (ADR-001):** a block using TypeScript/React must be compiled with
  `npm run build` before committing, and `build/` committed with it — the deploy has no build
  step, so a stale `build/` means the change isn't live (CI blocks it).
- **CSS class names are global — namespace them per component** (a block's stylesheet and
  `assets/css/style.css` load on the same page, so shared names style each other), and grep for
  a class before naming it (the footer once reused the datasheet's `.dh-plate` and turned unreadable; it is
  `.dh-foot-plate` now).
- **Touch targets are 44px (AMM-172).** Pills and header controls take taps through a
  transparent `::before` (`style.css` section 4, one `:is()` list): a new pill style or header
  control joins that list, and a wrapped row of them needs `spacing-30` between rows. Other new
  controls are 44px themselves.
- **No jQuery, no classic WooCommerce assets** (AMM-154). Front-end code is dependency-free (plain
  JS, TypeScript modules, the Interactivity API). After any enqueue change, run a network trace on
  home, catalogue and product pages and compare with AMM-154's table.
- **A new file in `patterns/` needs a version bump:** WordPress caches the pattern list keyed on
  `Version:` in the root `style.css`, so a new pattern file is invisible (a `wp:pattern` pointing
  at it renders nothing) until it changes; bump it and `DEMAS_THEME_VERSION` in `functions.php`
  together (0.2.0 `not-found`, 0.3.0 `footer`, 0.4.0 `services`, 0.5.0 `contact`, 2026-10-01).
- **`three`, `@react-three/fiber`, `@react-three/drei`** in `package.json` are reserved for a
  planned phase-2 scroll-driven pipe scene (the system design doc's "Future ideas"): don't remove
  them, don't start that work unscheduled.
- Keep this file and the rule files current: when the architecture changes, update them in the
  same commit.
