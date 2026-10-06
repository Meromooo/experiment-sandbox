# ADR-001 — Commit compiled block output (`build/`) alongside its source

**Status:** Accepted
**Date:** 2026-09-14 (decided, with the build tooling in `2f31e34`); written up 2026-10-06 (AMM-157)
**Deciders:** Ammar
**Supersedes:** nothing
**Related:** ADR-002 (platform and theme architecture), AMM-156 (CI), AMM-173 (runtime-only deploy)

---

## Context

The theme's custom blocks are written in TypeScript and React and compiled by
`@wordpress/scripts` (`npm run build`, which runs `wp-scripts build --experimental-modules`).
The source is in `src/<block>/`; the compiled output, which is what WordPress actually loads,
is in `build/<block>/`: each block's `block.json`, `render.php`, the compiled editor script,
the view module and the compiled stylesheets. There were 23 blocks as of 2026-10-06, from the
mega menu (the first, 2026-09-14) to the request form.

The sandbox is on Hostinger shared hosting, deployed by Hostinger's Git integration. That
integration copies files from a branch into `wp-content/themes/demas-theme`. It runs no build:
there is no Node, no npm and no hook to run one. Whatever is in the branch is what WordPress
reads.

So compiled output has to come from somewhere other than the server.

## Decision

**`build/` is committed to git, next to `src/`.** Any change to a block is followed by
`npm run build`, and the commit includes both the source change and the rebuilt `build/`.

How this is enforced today:

- **CI rebuilds and compares** (AMM-156, 2026-09-28). The `build` job in
  `.github/workflows/checks.yml` runs `npm ci` and `npm run build` on Node 24 and fails if
  `build/` changed. A push whose `build/` doesn't match its `src/` never deploys.
- **Only runtime files deploy** (AMM-173, 2026-10-03). The deploy job commits `build/` onto the
  `deploy` branch with the other files WordPress reads. `src/`, `package.json` and the lockfile
  stay in the repo and never reach the server.

## Alternatives considered

| Option | Verdict |
|---|---|
| **Build on the server** | Rejected. Shared hosting has no Node, and Hostinger's Git deploy has no build hook. |
| **No compiled blocks: plain JavaScript only** | Rejected for blocks. Fine for `assets/js/main.js` and `editor.js`, which are plain files. But the Interactivity API view modules (the quote list, the mega menu), the TypeScript view modules (the finder, the Branch Finder, the request form, the schematic's film) and the editor sidebars of the theme's blocks all need the `@wordpress/scripts` toolchain: JSX, module output, the generated `*.asset.php` dependency lists. |
| **Build in CI and commit only to `deploy`** | Not possible on 2026-09-14, when Hostinger deployed `main` directly. Possible since AMM-173, because CI now writes the `deploy` branch. It would take `build/` out of `main` and the review diffs. See *Revisit*. |
| **Upload build output by hand (FTP, File Manager)** | Rejected. Deploys would no longer be traceable to a commit, and the sandbox and the repo would drift apart. |

## Consequences

### Positive

- The branch is the deployment: what is committed is exactly what WordPress reads. No server
  step can fail or differ.
- A deploy can be checked by reading `build/` on the `deploy` branch. No build has to be
  reproduced to see what is live.

### Negative, accepted knowingly

- **Forgetting to rebuild means the change is not live.** Before AMM-156 this failed silently:
  the source changed, the old compiled file kept running, and nothing said so. Now the CI
  `build` job blocks the push from deploying, so the cost is a failed check and a second push,
  not a silent mismatch.
- **Build with the lockfile's versions.** CI compares byte for byte, so a local build made with
  different package versions can fail the check even when the source is right. Install with
  `npm ci` (the lockfile's exact versions), not `npm install`.
- **Noisy diffs.** Every block change shows twice, once in `src/` and once minified in
  `build/`. When reviewing, read `src/`; `build/` only has to be present and current.
- **Merge conflicts in `build/` are never resolved by hand.** Resolve the conflict in `src/`,
  run `npm run build`, and commit the result.

## Revisit this ADR when

- **The rebuild step becomes a burden.** Since AMM-173, the CI deploy job could run
  `npm run build` itself and commit `build/` only to `deploy`. `main` would then hold source
  only, and the "build matches source" check would no longer be needed. The cost: `main` alone
  would no longer be a runnable theme (for a local environment, AMM-165, or a manual upload),
  and the deploy job would become a build step that can fail.
- **Hosting changes** to something that builds on deploy.
