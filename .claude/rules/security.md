---
paths:
  - "inc/**"
  - "src/**/view.ts"
  - "src/**/render.php"
  - ".github/**"
---

# Security-sensitive work and the security-audit skill

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

## `security-audit` — modes and scope

- **`security-audit`** (added 2026-10-05 at Ammar's request; project skill in
  `.claude/skills/security-audit/`, from github.com/cloudflare/security-audit-skill, commit
  c1c8a8c, MIT) — Cloudflare's source-first security review. It confirms only a real
  trust-boundary failure (who crosses which control, with what result), keeps an unresolved one
  as `needs_validation` instead of guessing, has every finding re-checked by a fresh agent, and
  never probes live systems. Two modes, used here like this:
  1. **Guidance mode — on for every security-sensitive change**, at the plan step and again
     before In Review: anything that accepts input from a visitor or the network (forms — the
     request form's handler, AMM-140, above all — REST routes, `admin-post`/AJAX handlers, query
     and search filters), anything that renders stored or visitor-supplied content into a page
     (`render_block` filters, the quote list and print sheet reading `localStorage`), personal
     data and the branch → address lookup, nonces, capabilities, rate limits, redirects, and the
     CI deploy job and its token. Loads only `SKILL.md` (~5k tokens) plus the one companion file
     that fits (usually `WEB-PROTOCOL-AND-AUTH.md` or `CLIENT-SIDE.md`); no report files.
  2. **Full audit mode — once, before cutover (AMM-158)**, as a `quick`, scoped run over the
     public surfaces (finder REST route, search, request form, quote list, content filters,
     `.github/workflows/`), and again only if Ammar asks. It launches many sub-agents, so it is
     run only on his explicit request, with a budget agreed first. Reports go to its default
     folder outside the repo (`~/security-audit-skill/demas-theme/run-<N>`); findings become
     Linear issues and fixes follow the usual plan → go-ahead → build flow, with `ponytail`.

  **Not for** design, markup, CSS or motion work, and not for server or hosting tasks (AMM-161
  is Ammar's SSH work). No sandbox that can run target code exists on this Windows machine, so
  it reviews source only; that suits a PHP theme with no local WordPress (AMM-165). It never
  tests the live site or the sandbox's server, matching "live site untouchable".

  These three are committed with the repo (`git add -f`, since `.git/info/exclude` lists
  `.claude/skills/`), so every worktree session has them; since AMM-173 nothing outside the
  theme's runtime files deploys, so they stay private.
