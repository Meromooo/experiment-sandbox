"""Build the Demas icon set from two rectangle-only masters.

FULL: the logo's "dm" mark, measured from Ammar's 1536px PNG (units = its px), 490 x 770.
SMALL: the tab-size simplification (shape from the ChatGPT reference), drawn on a 16 x 16
grid so every edge is a whole pixel at 16, 32 and 48.
"""
import json, os, sys
from PIL import Image, ImageDraw

OUT = sys.argv[1]  # usage: python tools/make-icons.py assets/images
BLUE, INDIGO, PAPER, WHITE = "#1B7EB2", "#232D8E", "#FAF9F6", "#FFFFFF"

FULL_W, FULL_H = 490, 770
FULL = [  # x, y, w, h, colour
    (182, 0, 55, 493, BLUE),     # stem (also the bowl's right side)
    (0, 276, 489, 50, BLUE),     # crossbar
    (0, 276, 55, 217, BLUE),     # bowl left
    (0, 445, 237, 48, BLUE),     # bowl bottom
    (308, 276, 51, 217, BLUE),   # m middle leg
    (437, 276, 52, 494, BLUE),   # m right leg and tail
    (55, 326, 127, 119, WHITE),  # the bowl's white ground, as in the logo
    (77, 346, 80, 80, INDIGO),   # the square in the bowl
]

SMALL = [  # 16 x 16 grid, mark 16 wide x 12 tall, rows 2-13
    (6, 2, 2, 10, BLUE),   # stem stub + bowl right side
    (0, 4, 16, 2, BLUE),   # crossbar
    (0, 4, 2, 8, BLUE),    # bowl left
    (0, 10, 8, 2, BLUE),   # bowl bottom
    (10, 4, 2, 8, BLUE),   # m middle leg, level with the bowl bottom
    (14, 4, 2, 10, BLUE),  # m right leg, one step lower (the tail)
    (2, 6, 4, 4, WHITE),   # the bowl's white ground: frames the square on a dark tab too
    (3, 7, 2, 2, INDIGO),  # the square, 1px clear on every side
]


def svg(rects, w, h, extra=""):
    body = "\n".join(
        f'  <rect x="{x}" y="{y}" width="{rw}" height="{rh}" fill="{c}"/>' for x, y, rw, rh, c in rects
    )
    return f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}"{extra}>\n{body}\n</svg>\n'


def draw_small(px):
    """Exact pixel render: the 16-grid scaled by a whole number, no resampling."""
    k = px // 16
    im = Image.new("RGBA", (px, px), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)
    for x, y, w, h, c in SMALL:
        d.rectangle([x * k, y * k, (x + w) * k - 1, (y + h) * k - 1], fill=c)
    return im


def draw_full(height):
    """Full mark at a given height, 8x supersampled then reduced."""
    s = 8
    k = height * s / FULL_H
    im = Image.new("RGBA", (round(FULL_W * k), height * s), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)
    for x, y, w, h, c in FULL:
        d.rectangle([x * k, y * k, (x + w) * k - 1, (y + h) * k - 1], fill=c)
    return im.resize((round(FULL_W * height / FULL_H), height), Image.LANCZOS)


def tile(size, mark_height):
    t = Image.new("RGBA", (size, size), PAPER)
    m = draw_full(mark_height)
    t.alpha_composite(m, ((size - m.width) // 2, (size - m.height) // 2))
    return t.convert("RGB")


os.makedirs(OUT, exist_ok=True)
open(os.path.join(OUT, "demas-mark.svg"), "w", newline="\n").write(svg(FULL, FULL_W, FULL_H))
open(os.path.join(OUT, "favicon.svg"), "w", newline="\n").write(svg(SMALL, 16, 16))

draw_small(48).save(
    os.path.join(OUT, "favicon.ico"),
    sizes=[(16, 16), (32, 32), (48, 48)],
    append_images=[draw_small(16), draw_small(32)],
)

tile(180, 144).save(os.path.join(OUT, "apple-touch-icon.png"), optimize=True)   # mark 80% tall
tile(192, 154).save(os.path.join(OUT, "icon-192.png"), optimize=True)
tile(512, 410).save(os.path.join(OUT, "icon-512.png"), optimize=True)
# Maskable: Android crops to a circle of radius 40% (205px). The tall mark's half-diagonal
# is 0.593 x its height, so a 330px-tall mark stays inside it.
tile(512, 330).save(os.path.join(OUT, "icon-maskable-512.png"), optimize=True)

manifest = {
    "name": "Demas Company Trading & Contracting",
    "short_name": "Demas",
    "start_url": "/",
    "scope": "/",
    "display": "browser",
    "background_color": PAPER,
    "theme_color": PAPER,
    "icons": [
        {"src": "icon-192.png", "sizes": "192x192", "type": "image/png"},
        {"src": "icon-512.png", "sizes": "512x512", "type": "image/png"},
        {"src": "icon-maskable-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable"},
    ],
}
open(os.path.join(OUT, "site.webmanifest"), "w", newline="\n").write(json.dumps(manifest, indent=2) + "\n")

for f in sorted(os.listdir(OUT)):
    print(f, os.path.getsize(os.path.join(OUT, f)), "B")
