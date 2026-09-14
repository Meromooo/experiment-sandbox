#!/usr/bin/env bash
#
# Create the locked two-level product category tree on the sandbox.
# Source of truth: demas-mega-menu-content-spec.md (verified against the
# live demas-group.com menu on 2026-09-14).
#
# Slugs mirror the live site so product URLs survive the eventual migration
# without redirects. The two live terms that sit three levels deep there
# (Accessories, Valve Boxes) are flattened to the spec's two levels here and
# will need a redirect each at cutover.
#
# Idempotent: a term is matched by slug and renamed / re-parented if it
# already exists, so the three dummy categories on the sandbox today
# (cutting-tools, filtration, welding-machines) are moved under their real
# parents and keep their products.
#
# Run from the WordPress root on the sandbox host:
#   bash wp-content/themes/demas-theme/tools/create-product-categories.sh
#
set -euo pipefail

TAX="product_cat"

term_id_by_slug() {
	wp term list "$TAX" --slug="$1" --field=term_id --format=csv 2>/dev/null | head -n 1
}

# ensure NAME SLUG [PARENT_ID]  -> prints the term id on stdout, logs to stderr
ensure() {
	local name="$1" slug="$2" parent="${3:-0}" id
	id="$(term_id_by_slug "$slug" || true)"
	if [[ -n "$id" ]]; then
		wp term update "$TAX" "$id" --name="$name" --parent="$parent" >/dev/null
		printf '  updated  %-48s #%-4s parent=%s\n' "$slug" "$id" "$parent" >&2
	else
		id="$(wp term create "$TAX" "$name" --slug="$slug" --parent="$parent" --porcelain)"
		printf '  created  %-48s #%-4s parent=%s\n' "$slug" "$id" "$parent" >&2
	fi
	printf '%s' "$id"
}

echo "Creating product categories…" >&2

IRR="$(ensure "Irrigation Products" "irrigation")"
ensure "Pipes"        "pipes"                    "$IRR" >/dev/null
ensure "Fittings"     "fittings"                 "$IRR" >/dev/null
ensure "Filtration"   "filtration"               "$IRR" >/dev/null
ensure "Accessories"  "cp-accessories"           "$IRR" >/dev/null
ensure "EF Fittings"  "electro-fusion-fittings"  "$IRR" >/dev/null

LND="$(ensure "Landscape" "landscape")"
ensure "Rotors"       "rotors"                   "$LND" >/dev/null
ensure "Controllers"  "controllers"              "$LND" >/dev/null
ensure "Valves"       "landscape-valves"         "$LND" >/dev/null
ensure "Valve Boxes"  "valve-boxes-fittings"     "$LND" >/dev/null

FOG="$(ensure "Fog Systems" "fog-systems")"
ensure "Controllers & Dosing Pumps"  "controllers-dosingpumps-electromagneticvalves"  "$FOG" >/dev/null
ensure "Fittings"                    "tecnocooling-fittings"                          "$FOG" >/dev/null
ensure "Nozzles and Extensions"      "nozzles-and-extensions"                         "$FOG" >/dev/null
ensure "Water Treatment"             "water-treatment"                                "$FOG" >/dev/null

IND="$(ensure "Industrial Tool Services" "industrial-tools-services")"
ensure "Bandsaw Blade & Accessories"  "band-saw-accessories"  "$IND" >/dev/null
ensure "Cutting Tools"                "cutting-tools"         "$IND" >/dev/null
ensure "Welding Machines & Wires"     "welding-machines"      "$IND" >/dev/null
ensure "Magnetic Machines & Drills"   "magnetic-drills"       "$IND" >/dev/null

# Top-level only. The menu renders this group as an outbound link to
# demasnonwoven.com rather than a category archive; that is a menu concern.
ensure "Non-Woven" "non-woven" >/dev/null

echo >&2
echo "Done. Current tree:" >&2
wp term list "$TAX" --fields=term_id,name,slug,parent,count --format=table
