"""Write the one-colour (paper) Demas logo for dark surfaces, such as the header's canopy band.

Reads the traced logo (demas-logo.svg: the Arabic and English lines as paths, the "dm" mark as
the rectangles of make-icons.py's FULL master) and turns every fill paper. The bowl's white
ground is dropped, so the band shows through and the square reads as paper: the standard
one-colour reversal. Re-run on the designer's file when it arrives.

usage: python tools/make-logo-white.py <path to demas-logo.svg> assets/images
"""
import os, re, sys

SRC, OUT = sys.argv[1], sys.argv[2]
PAPER = "#FAF9F6"  # theme.json palette "paper", the band's own type colour

svg = open(SRC, encoding="utf-8").read()
svg, grounds = re.subn(r'\s*<rect [^>]*fill="#FFFFFF"/>', "", svg)
svg = re.sub(r'fill="#[0-9A-Fa-f]{6}"', f'fill="{PAPER}"', svg)

assert grounds == 1, f"expected one white bowl ground, found {grounds}"
assert set(re.findall(r'fill="([^"]*)"', svg)) == {PAPER}, "a fill other than paper is left"

with open(os.path.join(OUT, "demas-logo-white.svg"), "w", encoding="utf-8", newline="\n") as f:
    f.write(svg)
