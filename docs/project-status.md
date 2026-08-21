# Mentra Vietnam — Current Project Status

Last updated: 2026-08-21
Current stable phase: Phase 4.5 — Visual Fidelity Repair (TECHNICAL PASS / VISUAL VERIFICATION PENDING)
Current branch: `fix/visual-fidelity` (based on `feature/news-posts`)
Git remote: `origin` = `https://github.com/VanTaiN17/mentravn.git`
Push status: nothing pushed to GitHub yet.

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
Status: TECHNICAL PASS / VISUAL VERIFICATION PENDING. Root cause: `page-mentra-live.php`/`page-mentra-os.php` were built against an orphaned, never-enqueued `main.css`, not the real `mentra.css`/`utilities.css` design system — both rendered essentially unstyled with an oversimplified DOM. Rebuilt both against the real WGET structure (Mentra Live: full product-detail gallery/buy-box/accordions/charging section with `id="charging"` restored; MentraOS: all 8 real sections, correct light/dark backgrounds). Fixed a site-wide reveal-animation bug (inline styles from the original capture out-specified the CSS). Dequeued unnecessary WooCommerce frontend CSS/JS site-wide. All structural/data/business-rule/regression checks pass; actual browser visual verification is outstanding (no browser-automation tooling available in this environment) — **owner/PM should visually confirm in a real browser before this phase is considered fully closed.** Full detail: `docs/phase-4.5-report.md`, `docs/visual-fidelity-audit.md`.

## Current architecture

1. **Static marketing pages**: still served by `functions.php`'s `mentra_vn_source_map()` → `mentra_vn_render_source()`, echoing a static HTML file from `templates/source/*.html` inside a real WP Page. Editing these pages means editing the HTML file, not the WP editor.
2. **Mentra Live**: backed by WooCommerce catalog data. `page-mentra-live.php` (hand-authored, not the static-source renderer; rebuilt Phase 4.5 to the real source product-detail structure) pulls stock status, SKU, and the full gallery (featured image + gallery images) live from a WooCommerce variable product (SKU `MENTRA-LIVE`) via `mentra_vn_get_product_by_sku()`. WooCommerce supplies data only; the theme owns the visual template. `#charging` anchor exists on this page (consolidated from the homepage's real `.charge-row` block — see `docs/phase-4.5-report.md`).
2a. **MentraOS**: `page-mentra-os.php` (hand-authored; rebuilt Phase 4.5 to the real 8-section source structure/backgrounds) — no WooCommerce data, pure static/marketing page.
2b. **WooCommerce frontend CSS/JS is dequeued site-wide** (not just on product-adjacent pages) — this site never renders a WC shop/cart/checkout template to a visitor, so `woocommerce-general/layout/smallscreen.css`, `wc-blocks-style`, and the order-attribution tracking scripts are removed on the frontend only (`!is_admin()`), documented in `functions.php`.
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
- Legal content review (Privacy/Terms/Shipping/Refund body text) — includes the "Đổi trả & bảo hành" block restored on `/mentra-live/` in Phase 4.5, flagged inline
- **Manual browser visual verification of `/mentra-live/`, `/mentra-live/#charging`, `/mentra-os/`, `/mang-xa-hoi/` at desktop (1440px) and mobile (390px)** — Phase 4.5 could not perform this (no browser-automation tooling available in this environment); everything else about that phase passed. Do this before treating Phase 4.5 as fully closed.
- Manual visual QA in an actual browser generally (all verification so far is HTTP/DB-level)
- Production deployment (still local-only)

## Non-blocking technical debt

- `assets/media/` — 44 MB duplicate of `assets/`, unreferenced, safe cleanup candidate
- Two video files with fragile `*.mp4@v=N` filenames
- 2 homepage hero videos missing `poster` attributes
- Dead CSS (`.logo-for-light`/`.logo-for-dark`), unreferenced `shopping-bag*.svg` files
- Orphaned, unused `assets/js/main.js`
- Dormant `templates/source/live.html` still contains literal `$449` (unreachable, not fixed)
- Yoast SEO metadata still mostly defaults; **Yoast JSON-LD/OpenGraph still reports `og:locale`/`inLanguage` as `en_US`/`en-US`** despite `html lang="vi-VN"` (found Phase 4.5, not fixed — needs Yoast's locale-mapping filter, out of that phase's scope)
- Article reading-time labels not carried into migrated posts (deliberate, not required)
- Inline article images/author avatars remain static theme assets, not Media Library attachments (only featured images were sideloaded)
- Mentra Live gallery thumbnail `alt` text is generically "Mentra Live" for all WooCommerce-sourced images (source has distinct per-image alts); minor a11y-quality gap, not a visual regression

## Next planned phase

**Phase 5 — Forms Architecture** (unify contact routing to `contact@domain.vn`, build the Career form backend, add reCAPTCHA v2 + server-side verification, configure WP Mail SMTP). Before starting: a human should visually confirm Phase 4.5's browser-verification-pending items (see Known deferred work above).

**DO NOT START WITHOUT USER/PROJECT-MANAGER INSTRUCTION.**

## Required reading for next Claude context

1. `CLAUDE.md`
2. `docs/project-status.md` (this file)
3. `docs/phase-4.5-report.md` (latest phase report)
4. `docs/visual-fidelity-audit.md` (if further visual/template work on Mentra Live, MentraOS, or Socials is needed)
5. The relevant specialized document for whatever phase is being started next (e.g. `docs/route-map.csv` for routing, `docs/news-migration-manifest.md` for further news work)

Do not reread every historical document unless the task genuinely requires it.
