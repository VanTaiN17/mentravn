# Phase 3 Report — WooCommerce Catalog (Mentra Live)

Date: 2026-08-21
Branch: `feature/woocommerce-catalog` (based on `fix/static-pages`)
Scope: WooCommerce used strictly as a product-data CMS for **Mentra Live only**. No ecommerce transaction flow. Even Realities, NIMO, and Prescription lenses remain static pages per classification below.

## Git

- Branch `feature/woocommerce-catalog` created from the completed `fix/static-pages` baseline (working tree was clean, verified before starting).
- `origin` remote configured: `https://github.com/VanTaiN17/mentravn.git` (none existed before — no conflicting URL to stop and report).
- Commits, in order:
  1. `docs: add Phase 3 product classification`
  2. `feat: idempotent Mentra Live WooCommerce product import`
  3. `feat: WooCommerce catalog-only hardening and canonical redirect`
  4. `feat: wire Mentra Live page to WooCommerce product data`
- Working tree clean after every commit. No `git reset --hard` / `git clean -fd` / `git restore .` / force-push used. **Nothing pushed to GitHub.**

## Product classification

Full detail in `docs/product-classification.md`. Summary: inspected `WGET_REFERENCE` directly for embedded Shopify product objects on all four candidate pages.

| Item | Result |
|---|---|
| Mentra Live | Real Shopify product object found (SKU/handle, 2 variants with differing availability) → **WooCommerce Product** |
| Even Realities G2 | Zero product object; external "Buy" CTA to evenrealities.com → **STATIC PAGE** (unchanged) |
| NIMO | Zero product object; pre-launch, no purchase path at all → **STATIC PAGE** (unchanged) |
| Prescription lenses | Zero product object; custom/quote add-on via sales contact → **STATIC PAGE** (unchanged) |

Matches the expected default exactly — no disagreement, no changes made to `/even-realities/`, `/nimo/`, or `/trong-kinh/`.

## WooCommerce data model

**Product type:** Variable product (required — Black and Transparent have independently differing stock states, per instructions' "prefer variable ONLY if separate stock state per color is required").

**Identity:**
- Name: "Mentra Live Camera Glasses" (matches the Shopify source title exactly)
- Slug: `mentra-live-camera-glasses` (matches the Shopify handle; deliberately **not** `mentra-live`, which is the existing WP Page's slug — avoids any slug collision)
- SKU: `MENTRA-LIVE` (parent); `MENTRA-LIVE-DEN` / `MENTRA-LIVE-TRONG-SUOT` (variations) — stable, human-legible identifiers, since Shopify's data provides a GID (`gid://shopify/Product/8963292102908`) and a handle but not a literal "SKU" string to reuse.

**Attribute:** local (non-taxonomy) attribute "Màu sắc" with options `Đen` / `Trong suốt`, `variation=true`.

**Price fields:** never set on the parent or either variation — left genuinely empty, not faked as `0`. Verified via direct object inspection: `get_regular_price()`, `get_price()`, and `get_price_html()` all return `''`.

**Images:** featured image + 4-image gallery, sideloaded once from existing theme assets (`closed_mentra_live.webp`, `product_photos/frame.png`, `product_photos/frame2.png`, `micro_charge_cable_mentra_live.png`, `product_photos/chargingcase.webp`) into the Media Library, idempotently tracked via a `_mentra_vn_source_asset` postmeta key so re-running the import never creates duplicate attachments.

**Description / short description:** populated in the WC admin editor (compiled from the page's own existing marketing copy — camera/audio/SDK, design, specs) for admin reference. The **frontend page does not render these** — it keeps its own hand-authored HTML per the "theme owns visual layout" principle (see below). This is a deliberate separation, documented so a future editor isn't confused about why editing the WC description doesn't change the live page.

## Mentra Live import

Implemented in `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php`, following the exact same pattern Phase 2 established for page sync:

- `maybe_create_mentra_live_product()`, hooked on `init` at priority 25 (after page sync at 20).
- Guarded by `get_option('mentra_vn_product_mentra_live_v1')` so it only ever attempts creation once under normal operation.
- **A real duplication bug was found and fixed during testing.** The first implementation checked idempotency only via `wc_get_product_id_by_sku()` inside `create_mentra_live_product()`. On the very first live HTTP trigger, this produced **two** identical products with duplicate SKUs (confirmed: both created within the same second, same request). Root cause: WooCommerce's SKU lookup can read from a lookup table that lags behind a same-request insert, so the "does it already exist" check can pass twice in near-simultaneous execution paths before either finishes writing.
  - **Fix:** `maybe_create_mentra_live_product()` now takes an atomic lock first via `add_option('mentra_vn_product_mentra_live_lock', 1, '', 'no')` — `add_option()` returns `false` if the row already exists, which is an atomic "insert-if-not-exists" at the DB level, not vulnerable to the same race.
  - `create_mentra_live_product()` itself was also changed to check existence via a direct `get_posts()` query against `_sku` postmeta (bypassing the lookup table) as a second line of defense.
  - **Verified fixed**: deleted both duplicate products, reset the option flags, then ran `maybe_create_mentra_live_product()` 5 times back-to-back via CLI *and* fired 3 real HTTP requests at the homepage — exactly **one** product exists after both tests.
- The public `create_mentra_live_product($force = false)` method can be called directly (e.g. from wp-admin via WP-CLI later, or a debug script) and is itself safe to call repeatedly — it always re-checks by SKU first and is a no-op if found, `$force` is available but unused/not exposed anywhere public.
- **Never overwrites editor changes**: because the guard is "does a product with this SKU exist at all", if an admin edits the product's description, images, or attributes afterward, this routine will never touch it again (the option flag is also permanently set after the first successful run).
- No destructive operations anywhere in the routine — pure `wp_insert_post`/`WC_Product_Variable`/`WC_Product_Variation` creation, no deletes, no `TRUNCATE`, no raw `DELETE` SQL.

## Product URL architecture

**Canonical URL:** `/mentra-live/` (the pre-existing WP Page, rendered by `page-mentra-live.php`) — **unchanged from Phase 2.**

**Problem avoided:** WooCommerce's own product post type generates its own URL, `/product/mentra-live-camera-glasses/`, which would otherwise be a second, competing, indexable URL for the same content.

**Solution:** the product carries a `_mentra_vn_canonical_url` meta value (`/mentra-live/`). A new `mentra_vn_product_canonical_redirect()` hook (priority 5 on `template_redirect`, functions.php) checks `is_product()` and 301-redirects to that meta value whenever anyone reaches the WC-generated URL directly. Verified: `GET /product/mentra-live-camera-glasses/` → `301 Location: http://mentra-vn.local/mentra-live/`.

**Belt-and-suspenders**: the product post also has `_yoast_wpseo_meta-robots-noindex = 1` set directly, so even before any crawler follows the redirect, Yoast excludes it from `product-sitemap.xml` (verified: the sitemap contains only `/shop/`, no product URL at all).

Result: exactly one canonical, indexable Mentra Live URL exists (`/mentra-live/`); the WooCommerce-generated URL is real (admin can still open it from wp-admin's "View Product" link) but always redirects away for any actual visitor.

## Inventory implementation

- Stock is tracked via WooCommerce's native `stock_status` field (not `manage_stock` quantity tracking — the frontend only ever needs binary availability, matching the instructions exactly).
- `mentra_vn_stock_label($stock_status)` (functions.php) maps `instock` → "Còn hàng", `outofstock` → "Hết hàng", and (reusable for later, not currently used by any real data) `onbackorder` → "Sắp ra mắt".
- `page-mentra-live.php` calls this on both the parent product's aggregate stock status (shown as the main badge) and each variation's own stock status (shown in the per-color spec-grid rows) — **zero hardcoded HTML for stock state.**
- **Verified live**: toggled the "Đen" (Black) variation to `outofstock` via a direct WC API call (simulating what an admin does by clicking "Out of stock" in wp-admin and saving), confirmed the frontend badge changed from "Còn hàng" to "Hết hàng" and the per-color row updated, with **zero theme file edits** — then restored the original state (Black `instock`, matching the source data) and re-verified.

## Catalog-only restrictions

Implemented centrally in `functions.php`, applying site-wide and permanently (not just to Mentra Live — protects any future product too):

- `add_filter('woocommerce_is_purchasable', '__return_false')`
- `add_filter('woocommerce_variation_is_purchasable', '__return_false')`
- `add_filter('woocommerce_get_price_html', '__return_empty_string')`
- Dequeued `wc-cart-fragments` (mini-cart AJAX refresh) and `wc-add-to-cart` scripts site-wide (confirmed no longer requested on `/mentra-live/`).
- Phase 2's `/shop/`, `/cart/`, `/checkout/`, `/my-account/` → home redirects (302) **re-verified intact** after all Phase 3 changes — nothing in this phase touched or could have touched that hook.
- `is_admin()` is not needed as an explicit guard on most of these: `is_product()`/`is_shop()`/etc. conditional tags and `template_redirect` simply don't fire in the wp-admin context, so none of this hardening affects the Products admin screens. **Verified**: wp-admin product creation flow (via the CLI-safe import routine, which is the real-world equivalent of an admin using "Add Product") works correctly, and there is no `is_admin()` check anywhere blocking normal product editing.

## Price removal

Searched the entire theme and plugin (all `.php` files) for: `$449`, `$349`, `product-detail-price`, `get_price_html`, `regular_price`, `sale_price`, `add_to_cart`/`add-to-cart`, `woocommerce_template_single_price` — see **Tests** section below for the exact commands and results. The only matches remaining are the intentional hardening code itself (the `woocommerce_get_price_html` filter definition, and the `wc-add-to-cart` dequeue call/comment).

**One residual, non-live finding**: `templates/source/live.html` (the Phase 1/2 static-source mirror file, a byte-for-byte-ish translated copy of the original Shopify capture) still contains the literal `$449` text. This file is **not currently reachable by any live route** — no WordPress Page exists with the English slug `live` that would trigger `mentra_vn_render_source('live')` (only the `mentra-live` slug exists, which resolves via `page-mentra-live.php`, not the static-source path). It is dormant, unused legacy content, left untouched as out-of-scope static-page content (Phase 3 is about the WooCommerce-backed product route specifically). Flagged here for visibility in case a future page/redirect ever exposes that slug.

**Structured data**: confirmed no JSON-LD `Product`/`offers`/`price` schema is emitted on `/mentra-live/` at all (checked the live page source directly) — WooCommerce's automatic Product structured data only fires when `is_product()` is true, which is never the case for this WP Page—based route.

## CTA behavior

Primary CTA changed from "Liên hệ đặt hàng tại Việt Nam" (→ `/lien-he/?chu-de=dat-hang`, a query param the contact page doesn't actually read) to the exact business-rule text **"Liên hệ mua hàng"** → `/lien-he/?topic=sales`. No cart state, no quantity, no checkout link anywhere. The unrelated "Liên hệ kinh doanh" (bulk/enterprise contact) CTA further down the page is untouched — different purpose, not a purchase CTA, out of this phase's scope.

## SEO / schema

- One canonical URL (`/mentra-live/`) — verified.
- No duplicate `/product/` URL indexed — verified via `product-sitemap.xml` (empty of product URLs) and the 301 redirect.
- No price schema anywhere — verified (no JSON-LD on the page at all).
- No stock-availability schema was added either (kept minimal/conservative — instructions say "only if accurate and appropriate"; since this isn't rendered through WooCommerce's own templates, there's no automatic schema to selectively fix, and hand-authoring new schema wasn't required by the gate criteria).
- Title/meta on `/mentra-live/` unchanged (still driven by the WP Page's own title, unaffected by any of this phase's changes).
- Yoast behavior on unrelated pages: not touched by any Phase 3 code — all hooks added are scoped to `is_product()`/`woocommerce_*` filters, which are no-ops on every other route.

## Tests

- `php -l`: 100% pass across all theme (10 files) and plugin (1 file) PHP files, before and after all changes.
- `node --check`: both theme JS files pass (neither modified this phase).
- **Route:** `/mentra-live/` → HTTP 200, confirmed no `error404` class, full nav/footer/font present (regression-verified identically to Phase 2).
- **No forbidden output on `/mentra-live/`**: verified via direct grep of the rendered HTML — zero matches for `$449`/`$349`, zero `add_to_cart`/`add-to-cart` visible markup (the only 4 matches were the harmless `wc-add-to-cart-js` script tag reference before it was dequeued — now 0), zero `name="quantity"` input, zero cart/checkout links beyond WooCommerce's own inert JS config variable (also harmless — no button calls it).
- **Stock state**: admin-simulated toggle of the Black variation's stock status verified reflected on the frontend live, with zero theme file edits, then correctly restored.
- **Variable product**: both variations verified independently — Đen (Black) → Còn hàng, Trong suốt (Transparent) → Hết hàng, matching the source data exactly.
- **Header/mega-menu/footer/animations/images/responsive structure**: spot-checked via HTML source inspection (same method as Phase 2 — no headless browser tooling available/added this phase) — `desktop-nav-rail`, `footer-navigation-grid`, Red Hat Display stylesheet all present and unchanged on `/mentra-live/`.
- **Regression**: `/`, `/mentra-os/`, `/trong-kinh/`, `/even-realities/`, `/nimo/`, `/tin-tuc/`, `/lien-he/` all re-verified HTTP 200 after every commit in this phase.
- **Codebase-wide search** (theme `.php` + plugin `.php`) for `$449`, `$349`, `product-detail-price`, `get_price_html`, `regular_price`, `sale_price`, `add_to_cart`/`add-to-cart`, `woocommerce_template_single_price`: only intentional matches (the hardening filter definition itself, and the dequeue call for `wc-add-to-cart`) — reported above under Price removal, not treated as failures.
- **Idempotency stress test**: 5 sequential CLI calls + 3 real HTTP requests → exactly 1 product, 2 variations, no duplicates (see Mentra Live import section for the bug this caught and fixed).
- **WooCommerce route redirects** (`/shop/`, `/cart/`, `/checkout/`, `/my-account/`) re-verified intact (still 302 → home) after all Phase 3 changes.
- No manual browser/visual testing performed — all verification is HTTP-response, source-level, and direct WooCommerce object inspection via a one-off CLI bootstrap script (deleted after each use, per the same "controlled, CLI-safe, no WP-CLI available" approach Phase 2 used for content cleanup).

## Deferred items

- Even Realities, NIMO, Prescription lenses remain static — not deferred exactly, but explicitly confirmed correct and out of scope per classification.
- Product Description/Short description are populated in wp-admin but not consumed by the frontend template (deliberate — see WooCommerce data model section). If a future phase wants the frontend copy to be fully admin-editable (not just stock/image/SKU), that would need a larger template rewrite — not attempted here to respect "no redesign."
- The secondary decorative image in the page's "Thiết kế" (Design) section (`product_photos/frame2.png`) remains a static theme asset reference, not pulled from the WC gallery — a deliberate scope decision (see Phase 2 report's asset-localization note for the same reasoning pattern: the primary/hero image is the highest-value dynamic wiring target; a secondary editorial image is lower priority and lower risk left static).
- `templates/source/live.html` still contains literal `$449` — dormant/unreachable, not fixed, flagged above.
- WP-CLI is still not available in this environment; all admin-data operations continue to use one-off, deleted-after-use `wp-load.php` bootstrap scripts, same pattern as Phase 2.
- No stock-availability structured data (`schema:Offer availability`) was added — out of scope per the conservative reading of "only if accurate and appropriate" and not required by the gate criteria.
