"""
Marketing photos (AMM-239): gives the homepage hero and Services photos one
look, and cuts each to its slot.

    python tools/grade-photos.py SRC_DIR OUT_DIR

SRC_DIR holds the originals named by Media Library ID (1230.jpg, ...), as
downloaded from the sandbox or as they came off the phone; OUT_DIR gets
demas-<name>.jpg for each row of PHOTOS, which are uploaded to the Media
Library and placed in patterns/hero.php, patterns/services.php and the saved
pages. Add a row for a new photo; a row whose original is missing is skipped.

The photos are phone shots taken in harsh sun: sand rendered traffic-cone
orange, blown highlights, and on the homepage an indigo canopy overlay laid
over them. For each photo this:

 - crops it (fractions of the frame: left, top, right, bottom) to take off a
   camera's burned-in stamp, or to the Services slot's shape (4:3, 3:2,
   16:11), or so the subject sits inside the hero's wide desktop band;
 - lowers saturation a little, and orange sand more, toward the sand token;
 - adds a soft S-curve, rolls off the highlights and lifts the blacks a hair;
 - cools the shadows slightly toward canopy, so they meet the hero overlay
   without a muddy band;
 - sharpens lightly and saves a progressive JPEG with no camera data.

Not upscaled: a photo is only as sharp as its original. Needs Python 3 with
Pillow and NumPy. Not deployed (AMM-173).
"""

import sys
from pathlib import Path

import numpy as np
from PIL import Image, ImageFilter

CANOPY = np.array([0x23, 0x2D, 0x8E], dtype=np.float32) / 255  # theme.json canopy

# Media ID: (output name, crop as left, top, right, bottom fractions or None)
PHOTOS = {
    1196: ('hero-finished-site', None),
    1230: ('hero-installation-crew', None),
    1209: ('hero-pipework', None),
    1206: ('hero-filter-station', (0, 0, 1, .85)),       # less ground: the worker keeps his head at desktop width
    1226: ('hero-misting-system', (0, .02, 1, .80)),     # drops the camera's date stamp
    1164: ('services-crew', None),
    1227: ('services-site-survey', None),
    1233: ('services-gate-valve', (0, .145, 1, .70)),    # 4:3
    1201: ('services-valve-box', (0, .25, 1, .75)),      # 3:2
    1208: ('services-trenching', (0, .19, 1, .706)),     # 16:11
    1161: ('services-drip-lines', None),
}


def grade(im):
    a = np.asarray(im.convert('RGB'), dtype=np.float32) / 255
    lum = a @ np.array([.2126, .7152, .0722], dtype=np.float32)
    r, g, b = a[..., 0], a[..., 1], a[..., 2]
    orange = np.clip((r - b) * 2.2, 0, 1) * np.clip(1 - np.abs(g - (r + b) / 2) * 3, 0, 1)
    sat = 0.90 - 0.24 * orange
    a = np.clip(lum[..., None] + (a - lum[..., None]) * sat[..., None], 0, 1)
    a = a + 0.14 * (a - 0.5) * (1 - np.abs(2 * a - 1))
    hi = a > 0.82
    a[hi] = 0.82 + (a[hi] - 0.82) * 0.78
    a = 0.025 + a * 0.97
    shadow = np.clip(1 - lum * 2.2, 0, 1)[..., None]
    a = a * (1 - 0.06 * shadow) + CANOPY * 0.06 * shadow
    out = Image.fromarray((np.clip(a, 0, 1) * 255 + 0.5).astype(np.uint8))
    return out.filter(ImageFilter.UnsharpMask(radius=1.4, percent=45, threshold=2))


def main(src_dir, out_dir):
    src_dir, out_dir = Path(src_dir), Path(out_dir)
    out_dir.mkdir(parents=True, exist_ok=True)
    for media_id, (name, crop) in PHOTOS.items():
        src = src_dir / f'{media_id}.jpg'
        if not src.exists():
            print(f'skip {media_id}: no {src.name}')
            continue
        im = Image.open(src).convert('RGB')
        if crop:
            w, h = im.size
            l, t, r, b = crop
            im = im.crop((round(l * w), round(t * h), round(r * w), round(b * h)))
        out = out_dir / f'demas-{name}.jpg'
        grade(im).save(out, quality=84, optimize=True, progressive=True)
        print(f'{out.name}  {im.size[0]}x{im.size[1]}')


if __name__ == '__main__':
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    main(sys.argv[1], sys.argv[2])
