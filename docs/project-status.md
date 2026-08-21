# Mentra Vietnam — Current Project Status

Last updated: 2026-08-21
Current stable phase: Phase 4 — News/Blog Migration (PASS)
Current branch: `feature/news-posts`
Git remote: `origin` = `https://github.com/VanTaiN17/mentravn.git`
Push status: nothing ever pushed to any remote

**Phase reports are historical records.** If a Phase 1 finding was later corrected by Phase 2 or Phase 3, the newer report is authoritative — do not blindly reuse an old finding. Example: Phase 1's `docs/translation-audit.md` said `accessibility.html` was 100% English; Phase 2 found this was incorrect on direct inspection (already translated). Trust the correction, not the original audit line.

## Completed phases

### Phase 1 — Full Site Audit
Status: DONE (audit only, no fixes)
Important outcome: Produced `docs/site-audit.md`, `docs/route-map.csv`, `docs/translation-audit.md`, `docs/asset-audit.md`, `docs/link-audit.md`. Found the site's core architecture (static-source-mirror rendering + broken page-creation sync causing widespread soft-404s) and a P0 fatal error on `/mentra-live/` and `/mentra-os/`. Overall audit status: FAIL (not ready to proceed) — this is exactly why Phases 2–3 existed.

### Phase 2 — Static Site Stabilization
Status: PASS
Important outcome: Fixed the P0 fatal error, made page-sync self-healing/idempotent (fixed 12 soft-404 static routes + 4 legal slugs), resolved legal-page slug duplication via 301 redirects, fixed `/get-mentra`, set `vi-VN` document language, localized all remaining `cdn.shopify.com` assets, disabled default WooCommerce/WordPress routes (`/shop/` `/cart/` `/checkout/` `/my-account/` `/category/uncategorized/` `/author/*` → redirect; demo content trashed), translated ~48 remaining English strings on static pages. Full detail: `docs/phase-2-report.md`.

### Phase 3 — WooCommerce Catalog
Status: PASS
Important outcome: Classified the 4 candidate products (`docs/product-classification.md`) — only Mentra Live has a real source product object, so only Mentra Live became a WooCommerce product; Even Realities/NIMO/Prescription lenses stay static. Built an idempotent, self-healing product-import routine (found and fixed a real SKU-lookup race-condition duplication bug during testing). Wired `/mentra-live/` to read stock/image/SKU live from WooCommerce while keeping the exact existing visual design. Applied permanent, site-wide catalog-only hardening (no purchasable, no price HTML, no cart scripts). Full detail: `docs/phase-3-report.md`.

### Phase 4 — News/Blog Migration
Status: PASS
Important outcome: Migrated all 16 owned articles to real `post_type=post` entries (category `Bài viết`, full Vietnamese translation, Media Library featured images, `_mentra_vn_article` marker meta) via an idempotent, self-healing importer — found and fixed a real "stuck at 15/16" bug during testing (an add_option lock pattern copied from Phase 3's product importer permanently blocked retry after one transient partial-batch failure; fixed by switching to `maybe_create_pages()`'s no-lock, per-item-existence-check pattern instead). Rebuilt `/tin-tuc/` as a dynamic `page-tin-tuc.php` (WP_Query for articles + static `data/press.php` for the 6 press items, combined chronologically) requiring zero JavaScript changes since the existing tab/search filter is fully data-attribute-driven. Added scoped `/tin-tuc/{slug}/` permalinks and 301 redirects from all 16 legacy `/blogs/blog/{slug}/` URLs. Full detail: `docs/phase-4-report.md`.

## Current architecture

- **Static source renderer**: most non-product pages are still served by `functions.php`'s `mentra_vn_source_map()` → `mentra_vn_render_source()`, which echoes a static HTML file from `templates/source/*.html` (translated in place) inside a real WP Page. This is NOT normal WordPress post_content — editing these pages means editing the HTML file, not the WP editor.
- **Real WP page routing**: `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php`'s `maybe_create_pages()` (hooked on `init`, option-gated, idempotent) ensures every slug in the source map has a real published WP Page, so nothing falls through to the `404.php` soft-404 fallback anymore.
- **Mentra Live WooCommerce backing**: `page-mentra-live.php` (a hand-authored template, NOT the static-source renderer) pulls stock status, featured image, and SKU live from a WooCommerce variable product (SKU `MENTRA-LIVE`) via `mentra_vn_get_product_by_sku()`. Marketing copy/layout stays hand-authored in the PHP file — WooCommerce provides data only, never controls the visual template.
- **Canonical URL architecture**: `/mentra-live/` (a WP Page) is the only indexable Mentra Live URL. The WooCommerce-generated `/product/mentra-live-camera-glasses/` URL 301-redirects to it (`mentra_vn_product_canonical_redirect()` in `functions.php`) and is Yoast-noindexed. Same pattern (`_mentra_vn_canonical_url` postmeta) is reusable if a future product needs it.
- **Catalog-only hardening**: `woocommerce_is_purchasable` and `woocommerce_variation_is_purchasable` filtered to `false` site-wide and permanently; `woocommerce_get_price_html` filtered to empty string; `wc-cart-fragments`/`wc-add-to-cart` scripts dequeued; `/shop/` `/cart/` `/checkout/` `/my-account/` redirected to home (302, temporary — `/shop/` may become a real catalog listing later).
- **News architecture**: `/tin-tuc/` is `page-tin-tuc.php` (WordPress `page-{slug}.php` template-hierarchy precedence, same pattern as `page-mentra-live.php`), dynamically querying `post_type=post` in category `bai-viet` (16 real WordPress Posts) combined chronologically with the 6 static press items from `wp-content/themes/mentra-vietnam/data/press.php`. Individual articles render via a dedicated `single.php`, scoped to posts carrying `_mentra_vn_article=1` postmeta (any other/future post falls back to a generic template, so this never affects unrelated content). Canonical article URL is `/tin-tuc/{slug}/` (scoped rewrite rule + `post_type_link` filter in `functions.php`); the legacy `/blogs/blog/{slug}/` URLs 301-redirect to canonical. `templates/source/blogs/blog/*.html` (16 files) and the `blogs.html`/`blog.html` static listing sources are kept on disk as reference/dormant only — no live route depends on them. Press items are never WordPress Posts (verified: 0 in `wp_posts`).
- **Forms current architecture**: Newsletter and Contact/Sales/Support/Partnership/Media forms work (AJAX to `wp_ajax_mentra_vn_newsletter` / `wp_ajax_mentra_vn_contact_ajax` in the plugin, nonce-protected). Contact form still routes via a `sales_email`/`support_email` split in plugin settings, not yet a single `contact@domain.vn`. Career application form has no backend at all (page renders fine, submission does nothing). No reCAPTCHA, no rate limiting, no WP Mail SMTP configuration yet.

## Current important IDs/data

- Mentra Live WooCommerce product ID: **65 on this install** — IDs are local-install-specific, do not hardcode; always look up by SKU via `wc_get_product_id_by_sku('MENTRA-LIVE')` or `mentra_vn_get_product_by_sku('MENTRA-LIVE')`.
- Parent SKU: `MENTRA-LIVE`
- Variation SKUs: `MENTRA-LIVE-DEN` (Black/Đen, stock: instock), `MENTRA-LIVE-TRONG-SUOT` (Transparent/Trong suốt, stock: outofstock)
- Canonical public URL: `/mentra-live/`
- Stock model: WooCommerce native `stock_status` (instock/outofstock), not quantity-tracked. Mapped to Vietnamese via `mentra_vn_stock_label()` in `functions.php` (instock→Còn hàng, outofstock→Hết hàng, onbackorder→Sắp ra mắt — the third state is reusable but currently unused by any real product).
- News category: `Bài viết`, slug `bai-viet` — look up via `get_term_by('slug', 'bai-viet', 'category')`, do not hardcode the term ID (install-specific).
- Article identity: `_mentra_vn_article = 1` marks a post as one of the 16 migrated articles; `_mentra_vn_legacy_slug` holds the original source slug (identical to the current slug in all 16 cases today, but the importer keys off this meta, not the slug, for idempotency). `_mentra_vn_article_authors` holds an array of `['name','role','avatar']`.
- Canonical article URL pattern: `/tin-tuc/{slug}/`. Legacy `/blogs/blog/{slug}/` 301s to it.
- Press data source: `wp-content/themes/mentra-vietnam/data/press.php` → `mentra_vn_press_items()` (6 items, static PHP array — never query `wp_posts` for press content).

## Business decisions

- WooCommerce is a catalog-data CMS only, forever — no checkout, no cart, no payment, no coupons, no customer accounts. This is permanent site-wide hardening, not a per-product setting.
- Only items with a real, confirmed source product object become WooCommerce products. Everything else stays a static page. Do not force-fit informational/partner/service pages into WooCommerce.
- Purchase CTA text and target are fixed: "Liên hệ mua hàng" → `/lien-he/?topic=sales`.
- The 16 blog articles and the 6 press items are permanently separate concepts — the 6 press items must never become WordPress Posts. (Implemented in Phase 4: 16 real `post_type=post` entries in category `Bài viết`; 6 press items in static `data/press.php`, verified 0 in `wp_posts`.)
- Legal policy body content (Privacy/Terms/Shipping/Refund) is explicitly NOT to be translated or rewritten by Claude — it needs human legal review because the source text is US/Shopify-era. Routing/slug-conflict fixes are fine; content changes are not.
- Brand/product names (Mentra, Mentra Live, MentraOS, NIMO, Even Realities, GitHub, Android, etc.) are never translated.

## Known deferred work

- Contact form recipient unification to a single `contact@domain.vn`
- Career application form backend (currently no handler at all)
- Google reCAPTCHA v2 Checkbox + server-side verification
- WP Mail SMTP configuration for `wp_mail()` transport
- Legal content review (Privacy/Terms/Shipping/Refund body text — still untranslated US/Shopify-era source)
- Manual visual QA in an actual browser (all verification so far has been HTTP-response/source-level only, no headless browser tooling used)
- Production deployment (still local-only, `http://mentra-vn.local/`)

## Non-blocking technical debt

- `wp-content/themes/mentra-vietnam/assets/media/` — 44 MB byte-for-byte duplicate of `assets/`, confirmed unreferenced, safe cleanup candidate
- Two video files with fragile `*.mp4@v=N` filenames (not `?v=N` query strings)
- 2 homepage hero videos missing `poster` attributes
- Dead CSS (`.logo-for-light`/`.logo-for-dark`), 2 unreferenced `shopping-bag*.svg` files
- Orphaned, unused `assets/js/main.js` (duplicates newsletter logic already in `mentra.js`, never enqueued)
- `templates/source/live.html` (dormant static-source mirror, not reachable by any live route) still contains literal `$449` — flagged, not fixed, since nothing serves it
- Desktop mega-menu hover/dropdown behavior still needs a manual browser check — CSS support exists but interactive behavior has never been visually verified
- Article reading-time labels ("N phút đọc") present in some source articles were not carried into the migrated posts — not required by Phase 4's gate criteria, deliberately deferred rather than scope-creeping a field the design doesn't strictly need
- Inline article images and author avatars remain static theme-asset references rather than Media Library attachments (only the featured/hero image per article was sideloaded) — same secondary-asset scope decision Phase 3 made for `page-mentra-live.php`

## Next planned phase

Not yet chosen. Candidates visible in "Known deferred work" above (contact-email unification, career form backend, reCAPTCHA, WP Mail SMTP, legal content review, production deployment) — do NOT begin any of these without explicit instruction.

## Required reading for next Claude context

A fresh context should read, in this order:

1. `CLAUDE.md`
2. `docs/project-status.md` (this file)
3. The latest phase report (`docs/phase-4-report.md` as of this writing)
4. The relevant specialized document for whatever phase is being started next (e.g. `docs/route-map.csv` for routing work, `docs/news-migration-manifest.md` only if doing further news work, etc.)

Do not reread every historical document unless the task genuinely requires it.
