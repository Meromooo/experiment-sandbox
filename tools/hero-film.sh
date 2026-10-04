#!/usr/bin/env bash
#
# The homepage hero film (AMM-175): four 3-second Vidu clips of one site plan
# (lines laid, station fitted, zones run, garden grown) joined into one film
# for the schematic card, plus its two stills.
#
#   bash tools/hero-film.sh OUT_DIR CLIP1 CLIP2 CLIP3 CLIP4
#
# The clips go in story order. Each must be 1920x1080, 24 fps, with the
# drawing where the 2026-10-03 clips have it (x 676-1919, y 69-997). The four
# joins are seamless (SSIM >= 0.99), so they are cut together straight.
#
# What it does to the picture:
#  - crops the empty left of the frame (x 616 on), keeping the full height,
#    then pads the right edge so the plot's corner doesn't touch it;
#  - grades each clip's background to the theme's paper (#FAF9F6). Vidu's
#    off-white is warm (about #FEF8EE) and drifts warmer clip by clip (to
#    about #FAF1E7 by the end), so each clip is measured (the frame's empty
#    top-left corner, mid-clip, decoded as browsers decode it: BT.709) and
#    scaled to paper on its own;
#  - turns the pipes from royal blue to about the theme's water teal (hue -30
#    on blues and cyans);
#  - holds the bare plan for 0.4 s before the lines start.
#
# Writes four files to OUT_DIR, which are uploaded to the Media Library and
# chosen in the schematic block's "Film" panel:
#   demas-hero-film-av1.webm   AV1, for browsers that decode it
#   demas-hero-film-h264.mp4   H.264, plays everywhere
#   demas-hero-film-start.webp the first frame, the video's poster
#   demas-hero-film-end.webp   the last frame, shown still with reduced
#                              motion or Save-Data
#
# Nothing here removes or hides the "Vidu AI" mark: the crop keeps the full
# frame height, where it sits. Clean clips come from a Vidu plan that allows
# commercial use; re-run this with them.
#
# Needs ffmpeg with libsvtav1, libx264 and libwebp. Not deployed (AMM-173).

set -euo pipefail

if [ "$#" -ne 5 ]; then
	echo "usage: $0 OUT_DIR CLIP1 CLIP2 CLIP3 CLIP4" >&2
	exit 1
fi

out=$1
shift
mkdir -p "$out"

rgb="scale=in_range=full:in_color_matrix=bt709:out_range=full"

# One clip's background as "R G B": the mean of its empty top-left corner,
# 1.5 s in.
background() {
	ffmpeg -v error -ss 1.5 -i "$1" -frames:v 1 		-vf "crop=200:120:20:20,${rgb},format=rgb24,scale=1:1:flags=area" -f rawvideo - | od -An -tu1
}

graph=""
for i in 0 1 2 3; do
	read -r r g b <<<"$(background "${@:$((i + 1)):1}")"
	echo "clip $((i + 1)) background: $r $g $b" >&2
	graph+="[$i:v]${rgb},format=gbrp,lutrgb=r='min(255,val*250/$r)':g='min(255,val*249/$g)':b='min(255,val*246/$b)'[c$i];"
done
graph+="[c0][c1][c2][c3]concat=n=4:v=1:a=0,crop=1304:1080:616:0,huesaturation=hue=-30:colors=b+c:strength=10,"
graph+="pad=1364:1080:0:0:color=0xFAF9F6,scale=1200:950:flags=lanczos,tpad=start_duration=0.4:start_mode=clone,"
graph+="scale=out_range=tv:out_color_matrix=bt709,format=yuv420p[v]"
tags=(-colorspace bt709 -color_primaries bt709 -color_trc bt709 -color_range tv)

ffmpeg -v error -y -i "$1" -i "$2" -i "$3" -i "$4" -filter_complex "$graph" -map '[v]' -an \
	-c:v libsvtav1 -preset 4 -crf 40 -g 120 "${tags[@]}" "$out/demas-hero-film-av1.webm"

ffmpeg -v error -y -i "$1" -i "$2" -i "$3" -i "$4" -filter_complex "$graph" -map '[v]' -an \
	-c:v libx264 -preset veryslow -crf 26 -tune animation -profile:v high -g 48 "${tags[@]}" \
	-movflags +faststart "$out/demas-hero-film-h264.mp4"

# The stills go through RGB, decoded as BT.709 like the video, so WebP's own
# conversion (BT.601) starts from the right colours and they match the film.
still="scale=in_range=tv:in_color_matrix=bt709:out_range=full,format=bgra"
ffmpeg -v error -y -i "$out/demas-hero-film-h264.mp4" -frames:v 1 -vf "$still" -c:v libwebp -quality 60 "$out/demas-hero-film-start.webp"
ffmpeg -v error -y -sseof -0.1 -i "$out/demas-hero-film-h264.mp4" -frames:v 1 -update 1 -vf "$still" -c:v libwebp -quality 65 "$out/demas-hero-film-end.webp"

ls -l "$out"/demas-hero-film-*
