# Demas Sandbox — Motion Reference (analysed from `references/website-animation.mp4`)

**Scope:** motion/interaction analysis of one reference screen recording, plus a filtered list of what to adopt for
Demas. No code — standing reference material, same role as `demas-design-direction.md`. Read alongside that doc:
this one supplies the *mechanics* (which primitives, what durations, what easing) for the reveal/counter/sticky-header
patterns that doc already locked in.

**Source clip:** `references/website-animation.mp4/70a34983bb6845543f1ff39d2b2e168a.mp4`
(note: the `.mp4` path is a **folder** containing the actual file).
Screen recording of a third-party site, "GreenVest" (agriculture sector).

**Reference status:** studied for principles only. Nothing here is a design to copy — the section order, copy,
palette and photography of the reference are not ours. What transfers is the motion vocabulary and its restraint.

---

## Clip facts

| Property | Value |
|---|---|
| Resolution | 1600 × 1080 |
| Codec | H.264 + AAC |
| Frame rate | 60 fps, constant |
| Duration | 41.00 s (2460 frames) |
| Bitrate | ~1.9 Mbps |
| Viewport | single desktop viewport, never resized |

**Mobile behaviour is not observable in this clip.** No responsive breakpoint, no device frame, no resize event.
Any statement about the reference's mobile behaviour would be invented — treat mobile as unanswered by this source.

Method: `ffprobe` for the properties above; `ffmpeg` for 82 stills at 0.5 s intervals (whole-timeline pass) plus nine
dense 10 fps windows around each transition. Frames were written to the session scratchpad, never into the repo.

---

## Sequence, step by step

### 1. Preloader / hero assembly — 0.0 → 2.2 s

- **t = 0:** three thin vertical slivers, evenly spaced across mid-screen. Width ≈ 0, height ≈ 100 px.
- **0.0 → 0.5:** each sliver expands horizontally into a square; slight height growth alongside.
- **0.5 → 1.15:** panels keep widening *and* translate toward each other — the gaps close.
- **1.15 → 1.4:** panels meet; seams cross-fade out into one continuous strip.
- **1.4 → 1.8:** strip grows in width to the full viewport.
- **1.8 → 2.15:** strip grows in **height** to the full-bleed hero. The image crop widens — it is a reveal, not a zoom.
- All three panels are crops of a **single** image, so the merge reads as reassembly rather than collage. That single
  detail is what makes the effect work; three unrelated images would read as a slideshow.

### 2. Hero UI — 2.15 → 2.6 s

Nav pill, eyebrow, headline, video card, paragraph and button fade up together *after* the image has landed —
image first, interface second. Headline arrives grey and darkens. The header is a floating rounded pill, not a
full-width bar.

### 3. Idle — 2.6 → 6.8 s

Nothing moves. No hero video autoplay, no Ken Burns drift. The page is willing to sit still.

### 4. Hero → About — 6.8 → 7.4 s

Roughly one viewport of scroll in 0.6 s with a visible momentum tail: a smooth-scroll library (Lenis-class),
not native scrolling. The header is **not** sticky — it scrolls away at 7.0 s and never returns in the full 41 s.

### 5. About — 7.2 → 8.4 s

- The large paragraph reveals **word by word**, grey (≈ `#c9c9c9`) → near-black, left to right, driven by scroll
  position rather than a timed tween: it advances with the scroll and stalls when the scroll stalls.
- Three images below enter staggered. Each is an **inset clip-path expanding from the left edge**, with the image
  itself stationary underneath — content slides into a growing window instead of the frame moving. ~0.4 s per image,
  ~0.18 s stagger.

### 6. Feature cards — 8.5 → 12.7 s

Each card image has **its own vertical parallax inside a fixed mask**: image at ~115 % height, `translateY` driven by
scroll. Sky is visible at the top of travel, ground at the bottom. The cards themselves never move.

### 7. "How it works" — 13.0 → 18.5 s

- Heading uses the same word-reveal primitive as §5.
- A **horizontal accordion** of four step cards. The active card is wide: photo, dark bottom gradient, title, body.
  Inactive cards collapse to narrow white cards showing the title only.
- **16.0 → 16.7 s — the cursor is visible.** Hovering step 02 swaps the active card: widths tween while both photos
  cross-fade, ~0.55 s, decelerating, no overshoot. Body copy fades in *after* the width settles, not during.

### 8. Services — 18.5 → 26.4 s (the centrepiece)

- **19.0 → 19.3:** the dark full-bleed section enters; its background image parallaxes slower than the page.
- **19.3 → 20.1:** *entrance settle* — the centred card starts at ~1.15 scale and semi-transparent, converging down
  to 1.0 as the heading word-reveals to white. Scale-down-into-place, not scale-up.
- **20.1 → 26.3:** the section is **pinned** and scroll drives a three-slide carousel:
  - card content pushes **up and out** while the next slides up from below, masked to the card bounds (~0.35 s/swap);
  - the full-bleed background cross-fades in ~0.2 s at each crossover (mountains → forest → green/orange field);
  - a left-hand counter rolls `[01-03]` → `[02-03]` → `[03]`;
  - a right-hand hint reads `[ Keep scrolling ]` — an explicit affordance, because scroll has been hijacked.
- **26.4:** the pin releases and normal page scroll resumes.

### 9. Impact — 26.5 → 34.3 s

- Heading word-reveal, then a 4-up stat row enters. **The values are static — there is no count-up.**
- **28.7 → 29.2:** a world-map region fills grey → green as an opacity ramp (~0.5 s), a pin pops, and a tooltip card
  with an avatar slides in from the left (~0.25 s).
- **30.5 → 34.3:** the map pans and zooms from Latin America to South-East Asia; the old region unfills, the new one
  fills, the tooltip re-anchors. The cursor appears at 28.4 s and 30.5 s but the sequence is scroll-driven.

### 10. Partners — 34.6 → 36.5 s

Heading word-reveal, then a 2 × 4 logo grid fades in cell by cell, row-major, ~0.1 s stagger. **Opacity only, no
movement.** The hairline cell borders fade in with the logos.

### 11. CTA + footer — 36.6 → 41.0 s

- Full-bleed aerial image with vertical parallax; heading word-reveals to white.
- **37.9 → 38.4:** a white footer card with rounded top corners **slides up over** the image (~0.45 s). The image keeps
  parallaxing behind it, so the footer reads as sliding over a window rather than pushing content.
- **38.5 → 39.2:** footer link columns stagger in — column by column, then item by item.
- **39.2 → 41.0:** idle.

---

## Motion vocabulary

The entire 41 s is built from six primitives, reused across every section. That reuse — not any individual effect —
is the substantive finding.

| Primitive | Where it appears | Duration | Character |
|---|---|---|---|
| Word-by-word text darkening, scroll-linked | every section heading and lead paragraph | scroll-bound | grey → ink, ~0.06 s per-word offset |
| Clip-path reveal from one edge, image static beneath | hero panels, about images | 0.4 s | decelerating |
| Image parallax inside a fixed mask | feature cards, services background, CTA | scroll-bound | slower than page scroll |
| Width tween + cross-fade | how-it-works accordion | 0.55 s | decelerating |
| Masked vertical push (out upward / in from below) | services carousel, counter roller | 0.35 s | decelerating |
| Opacity stagger across a grid | partner logos, footer columns | 0.1 s step | near-linear fade |

**Easing:** everything decelerates. No bounce, no overshoot, no elastic anywhere in 41 seconds.

**Durations:** entrances land in 0.3–0.6 s. Nothing exceeds 0.6 s except the 2.2 s preloader.

**Composition:** ~1280 px content column inside a 1600 px viewport; generous white space; one photo-heavy dark section
set between three light ones for rhythm; a single accent colour (yellow dot eyebrows) used strictly as a marker,
never as a fill.

---

## What to adopt for Demas

Filtered against `demas-design-direction.md` and its "smaller scale than the references" principle.

### Adopt

1. **One reveal vocabulary, reused site-wide.** The design direction already calls for scroll-triggered reveals; the
   discipline is the lesson. Pick three or four primitives and apply them identically across the homepage,
   `archive-product.html` and `single-product.html`. Consistency reads as engineering rigour — appropriate for a
   supplier with 45 years and ISO marks behind it.
2. **Scroll-linked word reveal on the one big statement per section.** Cheap to build (per-word spans plus
   IntersectionObserver, or `background-clip: text` driven by a scroll-progress custom property) and it paces long
   technical copy. Use it once per section. Never on navigation, product titles, specs or prices.
3. **Clip-path reveal with a static image underneath.** Better than the usual translate-and-fade for product
   photography: the image never moves, so detail stays legible throughout the reveal. Direct fit for the category
   cards in the homepage brief.
4. **Parallax inside a mask, small amplitude.** Cap travel at ~8–12 % of card height. Depth without motion sickness,
   at the cost of one `transform` per card.
5. **Label any pinned section.** The reference's `[ Keep scrolling ]` hint is honest UX, not decoration. If we ever
   pin, we label.
6. **Footer sliding over a fixed CTA image.** Achievable with `position: sticky` and z-index alone — no JavaScript.
   Strong closing beat at near-zero cost.
7. **Photo-overlay captions with counters.** The design direction already specifies numbered overlay captions
   (`01 — Beneath the surface`); the counter-roller treatment extends naturally to the category gateway.

### Adapt before use

8. **The accordion for a process or capability section.** The reference's is hover-only — a dead end for keyboard and
   touch. Ours must be click/focus-driven with hover as an enhancement, with roving `tabindex`, and must stack
   vertically below ~900 px.
9. **The stat row.** The reference's values are static. Ours should count up: the design direction already specifies
   counters for 45+ years, ~700 products and 3 ISO certifications, and real numbers earn the emphasis. Keep the
   reference's restrained layout (4-up, small-caps label beneath a large figure); add the count-up.

### Skip

10. **The pinned, scroll-jacked carousel.** The highest-cost item in the clip — it needs a scroll library plus
    pin/progress machinery — and it fights a catalogue site's core job of getting people to products. Present the
    same three items as a plain grid.
11. **The smooth-scroll library.** It is what makes the reference feel expensive, and it is also 15–20 KB that breaks
    native scrolling, anchor links and browser find-in-page. Not worth it for a WooCommerce catalogue.
12. **The 2.2 s preloader.** Genuinely well-made, and also 2.2 seconds of blocked LCP. On a product site that is a
    conversion tax.
13. **The non-sticky header.** The reference drops its navigation entirely after the hero. The design direction
    specifies a sticky condensing header, which is the correct call for ~700 SKUs and a mega-menu. Do not let this
    reference argue us out of it.

---

## Constraints on anything built from this

- Every reveal needs a `prefers-reduced-motion: reduce` branch that renders the end state immediately.
- Every scroll-linked effect must leave content readable with JavaScript disabled. The word reveal in particular must
  default to full-contrast text, never grey.
- Motion must not gate product data. Prices, specifications, stock state and add-to-cart controls render at full
  opacity on first paint, independent of scroll position.
- These are progressive enhancements over the block markup already planned — no page builder, no new framework.
  Anything needing a compiled block follows ADR-001 (`npm run build`, `build/` committed).

## Status

Reference material, current as of 2026-09-02. Analysis only — no theme files were changed in producing it.
Extracted frames lived in the session scratchpad and were not committed.
