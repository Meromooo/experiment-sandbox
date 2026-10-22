---
paths:
  - "inc/{quote,contact,branches,request,request-email}.php"
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
    native modal `<dialog>`; since AMM-193 it opens and closes as one CSS transition with
    `@starting-style` and discrete `display`/`overlay`, grown from its top corner, or slid up
    as a sheet below 40em, faded only with reduced motion). They share one Interactivity API store, `demas-theme/quote`,
    defined in `src/quote-drawer/view.ts` — so the drawer must stay in `parts/header.html` or
    every quote button stops working. The list lives in the buyer's `localStorage` as a snapshot
    per part; nothing is sent anywhere until AMM-140. **Print / Copy list / Share** (AMM-163,
    2026-09-28): the dialog foot prints the list as a parts sheet (built as DOM on `<body>` only
    while printing, under `html.dh-print-quote`, so it prints from any page; Ctrl+P while the
    list is open does the same), and copies it as plain text — or opens the phone's share sheet
    where `navigator.share` exists. The sheet's contact line is filterable
    (`demas_theme_quote_sheet_contact`); it carries the company phone and the Branch Desk, never
    a staff email.
    **Request prices (AMM-209, 2026-10-18):** the foot's main action is a solid pill link
    (`.dh-pill .dh-pill--solid .dh-quote-dialog__request`, markup in `render.php`) to
    `demas_theme_contact_url( '', 'parts' )`, so the request form opens with Parts & prices
    chosen and the list shown. On the Contact page itself (`is_page( 'contact-us' )`) the
    href is `#request`, a same-page jump, so a form already half typed is not lost to a
    reload. It runs `actions.close` as it is followed. The foot is a two-column grid: the
    note and the button, then the actions row (Print, Copy list, Clear all at the inline
    end); under 40em one column, the button full width, and on a short phone (also
    `max-height: 40em`) the note is hidden so the list keeps its room. Print, Copy list and
    Clear all are 44px tall. The heading count is plain ("50 parts"); only the line numbers
    and the header badge are zero padded. No branch picker in the list, and
    no sending (AMM-140).

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
    footer key plan's real land drawn on the dark indigo, divided into one area per branch,
    each the part of the Kingdom nearer to that branch than to any other (a Voronoi diagram,
    computed in `render.php` from the branch list, so a new branch redraws them); the chosen
    branch's area filled sky blue, its dot ringed and named, and pointing at an area, a
    dot or a city lights that area). Works without JavaScript: every city, area and dot
    links to its card (`#branch-jed`) and CSS shows the `:target` one; the
    view module (`src/branch-finder/view.ts`) swaps cards in place and announces choices as a
    `demas-theme:branch` event for the request form. A photo per branch is the block's only
    setting (its sidebar; the drawn placeholder until then), so the editor gets the branch
    list as `window.demasThemeBranches`. The block's wrapper is `display: contents`; the
    page's hero grid (`style.css` section 15) places its list and its stage (the map, the
    card under it, on its side where the card has 36rem or more); above 56rem the list
    stays in view (sticky) while the map and card scroll past.
    Also registers `demas-theme/request-form` (AMM-169 step 2): what the buyer needs (pill
    radios; `?need=parts|survey|repair|other` preselects), the branch (`?branch=`; kept in step
    with the finder both ways through the `demas-theme:branch` event), name, phone, email,
    company, message, and the buyer's quote list (read from the same `localStorage` key as the
    quote drawer) with an "Include" box. **Sending (AMM-140 stage 3, 2026-10-22):** its view module checks the fields
    (messages on the field plus a summary that takes focus), then posts the form as JSON to
    `inc/request.php` (the endpoint is the form's `data-endpoint`; items go as `{ id, qty }`
    only when "Include my quote list" is ticked). Send reads "Sending…" and ignores presses
    until the answer comes. One panel shows the result and takes focus: **sent** (200: "Sent
    to the X branch" and the reference; the typed fields are cleared, the quote list kept),
    **not connected** (`code: "not_connected"`, matched on the code, not on 503, which a
    server error page could also give), or **wasn't sent** (anything else, offline included);
    the last two keep everything typed and offer head office's number and the branch's
    directions. Replies are shown with `textContent` only. The `maxlength` on name, phone,
    email, company and message match the handler's limits. No honeypot input and no timeout
    (ponytail: add them if spam or a hung request is ever seen). Without JavaScript Send stays disabled with a note.
    **The short privacy notice (AMM-162, 2026-10-22)** sits under Send (`.dh-ct-form__privacy`):
    who gets the details and what for, that the site is hosted in the United States and the
    request goes by email through Microsoft 365 in Saudi Arabia, then "Read our privacy
    notice." linked to `get_privacy_policy_url()`, the page chosen in Settings → Privacy
    (database content, Ammar's). With no page chosen the sentence shows without the link. The
    full notice is Linear's "Privacy notice: draft v0.3" (English and Arabic), awaiting
    Demas's legal review; privacy requests come in through "Something else". Change the
    short text and the drafts together. No consent box: sending the
    form after reading the notice is the consent.
    **The message is optional when the list carries the request (AMM-209):** with Parts &
    prices chosen, "Include my quote list" ticked and parts in the list, `view.ts`'s
    `syncMessage()` takes `required` off the message, tags it "Optional" and changes the hint
    to "Anything to add, such as delivery or the project."; `problem( 'message' )` then
    passes an empty message. It re-runs on a need change, the Include box and every redraw
    of the list (storage event, dialog close). Any other combination is as before.
    The handler is `inc/request.php` (AMM-140 stage 2, below). Wherever the Microsoft 365
    settings are missing it answers "not connected" before it counts or stores anything, so
    nothing typed is kept. Since stage 4 (2026-10-22) the sandbox sends, every request to the
    test address. Errors use `theme.json` `custom.alert` (#A3361F, 5:1
    on sand), always with words too.

## `inc/request-email.php` — the request email and its preview (AMM-140 stage 1, 2026-10-21)

  - `demas_theme_request_email( $request )` builds what a branch receives: one email, no
    attachment (Ammar, 2026-10-18). Subject `Price request: <n> parts, <City> branch (ref.
    <nnnn>)`, or `<Need>: <City> branch (ref. <nnnn>)` when no parts are attached. Body in one
    column: an indigo band (need, branch, ref), the buyer's details as label and value rows
    with sand hairlines (phone as a `tel:` link, email as a `mailto:` link only when valid), a
    line saying how to answer (reply, or call when no email was given), the parts table (line,
    part linked to its page, part number, quantity), and a footer line in Riyadh time.
    Tables and inline styles only, so Gmail and Outlook show it the same; the colours are the
    `theme.json` palette written out. Every value is escaped; the buyer's text and part names
    carry `dir="auto"`. It takes parts as `{ name, sku, url, qty }` read from the store by the
    handler, never from the browser. It sends nothing.
  - `demas_theme_request_needs()` names the four needs as the form's choices do; the form's
    `render.php` still has its own copy with the hints, so change both together.
  - **Tools → Request email** (`manage_options` only) previews three made-up requests built
    from real published parts (three parts; a site visit with no parts or email; a 40-line
    worst case with the longest names and long Arabic and English text), each at desktop and
    phone width in sandboxed `srcdoc` frames. The sample buyer's email is the logged-in
    administrator's own, read at runtime, because CI fails on any address in the repo. It also
    lists the settings the handler will read from `wp-config.php` (`DEMAS_GRAPH_TENANT`,
    `DEMAS_GRAPH_CLIENT_ID`, `DEMAS_GRAPH_SECRET`, `DEMAS_GRAPH_SENDER`,
    `DEMAS_BRANCH_MAILBOXES`, `DEMAS_REQUEST_TEST_TO`) as set or not, never their values, and
    the visitor address headers the server receives, so the rate limit (stage 2) knows whether
    the CDN hides visitors' addresses.

## `inc/request.php` — the request form's handler (AMM-140 stage 2, 2026-10-21)

  - `POST /wp-json/demas-theme/v1/request`, JSON `{ need, branch, name, phone, email, company,
    message, items: [ { id, qty } ], hp }`, public. In order: a filled honeypot (`hp`) gets a
    fake 200; every field is checked on the server (400 `{ code: "invalid", fields: [...] }`,
    the field names as the form uses them); without the Microsoft 365 settings, 503
    `{ code: "not_connected" }`; over 5 requests an hour from one visitor or 30 an hour
    site-wide, 429 `{ code: "busy" }` (only valid requests count); then the next ref, the
    email from `inc/request-email.php`, and Microsoft Graph `sendMail`: 200 `{ ref, branch }`
    (`ref` zero padded, as in the email), or 502 `{ code: "not_sent" }`.
  - **Checks:** need one of the four, branch one of the 15 codes, name 1–100 characters,
    phone the form's own rule (`+ ( ) -`, spaces, 9–15 digits), email optional but valid,
    company up to 150, message up to 4000 and required unless the need is Parts & prices and
    a published part comes through (AMM-209). Items: up to 200, quantity 1–9999, each part
    re-read from the store (`demas_theme_request_line()`, shared with the preview); an
    unpublished or missing part is dropped. Typed text is cleaned by
    `demas_theme_request_text()`, not `sanitize_text_field()` (which turns "< 50 mm" into an
    entity); the email escapes everything.
  - **No nonce** (Ammar, 2026-10-21): pages are cached and shared, so a nonce in the HTML
    would be stale, and the form needs no login, so a forged post can only do what any
    visitor can, within the rate limit.
  - **Rate limit:** transients for an hour from the last counted request, keyed by
    `wp_hash()` of `REMOTE_ADDR` (never the address itself). `REMOTE_ADDR` is the visitor's
    real address on this host, behind the CDN too (measured 2026-10-21), so no forwarded
    header is read. Stage 5 (2026-10-22) checked that forged `X-Forwarded-For`,
    `X-Real-IP`, `CF-Connecting-IP`, `True-Client-IP` and `X-Client-IP` don't change it: from
    one fixed address, each request claiming a different one, the fifth counted request got
    429. Someone with many real addresses still gets 5 each (a cloud session's outgoing
    address changes on every request, so it can't run this check); the site-wide 30 an hour
    is the backstop for that.
  - **Stage 5 end to end (2026-10-22):** a bad field gets 400 and sends nothing; a filled
    honeypot gets a fake 200 and sends nothing; four needs, 40 parts, Arabic and English text,
    no buyer email (the email says to call), a missing part id (dropped), and a phone send
    over mobile data all arrived in the inbox and read right, Reply going to the buyer.
  - **Sending:** a client-credentials token, used once and never stored, then `sendMail` as
    `DEMAS_GRAPH_SENDER`, kept in its Sent Items (the only record; WordPress stores nothing
    about a request but the `demas_theme_request_ref` counter). Recipient:
    `DEMAS_REQUEST_TEST_TO` when set (the sandbox), else the branch's mailbox in
    `DEMAS_BRANCH_MAILBOXES`, else its `default` (head office); one address each (a branch
    with several people uses a Microsoft 365 group). Reply-To only for a valid buyer email.
    Failures go to the PHP error log with the HTTP status only.
  - **Microsoft 365 setup (AMM-140 stage 4, 2026-10-22, Ammar in the Demas tenant).** The
    sender is a shared mailbox named `website` on the company domain (no licence, no
    password). The Entra app "Demas website mail" has **no** Entra API permissions: an Entra
    `Mail.Send` grant would let it send as anyone and would override the limit below. The limit
    is Exchange RBAC for Applications (Microsoft's replacement for application access
    policies): `Enable-OrganizationCustomization` (needed once, then the role assignment took
    a few hours to be allowed), the management scope "Demas website mailbox" (a recipient
    filter on that one mailbox) and the role "Application Mail.Send" assigned to the app on
    that scope. `Test-ServicePrincipalAuthorization` shows `InScope True` for the website
    mailbox and `False` for any other. Client secret "sandbox", 24 months (expires 2028-10-22;
    renew a month before, Linear AMM-140); the live site gets its own "live" secret. The
    sandbox's `wp-config.php` holds the "sandbox" secret, `DEMAS_REQUEST_TEST_TO` (Ammar's address) and an empty
    `DEMAS_BRANCH_MAILBOXES`; at cutover the live site gets the "live" secret, the branch
    list from Demas, and no test address. The first real send arrived in the inbox, not junk:
    mail from the tenant's own mailbox needs no DNS change. Rejected as more work or less safe
    (2026-10-22): SMTP AUTH (off by default from end of 2026), Direct Send and relay
    connectors (port 25 or a fixed IP, plus DNS), outside mail services (DNS records on the
    old site's domain), and `wp_mail()` from Hostinger (spam, AMM-148).

## The Contact page template, and every contact link

  The Contact Us page uses the Designed page template (`templates/designed-page.html`) with the `contact` pattern (`patterns/contact.php`, AMM-169: the
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
  homepage hero (page 17) and Services (page 822) buttons were re-pointed in the editor.

  **The request section (AMM-191, 2026-10-08)** is one canopy frame round the copy and the
  form (`.dh-ct-request__grid`, `style.css` section 15), the form on its sand sheet inside it.
  The copy is in paper; **only the copy** takes the paper colour, because the form's unchosen
  choice labels inherit their colour and went paper on paper when the frame set it. "need" in
  its heading carries the Highlight format, drawn on canopy as sky blue with ink on it (the
  ink comes in with the sweep). Above 56rem the copy is sticky beside the form. "What happens
  next" (`.dh-ct-next`) is drawn as a route: CSS-counter numbers in paper tabs one text line
  tall, one track joining them, and sky blue drawn down it as the list scrolls up (the
  list's own `view-timeline`; ranges end by 40% so the sticky copy can't freeze it half
  drawn); finished with reduced motion. CSS only on the saved markup, apart from the
  highlight span (in the pattern, and added in the editor on the sandbox). **Since AMM-192
  the route's rules are shared** with the Services visit (`:is(.dh-ct-next, .dh-svc-route) >
  li`, timeline `--dh-route`, keyframes `dh-route-light` / `dh-route-draw`): only the list's
  own items are steps, and the colours are variables on each list (`--_tab`, `--_tab-ink`,
  `--_tab-off`, `--_tab-edge`, `--_track`). Since AMM-195 both lists sit on canopy
  (Contact's request frame, Services' folder board), so both have paper tabs; Services' steps
  are blocks with the tab in their margin (a name in Bold and one line).
