---
name: model-routing
description: Plan step of any task with three or more steps, once per task. Splits the plan into steps and gives each to the cheapest model that can do it well - Opus in the main session for thinking, the Haiku helpers (Explore, mechanic) for long reads and repeated exact edits, the Sonnet checker for sandbox measurements. Also the brief template, how to check a helper's work, and how to measure the routing (usage.js).
---

# Model routing

Ammar's standing instruction (2026-10-13): hand the jobs below to the helper agents in
`.claude/agents/`. This is his explicit request to use subagents in this repo.

## Why it pays, and where it doesn't

Measured on this repo's logs on 2026-10-13 (`usage.js all`): 5,349 calls, all on the main
model, none on a helper; the main conversation averaged **460k tokens per call** (up to 970k),
because every call re-reads the whole conversation. That re-reading is most of the cost. A
helper saves twice: it runs on a cheaper model, and **what it reads never enters the main
conversation**, so it isn't re-read on every later call. A handoff costs the main session the
brief it writes, the answer it reads and the check it makes; for a one-line edit that is more
than doing the edit.

## Who does what

| Step | Who | Model |
|---|---|---|
| Planning, design and copy choices, debugging and root cause, security-sensitive code (`.claude/rules/security.md`), CI and the deploy job, anything Ammar reads (plans, reports, Linear, docs, commit messages) | main session | Opus |
| One or two edits, or anything in a file you already have open | main session | Opus |
| Long reads: where X is defined or used across the repo, which files match, git history, CI or build logs, what the sandbox serves (curl) | `Explore` | Haiku |
| The same exact edit in 5+ places or 3+ files, a rename with its references, a list of exact old-to-new strings, `npm run build` and the `build/` cleanup | `mechanic` | Haiku |
| Measuring on the sandbox: widths, computed sizes, tap targets, console and network, reduced motion | `checker` | Sonnet |
| Waiting for CI and the deploy | background shell loop (`working-with-ammar.md`) | none |

Hand off only when it saves. **Do it yourself** when you already know the `file:line`, when
you will have to read the content anyway to make the decision, when the brief would be longer
than the work, or when the step is a judgement. **Hand off** when the output you'd otherwise
read is long (a whole long file, many search hits, a log, a browser page), or the edit repeats.
One brief per batch, never one per item. Run helpers in parallel only for independent jobs.

## In the plan

Tag every step of a plan for Ammar with who does it, so he can see the routing:

```
1. [Opus] decide where the press state lives
2. [Explore·Haiku] list every :active rule in assets/css and src/
3. [mechanic·Haiku] add the 13 classes to the :is() list in style.css section 4
4. [Opus] review the diff
5. [checker·Sonnet] measure press scale and tap height at 320/768/1024/1440
```

## The brief

A helper starts with no memory of the conversation and doesn't load CLAUDE.md, so the brief
carries everything:

```
Goal:   one sentence.
Where:  exact paths, or the search scope.
Do:     the exact edits (old -> new) or commands, or the exact question.
Expect: the count or values you expect (the helper stops on a mismatch).
Don't:  paths or actions that are off limits for this job.
Return: the format and the line cap, if they differ from the agent's own.
```

For the checker, write each check as a page, a width, an `evaluate_script` snippet and the
expected value. Name the deploy it should see (`Deploy <sha>`) so it can tell old content from
new.

## Check every handoff

- **mechanic:** read `git diff --stat`, then the diff itself when it is under about 60 lines,
  or two hunks of it when larger. A count that differs from the brief's is a failure.
- **Explore:** before a finding drives a change, Read the one line it cites.
- **checker:** every pass must carry a number; a pass without one counts as not run.
- A helper's answer ending in `ambiguous:`, `count-mismatch:`, `needs-opus:`, `needs-confirm:`
  or `blocked:` comes back to you. Don't send the same brief again. After two failed handoffs
  in one task, do the rest of that task yourself.

## Keep the main conversation small

- Read line ranges, not whole long files, when you know where to look.
- No screenshots unless something has to be judged by eye; measure instead.
- One Linear issue, or one batch, per session. Ammar's session habits (model at start,
  effort, advisor) are in `.claude/rules/working-with-ammar.md`.

## Cloud sessions

The agents are committed, so cloud sessions have them. There is no browser in the cloud: the
checker runs what curl can check and lists the rest as not run.

## Measure

```
node .claude/skills/model-routing/usage.js       # last 7 days
node .claude/skills/model-routing/usage.js all   # everything
```

It prints calls, output and context read per model (main or helper), the helpers' share of
calls, the main conversation's average size per call, and the same per session. Compare with
the 2026-10-13 baseline above: helper calls should appear in sessions that searched, built or
measured, and the average main conversation should fall (the 300k auto-compact window in
`.claude/settings.json` caps it). If a helper's work keeps needing to be redone, move that
row of the table back to Opus.
