---
paths:
  - ".github/**"
  - "phpcs.xml.dist"
  - "package.json"
---

# CI and the deploy branch: the full account

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

CLAUDE.md has the rules; this is the history behind them.

## CI, the `deploy` branch, and why only the runtime files deploy

Deploys via Git auto-deploy, **gated by CI** (AMM-156, 2026-09-28): a push to `main` runs
`.github/workflows/checks.yml` — `php -l` on every PHP file (PHP 8.3, as the sandbox), WordPress
Coding Standards (`phpcs.xml.dist`, zero violations and blocking), `build/` must match `src/`,
`theme.json`/`block.json` must parse, and no email address anywhere in the repo. Only when all
pass does the `deploy` job fast-forward the **`deploy` branch**, which is what Hostinger deploys
into `wp-content/themes/demas-theme` on the sandbox (the sandbox site's Git setting was switched
from `main` to `deploy` on 2026-09-29, with Ammar's OK, via the Hostinger API — sandbox domain
only). Proven end to end the same day: a stale `build/` pushed to `main` was blocked and never
reached the sandbox; the rebuild passed, moved `deploy`, and Hostinger deployed it on its own. A failing push never reaches the sandbox;
GitHub emails the owner. Never push to `deploy` by hand. Results are readable without signing
in through the commit's check runs (`/commits/<sha>/check-runs` in GitHub's API); the PHPCS
result is posted as its own "PHPCS report" check with one annotation per violation. There is no
server-side build step — the theme's files in this repo are the files WordPress reads, which is
why compiled block output is committed (see ADR-001 under Working agreement).

**Only the runtime files deploy (AMM-173, 2026-10-03).** Hostinger serves the theme folder
as-is, so every file in it can be downloaded — until then that was the whole repo (this file,
the briefs, `docs/`, `tools/`, `src/`, the package files, `.github/`). The deploy job no longer
fast-forwards `deploy` to `main`: it commits only `style.css`, `functions.php`, `theme.json`,
`screenshot.png`, `inc/`, `build/`, `templates/`, `parts/`, `patterns/`, `assets/` and
`woocommerce/` (the `RUNTIME` list in the workflow) on top of the previous `deploy` commit, still
fast-forward only. So `deploy` is its own line of commits: each is titled `Deploy <main sha>`,
which is how to tell which `main` commit is on the sandbox, and an older commit's re-run is
skipped rather than rolling the sandbox back. **A new top-level file or folder WordPress reads
(`styles/`, `languages/`) must be added to `RUNTIME`, or it never reaches the server.** Anything
else committed here stays private by default. At cutover (AMM-158) the live theme must come from
`deploy`, not `main`.
