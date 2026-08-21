# Phase 2 Report — Static Site Stabilization

Date: 2026-08-21
Branch: `fix/static-pages` (based on `audit/site-inventory`)
Scope: static/non-blog/non-commerce/non-form-backend route stabilization only. Does not touch WooCommerce catalog, product import, price/stock, WordPress Post import, the 16 blog articles, reCAPTCHA, new form backend architecture, or SMTP.

## Git

- Repo initialized fresh in this phase (`git init` — no prior repository existed).
- Branch `audit/site-inventory`: one commit, the Phase 1 audit docs only.
- Branch `fix/static-pages`, created from `audit/site-inventory`, 10 commits:
  1. `chore: add .gitignore for WordPress project`
  2. `fix: restore Mentra Live and MentraOS routes` (P0)
  3. `fix: publish missing static routes`
  4. `fix: resolve legal page slug conflicts`
  5. `fix: resolve /get-mentra CTA across static pages`
  6. `fix: set Vietnamese document language`
  7. `fix: localize external theme assets`
  8. `fix: remove obsolete public routes`
  9. `fix: localize static page content (Vietnamese translation cleanup)`
  10. `chore: track remaining theme baseline files`
- Working tree: clean after each commit; verified via `git status --short` before every commit. No `git reset --hard` / `git clean -fd` / `git restore .` / force-push used at any point.
- Nothing pushed to GitHub (not instructed to).
- `origin` remote: not configured (no push was attempted or requested).

## Fixed P0

`/mentra-live/` and `/mentra-os/` were returning real HTTP 500 (`Call to undefined function mentra_vn_asset()`). Root cause: the function was never defined anywhere in the theme or plugin — only the unrelated, plural `mentra_vn_assets()` (an asset-enqueue hook) existed.

Fix: defined `mentra_vn_asset($path)` in `functions.php` as a minimal URL-building helper. No redesign — both pages' existing markup, copy, and layout are untouched.

Secondary finding while fixing this: both pages called `get_header()`/`get_footer()`, which resolve to the theme's *minimal fallback* `header.php`/`footer.php` (logo only, no nav — a second, thinner "header system" than the one used by every other page on the site, flagged in the Phase 1 audit). Left as-is, these two important product pages would have loaded with no navigation at all once the fatal error was fixed. Extracted the real site header/footer (the same markup every other page uses, "filled" variant matching non-transparent interior pages) into reusable `templates/parts/site-header.html` / `site-footer.html`, rendered via a new `mentra_vn_render_partial()` helper, and switched both pages to use them instead.

Verified via HTTP + source inspection: both routes return 200, correct `<title>`, no `error404` body class, full desktop nav / mobile menu / footer nav present, no PHP warnings/notices, no new entries in `wp-content/uploads/wc-logs/fatal-errors-*.log` after the fix.

## Routing changes

Root cause of the soft-404 pattern (documented in Phase 1): the theme's `mentra_vn_source_map()` renders static content for ~50 slugs, but the plugin's `create_pages()` — which creates the actual WordPress Page each slug needs — only ran once, on plugin *activation*, and several needed slugs were missing from its list entirely. When no WP Page exists for a mapped slug, WordPress falls through to `404.php`, which recognizes the slug via the same map and renders the content anyway at HTTP 200 with `<title>Page not found</title>` and `error404` body class — a soft-404.

Fix, in `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php`:
- Added `maybe_create_pages()`, hooked to `init` and gated by an option flag (`mentra_vn_pages_synced_v2`), so page sync is self-healing on any future request rather than depending solely on a one-time activation hook. `create_pages()`/`page()` were already idempotent (skip existing slugs) — verified no duplicate pages are created on repeated requests.
- Added the missing `'Legacy MentraOS' => 'legacy'` entry (the only slug missing from the list among the 12 target soft-404 routes).
- Removed `'Quyen rieng tu' => 'quyen-rieng-tu'` — this slug maps to the `privacy` source key, which the Phase 1 audit found is byte-identical to the homepage in the captured source (a broken/misrouted wget capture, not real content). Per instructions, `/privacy/` was **not** recreated; it remains a soft-404 and always will unless a real Privacy Policy is written for that slug specifically.

Routes fixed (soft-404 → real 200, verified via `curl` — title, body class, and content all correct):
`/nha-phat-trien/`, `/discord/`, `/doi-tac/`, `/kha-nang-tiep-can/`, `/mang-xa-hoi/`, `/mentra-notes/`, `/phu-de/`, `/thu-hoi/`, `/truyen-thong/`, `/nimo/`, `/even-realities/`, `/legacy/`, plus the 4 canonical legal slugs (`/chinh-sach-quyen-rieng-tu/`, `/dieu-khoan-dich-vu/`, `/chinh-sach-van-chuyen/`, `/chinh-sach-doi-tra/`).

`/privacy/` and `/quyen-rieng-tu/` deliberately left as soft-404 (anomaly, not recreated, per instructions).

## Redirects added

All implemented as `template_redirect` hooks in `functions.php` (no `.htaccess` rewrites — kept in WordPress-native PHP so behavior stays visible/auditable in code, not scattered across two systems):

| From | To | Type | Why |
|---|---|---|---|
| `/chinh-sach-bao-mat/` | `/chinh-sach-quyen-rieng-tu/` | 301 | Was a separately-published placeholder-stub page competing with the canonical slug |
| `/dieu-khoan/` | `/dieu-khoan-dich-vu/` | 301 | Same pattern |
| `/van-chuyen/` | `/chinh-sach-van-chuyen/` | 301 | Same pattern |
| `/doi-tra/` | `/chinh-sach-doi-tra/` | 301 | Same pattern |
| `/get-mentra` | `/lien-he/?topic=sales` | 301 | True 404 (old Shopify-era CTA target), no ecommerce checkout exists |
| `/shop/`, `/cart/`, `/checkout/`, `/my-account/` | `/` (home) | 302 | Default WooCommerce routes, out of scope (no checkout). 302 (temporary) rather than 301 — `/shop/` in particular may become a real public catalog listing in a later product phase, and a 301 would be harder to reverse (browser/search-engine caching) |
| Author archives (`/author/*/`), `/category/uncategorized/` | `/` (home) | 302 | Default WordPress archives with no real content; also avoids publicly exposing usernames via author archives |

## Static translation changes

Per `docs/translation-audit.md`, translated remaining visible English on: homepage (2 strings — SVG chart title/desc), about (2 — "Advisors" heading, one advisor's role), compare (7 — table headers, stats, FAQ tab labels), contact (9 — dropdown options, sidebar headings, form placeholders), support (6 aria-labels), devs (1 sentence fragment), discord (2 — "Community", "Join Discord" ×2 instances), media-inquiries (2 — eyebrow + heading), captions (2 — button label, stat), mentra-notes (7 — hero heading/paragraph, CTA, 2 feature headings, 2 more sentence fragments found during verification that Phase 1 had missed), socials (3 — hero heading/subheading ×2 responsive variants each, filter label), legacy (1 — eyebrow label), nimo (3 — spec-table field labels), even-realities (3 — heading, sentence, button label). Also fixed one inconsistent placeholder on careers.html (`jane@example.com` → `email@vidu.vn`) for consistency with the rest of that form, though translation wasn't the primary issue there.

**Correction to the Phase 1 audit**: `accessibility.html` was found, on direct inspection, to already be fully translated to Vietnamese — the Phase 1 live-crawl audit's finding that it was "100% English" appears to have been in error (possibly reading a cached or different render). No changes were needed. One phrase (`"như thanh toán Shopify"` — an example use of a third-party payment service in the body text) is content-accuracy-flagged for the eventual legal/copy review, not a translation issue — left untouched as instructed.

**Investigated and closed**: the Phase 1 audit flagged `/mang-xa-hoi/` as a possible duplicate-content render bug (the hero text appeared twice in a plain-text extraction). Confirmed on inspection this is **not** a bug — it's the same legitimate mobile/desktop responsive split used elsewhere on the site (one block wrapped `md:hidden`, the other `hidden md:grid`), analogous to the 3-copy header logo pattern already documented as correct. Both variants were translated identically.

**Not touched** (explicitly out of scope):
- The 16 blog article bodies (`templates/source/blogs/blog/*.html`) — Phase 4.
- The 4 legal policy documents' body content (privacy-policy, terms-of-service, shipping-policy, refund-policy) — still the untranslated, Shopify/Stripe-era US source text. Only their *routing* was fixed this phase (see above). **This content is not approved for Vietnam and needs legal review before launch.**
- The site-wide social-icon aria-labels (`Follow Mentra on X/Instagram/LinkedIn`, `opens in new tab`, etc.) duplicated identically across most of the 47 static templates — deferred as LOW severity per the audit, with disproportionately high edit cost (same string in up to 47 files) relative to its impact (screen-reader-only text). Two of the newly-created `templates/parts/*.html` partials (used only by the 2 P0 pages) had their equivalents translated already, incidentally, while extracting that markup.

## Asset localization

- Fixed the one confirmed broken local asset: `templates/source/notes.html` referenced `assets/app_icons/Mentra_Ghi chu.png` (does not exist — a partial rename never applied to the actual file); now points to the real `assets/app_icons/Mentra_Notes.png`.
- Downloaded and localized all 33 images still hotlinked from `cdn.shopify.com` into a new `wp-content/themes/mentra-vietnam/assets/news/` folder (~35 MB), and repointed every reference across `blog.html`, `blogs.html` (news listing thumbnails), all 16 blog article pages (hero images + author avatar), and `Legacy.html` (TestFlight/APK download badges — these say "Download on TestFlight" / "Download the Android APK", not the standard App Store/Google Play badges, so they were kept as their own distinct localized images rather than swapped for the theme's unrelated `assets/badges/apple_badge.svg` / `google_play_badge.png`).
- All downloaded filenames are already ASCII-safe (English/numbers/hyphens/underscores); kept as-is rather than renamed, since renaming ~33 files across 19 referencing templates for no functional benefit would add risk without value.

## Routes removed/disabled

- `/shop/`, `/cart/`, `/checkout/`, `/my-account/` — 302 to home (see Redirects table above). WooCommerce plugin itself **not** removed/deactivated — still installed for the future catalog-only phase.
- `/category/uncategorized/`, all `/author/*/` archives — 302 to home.
- `/hello-world/` (default post), `/sample-page/` (default page) — **trashed** (not permanently deleted) via a one-time `wp-load.php` bootstrap script (no WP-CLI in this environment). Both routes now correctly return a real 404. The script was deleted after running; it is not part of the theme/plugin.
- wp-admin authentication verified unaffected: `/wp-login.php` and `/wp-admin/` both still reachable and functioning after all redirect logic was added.

## Remaining English

Full detail in `docs/translation-audit.md` (superseded in part by the fixes above — treat the "fully translated" / "not translated" lists there as a Phase-1-time snapshot). As of this phase:
- 16 blog article bodies — deferred to Phase 4 (explicit instruction).
- 4 legal policy documents' body text — deferred, needs legal review, not a translation task alone.
- Site-wide social-icon `aria-label` text (`Follow Mentra on X`, etc.) across most of the 47 static templates — deferred, LOW severity, high edit cost for the benefit.
- Default WooCommerce "coming soon" copy on `/shop/`/`/cart/` themselves — moot now that both routes redirect to home before that content ever renders; not translated since the goal was removing the route from the public flow, not localizing content that shouldn't be public.

## Remaining Shopify/CDN references

Re-ran `grep -rl "cdn.shopify.com"` across the entire theme and plugin after all asset localization work: **zero matches**. No `cdn.shopify.com` references remain anywhere in `wp-content/themes/mentra-vietnam/` or `wp-content/plugins/mentra-vietnam-core/`.

Remaining **non-CDN** external Mentra-domain references (`docs.mentraglass.com`, `console.mentraglass.com`, `apps.mentraglass.com`, `mailto:support@mentraglass.com`) are unchanged — these are legitimate external Mentra properties outside this WordPress migration's scope, as documented in the Phase 1 audit, not Shopify/CDN dependencies.

## Deferred to Product Phase

- WooCommerce catalog implementation (products, SKU, gallery, attributes, variations, stock status).
- Removing price ($449 on Mentra Live) and building the "Liên hệ mua hàng" CTA pattern site-wide for products.
- `/shop/` potentially becoming a real public catalog listing (currently redirects to home — see Redirects table).

## Deferred to News Phase

- Importing the 16 blog articles as real `post_type=post` entries (architecture gap noted in Phase 1: `single.php` already exists and works, but nothing feeds it yet).
- Translating the 16 article bodies.
- Rebuilding the `/tin-tuc/` listing as a real `WP_Query` loop instead of static HTML (the index itself already correctly shows 16 posts + 6 press = 22 under "Tất cả", matching the business rule — only the underlying data model needs to change).
- Legacy blog URLs to redirect during that migration: all 16 are currently `/blogs/blog/{slug}/` (soft-404 today, fixed by this phase's routing infra being extended, or by real posts existing at the same path). No URL structure change is anticipated, so no redirect mapping should be needed — noted here in case the eventual post import chooses different final slugs.

## Deferred to Forms Phase

- reCAPTCHA v2 — not implemented (not required for Phase 2 pass).
- Career application backend — still non-functional (no JS handler, no server-side AJAX action registered anywhere). Page rendering/UI is not broken (the page returns 200, form displays correctly) — only submission does nothing. Left as-is per instructions ("may remain functionally pending... must not break page rendering/UI" — confirmed not broken).
- Unifying all form recipients to a single `contact@domain.vn` — Contact form currently still routes via `sales_email`/`support_email` split in plugin settings, unchanged this phase.
- `Reply-To` header CRLF-injection hardening on the contact form (flagged in Phase 1 security pre-audit) — not fixed, tracked for the forms-hardening phase.

## Tests

- `php -l` against every `.php` file in the theme (9 files) and plugin (1 file): 100% pass, before and after all changes.
- `node --check` against both theme JS files (`mentra.js`, `main.js`): both pass (neither was modified).
- Live HTTP verification (via `curl`, both status code and body content) for every route touched this phase:
  - P0: `/mentra-live/`, `/mentra-os/` → 200, correct title, full nav/footer present, no `error404` class, no fatal-error log entries after the fix.
  - 12 formerly-soft-404 static routes + 4 canonical legal slugs → 200, no `error404` class.
  - 4 legal stub redirects, `/get-mentra` → correct 301 + `Location` header.
  - `/shop/`, `/cart/`, `/checkout/`, `/my-account/`, `/category/uncategorized/`, all author archives → 302 to home.
  - `/hello-world/`, `/sample-page/` → 404.
  - `/wp-login.php`, `/wp-admin/` → still reachable (auth unaffected).
  - Previously-working routes (`/`, `/ve-mentra/`, `/ho-tro/`, `/lien-he/`, `/trong-kinh/`, `/so-sanh/`, `/tuyen-dung/`, `/ung-dung/`, `/tin-tuc/`) → still 200, no regressions.
  - Header/footer consistency spot-check across `/mentra-live/`, `/mentra-os/`, `/nha-phat-trien/`, `/nimo/`, `/legacy/`: all show the full `site-header--filled` nav (desktop rail + mobile menu button), full footer nav, no cart-icon markup, Red Hat Display stylesheet enqueued.
  - Automated re-scan (Node script, common English function words) across all 15 translated pages after editing: only 1 false positive (a CSS `min-width` media query matching "with").
- No manual browser/visual testing was performed (no headless browser tooling used this phase) — all verification is HTTP-response and source-level. Recommend a manual visual pass (desktop hover menu behavior in particular — see below) before considering this fully signed off.

## Remaining issues

- **Desktop mega-menu / hover-dropdown behavior**: the Phase 1 audit flagged this as unverified via static crawl — the nav renders as a flat 5-item list with `aria-haspopup="true"` but no dropdown panel markup was found in the static HTML. This phase did not add browser/interaction testing tooling, so this remains **unverified**, not confirmed broken. Flagged for a manual browser check before Phase 2 is considered fully closed on the UX side (does not block the Phase 2 gate criteria as written, which are about routing/status codes/language, not interaction testing).
- `assets/media/` (44 MB byte-for-byte duplicate of `assets/`, confirmed unreferenced by any code) — still present, not deleted. Out of scope for "static page stabilization"; flagged again here as a safe cleanup candidate for a future housekeeping pass.
- Two video files with fragile `*.mp4@v=N` filenames (`Herosectionvideo-trimmed.mp4@v=3`, `mobile_hero_smol1-trimmed.mp4@v=2`) — not renamed this phase (low risk, not blocking, would need care to update the 2 referencing templates simultaneously).
- 2 homepage hero videos still missing `poster` attributes — cosmetic, not fixed this phase.
- Dead CSS (`.logo-for-light`/`.logo-for-dark`), 2 unreferenced `shopping-bag*.svg` files, stale Hydrogen-era code comments in `mentra.css`/`mentra.js`, and the orphaned unused `assets/js/main.js` — all still present, none removed this phase (cosmetic/housekeeping, no functional impact, `main.js` in particular deliberately left alone since it's dead code that does no harm and touching it wasn't requested).
- `.htaccess` and `wp-config-sample.php` are now tracked in git (baseline commit) but were not otherwise modified.
