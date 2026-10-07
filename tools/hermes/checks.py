#!/usr/bin/env python3
"""Scheduled checkups on the sandbox (AMM-183).

Plain scripts, no AI: each check visits public pages the way a visitor would and
reports what is wrong. Hermes Agent runs them on a schedule and only asks the
model to write a Linear issue when a check finds something, so the ChatGPT
allowance is spent on findings, not on quiet days.

    python3 checks.py smoke      # daily: key pages, markers, theme assets, finder
    python3 checks.py deploy     # every 15 min: the newest deploy is what the sandbox serves
    python3 checks.py taste      # weekly: the taste rules on the marketing pages
    python3 checks.py links      # weekly: every link on the marketing pages

Run by hand, each prints every check with ok / FAIL / WARN. With --hermes it
prints only the findings, then one last line Hermes reads: {"wakeAgent": false}
when there is nothing to report (a silent tick), {"wakeAgent": true} otherwise.
Every finding starts with a key in square brackets, the same for the same
problem on every run, so the agent can find the Linear issue it filed before
instead of filing a second one.

Exit status is 0 whenever the check ran, findings or not; anything else means
the script itself broke, which Hermes reports as an error.

Standard library only (Hermes runs cron scripts with its own Python 3.11).
Read-only: GET requests to public URLs, and `git ls-remote` for the deploy
branch. It never signs in, submits a form or writes anywhere except its own
state file (--state, used by `deploy`).

Not deployed: nothing in tools/ reaches the server (AMM-173). Hermes only runs
scripts from ~/.hermes/scripts/, so copy this file there after changing it.
"""

import argparse
import json
import os
import re
import subprocess
import sys
import time
import urllib.error
import urllib.parse
import urllib.request
from html.parser import HTMLParser

SANDBOX = "https://cornflowerblue-fish-235112.hostingersite.com"
REPO = "Meromooo/experiment-sandbox"
THEME_PATH = "/wp-content/themes/demas-theme/"
USER_AGENT = "demas-checks/1 (AMM-183; read-only)"
TIMEOUT = 25
DEPLOY_GRACE_MINUTES = 15

# The marketing pages the taste rules cover, as they stood after the audits in
# AMM-170, AMM-171 and AMM-169 step 3. `lead` is the hero subtext's class,
# `eyebrows` the number of .dh-eyebrow elements the audit accepted (the 404's
# three system-line headings carry the class too), `strips` the moving strips
# allowed (the homepage's second one is the certificates belt, AMM-178).
TASTE_PAGES = {
    "/": {"lead": "dh-hero__lead", "eyebrows": 1, "strips": 2},
    "/services/": {"lead": "dh-svc-lead", "eyebrows": 2, "strips": 1},
    "/contact-us/": {"lead": "dh-ct-lead", "eyebrows": 1, "strips": 1},
    "/no-such-page-demas-check/": {"lead": "dh-404__lede", "eyebrows": 4, "strips": 1, "status": 404},
}
LEAD_MAX_WORDS = 20
STRIP_CLASSES = ("wp-block-demas-theme-marquee", "wp-block-demas-theme-credentials")

# path, expected status, markers that must be in the HTML.
SMOKE_PAGES = [
    ("/", 200, ["dh-hero", "wp-block-demas-theme-credentials", "wp-block-demas-theme-marquee"]),
    ("/products/", 200, ["dh-grid--catalogue"]),
    ("/product-category/landscape/controllers/hunter-irrigation/", 200, ["dh-grid--catalogue"]),
    ("/services/", 200, ["dh-svc-hero"]),
    ("/contact-us/", 200, ["wp-block-demas-theme-branch-finder", "wp-block-demas-theme-request-form"]),
    ("/?s=hunter&post_type=product", 200, ["dh-grid--catalogue"]),
    ("/no-such-page-demas-check/", 404, ["dh-404"]),
]
PHP_ERRORS = ("There has been a critical error", "Fatal error:", "Parse error:", "Warning:</b>", "Notice:</b>")

# Files compared byte for byte between the deploy branch and the sandbox.
DEPLOY_FILES = ["style.css", "assets/css/style.css", "assets/js/main.js"]

BLOCK_TAGS = {
    "p", "h1", "h2", "h3", "h4", "h5", "h6", "li", "dt", "dd", "figcaption",
    "blockquote", "td", "th", "caption", "button", "label", "summary", "legend",
}
SKIP_TAGS = {"script", "style", "noscript", "template", "svg", "head"}
VOID_TAGS = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "source", "track", "wbr"}


class Report:
    def __init__(self, hermes):
        self.hermes = hermes
        self.findings = 0

    def ok(self, text):
        if not self.hermes:
            print(f"ok    {text}")

    def warn(self, text):
        if not self.hermes:
            print(f"WARN  {text}")

    def fail(self, key, text):
        self.findings += 1
        print(f"FAIL  [{key}] {text}" if not self.hermes else f"[{key}] {text}")

    def finish(self, name):
        if self.hermes:
            print(json.dumps({"wakeAgent": self.findings > 0}))
        else:
            print(f"\n{name}: {self.findings} finding(s)")


def fetch(url, method="GET"):
    """Return (status, final_url, headers, body_text). Status 0 means no response."""
    req = urllib.request.Request(url, method=method, headers={"User-Agent": USER_AGENT})
    try:
        with urllib.request.urlopen(req, timeout=TIMEOUT) as resp:
            body = resp.read() if method == "GET" else b""
            return resp.status, resp.geturl(), resp.headers, body.decode("utf-8", "replace")
    except urllib.error.HTTPError as err:
        body = err.read().decode("utf-8", "replace") if method == "GET" else ""
        return err.code, url, err.headers, body
    except (urllib.error.URLError, TimeoutError, OSError) as err:
        return 0, url, {}, str(getattr(err, "reason", err))


def fetch_bytes(url):
    req = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
    try:
        with urllib.request.urlopen(req, timeout=TIMEOUT) as resp:
            return resp.status, resp.read()
    except urllib.error.HTTPError as err:
        return err.code, b""
    except (urllib.error.URLError, TimeoutError, OSError):
        return 0, b""


def classes(attrs):
    return (dict(attrs).get("class") or "").split()


class PageReader(HTMLParser):
    """Visible text per block (split at <br>), labels, links and theme assets."""

    def __init__(self, leads=()):
        super().__init__(convert_charrefs=True)
        self.watch_leads = set(leads)
        self.stack = []          # open tags: (tag, skip, block)
        self.skip_depth = 0
        self.blocks = []         # finished lines of visible text
        self.current = [[]]      # text buffers, one per open block
        self.labels = []         # alt / aria-label / title values
        self.links = []
        self.assets = []
        self.class_counts = {}
        self.lead_text = {}      # class -> text of the first element with it
        self._lead_open = []     # [class, depth, parts]

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        for cls in classes(attrs):
            self.class_counts[cls] = self.class_counts.get(cls, 0) + 1
            if cls in self.watch_leads and cls not in self.lead_text and not any(l[0] == cls for l in self._lead_open):
                self._lead_open.append([cls, len(self.stack), []])
        if tag == "br":
            self._break()
        for name in ("alt", "aria-label", "title"):
            if a.get(name) and not self.skip_depth and tag != "svg":
                self.labels.append(a[name])
        if tag == "a" and a.get("href"):
            self.links.append(a["href"])
        src = a.get("src") if tag in ("script", "img") else a.get("href") if tag == "link" else None
        if src and (THEME_PATH in src or tag != "img"):
            rel = (a.get("rel") or "").lower()
            if tag != "link" or any(r in rel for r in ("stylesheet", "icon", "manifest", "preload", "modulepreload")):
                self.assets.append(src)
        if tag in VOID_TAGS:
            return
        skip = tag in SKIP_TAGS or "hidden" in a
        block = tag in BLOCK_TAGS
        if skip:
            self.skip_depth += 1
        if block:
            self.current.append([])
        self.stack.append((tag, skip, block))

    def handle_endtag(self, tag):
        if tag in VOID_TAGS or not any(t[0] == tag for t in self.stack):
            return
        while self.stack:
            open_tag, skip, block = self.stack.pop()
            if skip:
                self.skip_depth -= 1
            if block:
                self._flush(self.current.pop())
            for lead in list(self._lead_open):
                if lead[1] == len(self.stack):
                    self.lead_text[lead[0]] = " ".join("".join(lead[2]).split())
                    self._lead_open.remove(lead)
            if open_tag == tag:
                break

    def handle_data(self, data):
        if self.skip_depth:
            return
        self.current[-1].append(data)
        for lead in self._lead_open:
            lead[2].append(data)

    def _break(self):
        self._flush(self.current[-1])
        self.current[-1] = []

    def _flush(self, parts):
        text = " ".join("".join(parts).split())
        if text:
            self.blocks.append(text)

    def close(self):
        super().close()
        while len(self.current) > 1:
            self._flush(self.current.pop())
        self._flush(self.current[0])


def read_page(html, leads=()):
    reader = PageReader(leads)
    reader.feed(html)
    reader.close()
    return reader


# --- smoke --------------------------------------------------------------------

def check_smoke(rep):
    asset_urls = set()
    for path, want, markers in SMOKE_PAGES:
        url = SANDBOX + path
        status, _, _, html = fetch(url)
        key = f"smoke:{path}"
        if status != want:
            rep.fail(key, f"{path} answered {status or 'no response'}, expected {want}")
            continue
        missing = [m for m in markers if m not in html]
        errors = [e for e in PHP_ERRORS if e in html]
        if missing:
            rep.fail(key, f"{path} is missing {', '.join(missing)} (a block or template stopped rendering)")
        elif errors:
            rep.fail(key, f"{path} shows a PHP error: {errors[0]}")
        else:
            rep.ok(f"{path} {status}")
        for src in read_page(html).assets:
            full = urllib.parse.urljoin(url, src)
            if urllib.parse.urlparse(full).netloc == urllib.parse.urlparse(SANDBOX).netloc:
                asset_urls.add(full)

    # The finder's REST route, and one product page found through it.
    status, _, _, body = fetch(SANDBOX + "/wp-json/demas-theme/v1/find?q=hunter")
    parts = []
    if status == 200:
        try:
            parts = json.loads(body).get("parts") or []
        except ValueError:
            pass
    if not parts:
        rep.fail("smoke:finder", f"finder route answered {status or 'no response'} with no parts for 'hunter'")
    else:
        rep.ok(f"finder route {len(parts)} parts for 'hunter'")
        product = parts[0].get("url", "")
        status, _, _, html = fetch(product)
        if status != 200 or "wp-block-demas-theme-product-summary" not in html:
            rep.fail("smoke:product", f"product page {product} answered {status}, datasheet block {'missing' if status == 200 else 'not checked'}")
        else:
            rep.ok("product page datasheet")

    # No cart: /cart/ must still send buyers to the catalogue.
    status, final, _, _ = fetch(SANDBOX + "/cart/")
    if status != 200 or not final.rstrip("/").endswith("/products"):
        rep.fail("smoke:cart-redirect", f"/cart/ ended at {final} ({status}), expected /products/")
    else:
        rep.ok("/cart/ goes to /products/")

    failed = []
    for url in sorted(asset_urls):
        status, _ = fetch_bytes(url)
        if status != 200:
            failed.append(f"{url} ({status or 'no response'})")
    if failed:
        rep.fail("smoke:assets", f"{len(failed)} stylesheet/script/icon request(s) failed: " + "; ".join(failed))
    else:
        rep.ok(f"{len(asset_urls)} stylesheets, scripts and icons load")


# --- deploy -------------------------------------------------------------------

def deploy_head():
    out = subprocess.run(
        ["git", "ls-remote", f"https://github.com/{REPO}.git", "refs/heads/deploy"],
        capture_output=True, text=True, timeout=60, check=True,
    ).stdout.split()
    return out[0] if out else ""


def deploy_subject(sha):
    status, _, _, body = fetch(f"https://api.github.com/repos/{REPO}/commits/{sha}")
    try:
        return json.loads(body)["commit"]["message"].splitlines()[0] if status == 200 else sha[:7]
    except (ValueError, KeyError):
        return sha[:7]


def css_ver(html):
    m = re.search(r"themes/demas-theme/assets/css/style\.css\?ver=([\w.-]+)", html)
    return m.group(1) if m else None


def check_deploy(rep, state_path):
    sha = deploy_head()
    state = {}
    if os.path.exists(state_path):
        with open(state_path, encoding="utf-8") as fh:
            state = json.load(fh)
    if state.get("sha") != sha:
        state = {"sha": sha, "first_seen": time.time(), "done": False}
    waited = (time.time() - state["first_seen"]) / 60

    problems = []
    for path in DEPLOY_FILES:
        _, expected = fetch_bytes(f"https://raw.githubusercontent.com/{REPO}/{sha}/{path}")
        status, served = fetch_bytes(f"{SANDBOX}{THEME_PATH}{path}?deploy={sha[:7]}")
        if not expected:
            raise RuntimeError(f"could not read {path} at {sha[:7]} from GitHub")
        if status != 200:
            problems.append(f"{path} answered {status or 'no response'}")
        elif served != expected:
            problems.append(f"{path} differs from the deploy branch")

    # Search results are never cached, so they show the current build's
    # ?ver=; the cached homepage must link the same one (inc/cache.php purges
    # it on the first PHP request after a deploy, which this search is).
    _, _, _, search = fetch(SANDBOX + "/?s=hunter&post_type=product")
    _, _, _, home = fetch(SANDBOX + "/")
    fresh, cached = css_ver(search), css_ver(home)
    if fresh and cached and fresh != cached:
        problems.append(f"the cached homepage links style.css?ver={cached}, the current build is ver={fresh} (page cache not purged)")

    label = f"deploy {sha[:7]}"
    if not problems:
        state["done"] = True
        rep.ok(f"{label} is what the sandbox serves")
    elif waited < DEPLOY_GRACE_MINUTES:
        rep.warn(f"{label} not live yet after {waited:.0f} min (grace {DEPLOY_GRACE_MINUTES}): " + "; ".join(problems))
    elif state.get("done") or state.get("reported"):
        rep.warn(f"{label}: already reported: " + "; ".join(problems))
    else:
        state["reported"] = True
        rep.fail(f"deploy:{sha[:7]}", f"'{deploy_subject(sha)}' is not live {waited:.0f} min after it reached the deploy branch: " + "; ".join(problems))

    os.makedirs(os.path.dirname(state_path) or ".", exist_ok=True)
    with open(state_path, "w", encoding="utf-8") as fh:
        json.dump(state, fh)


# --- taste --------------------------------------------------------------------

def snippet(text, i, width=30):
    return "…" + text[max(0, i - width):i + width].replace("\n", " ") + "…"


def check_taste(rep):
    for path, rule in TASTE_PAGES.items():
        status, _, _, html = fetch(SANDBOX + path)
        if status != rule.get("status", 200):
            rep.fail(f"taste:{path}:status", f"{path} answered {status or 'no response'}; taste checks skipped")
            continue
        page = read_page(html, leads=[rule["lead"]])

        dashes = []
        for text in page.blocks + page.labels:
            for m in re.finditer("[–—]", text):
                dashes.append(snippet(text, m.start()))
        if dashes:
            rep.fail(f"taste:{path}:dash", f"{path} has {len(dashes)} em/en dash(es): " + " | ".join(dashes[:5]))
        else:
            rep.ok(f"{path} no em/en dashes")

        dots = [b for b in page.blocks if b.count("·") > 1]
        if dots:
            rep.fail(f"taste:{path}:middot", f"{path} has {len(dots)} line(s) with more than one middle dot: " + " | ".join(d[:80] for d in dots[:5]))
        else:
            rep.ok(f"{path} at most one middle dot per line")

        eyebrows = page.class_counts.get("dh-eyebrow", 0)
        if eyebrows > rule["eyebrows"]:
            rep.fail(f"taste:{path}:eyebrow", f"{path} has {eyebrows} eyebrows, {rule['eyebrows']} after its audit")
        else:
            rep.ok(f"{path} {eyebrows} eyebrow(s)")

        strips = sum(page.class_counts.get(c, 0) for c in STRIP_CLASSES)
        if strips > rule["strips"]:
            rep.fail(f"taste:{path}:strips", f"{path} has {strips} moving strips, {rule['strips']} allowed")
        else:
            rep.ok(f"{path} {strips} moving strip(s)")

        lead = page.lead_text.get(rule["lead"])
        words = len(lead.split()) if lead else 0
        if lead is None:
            rep.fail(f"taste:{path}:lead", f"{path} hero subtext (.{rule['lead']}) not found")
        elif words > LEAD_MAX_WORDS:
            rep.fail(f"taste:{path}:lead", f"{path} hero subtext is {words} words (max {LEAD_MAX_WORDS}): {lead}")
        else:
            rep.ok(f"{path} hero subtext {words} words")


# --- links --------------------------------------------------------------------

def check_links(rep):
    host = urllib.parse.urlparse(SANDBOX).netloc
    found = {}
    for path, rule in TASTE_PAGES.items():
        status, _, _, html = fetch(SANDBOX + path)
        if status != rule.get("status", 200):
            rep.fail(f"links:{path}", f"{path} answered {status or 'no response'}; its links not checked")
            continue
        for href in read_page(html).links:
            if href.startswith(("#", "mailto:", "tel:", "javascript:")):
                continue
            url = urllib.parse.urldefrag(urllib.parse.urljoin(SANDBOX + path, href))[0]
            found.setdefault(url, path)

    broken, unverified = [], []
    for url, page in sorted(found.items()):
        internal = urllib.parse.urlparse(url).netloc == host
        status, _, _, _ = fetch(url)
        if status == 200 or (not internal and 200 <= status < 400):
            continue
        if not internal and status in (0, 401, 403, 405, 429, 999):
            # Bot walls and rate limits, not a broken link.
            unverified.append(f"{url} ({status or 'no response'})")
        else:
            broken.append(f"{url} ({status or 'no response'}, on {page})")
        time.sleep(0.3)

    for item in unverified:
        rep.warn(f"could not verify {item}")
    if broken:
        rep.fail("links:broken", f"{len(broken)} broken link(s): " + "; ".join(broken))
    else:
        rep.ok(f"{len(found) - len(unverified)} of {len(found)} links answer")


def main():
    parser = argparse.ArgumentParser(description=__doc__.split("\n\n")[0])
    parser.add_argument("check", choices=["smoke", "deploy", "taste", "links"])
    parser.add_argument("--hermes", action="store_true", help="print findings only, then the wakeAgent line")
    parser.add_argument("--state", default=os.path.expanduser("~/.local/state/demas-checks/deploy.json"),
                        help="where `deploy` remembers the last deploy it saw")
    args = parser.parse_args()
    sys.stdout.reconfigure(encoding="utf-8")

    rep = Report(args.hermes)
    if args.check == "smoke":
        check_smoke(rep)
    elif args.check == "deploy":
        check_deploy(rep, args.state)
    elif args.check == "taste":
        check_taste(rep)
    else:
        check_links(rep)
    rep.finish(args.check)


if __name__ == "__main__":
    sys.exit(main())
