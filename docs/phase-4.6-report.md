# Phase 4.6 Report — Full Visual Fidelity Correction

Date: 2026-08-21
Branch: `fix/full-visual-fidelity` (based on `fix/visual-fidelity`)
Trigger: the owner manually inspected the local site in a real browser after Phase 4.5 and confirmed it had **not** actually fixed the reported problems, plus surfaced two new issues (Even Realities, Infinity Cable misclassification).
Scope: `/mentra-live/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, new `/products/mentra-live-charging-cable/`, plus a full top-level route sweep. No Phase 5 (Forms) work performed.

## What Phase 4.5 got wrong

Phase 4.5 correctly diagnosed the root cause (invented classnames with no matching CSS) and correctly rebuilt the DOM structure of Mentra Live and MentraOS, but the CSS it wrote to make that structure look right was **hand-approximated from guesswork**, not verified against any real source. That is why the owner still saw a broken page after Phase 4.5 "passed" its own gate. This phase fixes that by using the **real, exact CSS** wherever it exists — extracted directly from the live production site's compiled stylesheet or from embedded per-page `<style>` blocks in the WGET capture — instead of estimating values that merely looked plausible. Full technical detail and diffs of every guessed-vs-real value: `docs/visual-fidelity-audit.md` (Phase 4.6 addendum section).

## Git

- Branch `fix/full-visual-fidelity` created from the completed `fix/visual-fidelity` baseline (working tree was clean, verified before starting).
- Source-of-truth policy used both the immutable `WGET_REFERENCE` and, per explicit instruction for this phase, the current public `https://mentraglass.com/` site — its compiled CSS bundle and current page HTML were downloaded to a local scratch directory for reference only (never hotlinked, never shipped); `WGET_REFERENCE` itself was never modified.
- Commits, in order:
  1. `docs: audit remaining visual fidelity failures`
  2. `fix: restore source-accurate Mentra Live layout and remove invented charging section`
  3. `feat: add source-accurate Infinity Cable product page` (includes the permalink-filter bugfix below — found and fixed before this commit, so it's part of the same working, tested unit rather than a separate follow-up commit)
  4. `fix: restore Even Realities source styling and layout`
  5. `fix: restore Socials platform hover states`
  6. `fix: restore MentraOS download-promo and responsive layout`
  7. `docs: complete Phase 4.6 report`
- Working tree clean after every commit. No `git reset --hard` / `git clean -fd` / `git restore .` / force-push used. **Nothing pushed to GitHub.**

## VIS-001 — Socials platform-card hover

Root cause: the real interaction is JS mouse-event-driven, not CSS `:hover`/`group-hover:` — confirmed by diffing against a freshly-downloaded copy of the live production page (no hover-variant class exists on the card markup at all). Fixed with `initSocialPlatformHover()` in `mentra.js`, which reads each card's own platform accent color from the DOM at runtime (the accent-bar's inline `background-color`) rather than hardcoding a color table, so it works identically for all 8 platforms. The source markup's own `transition-all`/`transition-colors` Tailwind utility classes provide the smooth animation with no new CSS required. Keyboard `focus`/`blur` get the same treatment as `mouseenter`/`mouseleave` for accessibility parity.

## VIS-002 — Mentra Live desktop composition

Root cause: Phase 4.5's `.product-detail-*` CSS was guessed, not real (see addendum). Replaced with the exact ruleset extracted from `https://mentraglass.com/live`'s compiled stylesheet: real breakpoints at 48em/64em/96em (not a single flat 1024px split), real `height:max(280px,min(54vw,440px))` media-card sizing (not a fixed `aspect-ratio:1/1`), real `max-width:32.5rem` capped summary column (was unbounded), real flex-row scrollable thumbnails (was a fixed 6-column grid). `.product-detail-*` was un-scoped from the page (previously `.mentra-vn-mentra-live .product-detail-*`) since it's confirmed to be the site's real shared product-page template — verified reused verbatim on the new Infinity Cable page, and a `.product-detail-thumb` rule already existed unscoped since Phase 3.

## VIS-003 — Infinity Cable product page

Root cause: Phase 4.5 found no `id="charging"` anywhere in `live.html` and, not knowing a real product page existed, made a documented-but-wrong call to invent a charging section on Mentra Live to satisfy the pre-existing `/mentra-live/#charging` mega-menu link. The owner corrected the premise.

**Removed**: the entire invented "SẠC & INFINITY CABLE" section from `page-mentra-live.php` (both the Infinity Cable and Charging Case cards), including its FAQ cross-link, which now points to the real product page instead.

**Built**: a real, separate product page.
- WP Page, slug `mentra-live-charging-cable`, title "Infinity Cable cho Mentra Live" (added to `create_pages()`, `maybe_create_pages()` bumped `_v2` → `_v3` so the new page gets created without re-running the whole idempotent sync from scratch).
- Rendered by `page-mentra-live-charging-cable.php`, reusing the real (now-shared) `product-detail-*` structure/CSS — content and copy translated from the current public product page (`https://mentraglass.com/products/mentra-live-charging-cable`; the WGET capture predates this product and has no equivalent page at all).
- Canonical URL `/products/mentra-live-charging-cable/`, matching the real site's `/products/{handle}/` convention — implemented via a scoped `add_rewrite_rule` + `page_link`/`_get_page_link` filters (scoped to this one page's post_name only; no other Page's permalink is affected).
- Backed by a new WooCommerce simple product, SKU `MENTRA-INFINITY-CABLE` (source has no literal SKU string, only a Shopify GID — a documented stable internal SKU per the phase instructions), `stock_status=instock`, no price, no variations invented. Idempotent creation follows the same atomic-lock pattern as Phase 3's Mentra Live product (`maybe_create_infinity_cable_product()`/`create_infinity_cable_product()`).
- Mega-menu "Infinity Cable" link updated from `mentra-live/#charging` to `products/mentra-live-charging-cable/`.
- Full re-classification writeup: `docs/product-classification.md` (Phase 4.6 addendum).

## VIS-004 — Even Realities layout/CSS

Root cause (different category from Mentra Live/MentraOS): the page's real markup already used correct classnames, but they belong to a page-unique BEM component system (`.even-hero-grid`, `.even-glasses-grid`, `.even-app-feature`, `.even-setup-list`, `.even-g2-grid`, `.even-final`, ~40 classes) that never had a matching rule anywhere in the theme. On the real site this CSS lives in a `<style>` block embedded directly in `even-realities.html` itself, not in the shared compiled bundle — confirmed by checking every WGET page for embedded style blocks (10 found total; `live.html`/`OS.html` have none, one-off page compositions like `even-realities.html`/`index.html`/`careers.html` do). Fixed by extracting the complete embedded block verbatim (509 lines, values unchanged) and porting it into `mentra.css` scoped under `.mentra-vn-even-realities`.

**Same pattern checked everywhere**: verified every other WGET page with an embedded style block. `careers.html` (`/tuyen-dung/`) has an equivalent block but was found — after direct inspection, not just a heuristic flag — to already be fully ported in an earlier phase; no action needed there. `captions.html` (`/phu-de/`) has one small unported non-layout typography rule, flagged as low-priority debt, not fixed this phase.

## MentraOS re-check

Not flagged by a new owner screenshot this round, but re-checked per instructions rather than assumed fixed. The page's *structure* (rebuilt in Phase 4.5 from real inline styles copied verbatim from the source) was confirmed sound. Its `.green-grid-promo`/`.os-download-promo*` download-promo banner, however, had zero CSS anywhere in the theme in Phase 4.5 and was invented from scratch (guessed dark near-black gradient, centered layout) — replaced with the real extracted rules (green radial+linear gradient, dot-grid overlay, right-aligned two-column grid). Also added the real mobile-breakpoint (`max-width:767px`) block covering hero/compatibility-grid/miniapp-store/badge-grid/download-promo responsive behavior, which Phase 4.5 had no equivalent for at all.

## Full top-level route sweep

All 20 public top-level routes audited; results in `docs/full-visual-route-audit.md`. Method: (1) direct inspection of every WGET page's embedded `<style>` block (the newly-discovered failure category), and (2) a classname-coverage heuristic (every rendered class checked against `mentra.css`/`utilities.css`) as a secondary signal. Outcome: the 4 owner-reported routes plus the new Infinity Cable page were fixed/built; every other route showed no page-unique missing-class signal and is classified VISUALLY OK, except `/phu-de/` (one small unported non-layout rule, flagged not fixed).

## A bug found and fixed before commit

While DB-verifying the new Infinity Cable page via a one-off `wp-load.php` bootstrap script, `get_permalink()` returned the *wrong* URL (`/mentra-live-charging-cable/`, missing the `/products/` prefix) despite the custom rewrite rule working correctly for direct requests. Root cause: WordPress's `page_link`/`_get_page_link` filters pass the **post ID** (an integer) as their second argument, not a `WP_Post` object — `mentra_vn_product_page_permalink()` was written assuming a `WP_Post` (copying the pattern from Phase 4's *different* `post_type_link`/`post_link` filters, which genuinely do receive a `WP_Post`), so `$post->post_type` silently warned and the whole check short-circuited to always returning the unmodified default link. Fixed by calling `get_post($post_id)` first. This also surfaced a second, related gap: the default WordPress page URL (`/mentra-live-charging-cable/`) was never redirected away, meaning two indexable URLs existed for the same page — added `mentra_vn_infinity_cable_canonical_redirect()` (301, `template_redirect` priority 5) matching the same canonical-URL principle Phase 3 established for the WooCommerce product URL. Verified: the default URL now 301s to `/products/mentra-live-charging-cable/`, no redirect loop, `get_permalink()` now returns the correct canonical URL.

## Tests

- `php -l`: 100% pass across the full theme + plugin tree, verified after every commit.
- `node --check`: `mentra.js` passes after both new functions (`initSocialPlatformHover`, and the earlier `initPdpAccordion` from Phase 4.5).
- **CSS sanity**: brace-balance check across the full `mentra.css` after every edit (741 open / 741 close after the final edit).
- **Routes**: all 5 target routes (`/mentra-live/`, `/products/mentra-live-charging-cable/`, `/even-realities/`, `/mang-xa-hoi/`, `/mentra-os/`) → 200. All 16 other top-level regression routes → 200. `/shop/`, `/cart/`, `/checkout/` still 302.
- **Business-rule checks**: zero `$449`/price patterns, zero `add-to-cart`/`add_to_cart`, zero `name="quantity"` on both Mentra Live and Infinity Cable.
- **Residual reference search** (theme + plugin, `.php`/`.js`): `#charging` — 1 match, an explanatory code comment about the fixed historical bug (intentional). `product-page-hero` — 2 matches, both in explanatory code comments (intentional). `class="reveal"` — 0 matches. `cdn.shopify.com` — 0 matches.
- **Product tests**: Mentra Live — WC stock still dynamic (4× "Còn hàng"/2× "Hết hàng" spec rows across mobile+desktop summaries, matching live variation data), no price/quantity/Add to Cart/cart/checkout. Infinity Cable — exists exactly once (`SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='_sku' AND meta_value='MENTRA-INFINITY-CABLE'` → 1, verified via one-off bootstrap script), stable SKU, `stock_status=instock`, no price/Add to Cart, CTA is "Liên hệ mua hàng", canonical public URL is `/products/mentra-live-charging-cable/` (verified both directly and via the WooCommerce product's own `/product/infinity-cable-mentra-live/` URL 301-redirecting there).
- **Idempotency**: `mentra_vn_pages_synced_v3` gate confirmed to only create the one new page (existing 26 pages untouched, verified `create_pages()`/`page()` skip-if-exists logic unchanged). Infinity Cable product creation uses the identical atomic-lock (`add_option()`) pattern already proven race-condition-safe for Mentra Live in Phase 3.

## Phase 3 / Phase 4 regression check

- Mentra Live still reads SKU/stock/gallery images live from the real `MENTRA-LIVE` WooCommerce product.
- No price, quantity, or Add to Cart anywhere on any product-style page.
- `/shop/`, `/cart/`, `/checkout/` still 302.
- `/tin-tuc/` still 200, news architecture untouched — no changes made to any Phase 4 file this phase.
- All other top-level routes still 200.

## Manual browser verification

**Still not available in this environment** (no Playwright or other browser automation, confirmed again this phase). Per the Phase 4.5/4.6 gate criteria, this phase **cannot claim a full visual PASS**. Reported as **TECHNICAL READY FOR OWNER VISUAL REVIEW**. Unlike Phase 4.5, every CSS value applied this phase is a verified, exact reproduction of a real source (the live production stylesheet or an embedded page-specific `<style>` block) rather than an estimate — the specific failure mode the owner caught last time (plausible-looking guesses) should not recur, but only a real browser can confirm actual pixel-level correctness, animation feel, and responsive behavior at the specified breakpoints (1920/1440/1024/768/390/375).

## Remaining known gaps (documented, not blocking)

- `/phu-de/`'s one small unported `.captions-faq-answer` typography rule (non-layout).
- Yoast `og:locale`/`inLanguage` still `en_US`/`en-US` (documented since Phase 4.5, out of scope again this phase per instructions).
- No manual/automated screenshot-diffing tool introduced.
- Gallery thumbnail `alt` text on both product pages is generic (WC gallery images don't carry distinct per-image alt text) — same known debt item as Phase 4.5.
