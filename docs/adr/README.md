# Architecture decision records

One file per significant, hard-to-reverse decision: why it was made, what was rejected, and
what it costs. Written when the decision is taken, not after. Superseded ADRs stay in place
with a status line pointing at their replacement — the history is the point.

| # | Decision | Status |
|---|---|---|
| 001 | Compiled block output (`build/`) is committed to git — Hostinger's git auto-deploy has no build step | Accepted — **not yet written up**; currently recorded inline under "Working agreement" in `CLAUDE.md` |
| [002](0002-platform-and-theme-architecture.md) | Build on WordPress with a custom block theme, not a page builder or a headless front end | Accepted, with required corrections |

ADR-001 predates this folder and should be backfilled here so `CLAUDE.md` can link to it
rather than carrying the reasoning itself.
