# Phase 8 — Final QA / Pre-Production Audit — Report

Branch: `qa/pre-production` (from `fix/forms-captcha-500` HEAD, after owner approval of the Final Forms/Purchase/Email Hotfix).

## Summary

Full technical QA pass across routing, WooCommerce, Purchase flow, Forms, reCAPTCHA/rate-limiting, News, SEO (locale, metadata, sitemap), WordPress defaults, accessibility basics, JS syntax, safe technical debt, secrets, and Git hygiene. Found and fixed several real, previously-undiscovered defects (all confirmed live, not from static reading alone) — none of them touch owner-approved visual design or the Purchase/Forms flow the owner already signed off on. No new business features added. **TECHNICAL PASS** — see gate checklist at the end.

## 1. Route/redirect regression (before and after asset cleanup)

Full crawl of every route in the brief plus the complete `docs/route-map.csv` inventory: 47 primary/article routes, 8 WordPress/WooCommerce default routes, 4 legal stub redirects, 2 WooCommerce duplicate-product redirects, 16 legacy `/blogs/blog/{slug}/` redirects, and (new, see §3) 24 English-slug alias redirects.

- **47/47 primary + article routes: `200`** (both before and after the `assets/media/` removal — re-verified after the local dev server was restarted mid-phase).
- **All redirects correct**: WooCommerce defaults → home (`302`), legal stubs → canonical (`301`), duplicate product URLs → canonical (`301`), all 16 legacy article paths → canonical (`301`).
- **`/hello-world/`, `/sample-page/`: genuine `404`** (confirmed still trashed from Phase 2, not resurrected).
- Zero unintended `500`, zero unintended primary-route `404`.

### Asset-404 verification after `assets/media/` removal (explicit requirement)

Crawled every primary route plus `/so-sanh/`, `/mang-xa-hoi/`, `/ve-mentra/`, `/mentra-notes/`, `/nimo/`, `/discord/` (pages most likely to reference team/press/partner/social imagery), extracted every `src`/`href`/`poster`/`srcset` pointing at `wp-content/themes/mentra-vietnam/assets/` (116 unique URLs), and issued a `HEAD` request against each. **116/116 resolve `200`.** (One apparent `404` in the raw crawl was a false positive from an un-decoded `&amp;` in a filename containing a literal `&` — confirmed the real, correctly-encoded URL resolves `200`.) No page, CSS, JS, video, image, poster, or gallery reference broke from the removal — every real production asset lives only in `assets/`, `assets/media/` was a genuine, fully unreferenced 44MB duplicate (confirmed both by code search and by this live crawl).

## 2. Real defect: English-alias soft-200 duplicate content (fixed)

**Root cause.** `404.php`'s fallback (`mentra_vn_get_source_key()`'s raw-path branch) renders ANY `templates/source/*.html` file at `HTTP 200` if the URL path matches a key in `mentra_vn_source_map()` — including the map's English-alias keys (`support`, `contact`, `about`, `os`, `live`, `devs`, `apps`, `compare`, `prescriptions`, `captions`, `notes`, `careers`, `socials`, `partnerships`, `media-inquiries`, `privacy-policy`, `terms-of-service`, `shipping-policy`, `refund-policy`, `accessibility`, `recalls`, `blog`, `blogs`, `get`), which were never meant to be real, separately-indexable routes — only the Vietnamese-slug versions are supposed to be real Pages. Because this fallback runs on a `WP_Query` that already resolved to a 404, WordPress's `is_404()` conditional stays true even after the template's manual `status_header(200)` override — so **Yoast correctly suppresses all robots/canonical/OG output on these pages** (it thinks it's a real 404), while the **body still renders full, real duplicate content** and the `<title>` falls back to a literal "Page not found". Net effect: a `200` page with duplicate content, no SEO signal, and a title that says the opposite of what actually loaded. Also found: `/tai-ung-dung/` (the real app-download page, added post-Phase-4.6) was itself only ever reachable through this exact fallback — never a real published Page — so it had the identical bug on its own canonical URL. And two further aliases (`privacy`, `quyen-rieng-tu`) resolved to a source file that's byte-identical to the homepage (a known original-capture anomaly, `docs/site-audit.md`), meaning those paths served full duplicate homepage content.

**Fix.**
1. `tai-ung-dung` added to `create_pages()` (`mentra-vietnam-core.php`) — now a real published Page, same rendering (`page.php` → `mentra_vn_render_source('get')`), correct title/meta.
2. `privacy` and `quyen-rieng-tu` removed from `mentra_vn_source_map()` — both now correctly `404`. The real privacy policy is unaffected (`chinh-sach-quyen-rieng-tu` → `privacy-policy` source key, untouched).
3. New `mentra_vn_source_alias_redirects()` (`functions.php`, hooked to `template_redirect`) — the remaining 23 English-alias paths now `301`-redirect to their canonical Vietnamese-slug Page instead of rendering a duplicate.

Verified live, all 24: correct `301`/`404` target, confirmed both before and after the dev-server restart.

## 3. Real defect: SEO locale mismatch (root-caused and fixed)

Confirmed live: `<html lang="vi-VN">` (already correct, via a `language_attributes()` regex patch) but Yoast's `og:locale` = `en_US` and JSON-LD `inLanguage` = `en-US` on every page. Root cause: the site's actual WordPress locale (`WPLANG` option) was still empty (= `en_US`) — the old fix only patched the *rendered HTML attribute*, not the underlying locale WordPress itself resolves and that Yoast reads directly. New `Mentra_Vietnam_Core_99::maybe_set_site_locale()` sets the real site locale to `vi` (idempotent, self-healing on `init`, equivalent to an admin choosing "Tiếng Việt" at Settings → General). No `.mo` translation files are needed — every user-facing string on this site is custom theme/plugin-rendered Vietnamese content, not core WP i18n strings. Verified live on 7 representative pages (homepage, Mentra Live, Infinity Cable, MentraOS, `/tin-tuc/`, one article, `/lien-he/`): `html lang="vi-VN"`, `og:locale="vi_VN"`, `inLanguage="vi"` — fully consistent everywhere, before and after the dev-server restart.

## 4. Real defect: duplicate/system routes leaking into the Yoast sitemap (fixed)

`page-sitemap.xml` listed 8 pages that only ever exist to redirect elsewhere at runtime (4 legal stub slugs + WooCommerce's `shop`/`cart`/`checkout`/`my-account`) — the runtime redirect was already correct, but Yoast had no signal not to list them (it only respects its own noindex meta, not ad-hoc `template_redirect` hooks). Separately, `product-sitemap.xml` listed `/shop/` again via a *different* code path (the product post-type archive entry, built from Yoast's `noindex-ptarchive-{post_type}` option, not per-post meta) — and `product_cat-sitemap.xml` listed `/product-category/uncategorized/`, which turned out to be a live, unfixed defect on its own (§5).

**Fix:** `maybe_noindex_duplicate_pages()` sets `_yoast_wpseo_meta-robots-noindex` on the 8 duplicate pages (same established pattern already used for the WooCommerce product duplicate-URL noindexing). `maybe_noindex_product_archive()` sets Yoast's native `noindex-ptarchive-product` option. `maybe_noindex_product_category_archive()` sets Yoast's native `noindex-tax-product_cat` option (this site has no plan for a public category-browsing UI at all). All three idempotent, self-healing on `init`.

Verified live: `page-sitemap.xml` 29→29 entries (the 8 duplicates gone, all real content intact), `product-sitemap.xml` and `product_cat-sitemap.xml` both empty (both real products' own duplicate WC URLs were already correctly noindexed from an earlier phase; their canonical pages live in `page-sitemap.xml`). Re-confirmed after the dev-server restart.

## 5. Real defect: WooCommerce default "Coming Soon" content publicly live (fixed)

A background sub-agent's read-only audit (legacy-dependency search, WP-defaults check, tech-debt review, secret scan, git hygiene — full findings folded into this report) surfaced that `/product-category/uncategorized/` was reachable at `HTTP 200`, titled "Uncategorized Archives", rendering WooCommerce's stock English "Great things are on the horizon... Our store is in the works and will be launching soon!" placeholder — on an otherwise fully-Vietnamese, non-ecommerce site. Root cause: both real products are filed under WooCommerce's default "Uncategorized" `product_cat` term (no real category taxonomy exists). Individual product URLs were already correctly redirected/canonical and never leaked this. Fixed: `mentra_vn_disable_woocommerce_public_routes()` now also redirects `is_product_taxonomy()` to home (`302`, same treatment as `/shop/`), plus the sitemap fix in §4. Verified live: `302 → /`.

## 6. Real defect: generic gallery `alt` text (fixed)

Same sub-agent audit confirmed every Mentra Live and Infinity Cable gallery/hero image rendered the identical `alt="Mentra Live"` / `alt="Infinity Cable"` regardless of which actual photo it was (frame, charging cable, case, etc. all announced the same string to screen readers). Root cause was in the two page templates (`page-mentra-live.php`, `page-mentra-live-charging-cable.php`), not the product-creation code as `docs/project-status.md` previously assumed — each attachment already has a distinct, real `post_title` from `sideload_theme_asset()` (e.g. "Mentra Live - khung kính"), the templates just never read it. Fixed: both templates now prefer the real `_wp_attachment_image_alt` meta if set, else the attachment's own title, else a safe generic fallback. Verified live: Mentra Live's 5 gallery images now render 4 distinct alt strings (hero + featured share "Mentra Live", the rest genuinely per-image); Infinity Cable's 2 images now render 2 distinct strings.

## 7. Real defect: broken homepage `<title>` and missing meta descriptions (fixed)

Confirmed live: the WordPress site title (`blogname`) was still the literal install slug `"mentra-vn"`, never set to a real name — every page's `<title>` ended in `"- mentra-vn"`, and the homepage's own title (built from Yoast's `%%sitename%% %%page%% %%sep%% %%sitedesc%%` template, with both `%%page%%` and the empty tagline slot blank) rendered as the literally broken `"mentra-vn -"`. No page anywhere had a `<meta name="description">`. Two pages (`/lien-he/`, `/tin-tuc/`) leaked broken placeholder text into `og:description` instead (traced to Yoast's automatic description-fallback reading each WP Page's disconnected `post_content` field directly — this site's real visible content lives in `templates/source/*.html`, entirely separate from `post_content`; `/lien-he/`'s `post_content` literally still contained an unprocessed, never-registered `[mentra_contact_form]` shortcode remnant from before the current architecture existed).

**Fix** (`maybe_set_site_identity()`, `maybe_set_page_meta_descriptions()`, both idempotent/self-healing):
- `blogname` → "Mentra Việt Nam" (the project's own real name, per `CLAUDE.md`/every doc in this repo — not invented).
- `blogdescription` and the homepage's Yoast description → the homepage's own real hero-copy tagline, copied verbatim from `templates/source/index.html`.
- Six priority pages that had no/broken descriptions (`/lien-he/`, `/tin-tuc/`, `/mentra-live/`, `/products/mentra-live-charging-cable/`, `/mentra-os/`, `/even-realities/`) now get a real Yoast meta description, each copied verbatim from that exact page's own already-published intro copy or heading — no invented marketing claims. `/ve-mentra/` already had a correct one (untouched); the 16 real WordPress Posts already correctly auto-generate from their own real `post_content` (unaffected, unchanged).

Verified live on all 7: correct `<title>`, real `<meta name="description">`, real `og:description`, no leaked placeholder text. Note: the homepage `<title>` tag is now correct but somewhat long (site name + full tagline, per Yoast's own default home-title template) — cosmetic only, not a functional defect; left as Yoast's stock behavior rather than customizing the title template further (out of scope for a root-cause fix).

## 8. WooCommerce catalog-only regression

`/mentra-live/`, `/products/mentra-live-charging-cable/`: no `$`/price markup, no `class="price"`, no add-to-cart/checkout markup. Stock labels correct ("Còn hàng"/"Hết hàng" per Mentra Live color variant, "Còn hàng" for the cable). "Liên hệ mua hàng" CTA present and correctly wired (see §9). `/shop/`, `/cart/`, `/checkout/`, `/my-account/`, `/product-category/uncategorized/` all still redirect to home. No regression.

## 9. Purchase flow (Mentra Live + Infinity Cable) — re-verified, no regression

Full nonce-scoped tamper-test matrix was already verified live in the prior session (owner-approved). This phase re-ran smoke tests after the dev-server restart for both products: render → correct locked product/nonce/context → valid submission `200` success → tamper (product swap, same nonce) → `403`, no mail. Both pass identically to the owner-approved baseline. jsdom re-check of the rendered Purchase form DOM (Mentra Live): 0 JS errors, correct heading/badge/fields/labels/CAPTCHA/success copy — no regression from any Phase 8 change.

## 10. Forms spot-check (General/Support/Partnership/Media/Career) + reCAPTCHA + rate limiting

All five re-submitted live through the real pipeline (nonce + reCAPTCHA + rate limiting unbypassed): `200` success, mail sent, each with the correct scoped nonce for its own context. Missing-CAPTCHA-token case: `400`, fails closed, no mail. Rate-limit threshold re-verified on a fresh bucket: attempts 1–4 succeed, attempt 5 onward `429` — matches the documented 5-per-10-minute sliding window exactly. Newsletter not touched, not re-tested (out of this phase's scope per the brief — its own nonce/pipeline is independent and was unaffected by anything changed this phase).

## 11. News (16 Posts + 6 Press)

`/tin-tuc/` tab counts confirmed live: Tất cả = **22**, Bài viết = **16**, Báo chí = **6** — exact match. `post-sitemap.xml` lists exactly 16 article URLs (plus Yoast's own homepage-first-entry convention, not a defect). All 16 canonical `/tin-tuc/{slug}/` routes `200` (§1). All 16 legacy `/blogs/blog/{slug}/` routes `301` to canonical (§1). No duplicate public article routes.

## 12. SEO metadata — final state (priority pages)

| Page | Title | Description | Canonical | OG title/desc | Twitter card |
|---|---|---|---|---|---|
| Homepage | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| Mentra Live | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| Infinity Cable | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| MentraOS | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| Even Realities | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| Về Mentra | Already OK | Already OK | OK | Already OK | OK |
| Tin tức | Fixed (§7) | Fixed (§7) | OK | Fixed (§7) | OK |
| 16 articles | Already OK (real post_content) | Already OK | OK | Already OK, incl. `og:image` | OK |

## 13. Sitemap / indexability — final verification

`sitemap_index.xml` lists 5 sub-sitemaps. `page-sitemap.xml`: 29 canonical content pages, 0 duplicate/system routes. `post-sitemap.xml`: 16 articles. `product-sitemap.xml`, `product_cat-sitemap.xml`: both empty (both real products' WC URLs already noindexed from an earlier phase; the shop archive and uncategorized-category archive now noindexed this phase). `category-sitemap.xml`: 1 entry (`bai-viet`), correct. `robots.txt`: unchanged, correctly disallows WooCommerce log/transient paths and `add-to-cart` query strings, allows everything else, points at the sitemap index.

## 14. WordPress defaults

`/hello-world/`, `/sample-page/` genuinely `404` (trashed, confirmed still trashed). "Uncategorized" WP *post* category redirects to home (unchanged from Phase 2). WooCommerce "Uncategorized" *product* category — see §5 (new finding, fixed). No default WooCommerce demo/dummy product exists (confirmed via the Store API by the sub-agent audit — only the two real products present).

## 15. Responsive / visual regression

**No browser/screenshot tooling is available in this environment** (consistent with every prior phase's documented limitation) — reporting that honestly rather than claiming pixel-level verification. What was verified: no CSS was added anywhere this phase (only dead/unreferenced rules removed — `.logo-for-light`/`.logo-for-dark`, confirmed zero markup usage before removal); the removed `assets/media/` directory is confirmed unreferenced by any CSS/HTML/PHP; the asset-404 crawl (§1) confirms every image/video/CSS/JS reference the CSS actually depends on still resolves. No owner-approved frontend markup was redesigned. A real-browser pass at 1440/1024/768/390/375 by the owner is recommended before production, same standing recommendation as every prior phase.

## 16. JS interaction checks

`node --check` on `mentra.js`: pass. jsdom re-verification of the Purchase form (§9): 0 runtime errors, all interactive elements (locked product badge, Phone/Address fields, reCAPTCHA container, success card) present and correctly wired. No JS was changed this phase beyond what was already verified in the prior hotfix session — mega-menu, mobile drawer, galleries, PDP accordions, FAQ, social hover, scroll-reveal, News filters, Newsletter are all unmodified since their own last verification and were not touched this phase.

## 17. Accessibility basics

Homepage/Mentra Live/Infinity Cable/`/lien-he/`/`/tuyen-dung/`: 0 `<img>` without `alt`, 0 empty `<button></button>`, heading order single H1 with no skipped levels, all `target="_blank"` links carry `rel`. `aria-expanded` present on interactive menu/accordion controls. `aria-live`/focus-management on form success states confirmed via jsdom (§9), unchanged from the owner-approved hotfix. Gallery alt text fixed (§6). No WCAG certification claimed — clear, low-risk defects only.

## 18. Safe technical debt — resolved this phase

| Item | Action |
|---|---|
| `assets/media/` (44MB duplicate, confirmed unreferenced) | **Removed** (105 files) |
| `.logo-for-light`/`.logo-for-dark` dead CSS (confirmed 0 markup usage) | **Removed** (6 rule blocks) |
| Unreferenced `shopping-bag.svg`/`shopping-bag-green.svg` | **Removed** |
| Orphaned `assets/js/main.js` (never enqueued/referenced) | **Removed** |
| Generic WooCommerce gallery `alt` text | **Fixed** (§6) |

## 19. Safe technical debt — deferred (documented, not fixed)

| Item | Reason |
|---|---|
| Fragile `*.mp4@v=N` filenames (`Herosectionvideo-trimmed.mp4@v=3`, `mobile_hero_smol1-trimmed.mp4@v=2`) | Cosmetic filename risk only, not a functional bug; renaming requires updating the hardcoded template reference — low value for the risk of touching an owner-approved homepage hero, deferred |
| 2 homepage hero videos missing `poster` | No `ffmpeg` (or any video-frame-extraction tool) available in this environment to generate a real first-frame poster; forcing a mismatched placeholder image risks a visible flash inconsistent with the owner-approved homepage, so this was deliberately **not** patched with a low-quality substitute. Needs either `ffmpeg` availability or the owner supplying real poster stills. |
| `templates/source/live.html`'s dormant `$449` | Re-confirmed genuinely unreachable — `/mentra-live/` is served by `page-mentra-live.php`, which never calls `mentra_vn_render_source()`/this file. No action needed. |

## 20. Secret scan

No real secrets found in any tracked file. All password/secret/api_key/smtp/token pattern matches are variable/option **names**, UI labels, or narrative documentation. The reCAPTCHA key visible on live pages is Google's own published v2 test key pair (documented non-secret, consistent with project history) — the secret key is only ever read via `get_option()`, never hardcoded. `wp-config-sample.php` (WordPress core's own stock boilerplate, not the real config) is the only "password"-adjacent tracked file, and it's the standard placeholder template, not a real credential. **No SMTP credentials printed, inspected in detail, or modified this phase** — WP Mail SMTP's configuration was not touched.

## 21. Git hygiene

`git ls-files` clean: 348 tracked files, no `.env`, no DB dumps, no `node_modules/`, no `debug.log`, no leftover one-off diagnostic scripts (the project's established pattern of temporary webroot scripts was used twice this phase — one locale-check, one SEO-metadata-check — both deleted immediately after use, confirmed absent from `git status`). `.gitignore` adequate (excludes `wp-config.php`, logs, uploads, vendor plugins).

## 22. Syntax / logs

Full `php -l` sweep across `wp-content/themes/mentra-vietnam` and `wp-content/plugins/mentra-vietnam-core`: **all pass**. `node --check` on `mentra.js`: **pass**. `wp-content/debug.log`: does not exist (no PHP warnings/fatals logged during any of this phase's live testing).

## 23. Production configuration (owner/infra actions, not blocking)

- **HTTPS**: not applicable locally; required in production (standard hosting/CDN concern).
- **`WP_DEBUG=false`, `display_errors` off**: not verified as a code change this phase (out of scope — `wp-config.php` is gitignored/environment-specific); confirm on the production host before launch.
- **Backups**: production hosting concern, not evaluated here.
- **Production reCAPTCHA keys**: still Google's public test key pair on this install (Phase 6/7 finding, unchanged) — owner must enter real production keys at Settings → Mentra Việt Nam before launch.
- **Production mail provider / real From Email / real contact recipient**: WP Mail SMTP still points at the owner's personal Gmail relay for testing (Phase 7/hotfix finding, unchanged, not touched this phase); `mentra_vn_contact_email` is still the owner's personal test address. Both need a real production decision before launch — see `docs/mail-configuration.md`.
- **SPF/DKIM/DMARC**: production DNS, not applicable to a local install — documented requirement carried from Phase 7.
- **Permalink flush/check**: not needed this phase (no rewrite-rule structure changed, only `template_redirect`/query-var-level redirects and one new Page).
- **Yoast sitemap**: verified clean (§13) — no action needed beyond what's already fixed.
- **Cache purge**: N/A locally; standard pre-launch step on the real host.
- **Security headers**: hosting-dependent, not evaluated in this local environment.

Full detail: `docs/production-readiness.md`.

## 24. Legal — LEGAL ACTION (unchanged, not touched this phase)

Privacy/Terms/Shipping/Refund body content still requires Vietnam-specific human legal review before any body-text change (carried from Phase 2). Not touched this phase — routing for these pages was already correct and untouched.

## 25. Files changed this phase

- `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php` — `maybe_set_site_locale()`, `maybe_noindex_duplicate_pages()`, `maybe_noindex_product_archive()`, `maybe_noindex_product_category_archive()`, `maybe_set_site_identity()`, `maybe_set_page_meta_descriptions()`; `tai-ung-dung` added to `create_pages()`; page-sync version bumped to v4.
- `wp-content/themes/mentra-vietnam/functions.php` — `mentra_vn_source_alias_redirects()` (new); `privacy`/`quyen-rieng-tu` removed from `mentra_vn_source_map()`; `mentra_vn_disable_woocommerce_public_routes()` extended with `is_product_taxonomy()`.
- `wp-content/themes/mentra-vietnam/page-mentra-live.php`, `page-mentra-live-charging-cable.php` — real per-image `alt` text.
- `wp-content/themes/mentra-vietnam/assets/css/mentra.css` — removed 6 dead `.logo-for-light`/`.logo-for-dark` rules.
- Removed: `assets/js/main.js`, `assets/shopping-bag.svg`, `assets/shopping-bag-green.svg`, `assets/media/` (105 files, ~44MB).
- `docs/route-map.csv`, `docs/project-status.md`, `docs/phase-8-report.md` (this file), `docs/production-readiness.md`.

## 26. Phase 8 gate

| # | Requirement | Result |
|---|---|---|
| 1 | Zero intended HTTP 500 routes | **PASS** |
| 2 | Zero unintended primary 404 | **PASS** |
| 3 | Zero static soft-404 | **PASS** (the English-alias soft-200-duplicate-content class fixed, §2) |
| 4 | Primary navigation links work | **PASS** |
| 5 | Woo catalog-only rules intact | **PASS** (§8, plus new uncategorized-archive fix, §5) |
| 6 | Purchase flows intact | **PASS** (§9) |
| 7 | 16 Posts + 6 press intact | **PASS** (§11) |
| 8 | Forms intact | **PASS** (§10) |
| 9 | reCAPTCHA/rate-limit intact | **PASS** (§10) |
| 10 | No frontend secret exposure | **PASS** (§20) |
| 11 | No tracked real credentials | **PASS** (§20/21) |
| 12 | SEO locale fixed or documented | **FIXED at root cause** (§3) |
| 13 | Canonical/indexability correct | **PASS** (§4, §13) |
| 14 | Syntax checks pass | **PASS** (§22) |
| 15 | No serious frontend regression | **PASS** (§1 asset check, §9, §15/16/17) |
| 16 | Production blockers documented | **PASS** (§23, `docs/production-readiness.md`) |

**TECHNICAL PASS.** SMTP production credentials/domain configuration and Vietnam legal approval are explicitly non-blocking per the gate definition (owner action / legal action, both documented).
