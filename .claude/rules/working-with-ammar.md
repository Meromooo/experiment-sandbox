# Working with Ammar

Standing rules for every session on this repo, local or cloud. They were kept in one local
machine's memory until 2026-10-06; this file carries them to cloud sessions, which read the
repo's `.claude/rules/`. CLAUDE.md holds the architecture and project rules; this file holds how
Ammar works.

Ammar owns this project and directs it for a real client (Demas Group, a Saudi irrigation and
landscape supplier). He is learning web development as he goes.

## How to work with him

1. **Plan, say "ready", wait.** Do the reading and analysis first, then state "ready" with an
   exact list of what will change, and wait for his go-ahead. A go-ahead covers only the items
   he names; don't start the next piece on an earlier approval.
2. **A question means stop.** If his message contains any question, answer it and end the turn,
   even when the same message also says yes. The go-ahead is a separate message with no open
   question in it.
3. **Explain terms the first time you use them**: what the thing is and why it matters, in a
   line. Don't assume he knows RTL, a block theme, a token, LCP and the like.
4. **Small, reviewable tasks.** Offer options as A/B/C with a recommendation; he answers crisply.
   He often replies with a numbered list and "go on items N to M".
5. **Chat can be terse** (the caveman style is on), but explanations, plans, reports, Linear
   issues and repo docs are written in clear normal prose.
6. **Saved page content is his to change.** Page content lives in the WordPress database, and he
   applies it in the editor himself. When a change touches saved content (a pattern already
   inserted in a page, a link inside a page), end with numbered editor steps (page, block,
   link) and wait for him to say "saved".

## The live site is untouchable

- The current demas-group.com (the **old** site) stays live until Ammar decides the cutover
  (AMM-158). Nothing done here may change it. When you mean the old site, say "the current
  demas-group.com (old site)", never "live" alone; that word has been misread.
- The sandbox shares one Hostinger account (hPanel, CDN, settings) with the old site, so an
  account-level change can reach it even when theme code can't. **Never change hPanel, the CDN,
  cache settings, DNS, plugins or either database.** Any such change needs his explicit OK each
  time, scoped to the sandbox domain only, and the setting read back afterwards.
- Every plan carries a short "Live site stays untouched" section saying why.

## Server work goes through Ammar

There is no SSH from a Claude session. Anything on the server outside the theme folder is done by
Ammar over SSH: give exact commands, one per code block, and review the output he pastes back.
Inspect read-only first; archive anything with content (outside every web folder) and check the
archive's file count before giving a delete command. Permanent deletes are his to run.

## Deploys and CI

- A push to `main` runs CI (`.github/workflows/checks.yml`); only when every check passes does the
  deploy job commit the theme's runtime files onto the `deploy` branch, titled
  `Deploy <main sha>`, and Hostinger deploys that to the sandbox. Never push to `deploy`.
- **Cloud sessions work on a branch and open a pull request. They don't push to `main`.** Ammar
  merging the pull request is the go-ahead to deploy.
- To see CI finish, poll `git ls-remote origin refs/heads/deploy` for a change (about every 15 s),
  then check that `git log -1 --format=%s origin/deploy` names the pushed commit. Don't poll
  GitHub's API: unauthenticated, it allows only 60 calls an hour.
- Before pushing, `git fetch`: other sessions push to `main` too.
- Then confirm on the sandbox by its served content (curl and look for the change), not by
  assuming. If Hostinger misses a deploy, ask Ammar; he can click Deploy in hPanel.

## Skills and checks

- **`ponytail`** on every debugging, fixing or performance task, including the planning turn and
  every later turn of that task. Measure before changing; fix only what the measurement names.
- **The taste skill's rules are ones he likes**, not findings to debate. His standing exceptions:
  the homepage may have two moving strips (the supply-list marquee and the certificates logo
  strip, AMM-178), and the certificate and partner logos may be used.
- **Browser checks** use Chrome DevTools locally. A cloud session has no browser: check the
  sandbox with `curl` (its domain is allowed in the cloud environment), and say plainly that the
  visual and accessibility checks are still to be done in a local session.

## Settled decisions (don't re-open)

- The visual direction (the reference recording re-skinned in Demas's register); the old site's
  design is dropped, its content kept.
- 46 years of operation; 15 branches with named staff.
- The product category tree is locked: make it easier to navigate, never re-parent it.
- House SKUs are `DMS-<SEG>-<NNN>` (`tools/assign-house-skus.php`). The product plate labels every
  code "SKU"; don't add a "manufacturer vs house" legend to it.
- Staff email addresses never appear in the repo, in markup or in JavaScript.
