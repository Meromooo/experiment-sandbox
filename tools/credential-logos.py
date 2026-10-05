"""
The homepage credentials belt (AMM-178): turns certificate and partner logos,
as their owners publish them, into files that sit together on the belt.

    python tools/credential-logos.py OUT_DIR LOGO [LOGO ...]

The originals are made to no common standard: each has its own empty margin,
some sit on a white box and some are transparent, one (SOCOTEC's ISO 45001)
has a frame drawn round the whole file, and they range from round badges
(ISOQAR, SASO) to wide lock-ups (SIRI, WRAS + UKAS). Shown at one height, the
wide ones look huge and the round ones tiny. For each logo this:

 - finds its background (the commonest colour in the file) and makes it
   transparent, keeping anti-aliased edges soft;
 - drops any frame drawn round the file and trims to the mark itself;
 - sizes the mark for equal visual weight: every mark covers about the same
   area, a sparse one (thin lines, light fills: SOCOTEC, NWC) a little more,
   as it reads lighter than a solid one at the same size; capped at the
   belt's logo box (168 x 68 CSS px);
 - centres it on one transparent canvas, the same for every logo: the logo
   box at 2x (336 x 136). That is sharp on high-density screens; phones show
   the box smaller, so 2x covers their 3x screens too.

Writes OUT_DIR/<name>-belt.webp per logo, which are uploaded to the Media
Library and chosen as each credential's logo. The theme draws them in canopy
green at rest (the file as a CSS mask) and in their own colours on hover or
focus. Because every file has the same canvas, they all show at one size.

Needs Python 3 with Pillow (WebP support) and NumPy. Not deployed (AMM-173).
"""

import math
import sys
from pathlib import Path

import numpy as np
from PIL import Image

SCALE = 2                       # canvas pixels per CSS pixel: sharp at 2x, and phones show the box smaller
BOX_W, BOX_H = 168, 68          # the belt's logo box, CSS px (style.css .dh-cred__mark)
AREA = 5600                     # the area every mark covers, CSS px^2
DENSITY_REF = 0.32              # ink coverage of a typical mark
DENSITY_POWER = 0.3             # how much more room a sparse mark gets
THRESHOLD = 0.35                # how much ink counts as part of the mark


def background(rgb):
    """The file's commonest colour, to within a 16-level bucket."""
    buckets = (rgb // 16).reshape(-1, 3).astype(int)
    keys = buckets[:, 0] * 256 + buckets[:, 1] * 16 + buckets[:, 2]
    values, counts = np.unique(keys, return_counts=True)
    top = values[counts.argmax()]
    mode = np.array([top // 256, (top // 16) % 16, top % 16])
    return np.median(rgb.reshape(-1, 3)[(buckets == mode).all(axis=1)], axis=0)


def frame_depth(coverage, skip_blank):
    """How far in from one edge a frame reaches: past lines that are mostly
    ink, plus their anti-aliasing; 0 when the edge line is not a frame line.
    skip_blank first steps over blank lines (a stray JPEG line outside a box
    drawn round the mark). Not at the file's edge: there a frame touches it,
    and the first inked line after a blank margin is part of the mark."""
    i = 0
    while skip_blank and i < len(coverage) and coverage[i] < 0.02:
        i += 1
    if i == len(coverage) or coverage[i] <= 0.5:
        return 0
    while i < len(coverage) and coverage[i] > 0.5:
        i += 1
    return i + 6


def inside_frame(ink, skip_blank=False):
    """Bounds inside a frame, on whichever edges have one."""
    solid = ink > THRESHOLD
    rows, cols = solid.mean(axis=1), solid.mean(axis=0)
    return (frame_depth(rows, skip_blank), len(rows) - frame_depth(rows[::-1], skip_blank),
            frame_depth(cols, skip_blank), len(cols) - frame_depth(cols[::-1], skip_blank))


def mark_bounds(ink):
    """Bounds of everything that is ink."""
    ys, xs = np.nonzero(ink > THRESHOLD)
    if not len(ys):
        raise ValueError('no mark found')
    return ys.min(), ys.max() + 1, xs.min(), xs.max() + 1


def boxed(ink):
    """Whether all four edges are drawn lines: a closed box, not a mark that
    merely has a straight edge (WRAS + UKAS has a rule along its top only)."""
    solid = ink > THRESHOLD
    edges = (solid[:4].any(axis=0), solid[-4:].any(axis=0), solid[:, :4].any(axis=1), solid[:, -4:].any(axis=1))
    return all(edge.mean() > 0.8 for edge in edges)


def crop(rgb, ink, bounds):
    top, bottom, left, right = bounds
    return rgb[top:bottom, left:right], ink[top:bottom, left:right]


def belt_logo(path):
    rgba = np.asarray(Image.open(path).convert('RGBA')).astype(float)
    alpha = rgba[..., 3:] / 255
    rgb = rgba[..., :3] * alpha + 255 * (1 - alpha)   # flatten onto white
    bg = background(rgb)

    # How far each pixel is from the background: 0 is background, 1 is ink.
    ink = np.clip((np.sqrt(((rgb - bg) ** 2).sum(axis=-1)) - 18) / 120, 0, 1)

    # A frame drawn round the file, then the empty margin. If what is left is
    # still a closed box (SOCOTEC's ISO 9001 sits in one; its 14001 and 45001
    # don't), the box goes too, so the three read as a set.
    rgb, ink = crop(rgb, ink, inside_frame(ink))
    rgb, ink = crop(rgb, ink, mark_bounds(ink))
    if boxed(ink):
        rgb, ink = crop(rgb, ink, inside_frame(ink, skip_blank=True))
        rgb, ink = crop(rgb, ink, mark_bounds(ink))

    # Undo the background in the colours, so soft edges don't carry a halo.
    a = np.clip(ink * 1.2, 0, 1)
    colour = np.where(a[..., None] > 0, (rgb - bg * (1 - a[..., None])) / np.maximum(a[..., None], 1e-3), 0)
    mark = np.dstack([np.clip(colour, 0, 255), a * 255]).astype(np.uint8)

    h, w = ink.shape
    scale = math.sqrt(AREA / (w * h)) * (DENSITY_REF / max(ink.mean(), 0.05)) ** DENSITY_POWER
    scale = min(scale, BOX_W / w, BOX_H / h) * SCALE
    size = (max(1, round(w * scale)), max(1, round(h * scale)))

    canvas = Image.new('RGBA', (BOX_W * SCALE, BOX_H * SCALE), (0, 0, 0, 0))
    resized = Image.fromarray(mark, 'RGBA').resize(size, Image.LANCZOS)
    canvas.alpha_composite(resized, ((canvas.width - size[0]) // 2, (canvas.height - size[1]) // 2))
    return canvas


def main(argv):
    if len(argv) < 3:
        sys.exit(__doc__)
    out = Path(argv[1])
    out.mkdir(parents=True, exist_ok=True)
    for name in argv[2:]:
        source = Path(name)
        target = out / f'{source.stem}-belt.webp'
        belt_logo(source).save(target, 'WEBP', quality=88, alpha_quality=90, method=6)
        print(f'{target}  {target.stat().st_size // 1024} KB')


if __name__ == '__main__':
    main(sys.argv)
