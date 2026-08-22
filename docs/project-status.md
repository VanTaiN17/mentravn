# Mentra Vietnam — Current Project Status

Last updated: 2026-08-22
Current stable phase: Visual Fidelity Milestone — OWNER APPROVED / COMPLETE. Frontend page-building work is closed.
Current branch: `fix/full-visual-fidelity` (based on `fix/visual-fidelity`, based on `feature/news-posts`) — this branch holds the owner-approved frontend.
Git remote: `origin` = `https://github.com/VanTaiN17/mentravn.git`
Push status: see Git section below.

**Phase reports are historical records.** If an earlier phase's finding was later corrected by a newer phase, the newer phase is authoritative — do not blindly reuse an old finding. Example: Phase 1's audit said `accessibility.html` was 100% English; Phase 2 found it was already translated. Trust the correction.

## Completed phases

### Phase 1 — Full Site Audit
Status: DONE (audit only, no fixes). Found the static-source-mirror architecture, widespread soft-404s, and a P0 fatal error. Full detail: `docs/site-audit.md`.

### Phase 2 — Static Site Stabilization
Status: PASS. Fixed the P0 fatal error, made page-sync idempotent, resolved legal-slug conflicts, fixed `/get-mentra`, set `vi-VN`, localized CDN assets, disabled default WooCommerce/WP routes, translated remaining static-page strings. Full detail: `docs/phase-2-report.md`.

### Phase 3 — WooCommerce Catalog
Status: PASS. Only Mentra Live has a real source product object, so only Mentra Live became a WooCommerce product. Idempotent, self-healing product import (fixed a real SKU-lookup race-condition duplication bug). `/mentra-live/` reads stock/image/SKU live from WooCommerce. Permanent catalog-only hardening applied site-wide. Full detail: `docs/phase-3-report.md`.

### Phase 4 — News/Post Migration
Status: PASS. All 16 owned articles migrated to real `post_type=post` (translated, categorized, idempotent importer — fixed a real "stuck at partial batch" lock-design bug during testing). `/tin-tuc/` rebuilt as WordPress-native. 6 press items formalized as static data, never WP Posts. Full detail: `docs/phase-4-report.md`, `docs/news-migration-manifest.md`.

### Phase 4.5 — Visual Fidelity Repair
Status: superseded by Phase 4.6. Correctly diagnosed the root cause (invented classnames with no matching CSS) and rebuilt Mentra Live/MentraOS structurally, but the CSS it wrote was hand-approximated guesswork, not a verified reproduction — the owner confirmed in a real browser that the pages still looked wrong. Also mistakenly modeled "Infinity Cable" as an anchor/section inside Mentra Live rather than its own product page. Kept as a historical record; do not reuse its CSS values or its `#charging` decision without re-checking against Phase 4.6. Full detail: `docs/phase-4.5-report.md`.

### Phase 4.6 — Full Visual Fidelity Correction
Status: PASS (superseded procedurally by owner sign-off below, but the technical work stands). Fixed what Phase 4.5 got wrong by using **real, exact CSS** instead of guesses — extracted directly from the live production site's compiled stylesheet (Mentra Live's `product-detail-*`, MentraOS's `green-grid-promo`/`os-download-promo*`) or from embedded per-page `<style>` blocks in the WGET capture (Even Realities' entire ~40-class component system, ~509 lines, previously 100% unported). Removed the invented Mentra Live charging section entirely and built a real, separate Infinity Cable product page (`/products/mentra-live-charging-cable/`, new WooCommerce product SKU `MENTRA-INFINITY-CABLE`). Fixed Socials' platform-card hover. Audited all 20 top-level routes for the same failure pattern. A same-day addendum found and fixed 44 uncompiled bracket-notation Tailwind classes and a stale cache-version string. Full detail: `docs/phase-4.6-report.md`, `docs/visual-fidelity-audit.md`, `docs/full-visual-route-audit.md`.

### Visual Fidelity Milestone — Owner-Approved Finishing Pass (Antigravity)
Status: **OWNER APPROVED / COMPLETE.** After Phase 4.6, the owner completed the remaining frontend visual work directly together with Antigravity (a separate coding tool) and explicitly approved the resulting frontend state — this supersedes the earlier "VISUAL VERIFICATION PENDING" status; owner confirmation is authoritative regardless of whether every pixel was independently re-verified by Claude. 20 additional commits on `fix/full-visual-fidelity` (`6f25c70`..`9aa4d5b`), reviewed and kept as-is per instruction not to reopen approved visual work absent a technical breakage. Notable changes: Mentra Live gallery thumbnail switching + layout alignment fixes, Even Realities icon/typography fixes, Socials hover polish, a new `/tai-ung-dung/` (app download) page replacing the old `/get-mentra` → sales-contact redirect with a real download page, MentraOS logo sizing, comparison-page (`/so-sanh/`) FAQ interactivity + price removal from CTAs, captions-page (`/phu-de/`) FAQ content/styling, and several `MENTRA_VN_THEME_VERSION` cache-bumps (final: `3.14.0`). Reviewed for secrets/debug code/hardcoded local paths (none found) and re-validated `php -l`/`node --check` (both clean) before this handoff; a mechanical cleanup commit (`chore: strip stray UTF-8 BOM from static-source HTML files`) was added on top. **Live-route HTTP verification could not be performed during this handoff pass** — the local Local-by-Flywheel site was down (nginx up, PHP-FPM/MySQL not running) at review time; this is an environment/infrastructure state issue, not a code regression (confirmed: even the homepage 502'd, unrelated to any specific change). Recommend a quick route smoke-test once the local site is running again. **Frontend page-building milestone is now closed.**

## Current architecture

1. **Static marketing pages**: still served by `functions.php`'s `mentra_vn_source_map()` → `mentra_vn_render_source()`, echoing a static HTML file from `templates/source/*.html` inside a real WP Page. Editing these pages means editing the HTML file, not the WP editor.
2. **Mentra Live**: backed by WooCommerce catalog data. `page-mentra-live.php` (hand-authored, not the static-source renderer; rebuilt Phase 4.5, CSS corrected to real extracted values Phase 4.6) pulls stock status, SKU, and the full gallery (featured image + gallery images) live from a WooCommerce variable product (SKU `MENTRA-LIVE`) via `mentra_vn_get_product_by_sku()`. WooCommerce supplies data only; the theme owns the visual template. It has **no** `#charging` anchor/section (Phase 4.5 invented one; Phase 4.6 removed it — see 2c below).
2a. **MentraOS**: `page-mentra-os.php` (hand-authored; rebuilt Phase 4.5, CSS corrected to real extracted values Phase 4.6) — no WooCommerce data, pure static/marketing page.
2b. **WooCommerce frontend CSS/JS is dequeued site-wide** (not just on product-adjacent pages) — this site never renders a WC shop/cart/checkout template to a visitor, so `woocommerce-general/layout/smallscreen.css`, `wc-blocks-style`, and the order-attribution tracking scripts are removed on the frontend only (`!is_admin()`), documented in `functions.php`.
2c. **Infinity Cable for Mentra Live**: a real, separate product (Phase 4.6) — WooCommerce simple product, SKU `MENTRA-INFINITY-CABLE`, rendered by `page-mentra-live-charging-cable.php`, canonical URL `/products/mentra-live-charging-cable/` (custom rewrite + permalink filter; the default WP page URL and the WC product's own `/product/infinity-cable-mentra-live/` URL both 301-redirect there). Reuses the same `.product-detail-*` component as Mentra Live. Mega-menu "Infinity Cable" link points here.
2d. **`.product-detail-*` and MentraOS's `.os-*`/`.green-grid-promo` CSS are shared, unscoped theme components**, not page-scoped — confirmed reused verbatim across multiple real product/marketing pages on the source site. Page-*unique* one-off compositions (Even Realities' `.even-*`, Careers' `.career-*`) are scoped to their own `.mentra-vn-{slug}` body class.
3. **WooCommerce is permanently catalog-only**: no price, no Add to Cart, no cart, no checkout, no payment — site-wide and permanent, not per-product (`woocommerce_is_purchasable`/`woocommerce_variation_is_purchasable` forced `false`, price HTML forced empty, cart scripts dequeued).
4. **`/mentra-live/` is canonical**: the only indexable Mentra Live URL. The WooCommerce-generated `/product/mentra-live-camera-glasses/` URL 301-redirects to it and is Yoast-noindexed.
5. **`/tin-tuc/` is now WordPress-native**: `page-tin-tuc.php` (template-hierarchy precedence over `page.php`) dynamically queries `post_type=post` in category `bai-viet`, combined chronologically with static press data for the "Tất cả" tab. No hardcoded cards.
6. **Exactly 16 owned articles are real WP Posts** (category `Bài viết`), rendered via a dedicated `single.php` scoped to posts carrying `_mentra_vn_article=1` (any other/future post falls back to a generic template).
7. **Exactly 6 press items remain static/external**: `wp-content/themes/mentra-vietnam/data/press.php` → `mentra_vn_press_items()`. Never WordPress Posts (verified 0 in `wp_posts`).
8. **Article canonical URL pattern**: `/tin-tuc/{slug}/` — scoped rewrite rule + `post_type_link` filter in `functions.php`, only for posts with `_mentra_vn_article=1`.
9. **Legacy article URLs**: `/blogs/blog/{slug}/` 301-redirects to the canonical `/tin-tuc/{slug}/`. `templates/source/blogs/blog/*.html` (16 files) and `blogs.html`/`blog.html` are kept on disk as reference/dormant only.
10. **Forms have NOT yet been rebuilt**: current Newsletter/Contact AJAX handlers (Phase-1-era, `wp_ajax_mentra_vn_newsletter` / `wp_ajax_mentra_vn_contact_ajax`) still work as-is, nonce-protected, but route via a `sales_email`/`support_email` split in plugin settings, not a unified address. This is pre-existing functionality, not a Phase 4 deliverable — Phase 5 scope.
11. **Career form backend is still pending** — page renders, submission does nothing.
12. **reCAPTCHA v2 is still pending** — no bot protection on any form yet.
13. **Business-contact forms will eventually all use `contact@domain.vn`** (not yet unified).
14. **SMTP transport will be WP Mail SMTP** when configured — do not build custom SMTP transport.
15. **Legal-policy bodies** (Privacy/Terms/Shipping/Refund) still require human Vietnam-specific legal review before content changes; routing/slug fixes are fine.
16. **`/tai-ung-dung/` app-download page** (added during the owner/Antigravity finishing pass): a new static-source-mirror page (`templates/source/get.html`, source key `get`), replacing the old `/get-mentra` behavior — that legacy path now 301-redirects to `/tai-ung-dung/` instead of to sales contact. No price/cart content.
17. **Frontend visual page-building milestone is CLOSED**, owner-approved. Any further frontend work should be scoped as a new, deliberate task, not assumed to be still-open Phase 4.x cleanup.

## Current important IDs/data

- Mentra Live WooCommerce product ID is install-specific — always look up by SKU (`MENTRA-LIVE`) via `mentra_vn_get_product_by_sku()`, never hardcode.
- Variation SKUs: `MENTRA-LIVE-DEN` (instock), `MENTRA-LIVE-TRONG-SUOT` (outofstock).
- News category: `Bài viết`, slug `bai-viet` — look up by slug, do not hardcode the term ID.
- Article identity meta: `_mentra_vn_article=1`, `_mentra_vn_legacy_slug`, `_mentra_vn_article_authors` (array of `['name','role','avatar']`).
- Press data source: `wp-content/themes/mentra-vietnam/data/press.php` — never query `wp_posts` for press content.

## Current news state (Phase 4 verified)

- 16/16 articles migrated, 16/16 translated, 16/16 verified (`docs/news-migration-manifest.md`).
- 6/6 press items static; 0 press items exist as WP Posts.
- Category `Bài viết` / `bai-viet` — count = 16.
- Zero `cdn.shopify.com` references remain in migrated content.
- Migration is idempotent (stress-tested 5x reruns + simulated partial-failure self-heal, no duplicates).

## Business decisions

- WooCommerce is catalog-data CMS only, forever.
- Only items with a real, confirmed source product object become WooCommerce products.
- Purchase CTA text/target fixed: "Liên hệ mua hàng" → `/lien-he/?topic=sales`.
- The 16 articles and 6 press items are permanently separate concepts; press items must never become WordPress Posts.
- Legal policy body content is not to be translated/rewritten without human review.
- Brand/product names are never translated.

## Known deferred work

- Contact form recipient unification to a single `contact@domain.vn`
- Career application form backend (Phase 5 candidate)
- Google reCAPTCHA v2 + server-side verification (Phase 5 candidate)
- WP Mail SMTP configuration
- Legal content review (Privacy/Terms/Shipping/Refund body text) — includes the "Đổi trả & bảo hành" block on `/mentra-live/`, flagged inline
- ~~Manual browser visual verification~~ — **DONE.** The owner completed remaining visual work with Antigravity and explicitly approved the frontend. Closed, do not reopen.
- A live-route HTTP smoke-test is still recommended once the local server is confirmed running (it was down — PHP-FPM/MySQL not started — during the final handoff review; see the Visual Fidelity Milestone entry above).
- Production deployment (still local-only)

## Non-blocking technical debt

- `assets/media/` — 44 MB duplicate of `assets/`, unreferenced, safe cleanup candidate
- Two video files with fragile `*.mp4@v=N` filenames
- 2 homepage hero videos missing `poster` attributes
- Dead CSS (`.logo-for-light`/`.logo-for-dark`), unreferenced `shopping-bag*.svg` files
- Orphaned, unused `assets/js/main.js`
- Dormant `templates/source/live.html` still contains literal `$449` (unreachable, not fixed)
- Yoast SEO metadata still mostly defaults; **Yoast JSON-LD/OpenGraph still reports `og:locale`/`inLanguage` as `en_US`/`en-US`** despite `html lang="vi-VN"` (found Phase 4.5, not fixed — needs Yoast's locale-mapping filter, out of scope again in Phase 4.6)
- Article reading-time labels not carried into migrated posts (deliberate, not required)
- Inline article images/author avatars remain static theme assets, not Media Library attachments (only featured images were sideloaded)
- Mentra Live/Infinity Cable gallery thumbnail `alt` text is generically "Mentra Live"/"Infinity Cable" for all WooCommerce-sourced images (source has distinct per-image alts); minor a11y-quality gap, not a visual regression
- `/phu-de/` (captions.html) has one small unported non-layout CSS rule (`.captions-faq-answer` typography) — found Phase 4.6, low priority, not fixed

## Next planned phase

**Phase 5 — Forms Architecture** (unify contact routing to `contact@domain.vn`, build the Career form backend, add reCAPTCHA v2 + server-side verification, configure WP Mail SMTP). The frontend visual-fidelity precondition is now satisfied — owner approved. Forms has not started.

**DO NOT START WITHOUT USER/PROJECT-MANAGER INSTRUCTION.**

## Required reading for next Claude context

1. `CLAUDE.md`
2. `docs/project-status.md` (this file)
3. `docs/phase-4.6-report.md` (latest phase report — the owner-approved Antigravity finishing pass has no separate phase report; see this file's Completed Phases section above for its summary)
4. `docs/visual-fidelity-audit.md`, `docs/full-visual-route-audit.md`, `docs/owner-visual-bugs.md` (historical reference only — frontend milestone is closed, do not treat their "pending" language as current)
5. The relevant specialized document for whatever phase is being started next (e.g. `docs/route-map.csv` for routing, `docs/news-migration-manifest.md` for further news work)

Do not reread every historical document unless the task genuinely requires it.
