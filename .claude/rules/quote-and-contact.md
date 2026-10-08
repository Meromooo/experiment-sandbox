---
paths:
  - "inc/{quote,contact,branches}.php"
  - "src/{quote-button,quote-drawer,branch-finder,request-form}/**"
  - "patterns/contact.php"
---

# Quote list, branches, the Contact page and the request form

Moved from CLAUDE.md (AMM-190); kept as it was written. Read this before planning or changing anything in its area.

Staff email addresses never appear in the repo, markup or JavaScript; the request form will post a branch code, not an address.

## `inc/quote.php`

  - `quote.php` — registers the quote list's two blocks (AMM-139, 2026-09-24):
    `demas-theme/quote-button` ("Add to quote" — full on the product page, compact on catalogue
    cards) and `demas-theme/quote-drawer` (the header's "Your quote" control and the list, a
    native modal `<dialog>`). They share one Interactivity API store, `demas-theme/quote`,
    defined in `src/quote-drawer/view.ts` — so the drawer must stay in `parts/header.html` or
    every quote button stops working. The list lives in the buyer's `localStorage` as a snapshot
    per part; nothing is sent anywhere until AMM-140. **Print / Copy list / Share** (AMM-163,
    2026-09-28): the dialog foot prints the list as a parts sheet (built as DOM on `<body>` only
    while printing, under `html.dh-print-quote`, so it prints from any page; Ctrl+P while the
    list is open does the same), and copies it as plain text — or opens the phone's share sheet
    where `navigator.share` exists. The sheet's contact line is filterable
    (`demas_theme_quote_sheet_contact`); it carries the company phone and the Branch Desk, never
    a staff email.

## `inc/branches.php`, `inc/contact.php`

  - `branches.php` — the 15 branches as one list, keyed by the Branch Desk's three-letter
    codes (`demas_theme_get_branches()`: city, lat/lon, main) and `demas_theme_branch_url()`
    (`/contact-us/#branch-jed` since AMM-169: that branch chosen on the Contact page). AMM-140 will route by these
    codes. No email address belongs here. Each branch also carries its `person` (who answers
    there), shown on the homepage Branch Desk (AMM-153) — names only, never an address. Since
    AMM-169 each also has `map` (its Google Maps link, from the live Contact Us page), `address`
    and `hours`, read from those public listings on 2026-10-01 for Demas to confirm; the
    listings' phone numbers are deliberately not copied. Hours are written as people read them
    (`Sat-Thu 08:00-12:00 16:00-20:00; Fri closed`; empty = not listed) and parsed by
    `demas_theme_branch_hours()` into display lines and a week of minutes.
    `demas_theme_branch_plan_point()` is the one lat/lon → plan projection, used by the
    footer's key plan and the Contact page's map.
  - `contact.php` — the Contact page (AMM-169): registers `demas-theme/branch-finder` (the
    branches as a list of in-page links, the chosen branch's card — photo, who answers there,
    address, hours with an "Open now" status worked out in Riyadh time in the browser,
    directions — and, since AMM-189, a **store locator map with service areas**: the
    footer key plan's real land drawn on the dark green, divided into one area per branch,
    each the part of the Kingdom nearer to that branch than to any other (a Voronoi diagram,
    computed in `render.php` from the branch list, so a new branch redraws them); the chosen
    branch's area filled field green, its dot ringed and named, and pointing at an area, a
    dot or a city lights that area). Works without JavaScript: every city, area and dot
    links to its card (`#branch-jed`) and CSS shows the `:target` one; the
    view module (`src/branch-finder/view.ts`) swaps cards in place and announces choices as a
    `demas-theme:branch` event for the request form. A photo per branch is the block's only
    setting (its sidebar; the drawn placeholder until then), so the editor gets the branch
    list as `window.demasThemeBranches`. The block's wrapper is `display: contents`; the
    page's hero grid (`style.css` section 15) places its list and its stage (the map, the
    card under it, on its side where the stage is 36rem or wider); above 56rem the list
    stays in view (sticky) while the map and card scroll past.
    Also registers `demas-theme/request-form` (AMM-169 step 2): what the buyer needs (pill
    radios; `?need=parts|survey|repair|other` preselects), the branch (`?branch=`; kept in step
    with the finder both ways through the `demas-theme:branch` event), name, phone, email,
    company, message, and the buyer's quote list (read from the same `localStorage` key as the
    quote drawer) with an "Include" box. **Front end only:** its view module checks the fields
    (messages on the field plus a summary that takes focus) and, on a complete form, says
    sending isn't connected yet and offers head office's number and the branch's directions —
    nothing typed leaves the browser. Without JavaScript Send stays disabled with a note.
    AMM-140 adds the handler (branch code → address server-side, nonce, rate limit, honeypot),
    after the privacy notice (AMM-162). Errors use `theme.json` `custom.alert` (#A3361F, 5:1
    on sand), always with words too.

## The Contact page template, and every contact link

  the eyebrow). The
  Contact Us page uses it with the `contact` pattern (`patterns/contact.php`, AMM-169: the
  intro and the Branch Finder as the hero, then the request section; `dh-ct-` classes), its
  hero opening the same way at 56rem and up. Version 0.5.0 for that pattern file. It passed the
  taste skill's audit in step 3 (2026-10-03): one eyebrow (the hero's), an 18-word subtext, a
  headline sized to its column (`cqi`) so it takes two lines from 1024px up and the buttons show
  on a 320x568 phone, hyphens not en dashes in the hours, one label per intent ("Get
  directions"). **Every contact link goes through `demas_theme_contact_url( $branch, $need )`
  (`inc/contact.php`)** — the header, the footer's branch links, the homepage Branch Desk's
  "Message the X branch" (`?branch=`), the closing CTA and "Request a site visit" buttons
  (`?need=survey`), the 404, an empty search and the header finder (`?need=parts`), the quote
  sheet's contact line. Content already saved in pages keeps its links until edited: the
  homepage hero (page 17) and Services (page 822) buttons were re-pointed in the editor. There is no `single.html`: the site has
  no blog posts, and `index.html` covers the fallback.
