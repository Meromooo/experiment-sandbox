# Demas Mega Menu — Locked Content Spec

**Source of truth:** live demas-group.com desktop "Products" mega menu, captured directly (screenshot), 2026-07-28. This supersedes any category slugs currently in `inc/navigation.php`.

**Scope decision:** the sandbox mega-menu must match this exactly — same five top-level groups, same subcategories, same order. No more, no less. Depth is two levels (top-level category + immediate subcategories) — nothing deeper is shown in the live menu, so nothing deeper belongs in the sandbox one either.

## Structure

1. **Irrigation Products**
   - Pipes
   - Fittings
   - Filtration
   - Accessories
   - EF Fittings

2. **Landscape**
   - Rotors
   - Controllers
   - Valves
   - Valve Boxes

3. **Fog Systems**
   - Controllers & Dosing Pumps
   - Fittings
   - Nozzles and Extensions
   - Water Treatment

4. **Industrial Tool Services**
   - Bandsaw Blade & Accessories
   - Cutting Tools
   - Welding Machines & Wires
   - Magnetic Machines & Drills

5. **Non-Woven**
   - Visit DM Non-Wovens (external link to demasnonwoven.com — not a real subcategory, just an outbound link)

Each column has a bold header with an underline divider beneath it, then a vertical list of subcategory items, each with its own small line-icon to the left of the label (subcategory-level icons, not just one icon per top-level column).

## What this changes about the current sandbox build

- **WooCommerce categories don't match yet.** The sandbox's current dummy `product_cat` terms (`irrigation`, `drippers`, `filtration`, `landscape`, `fittings`, `tools`) need to be replaced with real categories and subcategories matching the tree above, before `render.php`'s query can return correct data.
- **`render.php` currently only queries top-level categories** (`parent => 0`) and renders flat links. It needs to also fetch each top-level category's direct children and render them as the subcategory list under each column.
- **`inc/navigation.php`'s icon mapping currently only covers 6 top-level slugs.** It needs one icon per subcategory — roughly 20 entries — not one per top-level category.
- **Column layout:** five columns matching the five groups above, each with a header + underline + subcategory list, matches the current flex-based `.dh-mega-menu__columns` structure reasonably well — this is a data/content change plus a subcategory-rendering change, not a structural rebuild.

## Note on an earlier discrepancy

An automated fetch of the live site during planning returned a version of this menu showing "Fog Systems → Coming Soon" and omitting Industrial Tool Services and Swimming Pool entirely — that was a stale/cached render, not the real menu. The screenshot capture above is the authoritative version and is what the sandbox should match.

## Status

Locked. Ready to hand to Claude Code as the content spec for the mega-menu's category/subcategory data and icon mapping — should be paired with actually creating the matching WooCommerce categories/subcategories in the sandbox first, since none of this can render correctly against dummy data.
