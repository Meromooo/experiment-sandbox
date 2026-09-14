# Demas Sandbox — Site-Wide Design Direction

> **Superseded — 2026-09-14.** The visual system below (navy/gold, serif headlines, Daylight /
> Titan Intake as references) is no longer the direction. The target is the reference recording
> analysed in `demas-motion-reference.md`, re-skinned in Demas's own register; tokens now live in
> `theme.json`. The *pattern* decisions here — sticky condensing header, animated stat counters,
> trust-strip hover detail, no testimonials without real ones, the "smaller scale than the
> references" principle — still stand and carry into the new direction. Full rewrite pending.

**Scope:** visual/motion design system for the whole site, applied first to the homepage. No code — this is direction for Claude Code to implement, same role as the homepage content brief.

**Reference sites evaluated:** godaylight.com, titanintake.com, farmminerals.com (farmminerals.com ruled out as a reference — too thin/minimal for a company needing to convey ~700 SKUs and decades of engineering depth).

**Guiding principle (explicit, from Ammar):** smaller scale than the references. Daylight and Titan Intake are the visual/functional direction, not the literal target — every pattern below was picked individually, not adopted wholesale, to avoid bloating the site with motion or sections that don't earn their place.

---

## Site-wide reusable patterns (apply across templates, not just the homepage)

**Scroll-triggered reveals.** Sections fade/slide in as the user scrolls to them. Lightweight — an IntersectionObserver-based enhancement, no framework needed, fits the existing Interactivity API approach already used for the mega-menu.

**Animated stat counters.** Numbers count up when scrolled into view (45+ years, ~700 products, 3 ISO certifications, etc. — see homepage brief Section 3). Same IntersectionObserver mechanism as reveals; cheap to build, direct fit for real numbers already confirmed.

**Sticky header that condenses on scroll.** Header compacts (smaller logo, tighter padding) once scrolled past the hero — CSS + a small scroll listener, no architecture change.

**Trust strip with hover detail.** The ISO/SASO/WRAS certification strip (homepage brief Section 2) becomes interactive: hovering or tapping a mark shows one line of context (e.g. "ISO 45001 — Occupational Health & Safety"), same content as already planned, just with the added interaction.

**Photo-overlay captions, extended beyond the hero.** The hero's numbered captions ("01 — Beneath the surface," etc.) extend to the product category cards (homepage brief Section 4) — each category card gets a short overlay phrase plus its product count directly on the photo, rather than caption text living below the image. Gives the whole page a consistent visual language instead of confining the technique to one section.

---

## Decorative extras — included, with scope notes

**Floating tag/word clusters.** Small floating text pills near feature blurbs, in the style of Titan's "+Sort +Triage +Transcribe." Safe to include since the content is just real category/product taxonomy terms (e.g. under "Five specialisms, one supplier": +Pipes +Fittings +Filtration +Controllers +Drippers +Rotors), not invented claims — low risk, easy to build as static styled spans.

**CTA repetition.** Included, but scaled down from Titan's literal density (which repeats "See a Demo" on nearly every scroll). Given the "no bloat" principle, this should land as a CTA at each natural section break (hero, category gateway, closing) rather than after every subsection — repetition that reinforces the page's structure, not a CTA on every scroll.

**Before/after comparison cards.** Included, with one hard constraint: needs a real number to back it, not an invented one. Right now the only confirmed comparative claim is the quotation turnaround ("responds with a specification and quotation — typically within two business days"). Recommend framing this qualitatively rather than inventing a competitor timeline: e.g. "Without a single partner: multiple vendors, multiple timelines" vs. "With Demas: one partner, one quotation, two business days" — real claim on our side, no fabricated number on the other side. If a genuine additional stat surfaces later (e.g. an average project timeline reduction), this section can carry it.

---

## Explicitly declined / deferred

**Testimonials/case studies.** No real client quotes or logos available yet — skipped rather than using placeholder material. Revisit once real testimonials exist.

**Interactive widget/app-UI mockups.** Daylight shows a live thermostat/energy-gauge card floating over its hero because it has a real companion app. Demas has no equivalent product dashboard to preview, so this would have no real content behind it — skipped.

---

## Technical feasibility

Nothing here requires a change to the existing architecture. Scroll reveals and stat counters are a single small IntersectionObserver-based script, consistent with how the mega-menu already uses the WordPress Interactivity API for its own state. The sticky header is CSS plus a minimal scroll listener. Photo-overlay captions and the trust-strip hover detail are markup/CSS work on top of the Cover and Group blocks already planned in the homepage brief's "native blocks mapping" section. No page builder, no new framework, no build-pipeline changes.

## Status

Ready to hand to Claude Code as `/spec` input for the homepage build (and to reference for later templates — product/archive pages should adopt the same reveal/counter/sticky-header patterns for visual consistency). Not yet run through Fable 5 for a copy pass on the new elements (the floating tags, the before/after card copy) — worth doing that first if you want drafted copy for those two new pieces rather than Claude Code writing it inline.
