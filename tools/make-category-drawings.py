"""Write inc/category-drawings.php: the homepage category cards' product drawings (AMM-186).

Usage, from the repo root:  python tools/make-category-drawings.py

One drawing per product_cat slug, all on the same sheet: viewBox x 40-300, y 0-144, pinned to
the card's top-right corner (preserveAspectRatio xMaxYMax), the product drawn whole and its
supply line (pipe, ground, plate, sheet) running off the card edge, past the viewBox where a
wider card shows more of it. Classes are dh-ca-* (paint, motion: assets/css/style.css, "The
product drawing"); ids and their url(#...) references start dh-cd-, which render.php replaces
with a unique prefix. The scattered parts (fog droplets, turf, soil, fibres) come from seeded
random numbers, so a re-run gives the same drawings. Python 3, standard library only.
"""
import math
import os
import random

OUT = os.path.join(os.path.dirname(__file__), '..', 'inc', 'category-drawings.php')


def n(v):
    """A coordinate: one decimal, no trailing zeros."""
    s = f'{v:.1f}'.rstrip('0').rstrip('.')
    return '0' if s == '-0' else s


def sheet(inner, defs=''):
    return ('<svg viewBox="40 0 260 144" preserveAspectRatio="xMaxYMax meet" focusable="false" '
            f'xmlns="http://www.w3.org/2000/svg"><defs>{defs}</defs>{inner}</svg>')


def fog():
    """A high-pressure fog line: end plug, gauge, three nozzles, the fog bank under them."""
    rnd = random.Random(11)
    xs = (158, 215, 272)
    defs = ('<radialGradient id="dh-cd-fog" cx=".5" cy=".42" r=".5">'
            '<stop class="dh-ca-stop-water" offset="0" stop-opacity=".36"/>'
            '<stop class="dh-ca-stop-water" offset=".55" stop-opacity=".16"/>'
            '<stop class="dh-ca-stop-water" offset="1" stop-opacity="0"/></radialGradient>')
    g = '<g class="dh-ca-fog">'
    for i, x in enumerate(xs):
        g += (f'<ellipse class="dh-ca-plume dh-ca-run" style="--t:{n(-i * 1.3)}s" cx="{x}" cy="100" rx="50" ry="38" '
              'fill="url(#dh-cd-fog)"/>')
    # the spray: droplets leave the orifice and slow into fog
    for x in xs:
        for i in range(26):
            a = rnd.uniform(-0.62, 0.62)
            d = rnd.uniform(30, 80)
            dx = d * math.sin(a) * 1.15 + rnd.uniform(-3, 3)
            dy = d * math.cos(a) * 0.9
            r = rnd.uniform(0.8, 1.7)
            t = -(i / 26 + rnd.uniform(0, 0.04)) * 2.4
            g += (f'<circle class="dh-ca-mist dh-ca-run" cx="{x}" cy="54" r="{r:.2f}" '
                  f'style="--x:{n(dx)}px;--y:{n(dy)}px;animation-delay:{t:.2f}s"/>')
    g += '</g>'
    g += '<rect class="dh-ca-body" x="137" y="26" width="179" height="10" rx="5"/><path class="dh-ca-hi" d="M143 28.8H314"/>'
    g += '<rect class="dh-ca-green" x="129" y="23" width="12" height="16" rx="2"/><path class="dh-ca-rib" d="M133 25v12M137 25v12"/>'
    g += '<path class="dh-ca-line" d="M186.5 26v-4"/><circle class="dh-ca-body" cx="186.5" cy="12" r="10"/>'
    ticks = ''
    for k in range(9):
        a = math.radians(-120 + k * 30)
        ticks += (f'M{n(186.5 + 6.4 * math.sin(a))} {n(12 - 6.4 * math.cos(a))}'
                  f'L{n(186.5 + 8.2 * math.sin(a))} {n(12 - 8.2 * math.cos(a))}')
    g += f'<path class="dh-ca-tick" d="{ticks}"/>'
    g += '<path class="dh-ca-needle" d="M186.5 12V4.6"/><circle class="dh-ca-dark" cx="186.5" cy="12" r="1.6"/>'
    for x in xs:
        g += f'<path class="dh-ca-green" d="M{x - 7} 36h14v8h-14z"/><path class="dh-ca-rib" d="M{n(x - 2.3)} 37v6M{n(x + 2.3)} 37v6"/>'
        g += f'<path class="dh-ca-body" d="M{n(x - 4.2)} 44h8.4l-1.8 8h-4.8z"/><circle class="dh-ca-dark" cx="{x}" cy="53" r="1.1"/>'
    return sheet(g, defs)


def landscape():
    """A pop-up rotor in turf, its stream thrown across the lawn."""
    rnd = random.Random(5)
    jet = 'M222 61Q140 -12 62 100'
    lx = 62
    defs = ('<linearGradient id="dh-cd-soil" x1="0" y1="0" x2="0" y2="1">'
            '<stop class="dh-ca-stop-canopy" offset="0" stop-opacity=".08"/>'
            '<stop class="dh-ca-stop-canopy" offset="1" stop-opacity="0"/></linearGradient>'
            '<clipPath id="dh-cd-ground"><rect x="-400" y="-60" width="800" height="163.5"/></clipPath>'
            '<mask id="dh-cd-throw" maskUnits="userSpaceOnUse" x="-400" y="-60" width="800" height="260">'
            f'<path class="dh-ca-draw" pathLength="1" d="{jet}" stroke="#fff" stroke-width="20" fill="none"/></mask>')
    g = '<rect x="-400" y="104" width="716" height="40" fill="url(#dh-cd-soil)"/>'
    grains = ''
    for _ in range(140):
        grains += f'M{n(rnd.uniform(-400, 316))} {n(rnd.uniform(109, 143))}h0'
    g += f'<path class="dh-ca-grain" d="{grains}"/>'
    # the feed from the lateral, below the body
    g += '<rect class="dh-ca-body" x="231" y="121" width="10" height="30"/>'
    blades, dark = '', ''
    x = -400.0
    while x < 316:
        if not 215 < x < 257:
            h = rnd.uniform(4, 10)
            lean = rnd.uniform(-2.6, 2.6)
            blade = f'M{n(x)} 104q{n(lean * .3)} {n(-h * .6)} {n(lean)} {n(-h)}'
            if rnd.random() < .3:
                dark += blade
            else:
                blades += blade
        x += rnd.uniform(2.4, 3.8)
    g += f'<path class="dh-ca-blade" d="{blades}"/><path class="dh-ca-blade dh-ca-blade--dark" d="{dark}"/>'
    g += '<path class="dh-ca-ground" d="M-400 104H316"/>'
    # the rotor: body in the ground, riser and nozzle turret popping up through the cap
    g += '<rect class="dh-ca-body" x="222" y="104" width="28" height="18" rx="2"/><path class="dh-ca-rib" d="M226 110h20M226 115.5h20"/>'
    g += ('<g clip-path="url(#dh-cd-ground)"><g class="dh-ca-riser">'
          '<rect class="dh-ca-body" x="231" y="66" width="10" height="44"/>'
          '<rect class="dh-ca-green" x="226" y="54" width="20" height="15" rx="3"/>'
          '<rect class="dh-ca-dark" x="221.5" y="58" width="5" height="6.5" rx="1"/></g></g>')
    g += '<rect class="dh-ca-dark" x="219" y="100.5" width="34" height="5" rx="2"/>'
    # the stream breaking into drops; it sweeps as the turret turns
    g += ('<g class="dh-ca-sweep dh-ca-run"><g mask="url(#dh-cd-throw)">'
          '<path class="dh-ca-spray dh-ca-run" d="M222 59Q146 -4 68 98"/>'
          f'<path class="dh-ca-jet dh-ca-run" d="{jet}"/></g>'
          f'<path class="dh-ca-splash" d="M{lx - 7} 98q-3-5-7-3M{lx + 7} 97q3-6 8-4M{lx - 4} 93l-2-5M{lx + 4} 92l2-5"/></g>')
    return sheet(g, defs)


def irrigation():
    """PE pipe cut away to show the water, a compression tee, a ball valve, an end plug."""
    p1, p2 = 'M316 112H206V-12', 'M206 112H62'
    defs = ('<mask id="dh-cd-water" maskUnits="userSpaceOnUse" x="-10" y="-20" width="340" height="180">'
            f'<path class="dh-ca-draw dh-ca-draw--first" pathLength="1" d="{p1}" stroke="#fff" stroke-width="16" fill="none"/>'
            f'<path class="dh-ca-draw dh-ca-draw--second" pathLength="1" d="{p2}" stroke="#fff" stroke-width="16" fill="none"/></mask>')
    g = ('<rect class="dh-ca-pe" x="212" y="104" width="104" height="16"/>'
         '<rect class="dh-ca-pe" x="198" y="-12" width="16" height="116"/>'
         '<rect class="dh-ca-pe" x="62" y="104" width="150" height="16"/>')
    g += '<path class="dh-ca-hi" d="M214 105.6H316M199.6 -12V102M62 105.6H196"/>'
    g += ('<g mask="url(#dh-cd-water)">'
          '<rect class="dh-ca-channel" x="62" y="107" width="254" height="10"/>'
          '<rect class="dh-ca-channel" x="201" y="-12" width="10" height="120"/>'
          f'<path class="dh-ca-flow dh-ca-run" d="{p1}"/><path class="dh-ca-flow dh-ca-run" d="{p2}"/></g>')
    g += '<rect class="dh-ca-green" x="193" y="99" width="26" height="26" rx="3"/>'
    g += '<rect class="dh-ca-green" x="219" y="100.5" width="11" height="23" rx="2"/>'
    g += '<rect class="dh-ca-green" x="182" y="100.5" width="11" height="23" rx="2"/>'
    g += '<rect class="dh-ca-green" x="194.5" y="88" width="23" height="11" rx="2"/>'
    g += '<path class="dh-ca-rib" d="M222.7 102.5v19M226.3 102.5v19M185.7 102.5v19M189.3 102.5v19M196.5 91.7h19M196.5 95.3h19"/>'
    g += '<rect class="dh-ca-body" x="104" y="103" width="7" height="18" rx="1.5"/><rect class="dh-ca-body" x="137" y="103" width="7" height="18" rx="1.5"/>'
    g += '<rect class="dh-ca-body" x="109" y="99.5" width="30" height="25" rx="6"/>'
    g += '<rect class="dh-ca-body" x="121" y="91" width="6" height="9"/>'
    g += '<rect class="dh-ca-green" x="118.5" y="84.5" width="44" height="7" rx="3.5"/><circle class="dh-ca-dark" cx="124" cy="88" r="2.4"/>'
    g += '<rect class="dh-ca-green" x="51" y="100.5" width="12" height="23" rx="2"/><path class="dh-ca-rib" d="M54.8 102.5v19M58.6 102.5v19"/>'
    return sheet(g, defs)


def tools():
    """A twist drill cutting into a plate, drawn in section; chips curling out of the hole."""
    plate = 'M316 108H224V116L214 122L204 116V108H124C116 115 132 122 122 136H316Z'
    body = 'M205 20V112L214 121L223 112V20Z'
    defs = (f'<clipPath id="dh-cd-plate"><path d="{plate}"/></clipPath>'
            '<clipPath id="dh-cd-flutes"><path d="M205 36V112L214 121L223 112V36Z"/></clipPath>')
    g = f'<path class="dh-ca-plate" d="{plate}"/>'
    g += ('<path class="dh-ca-hatch" clip-path="url(#dh-cd-plate)" d="'
          + ''.join(f'M{x} 138L{x + 32} 106' for x in range(96, 320, 7)) + '"/>')
    g += f'<path class="dh-ca-line" d="{plate}"/>'
    chip = 'M0 0c-3.6-7 4-13.6 10.3-9.7 4.6 3 1.8 10-4 8.8-3-.9-2.1-4.8 1.2-4.5'
    for side, ox in ((-1, 202), (1, 226)):
        g += f'<g transform="translate({ox} 106) scale({side} 1)">'
        for k in range(3):
            g += (f'<path class="dh-ca-chip dh-ca-run" d="{chip}" '
                  f'style="--x:{10 + k * 4}px;--r:{160 + k * 50}deg;animation-delay:{n(-k * 0.37)}s"/>')
        g += '</g>'
    g += '<g class="dh-ca-bit">'
    g += '<rect class="dh-ca-dark" x="193" y="-14" width="42" height="27" rx="3"/><path class="dh-ca-hi" d="M196 -3h36M196 4h36"/>'
    g += '<path class="dh-ca-chuck" d="M198 13H230L225 21H203Z"/>'
    g += f'<path class="dh-ca-body" d="{body}"/>'
    g += ('<g clip-path="url(#dh-cd-flutes)"><path class="dh-ca-flutes dh-ca-run" d="' + ''.join(
        f'M205 {y + 9}Q214 {y + 7} 223 {y}V{n(y + 6.5)}Q214 {n(y + 13.5)} 205 {n(y + 15.5)}Z'
        for y in range(4, 142, 16)) + '"/></g>')
    g += f'<path class="dh-ca-line" d="{body}"/><path class="dh-ca-hi dh-ca-hi--dark" d="M208.6 22V34"/>'
    g += '</g>'
    g += '<path class="dh-ca-centre" d="M214 -12V142"/>'
    return sheet(g, defs)


def nonwoven():
    """A roll of fabric, end on, the sheet off it; a detail of the web under a loupe."""
    rnd = random.Random(8)
    cx, cy, r = 150, 91, 37
    lx, ly, lr = 260, 54, 35
    T, tile0 = 72, 225
    base = cy + r
    # the web, one 72-unit tile: bond points in a grid, fibres laid at random
    bonds = ''
    for v in range(6, 108, 12):
        for u in range(0, T, 12):
            bonds += f'M{u + (6 if (v // 12) % 2 else 0)} {v}h0'
    fibres = {'dh-ca-fib': '', 'dh-ca-fib dh-ca-fib--light': '', 'dh-ca-fib dh-ca-fib--green': ''}
    for _ in range(32):
        u, v = rnd.uniform(0, T), rnd.uniform(22, 92)
        a = rnd.uniform(0, math.pi)
        length = rnd.uniform(16, 36)
        bx, by = length * math.cos(a), length * math.sin(a)
        c1 = (u + bx * .3 + rnd.uniform(-8, 8), v + by * .3 + rnd.uniform(-8, 8))
        c2 = (u + bx * .7 + rnd.uniform(-8, 8), v + by * .7 + rnd.uniform(-8, 8))
        cls = rnd.choice(('dh-ca-fib', 'dh-ca-fib', 'dh-ca-fib dh-ca-fib--light', 'dh-ca-fib dh-ca-fib--green'))
        fibres[cls] += (f'M{n(u)} {n(v - 12)}C{n(c1[0])} {n(c1[1] - 12)} {n(c2[0])} {n(c2[1] - 12)} '
                        f'{n(u + bx)} {n(v + by - 12)}')
    tile = f'<path class="dh-ca-bond" d="{bonds}"/>' + ''.join(f'<path class="{c}" d="{d}"/>' for c, d in fibres.items())
    defs = (f'<clipPath id="dh-cd-loupe"><circle cx="{lx}" cy="{ly}" r="{lr - .8}"/></clipPath>'
            f'<g id="dh-cd-web">{tile}</g>'
            f'<mask id="dh-cd-sheet" maskUnits="userSpaceOnUse" x="140" y="{base - 12}" width="190" height="30">'
            f'<path class="dh-ca-draw dh-ca-draw--now" pathLength="1" d="M{cx} {base + 2}H316" stroke="#fff" stroke-width="16" fill="none"/></mask>')
    g = f'<path class="dh-ca-ground" d="M-400 {base + 6}H316"/>'
    g += f'<g mask="url(#dh-cd-sheet)"><rect class="dh-ca-body" x="{cx}" y="{base}" width="166" height="6"/>'
    g += f'<path class="dh-ca-tex dh-ca-run" d="M{cx + 2} {base + 3}H316"/></g>'
    g += f'<circle class="dh-ca-body" cx="{cx}" cy="{cy}" r="{r}"/>'
    pts = []
    turns = 3.8
    for i in range(int(turns * 36) + 1):
        th = math.pi / 2 + i * math.tau / 36
        rr = (r - 1.5) - (r - 14) * i / (turns * 36)
        pts.append(f'{n(cx + rr * math.cos(th))} {n(cy + rr * math.sin(th))}')
    g += '<g class="dh-ca-unroll"><g class="dh-ca-turn dh-ca-run">'
    g += '<path class="dh-ca-spiral" d="M' + 'L'.join(pts) + '"/>'
    g += f'<circle class="dh-ca-green" cx="{cx}" cy="{cy}" r="11"/><circle class="dh-ca-hole" cx="{cx}" cy="{cy}" r="6"/>'
    g += f'<path class="dh-ca-line" d="M{cx} {cy - 6}V{cy - 11}"/></g></g>'
    mx, my = 216, base + 2
    ux, uy = lx - mx, ly - my
    d = math.hypot(ux, uy)
    ux, uy = ux / d, uy / d
    g += f'<circle class="dh-ca-mark" cx="{mx}" cy="{my}" r="7"/>'
    g += f'<path class="dh-ca-thin" d="M{n(mx + 7 * ux)} {n(my + 7 * uy)}L{n(lx - lr * ux)} {n(ly - lr * uy)}"/>'
    g += f'<g class="dh-ca-pop"><circle class="dh-ca-body" cx="{lx}" cy="{ly}" r="{lr}"/>'
    g += '<g clip-path="url(#dh-cd-loupe)"><g class="dh-ca-web dh-ca-run">'
    g += ''.join(f'<use href="#dh-cd-web" x="{tile0 + k * T}"/>' for k in (-1, 0, 1))
    g += f'</g></g><circle class="dh-ca-ring" cx="{lx}" cy="{ly}" r="{lr}"/></g>'
    return sheet(g, defs)


DRAWINGS = {
    'fog-systems': fog(),
    'landscape': landscape(),
    'irrigation': irrigation(),
    'industrial-tools-services': tools(),
    'non-wooven': nonwoven(),
}

for svg in DRAWINGS.values():
    assert "'" not in svg and '\\' not in svg  # each goes into a single-quoted PHP string as is

width = max(len(k) for k in DRAWINGS) + 2
items = ''.join(f"\t\t{repr(k).ljust(width)} => '{v}',\n" for k, v in DRAWINGS.items())

php = f"""<?php
/**
 * The homepage category cards' product drawings (AMM-186), one per product_cat
 * slug. GENERATED by tools/make-category-drawings.py: change a drawing there
 * and run it again, not here.
 *
 * Each is a decorative SVG for the card's top-right corner; the category cards
 * block (src/category-cards/render.php) puts it in place of the card's icon,
 * hidden from screen readers. Paint and motion live in assets/css/style.css
 * ("The product drawing"). A category without a drawing keeps its icon.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {{
	exit;
}}

/**
 * The drawing for a category, as SVG markup, or an empty string.
 *
 * Its ids, and the url(#...) references to them, start with dh-cd-: replace
 * that prefix with a unique one before printing.
 *
 * @param string $slug A product_cat slug.
 * @return string
 */
function demas_theme_get_category_drawing( $slug ) {{
	$drawings = array(
{items}	);

	return $drawings[ $slug ] ?? '';
}}
"""

with open(OUT, 'w', encoding='utf-8', newline='\n') as fh:
    fh.write(php)
print('wrote', os.path.normpath(OUT), sum(len(v) for v in DRAWINGS.values()), 'bytes of SVG')
