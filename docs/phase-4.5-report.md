# Phase 4.5 Report — Visual Fidelity Repair

Date: 2026-08-21
Branch: `fix/visual-fidelity` (based on `feature/news-posts`)
Trigger: owner-reported major visual fidelity regressions on `/mentra-live/` and `/mentra-os/`, discovered manually in a browser.
Scope: `/mentra-live/`, `/mentra-os/`, `/mang-xa-hoi/`. No Phase 5 (Forms) work performed.

## Root cause

Not missing CSS. `page-mentra-live.php` and `page-mentra-os.php` were hand-authored against an invented classname vocabulary (`product-page-hero`, `split`, `cards-3`, `feature-card`, `spec-grid`, `section-black/green/soft`, `class="reveal"`) that exists **only** in `assets/css/main.css` — a file **never enqueued anywhere** in `functions.php`. Both pages rendered essentially unstyled, browser-default HTML with an oversimplified DOM (Mentra Live had 1 combined `<section>` vs 2 real ones with a full product-detail grid; MentraOS had 4 sections vs 8 real ones, with an incorrect black hero). Full section-by-section evidence: `docs/visual-fidelity-audit.md`.

## Git

- Branch `fix/visual-fidelity` created from the completed `feature/news-posts` baseline (working tree was clean, verified before starting).
- Commits, in order:
  1. `docs: audit visual fidelity regressions`
  2. `fix: restore source-accurate Mentra Live layout and charging section`
  3. `fix: restore Socials page motion effects`
  4. `fix: restore source-accurate MentraOS layout`
  5. `fix: remove frontend WooCommerce style interference`
- **Commit-structure note**: the suggested 7-message list split Mentra Live's structure/charging/effects into 3 separate commits. Because those changes live inside one cohesively-rewritten `page-mentra-live.php` (a single-pass rebuild, not three independent edits) and share the same new CSS block, splitting them further would have required manual patch-hunk surgery with real risk of producing a broken intermediate commit. They're combined into one commit that represents one coherent, working, testable unit — matching the spirit of "small logical commits" (each commit compiles and passes the full route/regression matrix) rather than a literal 1:1 message mapping. The Mentra Live/Socials/MentraOS CSS *is* still cleanly separated per page/concern across commits 2–4 (verified via `git diff --stat` on each commit before committing).
- Working tree clean after every commit. No `git reset --hard` / `git clean -fd` / `git restore .` / force-push used. **Nothing pushed to GitHub.**

## Mentra Live structural rebuild

Rebuilt against the real WGET `product-detail-section` structure (see audit doc for full section table):
- Image gallery + 5–6 thumbnails, **dynamically wired to the WooCommerce product's featured image + gallery images** (`get_image_id()` / `get_gallery_image_ids()`), falling back to the same static theme assets only if the product record is ever missing.
- Buy-box (mobile + desktop duplicate, matching source): title, **stock status in place of `$449`**, description, single **"Liên hệ mua hàng" CTA in place of the quantity stepper + Order Now button**, "As seen in" press-logo row, spec table (static facts + SKU + **per-variation stock rows appended dynamically** from live WooCommerce data).
- "Giới thiệu Mentra Live" copy block.
- Three accordions (Features with 3 embedded videos, Specifications, FAQ) using a new `.pdp-accordion-item` component (CSS grid-template-rows collapse, same technique as the existing `.home-faq-answer`) + `initPdpAccordion()` in `mentra.js`.
- "Đổi trả & bảo hành" block between the Specifications and FAQ accordions, linking to the real `/chinh-sach-doi-tra/` page — **flagged inline with a code comment for Vietnam-specific legal review**, per CLAUDE.md; not silently rewritten.
- Newsletter signup using the existing `data-mentra-newsletter="1"` / `initNewsletter()` AJAX contract — zero JS changes needed.

## Charging section (confirmed bug, fixed)

The desktop mega-menu (`mentra.js`) has always linked to `/mentra-live/#charging`. Neither the WGET source nor the theme's dormant `templates/source/live.html` ever had an `id="charging"` anywhere — the real Infinity Cable/Charging Case content lives in a `.charge-row` block on the **homepage**, not the product page (CSS for `.charge-row`/`.charge-col` already existed in `mentra.css`, built for that homepage block). Decision: consolidated that real block (verbatim Vietnamese copy and markup) onto `/mentra-live/` with `id="charging"` and `scroll-margin-top: calc(var(--height-nav) + 24px)` so the anchor clears the fixed ~60px header — matching the site's existing single-page catalog IA rather than inventing unrelated content. Verified live: `grep -o 'id="charging"'` on the rendered page, and a linked FAQ answer inside the page itself now also points to `#charging`.

## MentraOS structural rebuild

Rebuilt against the real 8-section WGET order and backgrounds (light hero → light compatibility grid → soft MiniApp Store → light overview list → light apps grid → gradient SDK panel → the one genuinely dark section, download promo → light newsletter) — the previous black-hero/green-SDK pattern did not match the source at all. New components: 5-card compatibility grid (Mentra Live, Even Realities G1 & G2, Vuzix Z100, NIMO "Sắp hỗ trợ"), MiniApp logo grid + phone mockup, 5-row numbered "Tổng quan MentraOS" list, 4-app grid, SDK code panel (GitHub-dark palette, real syntax-highlighted TypeScript sample), and a dark download-promo banner — this component (`green-grid-promo`/`os-download-promo*`) never had any CSS anywhere in the theme before this phase; authored new, page-scoped.

**Translation-quality bugs found and fixed while porting** (present in the dormant `templates/source/OS.html`, not introduced by this phase, but would have shipped live if copied verbatim): a "Tổng quan MentraOS" answer mixing untranslated English sentences and a broken "Nhà phát triểns" plural artifact — rewritten cleanly in Vietnamese; the SDK code sample's `data` parameter had been auto-translated to `của bạn`, which would have broken the TypeScript sample — restored to valid code (source code stays in English; only prose is translated).

## Mentra Live/MentraOS effects

Both pages' `class="reveal"` (dead markup — zero CSS rules, zero JS binding) replaced with the real `data-mentra-reveal` attribute throughout, so hero/section entrances now use the site's actual scroll-reveal system.

## Socials effects

Layout was already correct (confirmed, matches owner's own assessment). The reported missing effects traced to a **site-wide** bug, not a Socials-specific one: every `data-mentra-reveal` element's static source HTML carries a baked-in inline `style="opacity:1;transform:none;transition:none"` — a leftover artifact of the original Hydrogen/Framer Motion capture (the wget snapshot caught the DOM post-animation) — which out-specifies the external `[data-mentra-reveal]` CSS rule (an inline style always wins over a same/lower-specificity stylesheet rule without `!important`). `initReveal()` in `mentra.js` was already firing correctly; the reveal was a structural no-op everywhere it's used, site-wide, not just on Socials. Fixed by adding `!important` to the reveal rule and its `.is-visible` counterpart in `mentra.css` — a global, one-file fix, safe because it only completes an already-designed system that was already in correct use on every page (cannot regress anything that wasn't visually animating before).

## WooCommerce CSS/JS interference

Confirmed via live HTTP (curl against rendered `<link>`/`<script>` tags) that **every** public route — homepage, `/tin-tuc/`, `/mentra-live/`, `/lien-he/`, not just the two rebuilt pages — was loading `woocommerce-general.css`, `woocommerce-layout.css`, `woocommerce-smallscreen.css`, `wc-blocks.css` (handle `wc-blocks-style`), plus two order-attribution marketing/tracking scripts (`sourcebuster-js`, `wc-order-attribution`). None of these are needed — this site never renders a WooCommerce shop/cart/checkout template to a visitor. Dequeued in `functions.php`, scoped to `!is_admin()`. One handle (`wc-blocks-style`) required a second dequeue call on `wp_head` priority 20, because WooCommerce Blocks' `Notices::enqueue_notice_styles()` unconditionally re-enqueues it on every `wp_head` regardless of the earlier `wp_enqueue_scripts` dequeue — documented in the code comment at both dequeue sites. `coming-soon` was checked and was never actually present in the enqueued handle list on this install (nothing to dequeue there; included defensively in case it's registered by a future WC version). Verified: wp-admin's Products screens are untouched (the dequeue never fires under `is_admin()`), and no WooCommerce data/API call enqueues frontend assets in the first place.

## Phase 3 / Phase 4 regression check

- Mentra Live still reads SKU/stock/gallery images live from the real `MENTRA-LIVE` WooCommerce product (verified: stock labels rendered — 4× "Còn hàng", 2× "Hết hàng" — matching the live per-variation data, not hardcoded).
- No price, quantity input, or Add to Cart anywhere on `/mentra-live/` or `/mentra-os/` (verified via case-insensitive grep for `$449`, `add-to-cart`/`add_to_cart`, `name="quantity"` — zero matches on both pages).
- `/shop/`, `/cart/`, `/checkout/` still 302-redirect to home (Phase 2/3 hardening untouched).
- `/tin-tuc/` still 200, 16 canonical articles unaffected (spot-checked one legacy-slug article route), news architecture untouched — no changes were made to any Phase 4 file.
- `/`, `/trong-kinh/`, `/even-realities/`, `/nimo/`, `/tuyen-dung/`, `/ho-tro/`, `/lien-he/` all still 200.

## SEO secondary finding (documented, not fixed — out of phase scope)

`html lang` is correctly `vi-VN` (Phase 2), but Yoast's JSON-LD/OpenGraph output still reports `og:locale = en_US` / `inLanguage = en-US` on at least the routes spot-checked in this phase. Not fixed here per instructions (avoid a broad Yoast refactor unless small and clearly safe — this needs Yoast's own locale-mapping filter investigated, which wasn't scoped for this phase). **Added to deferred/QA documentation in `docs/project-status.md`.**

## Tests

- `php -l`: 100% pass across the full theme + plugin tree (not just changed files), verified after every commit.
- `node --check`: `mentra.js` passes after the `initPdpAccordion()` addition.
- **Routes**: `/mentra-live/` → 200, `/mentra-live/#charging` → `id="charging"` confirmed present in the rendered HTML, `/mentra-os/` → 200, `/mang-xa-hoi/` → 200.
- **Regression routes**: `/`, `/tin-tuc/`, `/lien-he/`, `/trong-kinh/`, `/even-realities/`, `/nimo/`, `/tuyen-dung/`, `/ho-tro/` all 200; `/shop/`, `/cart/`, `/checkout/` still 302.
- **Business-rule checks** (case-insensitive grep on rendered HTML): zero `$449` / price patterns, zero `add-to-cart`/`add_to_cart`, zero `name="quantity"` on either rebuilt page.
- **WooCommerce CSS/JS dequeue**: zero `woocommerce-*`/`wc-blocks-style` `<link>` tags on `/mentra-live/`, `/tin-tuc/`, `/lien-he/`, homepage — verified after both the initial dequeue and the `wp_head` follow-up dequeue.
- No PHP fatals observed: `wp-content/debug.log` is not present/enabled on this install, so verification relied on every route returning a full 200 response with expected content (a fatal error would surface as a 500 or a truncated/blank body — neither occurred on any tested route).

## Manual browser verification

**No Playwright or other browser-automation tooling is available in this environment** (checked: `npm ls -g`, `require.resolve('playwright')`, `where chrome`/`where msedge` — none found). Per the phase instructions, this means Phase 4.5 **cannot claim a full visual PASS** and is reported as **TECHNICAL PASS / VISUAL VERIFICATION PENDING** (see gate section below). What was verified instead:
- Full HTTP/DOM-level verification (structure, classnames, dynamic data, business-rule absence/presence) as detailed above.
- Direct comparison of every restored section's markup against the immutable WGET source, section by section (`docs/visual-fidelity-audit.md`).
- Confirmed the exact CSS rules a browser would apply are now present for every classname used (spot-checked via grep against `mentra.css`/`utilities.css` for the handful of new BEM classes, and relied on `utilities.css`'s compiled-Tailwind coverage — already scanned against this exact source HTML — for every utility class reused verbatim from the source).

**Not verified**: actual rendered pixel layout, spacing, image aspect ratios, responsive breakpoint behavior (1920/1440/1024/768/390/375), hover states, accordion open/close animation smoothness, and reveal-on-scroll timing/easing as experienced in a real browser. **The owner or project manager should open `/mentra-live/`, `/mentra-live/#charging`, `/mentra-os/`, and `/mang-xa-hoi/` in an actual browser at desktop (1440px) and mobile (390px) width before this phase is considered visually closed.**

## Remaining visual differences (known, documented)

- Gallery thumbnail `alt` text for WooCommerce-sourced images is generically "Mentra Live" for all 5 images (the WC gallery doesn't carry distinct per-image alt text), whereas the source's static fallback set has distinct alts ("Gọng kính", "Hộp sạc", etc.). Minor accessibility-quality gap, not a visual regression — noted as non-blocking debt.
- Accordion open/close and reveal-on-scroll timing/easing values were copied from the source's own inline transition values and the existing `.home-faq-answer`/`[data-mentra-reveal]` patterns, but have not been visually confirmed to feel identical to the original in a real browser.
- Press-logo "As seen in" row on Mentra Live links to external English-language articles (Forbes, GamesBeat, etc.) — kept as informational/credibility content per source, not translated (external publication names/URLs, out of scope to alter).
- No new manual/automated screenshot diffing tool was introduced this phase.

## Phase 4.5 gate

| # | Criterion | Status |
|---|---|---|
| 1 | Mentra Live substantially matches WGET source structure/design | PASS (structural/markup level) |
| 2 | Missing Mentra Live sections restored | PASS |
| 3 | `#charging` exists and works | PASS |
| 4 | Infinity Cable section visually exists | PASS (markup/CSS level) |
| 5 | Mentra Live WC stock remains dynamic | PASS |
| 6 | No price/cart/checkout reintroduced | PASS |
| 7 | MentraOS substantially matches WGET source structure/design | PASS (structural/markup level) |
| 8 | Source-equivalent MentraOS effects restored | PASS (markup level — `data-mentra-reveal` wired) |
| 9 | Socials layout remains correct | PASS (unchanged, confirmed) |
| 10 | Missing Socials effects restored | PASS (root cause fixed) |
| 11 | CSS is page-scoped where appropriate | PASS (`.mentra-vn-mentra-live`/`.mentra-vn-mentra-os` body-class scoping; reveal fix is intentionally global — see rationale above) |
| 12 | Unnecessary WC styling interference resolved/documented | PASS |
| 13 | Desktop visual verification completed | **NOT DONE** (no browser tooling available) |
| 14 | Mobile visual verification completed | **NOT DONE** (no browser tooling available) |
| 15 | No Phase 3 regression | PASS |
| 16 | No Phase 4 regression | PASS |
| 17 | PHP/JS syntax passes | PASS |

**Phase 4.5 status: TECHNICAL PASS / VISUAL VERIFICATION PENDING.** All structural, data-wiring, business-rule, and regression criteria pass. Criteria 13–14 (actual browser visual verification) are explicitly outstanding and require a human (or future browser-automation tooling) to confirm before this phase is fully closed.

**Current status (2026-08-22)**: superseded by Phase 4.6 (Phase 4.5's own CSS values were guesswork, later replaced with verified real values), and the overall frontend visual-fidelity milestone is now OWNER APPROVED / COMPLETE following owner + Antigravity finishing work — see `docs/project-status.md`. Kept here as a historical record only.
