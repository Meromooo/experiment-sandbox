---
name: favicon-cheat-sheet
description: Favicon and site-icon rules for the Demas theme, distilled from audreyfeldroy/favicon-cheat-sheet (MIT, its 2024+ "modern minimum") plus what the sandbox actually serves. Use whenever work touches favicons, the WordPress Site Icon, apple-touch-icon, the web app manifest, theme-color, browser tab / bookmark / home-screen icons, the logo or brand mark, icon links in <head>, a /favicon.ico or manifest 404 in a network trace, or the cutover (AMM-158) checklist.
---

# Favicons for the Demas theme

Source: [favicon-cheat-sheet](https://github.com/audreyfeldroy/favicon-cheat-sheet) (MIT, last updated 2026-01). This file is the short version for this project; read the source for legacy browsers (IE tiles, Safari 9-14 mask icons), which we do not target.

## The modern minimum (what "done" means)

```html
<link rel="icon" href="/favicon.ico" sizes="32x32">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png"><!-- 180x180 -->
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#FAF9F6">
```

| File | Size | Rules |
|---|---|---|
| `favicon.ico` | 16, 32, 48 inside one file | Browsers probe `/favicon.ico` at the domain root even without a link; a missing one is a 404 on every first visit. |
| `favicon.svg` | vector | Simple shapes only (no gradients or filters); must read at 16px. May carry a `prefers-color-scheme: dark` style so the mark stays visible on a dark tab strip. That is the browser's chrome, not the page, so the theme's light-only rule doesn't forbid it; ask Ammar before adding it. |
| `apple-touch-icon.png` | 180×180 | **Opaque** background (iOS fills transparency with black), mark inset ~10% from the edge. |
| manifest icons | 192×192 and 512×512 PNG | Plus one 512 with `"purpose": "maskable"` whose mark sits inside the central 80% safe zone (Android crops it to a circle or squircle). |
| `theme-color` | — | From `theme.json`, never a new hex: `paper` #FAF9F6 (or `canopy` #1E4D3A for a dark toolbar). The manifest's `background_color` uses the same token. |

When an icon changes, bust caches with a version query (`favicon.ico?v=2`); browsers hold favicons for a long time.

## What the theme serves (since 2026-10-05)

- `inc/site-icons.php` prints the full modern minimum from `assets/images/` and replaces WordPress's Site Icon tags on the front end. The Site Icon setting (`demas-site-icon`, the old 300px PNG) still serves the admin and login screens, and is what live prints until cutover.
- **Two masters, one script.** `tools/make-icons.py` holds the full "dm" mark (measured from the logo PNG; no designer's vector file exists) and a 16px-grid simplification for tab sizes, both as rectangles, and writes every file. Edit the masters there and re-run; never hand-edit a PNG.
- Still missing: a file at the domain root `/favicon.ico` (server work for Ammar). Browsers that read the link tags don't need it.

## Where things live in this project

- **Theme-owned files** go in `assets/images/` (reserved for logo and icons in CLAUDE.md). `assets/` is in the deploy `RUNTIME` list, so they reach the server. A new top-level file or folder would have to join `RUNTIME` (AMM-173).
- **Head tags**: a new concern gets its own file in `inc/` (working agreement). WordPress prints the Site Icon tags through the `site_icon_meta_tags` filter; replace or extend them there instead of printing a second set in `wp_head`.
- **The domain root** (`/favicon.ico`, `/apple-touch-icon.png`) is outside the theme folder. Putting files there is server work, which Ammar runs. Until then, the explicit `<link rel="icon">` tags point at the theme copies.
- **The Site Icon setting is database content.** Ammar changes it in the admin, and separately on live at cutover. **Nothing here may change the live site** before AMM-158; add the icon set to the cutover checklist instead.

## Verify (Chrome DevTools, per the global rule)

1. `list_network_requests` on home, a category and a product page: every icon and the manifest return 200, no 404.
2. `evaluate_script`: list `link[rel~="icon"]`, `link[rel="apple-touch-icon"]` (load it, check `naturalWidth === 180`), `meta[name="theme-color"]`; `fetch()` the manifest and confirm it parses with 192, 512 and maskable icons.
3. `lighthouse_audit`: no manifest or icon warnings.
4. Look at the tab icon in a real browser at 16px on a light and a dark tab strip (screenshot for Ammar).
