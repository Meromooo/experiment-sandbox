"""Write the one-colour (paper) Demas logo for dark surfaces, such as the header's canopy band.

Reads the traced logo (demas-logo.svg: the Arabic and English lines as paths, the "dm" mark as
the rectangles of make-icons.py's FULL master) and turns every fill paper. The bowl's white
ground is dropped, so the band shows through and the square reads as paper: the standard
one-colour reversal. Re-run on the designer's file when it arrives.

The traced logo is stacked (the mark top right, the two lines across the bottom 30%), which
leaves most of a wide, short header slot empty. This writes it side by side instead (Ammar,
2026-10-31): the mark on the left at full height, the two lines beside it at half the mark's
height, centred on it. Same shapes, moved and scaled, nothing redrawn.

usage: python tools/make-logo-white.py <path to demas-logo.svg> assets/images
"""
import os, re, sys

SRC, OUT = sys.argv[1], sys.argv[2]
PAPER = "#FAF9F6"  # theme.json palette "paper", the band's own type colour

# Measured on the traced file: the mark's rectangles span 0-489 x 0-770 inside their group;
# the two lines together span x 1-1463, y 546-769 of the 1536 x 772 drawing.
MARK_W, MARK_H = 489, 770
TEXT_X, TEXT_Y, TEXT_W, TEXT_H = 1, 546, 1462, 223
SCALE = MARK_H / 2 / TEXT_H        # the two lines are half the mark's height
GAP = 120                          # about a quarter of the mark's width
TX, TY = MARK_W + GAP, (MARK_H - TEXT_H * SCALE) / 2
WIDTH = round(TX + TEXT_W * SCALE)

svg = open(SRC, encoding="utf-8").read()
svg, grounds = re.subn(r'\s*<rect [^>]*fill="#FFFFFF"/>', "", svg)
svg = re.sub(r'fill="#[0-9A-Fa-f]{6}"', f'fill="{PAPER}"', svg)

svg, n_box = re.subn(r'viewBox="0 0 1536 772"', f'viewBox="0 0 {WIDTH} {MARK_H}"', svg)
svg, n_mark = re.subn(r'<g transform="translate\(1045 0\)">', "<g>", svg)
svg, n_text = re.subn(
    r'(<g fill="[^"]*" transform=")(translate\(0 772\) scale\(0\.025 -0\.025\))"',
    lambda m: m.group(1)
    + f"translate({TX - TEXT_X * SCALE:.2f} {TY - TEXT_Y * SCALE:.2f}) scale({SCALE:.4f}) "
    + m.group(2) + '"',
    svg,
)

assert grounds == 1, f"expected one white bowl ground, found {grounds}"
assert (n_box, n_mark, n_text) == (1, 1, 1), "the traced file's layout changed: re-measure"
assert set(re.findall(r'fill="([^"]*)"', svg)) == {PAPER}, "a fill other than paper is left"

with open(os.path.join(OUT, "demas-logo-white-wide.svg"), "w", encoding="utf-8", newline="\n") as f:
    f.write(svg)
print(f"demas-logo-white-wide.svg: {WIDTH} x {MARK_H}")
