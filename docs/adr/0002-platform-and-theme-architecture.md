# ADR-002 — Build on WordPress with a custom block theme, not a page builder or a headless front end

**Status:** Accepted, with required corrections (see below)
**Date:** 2026-09-15
**Deciders:** Ammar
**Supersedes:** nothing
**Related:** ADR-001 (build step for compiled blocks)

---

## Context

Demas Group needs a replacement for the Kadence-based demas-group.com. The constraints that
actually drive the decision:

- **~700 SKUs** across five product groups and roughly twenty subcategories, already modelled
  as WooCommerce `product_cat` terms with URLs that should survive migration.
- **Non-technical staff** must be able to add and edit products without a developer.
- **Bilingual English/Arabic with RTL**, not yet started, and architectural in impact.
- **Fifteen branches** with named contacts — content, not decoration.
- **Quotation-led B2B**, probably — whether customers ever check out with a card is still
  undecided (see Open question).
- **A solo maintainer who is learning web development**, working with an AI assistant. Not a
  development team. This is the binding constraint, and it outranks every technical argument.
- **Award-level design ambition** (Awwwards was named as the bar).
- Modest budget; currently Hostinger shared hosting with git auto-deploy and no build step.

The existing site is WordPress + WooCommerce + the Kadence page builder. Layout lives in the
database as builder markup: slow, locked in, and not a foundation for distinctive work.

## Decision

Keep **WordPress + WooCommerce**, and build a **custom block theme** from scratch — no page
builder, no front-end framework, no CSS framework.

Concretely:

- Layout in version-controlled block-markup template files, not in the database.
- Design tokens centralised in `theme.json` (palette, type scale, spacing, radii, motion).
- Hand-written CSS and dependency-free JavaScript for everything the platform doesn't provide.
- One compiled block (the mega menu) using TypeScript and the WordPress Interactivity API,
  built with `@wordpress/scripts` per ADR-001.
- Marketing sections as registered block patterns in `patterns/`.

## Evidence

Homepage measured on the sandbox, 2026-09-15, uncompressed:

| Source | Size |
|---|---|
| **Our code** (`style.css` + `main.js`) | **~40 KB** |
| WooCommerce CSS (7 files) | 221 KB |
| jQuery + jQuery Migrate | 99 KB |
| Cart + mini-cart JS | 24 KB |
| WordPress emoji script | 22 KB |
| WooCommerce order-attribution tracking | 15 KB |
| Self-hosted fonts (6 files, Latin only) | 251 KB |
| **Total** | **889 KB across 33 requests** |

Server response time: **595 ms** (target: under 200 ms).

**Our own code is about 4% of the page.** The rest is platform cost. Cart, mini-cart and the
full WooCommerce stylesheet currently load on a homepage that contains no products.

This number is the honest frame for the whole decision: the chosen platform is the dominant
factor in page weight, and the custom work is nearly free by comparison.

## Alternatives considered

| Option | Better at | Worse at | Verdict |
|---|---|---|---|
| **A. WP + Woo + custom block theme** *(chosen)* | Catalogue fit, staff editing, migration path, learnability, design freedom | Platform weight, needs code for layout changes | **Chosen** |
| **B. WP + a modern page builder (Bricks)** | Staff autonomy, build speed, no developer dependency | Custom interaction (the notch, schematic, Branch Desk) fights the builder; award-level motion out of reach | Rejected — the opposite trade to the one wanted. Viable fallback if maintenance burden becomes the priority. |
| **C. Headless — WP backend + Next.js/Astro** | Performance ceiling, animation freedom, i18n, what most award winners use | Two systems; preview breaks; **headless WooCommerce means rebuilding cart/checkout/payments**; Node hosting cost; roughly doubles what one maintainer must understand | Rejected for now. Strongest option *if a front-end developer is hired* — realistic phase 2. |
| **D. Astro + headless CMS, no WordPress** | Raw speed (~100 KB pages) | Discards the product data, category tree and staff familiarity; product management rebuilt from scratch | Rejected — right for a brochure site, wrong for a 700-SKU catalogue. |
| **E. Shopify** | Commerce reliability, hosted, native Arabic, no maintenance | Recurring cost; bounded design freedom; migration; **pays for a checkout that may never be used** | Rejected on current information. Reconsider if the site becomes genuinely transactional. |
| **F. Hybrid — Woo backend + Astro marketing front end** | — | Two codebases, two deploys, two design systems drifting apart | Rejected outright. |

## Consequences

### Positive

- Product catalogue, category archives and search come from WooCommerce rather than being built.
- Layout is in git: reviewable, revertable, diffable. No builder lock-in.
- One `theme.json` change propagates brand-wide — most agency WordPress builds never get this.
- Migration to the live site is a theme swap, not a platform change.
- Every line is readable PHP, CSS and JavaScript — the most documented stack on the web.

### Negative — accepted knowingly

- **~500 KB of platform weight** before our own code loads. Mitigable, not removable.
- **Layout changes require code.** Staff can manage products and pages; they cannot restructure
  a homepage section without a developer.
- **Award-level design is harder here** than on a custom JavaScript stack. Judges notice page
  weight. WordPress is a handicap, though not a disqualification.
- **Bus factor of one.** If the maintainer becomes unavailable, the next person inherits a
  bespoke theme rather than a mainstream builder that any freelancer knows.

## Required corrections

The decision is sound; the current execution has gaps. In priority order:

1. **Make pattern content editable.** Every homepage section is currently a `wp:html` block —
   raw HTML in a PHP file — so nobody at Demas can change a headline or a statistic without
   editing code. *This contradicts the reason for choosing WordPress at all.* Convert to real
   block markup, keeping raw HTML only where the notch and schematic genuinely require it.
   Left unfixed, the project carries all of WordPress's weight and none of its benefit.
   *(1–2 days)*
2. **Local development environment.** `@wordpress/env` (Docker) plus a documented
   staging→production plan. There is currently no PHP locally — code cannot even be
   syntax-checked before deploying — and the eventual cutover has no rehearsal. *(half a day)*
3. **Basic CI.** PHP syntax check, CSS/JS lint, build verification on push. A real bug shipped
   for want of this: the `4xl` font-size slug that WordPress silently renamed to `4-xl`, which
   made every large heading render at body size. *(2 hours)*
4. **Trim platform assets.** Conditionally dequeue WooCommerce CSS/JS and jQuery on pages with
   no shop content; drop the emoji script. *(half a day)*
5. **Decide the Arabic approach** — Polylang, WPML, or separate installs — *before* building
   more templates. It changes URL structure, the database and every template. *(decision only)*

## Revisit this ADR when

- A front-end developer joins → option C becomes genuinely viable.
- The site becomes transactional with real card checkout → option E deserves a second look.
- Maintenance burden on non-developers becomes the dominant complaint → option B is the
  honest fallback.
- Page weight blocks a performance target that matters commercially.

## Open question

**Is Demas selling online, or quoting?**

Everything in the company's own content points to quotation-led B2B. If that is confirmed,
WooCommerce is carrying roughly 320 KB of cart and checkout machinery that will never be used,
and a lighter custom product type becomes a defensible alternative. If customers genuinely
check out with a card, WooCommerce is correct and Shopify deserves reconsideration.

This is the last genuinely architectural question still open. Everything else is execution.
