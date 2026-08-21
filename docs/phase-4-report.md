# Phase 4 Report — News/Blog Migration

Date: 2026-08-21
Branch: `feature/news-posts` (based on `feature/woocommerce-catalog`)
Scope: Replace the static News architecture with native WordPress content. 16 owned articles become real `post_type=post` entries; the 6 press items become static/external data and are explicitly never imported as WordPress Posts.

## Git

- Branch `feature/news-posts` created from the completed `feature/woocommerce-catalog` baseline (working tree was clean, verified before starting).
- Commits, in order:
  1. `docs: add Phase 4 news migration manifest`
  2. `content: migrate and translate all 16 Mentra articles`
  3. `feat: add static press data source`
  4. `feat: add idempotent Mentra article importer`
  5. `feat: add WordPress-native article routing and legacy redirects`
  6. `feat: add dynamic Mentra article template`
  7. `feat: rebuild news listing from WordPress posts`
  8. `docs: complete Phase 4 report`
- Working tree clean after every commit. No `git reset --hard` / `git clean -fd` / `git restore .` / force-push used. **Nothing pushed to GitHub.**

## Migration architecture

- Source of truth for the 16 articles + 6 press items: `docs/news-migration-manifest.md`, independently verified against `templates/source/blogs/blog/*.html` (16 files by direct directory listing) and `templates/source/blogs.html` (22 cards: 16 `data-newsroom-kind="blogs"` + 6 `data-newsroom-kind="news"`).
- Article content and metadata live in `wp-content/plugins/mentra-vietnam-core/data/mentra-articles.php` (`mentra_vn_articles()`), a plain PHP data array — title, date, excerpt, featured-image path, author(s), and the full translated body HTML.
- Import logic lives in the plugin (`Mentra_Vietnam_Core_99`), matching Phase 2/3's data-layer convention: `maybe_create_news_category()` and `maybe_import_mentra_articles()` hooked on `init` at priorities 26/30 (after page sync at 20, after the Mentra Live product at 25).
- Routing/rendering logic lives in the theme, matching Phase 3's convention (WooCommerce canonical redirect lives in `functions.php`, not the plugin): the `/tin-tuc/{slug}/` rewrite rule, the `post_type_link`/`post_link` filter, and the legacy `/blogs/blog/{slug}/` → `/tin-tuc/{slug}/` redirect are all in `functions.php`.
- The listing page uses WordPress's own `page-{slug}.php` template-hierarchy precedence (`page-tin-tuc.php`), the same pattern Phase 3 established with `page-mentra-live.php`/`page-mentra-os.php` — this fully supersedes `page.php`'s static-source rendering for `/tin-tuc/` without needing to touch `mentra_vn_source_map()` (its `'tin-tuc' => 'blogs'` entry becomes dead/superseded, same as the pre-existing `'mentra-live' => 'live'` entry).
- The 16 articles use a dedicated `single.php`, scoped to posts carrying `_mentra_vn_article = 1` postmeta; any other/future `post_type=post` (none exist today) falls back to a generic prose template using the theme's default `header.php`/`footer.php`, so this phase never changes WordPress's default post behavior for unrelated content.

## Idempotency — design and a real bug caught during testing

`maybe_import_mentra_articles()` deliberately does **not** use the single-item `add_option()` lock pattern Phase 3 built for the Mentra Live product. It follows `maybe_create_pages()`'s no-lock, per-item-existence-check pattern instead, because this imports a *list* of 16 items, not one:

- **Bug found in testing**: the first version used a lock (`add_option('mentra_vn_news_migration_lock', ...)`) exactly like Phase 3's product importer. A live test run imported 15 of 16 articles, then stopped (a transient failure processing the 16th item, most likely first-run Media Library thumbnail generation without the `imagick` PHP extension loaded — GD fallback is slower). Because the lock had already been acquired, every subsequent `init` request found the lock present and silently no-op'd, **permanently stuck at 15/16** with no way to self-heal.
- **Fix**: removed the lock. `create_mentra_article()` already checks for an existing post by `_mentra_vn_legacy_slug` postmeta before ever inserting, so it's safe to call repeatedly. `maybe_import_mentra_articles()` now loops all 16 every time the "done" flag isn't set yet, only setting that flag once all 16 are confirmed present. Re-running is a cheap set of 16 existence checks once complete, and self-heals if a future partial failure ever recurs.
- **Verified**: deleted one imported article and cleared the "done" option to simulate a stuck partial batch — the next `init`-triggered pass recreated exactly the missing one and correctly reached 16/16 with no duplicates. Then ran the importer 5 times back-to-back with all 16 present — post count stayed at exactly 16 every time.
- Media (featured images) uses the same idempotent `sideload_theme_asset()` helper Phase 3 built for the Mentra Live product gallery (dedupes by `_mentra_vn_source_asset` postmeta), so re-running never creates duplicate attachments.
- Never overwrites an admin's edits: once a post exists (found by `_mentra_vn_legacy_slug`), the importer never touches it again — same permanent-idempotency guarantee as `maybe_create_mentra_live_product()`.

## Article inventory

All 16 articles migrated, translated, and verified. Full table (source title, Vietnamese title, source/new slug, date, image, author) in `docs/news-migration-manifest.md`, all rows marked `VERIFIED`.

- New WP slugs are identical to the legacy source slugs in every case — no collisions, so the legacy → canonical redirect is a straightforward 1:1 slug mapping.
- Author attribution: 14 articles by Cayden Pierce (CEO); 2 ("Making Mentra Live", "Mentra Live Shipping Update") co-authored with Alex Israelov (CTO) — stored as an array in `_mentra_vn_article_authors` postmeta and rendered as an avatar+name+role block per author, matching the source design exactly.

## Translation

All 16 article bodies translated in full from the English source into natural Vietnamese — headings, paragraphs, lists, links, and embedded YouTube iframes preserved structurally; nothing summarized or omitted.

- Brand/product names never translated: Mentra, Mentra Live, MentraOS, Mentra Live, Mentra Market, Mentra Miniapp Store, Mentra Miniapp SDK, Mentra Bluetooth SDK, Even Realities, NIMO, GitHub, Discord, Hacker News, MemCards, VisiCalc, etc.
- Technical terms kept in English where that's the natural register for a Vietnamese tech-community readership: SDK, API, miniapp, livestream, open source (rendered as "mã nguồn mở" where the Vietnamese term reads more naturally, e.g. in headings; "open source" kept in a couple of inline technical contexts where that matched the surrounding sentence better), BLE GATT, FFT, ASR, TTS.
- One article ("Phụ đề thời gian thực với MentraOS" / `real-time-captions-with-mentraos`) already had a partially Vietnamese body in the source (an earlier incomplete pass); it was reviewed in full and confirmed already faithful, needing no further changes.
- Deliberate, documented choice: historical dollar-figure references inside article bodies (e.g. "$299", "$8M", "$599") were preserved as journalism/announcement content, not redacted — the WooCommerce catalog-only price ban (Phase 3) applies to live product/catalog pages, not to translating what a historical blog post said about a funding round or a launch price. `templates/source/live.html`'s dormant, unreachable `$449` (flagged in Phase 3) is unrelated and untouched.
- Two small link corrections made during translation (both documented here, not silent): (1) "batch-1-almost-sold-out"'s CTA link originally pointed at the bare homepage with a "Buy" title — repointed to `/mentra-live/`, the real informational page, instead of leaving a vague/dead-feeling link; this does not add any cart/purchase mechanic. (2) "mentra-live-featured-in-engadget..."'s "you're following us" link originally pointed at `/subscribe`, which is not a real route on this site — repointed to `/tin-tuc/`, matching the Phase 2 precedent of fixing dead inbound links (`/get-mentra`) rather than preserving a 404.
- `august-22-community-update`'s source had a malformed nested-domain Discord URL (flagged in `docs/route-map.csv` back in Phase 1); the translation uses the correct `{{HOME_URL}}/discord/` route instead of reproducing the bug.

## Media migration

- Featured images sideloaded into the Media Library from the already-localized `assets/news/` (and two from `assets/` root) theme files Phase 2 put in place — idempotent via `_mentra_vn_source_asset` postmeta, same helper as Phase 3.
- Inline article images (screenshots, secondary photos) are left as direct theme-asset references via `{{THEME_URI}}`, same as the source and same scope decision Phase 3 made for `page-mentra-live.php`'s secondary design image — only the primary/featured image is the high-value dynamic-wiring target.
- Author avatars (`Cayden_Headshot.png`, `me_sf_square.png`) remain static theme assets, not Media Library attachments — decorative, reused across many posts, same reasoning as the inline images above.
- Verified: zero `cdn.shopify.com` references anywhere in migrated post content (`wp_posts.post_content` grep via direct DB query) and zero on the rendered `/tin-tuc/` page.

## Category

- `Bài viết` (slug `bai-viet`), created idempotently via `maybe_create_news_category()` (checks `term_exists()` before creating). All 16 articles assigned; verified `wp_terms` count = 16.
- No `Báo chí` WordPress category was created — press is static data, not a taxonomy term, per instructions.

## Permalink architecture

- Canonical: `/tin-tuc/{slug}/`, implemented via a scoped `add_rewrite_rule('^tin-tuc/([^/]+)/?$', ...)` plus a `post_type_link`/`post_link` filter that only rewrites the permalink for posts carrying `_mentra_vn_article = 1`. Any other/future WordPress Post keeps the site's default permalink structure untouched.
- Rewrite flush is one-time and option-gated (`mentra_vn_news_rewrite_v1`), same pattern as `maybe_create_pages()`'s page-sync flag — `flush_rewrite_rules()` never runs on every request.
- Verified live: all 16 `/tin-tuc/{slug}/` URLs return HTTP 200.

## Legacy redirects

- `mentra_vn_legacy_article_redirect()` (theme `functions.php`, `template_redirect` priority 4) matches any `/blogs/blog/{slug}/` request, looks up a published post by `_mentra_vn_legacy_slug` postmeta, and 301-redirects to `/tin-tuc/{slug}/`. Runs before `page.php`/`404.php` would otherwise try the static-source fallback, so no public route depends on `templates/source/blogs/blog/*.html` anymore.
- Built from postmeta lookup (data-driven), not 16 hardcoded conditionals.
- Verified live: all 16 legacy URLs return `301` with the correct `Location` header; no redirect loops (canonical URLs resolve directly to `200`, never re-redirect).
- `templates/source/blogs/blog/*.html` (16 files) are kept on disk as **REFERENCE / DORMANT** — not deleted, not linked from any live route.

## News listing

- `page-tin-tuc.php` (new, WordPress-native, uses `page-{slug}.php` template hierarchy) fully replaces the static 16-card source for `/tin-tuc/`.
- Articles: `WP_Query` against `post_type=post`, `category_name=bai-viet`, `posts_per_page=-1` — no hardcoded cards.
- Press: `mentra_vn_press_items()` from the static data file.
- "Tất cả" tab: articles and press items normalized into one array and sorted descending by timestamp (`usort`), matching the source's chronological intent rather than "16 then 6".
- Tab/search behavior required **zero JavaScript changes** — `mentra.js`'s existing `initNewsroomFilters()` is fully generic, driven entirely by the `data-newsroom-item`, `data-newsroom-kind`, `data-newsroom-filter`, and `data-newsroom-search` attributes the source markup already used; the dynamic template just reproduces that exact contract.
- Card visual markup (image, badge, title, excerpt/headline, date, CTA) reproduced verbatim from the source's own class names/structure — this is completing/wiring the existing design, not a redesign.

## Press static architecture

- `wp-content/themes/mentra-vietnam/data/press.php` → `mentra_vn_press_items()`: 6 items (publisher, headline, date, external URL, logo image), matching `docs/news-migration-manifest.md` exactly.
- Press cards route through `target="_blank" rel="noopener noreferrer"` directly to the external URL — never through `single.php`, never a WordPress Post.
- Gizmodo intentionally excluded as a 7th item (logo-strip mention only, not a Newsroom card in the source — Phase 1's finding, reconfirmed).
- Verified live: `SELECT COUNT(*) FROM wp_posts WHERE post_title = '<publisher>'` → 0 for all 6 publisher names; 0 non-`post` post types with those titles either.

## SEO

- One canonical URL per article (`/tin-tuc/{slug}/`); the legacy URL always 301s, never serves duplicate content.
- Vietnamese title on every post; `post_excerpt` set explicitly (not auto-truncated) for every article.
- Correct publish date preserved from the original source `<time datetime>` values (`post_date`/`post_date_gmt`).
- Featured image present on all 16 (verified: zero missing thumbnails).
- No fabricated SEO metadata — Yoast fields are left to Yoast's own defaults (derived from title/excerpt), matching Phase 3's conservative approach; nothing hand-authored here.

## Tests

- `php -l`: 100% pass across every theme and plugin PHP file (full sweep, not just changed files) — before and after all changes.
- `node --check`: `mentra.js` passes (not modified this phase — the existing newsroom-filter JS is fully data-attribute-driven and needed no changes).
- **Route tests** (live HTTP, `http://mentra-vn.local/`):
  - `/tin-tuc/` → 200.
  - All 16 `/tin-tuc/{slug}/` → 200.
  - All 16 legacy `/blogs/blog/{slug}/` → 301, `Location` header verified pointing at the matching canonical URL.
  - Regression: `/`, `/mentra-live/`, `/mentra-os/`, `/trong-kinh/`, `/even-realities/`, `/nimo/`, `/lien-he/`, `/tuyen-dung/`, `/ho-tro/` all still 200; `/shop/`, `/cart/`, `/checkout/` still 302 (Phase 2/3 WooCommerce-route redirects untouched).
- **Counts**: `/tin-tuc/` rendered output shows `Tất cả` = 22, `Báo chí` = 6, `Bài viết` = 16 (grepped directly from the tab badges and from `data-newsroom-item`/`data-newsroom-kind` attribute counts — 22 total items, 16 `blogs`, 6 `news`).
- **DB-level verification** (one-off `wp-load.php` bootstrap script, deleted after use — same controlled approach Phase 2/3 used, no WP-CLI available in this environment):
  - Exactly 16 posts with `_mentra_vn_article = 1`.
  - Idempotency stress test: `maybe_import_mentra_articles()` called 5x back-to-back → post count stays at 16 every time, no duplicates.
  - Self-heal test: deleted one article, cleared the "done" flag, re-ran → recreated exactly the missing one, reached 16/16, flag correctly set (see the bug/fix writeup above).
  - Zero `cdn.shopify.com` in migrated post content.
  - Zero press items present in `wp_posts` under any post type.
  - `Bài viết` category term count = 16.
  - Every article has: `_mentra_vn_article` marker, matching `_mentra_vn_legacy_slug`, a featured thumbnail, the `Bài viết` category, Vietnamese characters present in the body, and a `/tin-tuc/{slug}/` permalink.
- **Manual content verification**: all 16 translated articles reviewed against their English source paragraph-by-paragraph for faithfulness (not just spot-checked) before being written into `data/mentra-articles.php`; `docs/news-migration-manifest.md` rows all marked `VERIFIED` accordingly.
- **No Shopify CDN reintroduced**: recursive check across theme/plugin PHP and the migrated post content confirms zero `cdn.shopify.com` references.
- **A CSV-integrity near-miss caught before commit**: while updating `docs/route-map.csv`, a first update script assumed the `#` column's array index matched its row position 1:1. That assumption was wrong — row `4a` (`/live (price)`) is a non-integer sub-row, shifting every subsequent array index by one relative to the `#` label. The first script consequently edited the wrong rows (corrupted `/apps`, mis-converted the press-variant row into a fake "article" row, and skipped the 16th real article entirely). Caught by diffing against `git show HEAD:docs/route-map.csv` before treating the file as final; the file was restored from git and re-edited using a version of the script that matches rows by their actual `Source Route`/`Source File` content instead of positional index, then re-verified (18 rows changed, all with correct content, `/apps` confirmed untouched, all 16 article rows and both `/blogs*` rows confirmed correct). This is the same category of CSV lesson documented in `docs/phase-2-report.md` — recorded again here for visibility since it recurred with a different root cause (label irregularity, not quoting).

## Deferred work

- Reading-time labels ("N phút đọc") that appeared in some source articles were deliberately not carried over — not required by the gate criteria, and computing/storing them would be scope creep for a field the design doesn't strictly need.
- Inline article images and author avatars remain static theme-asset references rather than being sideloaded into the Media Library — a deliberate scope decision (see Media migration above), matching the same secondary-asset precedent Phase 3 set for `page-mentra-live.php`.
- No manual browser/visual QA performed — all verification is HTTP-response, source-level, and direct WordPress/DB object inspection via one-off CLI bootstrap scripts, same method as every prior phase (no headless browser tooling available/added).
- Yoast SEO fields are left at their computed defaults; no phase has hand-authored Yoast metadata for any content type yet.
- WP-CLI is still not available in this environment; all admin/DB verification continues to use one-off, deleted-after-use `wp-load.php` bootstrap scripts run through Local's bundled PHP CLI binary.
