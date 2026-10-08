---
paths:
  - "tools/**"
---

# tools/ scripts and the Hermes checks

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

## `tools/`

- `tools/` — one-off operational scripts run by a human on the host, never by the theme at
  runtime. `create-product-categories.sh` (WP-CLI, builds the locked category tree; superseded
  by the 2026-09-17 live clone). `assign-house-skus.php` + `house-skus.csv` (run with
  `wp eval-file`, dry-run by default): issues `DMS-<SEG>-<NNN>` house references to the 172
  products that had no manufacturer SKU and files the 23 uncategorised ones — see the file
  header for the grammar and why `DMS-` sits in the brand slot. Nine rows are flagged
  `DUPLICATE` and get a category but no number; they are for Demas to delete (applied on the
  sandbox: 172 `DMS-` SKUs). `hero-film.sh` (AMM-175, needs ffmpeg) joins the four Vidu clips
  of the homepage's site-plan film, grades each clip's background to `paper` and the pipes to
  about `water`, crops the empty left of the frame (keeping the full height), and writes the
  four files the schematic block's film mode uses; re-run it on new clips. It never hides the
  "Vidu AI" mark: clean clips come from a Vidu plan that allows commercial use, which is still
  open in AMM-175. `credential-logos.py` (AMM-178, Python 3 with Pillow and NumPy) turns
  certificate and partner logos as published into the belt's files: background made
  transparent, any frame round the file or box round the mark dropped, trimmed, sized for equal
  visual weight (equal area, a little more for sparse marks) and centred on one 336 x 136
  canvas, so every plate shows its logo at one size; the files are uploaded and picked per
  credential. `make-icons.py` (2026-10-05, Python 3 with Pillow; run locally as
  `python tools/make-icons.py assets/images`) writes the whole icon set from its two rectangle
  masters (see `assets/images/`). `make-category-drawings.py` (AMM-186, 2026-10-07; Python 3
  standard library; run locally as `python tools/make-category-drawings.py`) writes
  `inc/category-drawings.php`, the homepage category cards' product drawings: change a drawing
  in the script and re-run it, never the PHP by hand. Its scattered parts (fog droplets, turf,
  soil, fibres) come from seeded random numbers, so a re-run gives the same drawings.
  `make-key-plan.py` (AMM-188, 2026-10-08; Python 3 standard library) writes
  `inc/key-plan-land.php` from Natural Earth's public-domain 1:50m countries file, which is not
  in the repo: download `ne_50m_admin_0_countries.geojson` (3.1 MB, from
  github.com/nvkelso/natural-earth-vector) and run
  `python tools/make-key-plan.py path/to/that/file`. It projects, clips to the frame and
  simplifies; change the frame or the detail there.
  `hermes/checks.py` (AMM-183, 2026-10-07; Python 3 standard
  library only) is the scheduled checkups Hermes Agent runs from Ammar's machine: `smoke` (key
  pages, their block markers, every theme stylesheet/script/icon, the finder route, the no-cart
  redirect), `deploy` (the newest `deploy` commit is byte for byte what the sandbox serves and the
  cached homepage links the current `?ver=`), `taste` (the taste rules on home, Services, Contact
  and the 404: dashes in text and `alt`/`aria-label`, middle dots, eyebrows, moving strips, hero
  subtext words) and `links`. Read-only GETs to public URLs. Hermes runs it as script-only jobs
  (no model, no ChatGPT allowance) with `--file`: silent when clean, otherwise each finding
  becomes a Backlog issue labelled `hermes`, or one comment a day on the open issue that already
  carries its stable `[key]`, and one line per finding goes to Telegram. **The script is the only
  thing that writes to Linear, and only through `issueCreate` / `commentCreate`:** Linear's
  "Create issues" key permission also allows editing issues (a test edit went through on
  2026-10-07), so Hermes's own Linear connection is limited to read tools. When a page's audited eyebrow count, hero class or a smoke marker changes, update
  its tables at the top of the file in the same commit, or the check reports the change as a
  finding. Hermes only runs scripts from `~/.hermes/scripts/`, so copy it there after a change.
  Nothing here is loaded by `functions.php`, and since AMM-173 none
  of it is deployed: to run a tool again, upload it (with its CSV) outside the theme folder and
  point `wp eval-file` at that copy.
