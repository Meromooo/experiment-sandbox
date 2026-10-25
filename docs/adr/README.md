# Architecture decision records

One file per significant, hard-to-reverse decision: why it was made, what was rejected, and
what it costs. Written when the decision is taken, not after. Superseded ADRs stay in place
with a status line pointing at their replacement — the history is the point.

| # | Decision | Status |
|---|---|---|
| [001](0001-commit-compiled-block-output.md) | Compiled block output (`build/`) is committed to git — Hostinger's git auto-deploy has no build step | Accepted (decided 2026-09-14, written up 2026-10-06) |
| [002](0002-platform-and-theme-architecture.md) | Build on WordPress with a custom block theme, not a page builder or a headless front end | Accepted, with required corrections |

Design decisions that are not hard to reverse (palette, shape grammar, motion vocabulary) are
recorded with their reasons in `demas-design-direction.md` at the repo root, not here.
