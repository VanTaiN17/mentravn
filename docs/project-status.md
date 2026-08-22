# Mentra Vietnam — Current Project Status

Last updated: 2026-08-22
Current stable phase: Phases 1–7 all PASS/COMPLETE. Forms Hotfix + its UX/branding/acknowledgement-email extension RESOLVED/COMPLETE, but with one known remaining security gap (see "Current Forms Hotfix — Known Remaining Issue" below). **The immediate next task is a scoped hotfix, NOT Phase 8** — see "Next Task" below.
Current branch: `fix/forms-captcha-500`.
Latest commit: `d1410c4` — `docs: document branding, locked context, success state, and acknowledgement email`.
Working tree: clean.
Branch lineage: `fix/forms-captcha-500` → `feature/mail-delivery` (Phase 7) → `feature/recaptcha-security` (Phase 6) → `feature/forms-architecture` (Phase 5) → `fix/full-visual-fidelity` (owner-approved frontend).
Ahead of `feature/mail-delivery`: 5 commits (`3684c59`, `8f36b96`, `60cb7ef`, `b03d348`, `d1410c4`).
Upstream/tracking: `fix/forms-captcha-500` has **no upstream** — never pushed. `feature/mail-delivery`, `feature/recaptcha-security`, `feature/forms-architecture` are also local-only (created but not pushed). `fix/full-visual-fidelity` and five other earlier branches (`audit/site-inventory`, `fix/static-pages`, `feature/woocommerce-catalog`, `feature/news-posts`) already have matching `origin/*` refs from an earlier authorized push — those remain the only branches on GitHub.
Git remote: `origin` = `https://github.com/VanTaiN17/mentravn.git` (canonical, only remote configured).
Push status: **nothing pushed this session or since** — do not push without explicit owner authorization.

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
Status: **OWNER APPROVED / COMPLETE.** After Phase 4.6, the owner completed the remaining frontend visual work directly together with Antigravity (a separate coding tool) and explicitly approved the resulting frontend state — this supersedes the earlier "VISUAL VERIFICATION PENDING" status; owner confirmation is authoritative regardless of whether every pixel was independently re-verified by Claude. 20 additional commits on `fix/full-visual-fidelity` (`6f25c70`..`9aa4d5b`), reviewed and kept as-is per instruction not to reopen approved visual work absent a technical breakage. Notable changes: Mentra Live gallery thumbnail switching + layout alignment fixes, Even Realities icon/typography fixes, Socials hover polish, a new `/tai-ung-dung/` (app download) page replacing the old `/get-mentra` → sales-contact redirect with a real download page, MentraOS logo sizing, comparison-page (`/so-sanh/`) FAQ interactivity + price removal from CTAs, captions-page (`/phu-de/`) FAQ content/styling, and several `MENTRA_VN_THEME_VERSION` cache-bumps (final: `3.14.0`). Reviewed for secrets/debug code/hardcoded local paths (none found) and re-validated `php -l`/`node --check` (both clean) before this handoff; a mechanical cleanup commit (`chore: strip stray UTF-8 BOM from static-source HTML files`) was added on top. A subsequent route smoke-test (done during Phase 5, once the local site was back up) confirmed no regression from this pass. **Frontend page-building milestone is now closed.**

### Phase 5 — Forms Architecture
Status: PASS. Built the backend for all six business-contact form types (General, Sales, Support, Partnership, Media, Career) plus reviewed Newsletter (unchanged). Unified recipient routing to one configurable option, `mentra_vn_contact_email` (default `contact@domain.vn`, editable at Settings → Mentra Việt Nam), replacing the old `sales_email`/`support_email` split. Added `career_ajax()` — the Career page (`/tuyen-dung/`) previously had no backend at all. Rewrote `contact_ajax()` to whitelist the `#contact-subject` dropdown's fixed values into a form type (rejecting anything unrecognized) instead of the old fragile keyword-matching. Fixed a genuine pre-existing routing bug: `/lien-he/?topic=sales` and `/lien-he/?topic=support` were silently ignoring the query string and always rendering plain `/lien-he/` — now they serve the matching pre-selected static file that already existed on disk unused. Server-generated mail subjects (never client-supplied), CRLF/header-injection hardening on Reply-To, per-field max lengths, no new database/CPT for contact submissions (mail-only, as required). Full detail: `docs/phase-5-report.md`, `docs/forms-inventory.md`.

### Phase 6 — reCAPTCHA v2 + Anti-Spam Security
Status: PASS. Added Google reCAPTCHA v2 Checkbox (never v3/Invisible/Enterprise) to all seven public forms (the six business-contact types + Newsletter), a centralized fail-closed server-side verification pipeline (`enforce_recaptcha()`/`verify_recaptcha_token()`), and WP-transient rate limiting (5 attempts / 10-minute sliding window, per `wp_hash()`-salted client identity + whitelisted form type, no custom DB table, no raw IP ever persisted). New admin-configurable `mentra_vn_recaptcha_site_key`/`mentra_vn_recaptcha_secret_key` options — secret never reaches the frontend. reCAPTCHA is a no-op (forms still work) when unconfigured, with an admin-only warning notice; once configured, every failure mode (missing token, failed/malformed Google response, HTTP transport error) fails closed. The Google script loads only on routes that genuinely render a protected form, confirmed by a real per-page content audit (`mentra_vn_page_has_protected_form()`) — found and correctly excluded `/even-realities/`, `/nha-phat-trien/`, `/trong-kinh/`, whose Newsletter footer form is genuinely absent from their source HTML. Phase 5's recipient/subject/Reply-To hardening/field whitelists are untouched. No SMTP, no new CRM/database, no visual redesign beyond the widget itself. Full test matrix (7 valid-captcha cases, 4 failure modes, rate-limit threshold + type-scoping, security-order, secret-exposure) run live against the local site. Full detail: `docs/phase-6-report.md`, `docs/form-security.md`.

### Phase 7 — WP Mail SMTP Integration + Delivery Verification
Status: **IMPLEMENTATION PASS / DELIVERY CONFIGURATION PENDING** (not a code failure — see below). Audited WP Mail SMTP (v4.9.0, installed and active) and found it not yet pointed at a real provider: mailer is still native PHP `mail()`, no SMTP/API credentials configured for any mailer, and From Email is a leftover local-environment placeholder (`dev-email@wpengine.local`). Audited every `wp_mail()` call in the Mentra plugin and confirmed the existing header architecture already matches the recommended design — only `Content-Type` and a Reply-To built from the validated visitor email/name are ever set, never a From header — so **no code change was needed or made this phase**. Verified live through the real AJAX pipeline (nonce + Phase 6 reCAPTCHA/rate-limiting fully live, unbypassed): all six business forms reach `wp_mail()` with the correct recipient (`contact@domain.vn`), correct Vietnamese UTF-8 subject prefixes/body, and correct Reply-To; missing/failed CAPTCHA and rate-limiting all still correctly block before `wp_mail()`. Also investigated and cleared the two external mobile-menu commits Phase 6 flagged (`02b9bcb`, `c24432a`) — confirmed theme/frontend-only, no Forms/Security/Mail code touched, no secrets/debug code, kept as-is. Real external mail delivery is **not yet configured or verifiable** — that is an owner wp-admin action (provider selection + credentials + DNS), documented in `docs/mail-configuration.md`. Full detail: `docs/phase-7-report.md`.

### Forms Hotfix — reCAPTCHA invisible + AJAX 500 (RESOLVED)
Status: **RESOLVED.** After Phase 7, the owner began real WP Mail SMTP delivery testing and hit two real, reproduced issues. (1) A genuine code bug: `ensureRecaptchaWidget()` in `mentra.js` used `form.insertBefore(container, submit)`, which requires the submit button to be a *direct* child of the form — true for Contact/Career but not for Newsletter (button nested in a wrapper `<div>`), so it threw on Newsletter's own widget injection. Because every `init*()` call shared one unguarded `DOMContentLoaded` chain, that throw silently prevented `initContact()`/`initCareer()` from running at all on any page that also has the Newsletter footer form (nearly every page) — explaining why the reCAPTCHA widget never appeared and why Contact/Career's AJAX wiring wasn't attached. Fixed (`submit.parentNode.insertBefore(...)`) and hardened (each initializer now runs isolated via a `runInit()` wrapper, so one throwing can never again silently disable unrelated ones). (2) A live WP Mail SMTP configuration mistake (not a code issue): the SMTP Host field had been set to an email address instead of a real hostname, making `wp_mail()` genuinely fail — which Mentra's existing, already-correct Phase 5 failure handling reported as a controlled `HTTP 500` JSON error, exactly per spec (confirmed zero PHP warnings/fatals via a temporary local-only `WP_DEBUG_LOG`, reverted after diagnosis). Corrected (already-known Gmail SMTP host). Also found and corrected: the owner's "recipient" test was actually only touching WP Mail SMTP's From Email field, never Mentra's own `mentra_vn_contact_email` — set that option (DB only, never in source) to the owner's own already-known address so their real-delivery test actually reaches an inbox they can check. Empirically disproved (not just by inspection) the theory that saving the recipient setting clears reCAPTCHA options — the three settings are fully independent `register_setting()` calls. Full six-form + Newsletter matrix re-verified live after the fix, including rate-limit/CAPTCHA-failure/`wp_mail()`-forced-false negative paths — all correctly controlled. Full detail: `docs/forms-hotfix-report.md`.

### Forms Hotfix Extension — Branding, Locked Contact Context, Success State, Acknowledgement Email
Status: **COMPLETE.** Owner-approved UX additions on the same branch. Contact/Career forms now show the existing local Mentra logo (`assets/mentra_logo.svg`, no new asset) plus a context-specific Vietnamese heading. The five contact-style pages (including plain `/lien-he/`, locked to "Chung") no longer show a selectable form-type dropdown — `#contact-subject` is replaced with a non-interactive "Loại yêu cầu" badge, removing the duplicate-choice UX. **Security boundary**: the locked badge is UX only — `Mentra_Vietnam_Core_99::route_locked_type()` independently re-derives the authoritative type server-side from the request's `Referer` (`wp_get_referer()`), which a client-side HTML/hidden-input edit cannot spoof; verified live that a spoofed `subject=Sales` with `Referer: /doi-tac/` is still correctly mailed as Partnership. Falls back to the pre-existing Phase 5 whitelist when Referer is unavailable — never weaker than before. On successful submission the form (and its reCAPTCHA widget) is replaced by a dedicated, focus-managed, `aria-live` success card with a "Về trang chủ" CTA (`home_url('/')`, no hardcoded URL, no auto-redirect); on failure the form stays visible with entered values intact and a new inline error-message element. New feature (no prior implementation existed): every business-form submission now sends an **HTML admin notification** (upgraded from plain text) and, only after that succeeds, a best-effort **HTML customer acknowledgement** sharing the same short reference ID — both using the theme's own logo via `get_template_directory_uri()` (never a hardcoded domain). Verified both failure directions live (admin failure blocks the customer send and reports a controlled error; customer-only failure still reports success). Newsletter is untouched. Full detail: `docs/forms-hotfix-report.md` (extension section).

## Current Forms Hotfix — Known Remaining Issue (READ BEFORE TOUCHING FORMS)

The Forms Hotfix Extension (above) made `Mentra_Vietnam_Core_99::route_locked_type()` — which reads the request's `Referer` header via `wp_get_referer()` — the authoritative server-side source for which of the five contact-style form types (general/sales/support/partnership/media) a submission belongs to, replacing trust in the client-submitted `subject` field. This is a real improvement (verified live: a spoofed `subject=Sales` with `Referer: /doi-tac/` was still correctly classified and mailed as Partnership) and is **not a security hole** — every value it can produce is still one of the six whitelisted form types, so the worst case of a Referer being absent/spoofed by a non-browser client is a mail sent with an imprecise-but-still-legitimate classification, never an arbitrary subject/type.

**However, `Referer` is not a cryptographically sound security boundary** (a non-browser HTTP client can set it to anything) and must not be treated as the final word, especially once a **Purchase** intent/product context is added (see Next Task below), where getting the context wrong has more consequence than a mislabeled subject line. **Do not extend or rely further on Referer-based trust.** The next task replaces it with a scoped nonce bound to the rendered form type/context (and, for Purchase, the product key) — see below.

## Next Task — Final Forms / Purchase / Email Hotfix (owner-approved, NOT Phase 8)

This is the immediate next engineering task once work resumes. It is a scoped hotfix on top of the Forms Hotfix Extension, not the start of Phase 8 — **do not begin Phase 8 work until the owner explicitly approves this hotfix as complete.**

### A. Nonce security (replaces Referer as the authority)

Replace `route_locked_type()`'s Referer-based resolution with a **scoped nonce** that binds to:
- the normalized form type being rendered on that page, and
- for the Purchase flow (below), the purchase intent + product context.

A tampered type/product with a reused/mismatched nonce must fail server-side. Referer may remain as a defense-in-depth signal but must no longer be the deciding factor.

### B. Purchase-specific Sales form

When a visitor clicks "Liên hệ mua hàng" from a supported product, route them into a **Purchase** variant of the Sales form.

Expected URL model: `/lien-he/?topic=sales&intent=purchase&product=<stable-key>`

Support at minimum these stable product keys:
- `mentra-live`
- `mentra-live-charging-cable`

Purchase form fields: locked Product, Full name, Email, Phone, Address, Message/Note, reCAPTCHA. **Do not show Company in Purchase mode.** Plain `/lien-he/?topic=sales` (no `intent=purchase`) remains the normal Sales form and may still show Company.

### C. Product allowlist

Never trust raw `product` query text. Resolve it through a server-side stable-key allowlist (at minimum the two keys above). The product title shown in the UI and in mail must come from that server-approved mapping, never echoed from the raw query value.

### D. Product CTA wiring

Mentra Live's and Infinity Cable's "Liên hệ mua hàng" CTAs must link into Purchase mode using the stable product keys above. Build the URL via `home_url()`/relative paths — never hardcode `mentra-vn.local`.

### E. Purchase emails

Admin purchase email must contain: Product (server-approved name), Full name, Email, Phone, Address, Message, Reference ID, source/time. **Do not show Company.** Customer purchase acknowledgement must name the approved product and confirm the enquiry was received. This is explicitly **not** checkout/order completion — no price, no payment, no order state.

### F. Email logo bug (new finding, not yet fixed)

The current HTML emails (admin + customer) reference the Mentra logo via `get_template_directory_uri()`, which on this **local** environment resolves to `http://mentra-vn.local/...` — a hostname Gmail (and any external mail client) cannot reach, so the image doesn't render in local testing. This is expected on `.local` and will resolve correctly once the site has a real public production domain, but the next task should make this environment-independent: prefer an **email-safe embedded CID image** rather than a remote `<img src>` URL. Prefer a local PNG asset; if only the approved SVG exists, generate an email-safe PNG **derivative** without modifying the original SVG (do not download a new/different logo). Continue using `wp_mail()` for transport; a **scoped PHPMailer hook may be used only to attach/embed the CID image** — do not use it to configure SMTP (WP Mail SMTP owns that, per Phase 7).

## Security requirements carried into the next task (durable — see also CLAUDE.md)

- Referer is not a security boundary on its own.
- Hidden/readonly/disabled frontend fields are never trusted.
- Form type is always normalized + whitelisted server-side.
- The nonce must be bound to the rendered context (type, and for Purchase, product).
- The Purchase product key must be server-approved (allowlist), never raw client text.
- Tampering product/type while reusing a nonce issued for a different context must fail.
- reCAPTCHA v2 Checkbox remains mandatory (never v3/Invisible/Enterprise).
- Rate limiting remains mandatory.
- No raw IP persistence (salted hash only, unchanged since Phase 6).
- No SMTP credentials in source, docs, or Git — ever.

## Mail failure policy (durable, applies to Purchase too)

Admin notification is primary. If the admin `wp_mail()` fails → the submission fails (controlled JSON error) → the customer acknowledgement is never attempted. If the admin send succeeds but the customer acknowledgement fails → the submission is still reported successful to the visitor (the business inquiry was received; never tell the visitor it failed because of a secondary, best-effort email). Newsletter remains fully excluded from customer acknowledgement (it is not a business-contact form and does not call `wp_mail()` at all).

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
10. **Forms backend (Phase 5, PASS; extended by the Forms Hotfix)**: `wp_ajax_mentra_vn_newsletter` (unchanged), `wp_ajax_mentra_vn_contact_ajax` (General/Sales/Support/Partnership/Media), and `wp_ajax_mentra_vn_career_ajax` all route through a shared `send_form_mail()` pipeline in the plugin. All nonce-protected (`mentra_vn_public`). `send_form_mail()` now sends an HTML admin notification plus a best-effort HTML customer acknowledgement (same reference ID), and `contact_ajax()`'s form type is authoritatively resolved server-side from the request Referer (`route_locked_type()`), not a client-submitted field. See `docs/forms-inventory.md`/`docs/phase-5-report.md`, `docs/forms-hotfix-report.md`.
11. **Career form backend exists** (Phase 5) — `/tuyen-dung/` now sends real mail via `mentra_vn_career_ajax`, validated against the form's real fields only.
12. **reCAPTCHA v2 Checkbox is live** (Phase 6) on all seven public forms, admin-configurable at Settings → Mentra Việt Nam (`mentra_vn_recaptcha_site_key`/`_secret_key`). **Currently CONFIGURED on this install** with Google's official public v2 Checkbox test key pair (used for local testing throughout Phases 6/7 and the Forms Hotfix, not a production credential — see `docs/form-security.md` for the no-op-when-unconfigured behavior that applies whenever these are cleared). Server-side rate limiting (WP transients) is likewise live.
13. **Business-contact forms use a single configurable recipient** via `mentra_vn_contact_email` (Phase 5) — the coded default is `contact@domain.vn`, but this option is **currently set to the owner's personal address** on this install for real-delivery testing (see item below and `docs/forms-hotfix-report.md`) — always read the live option value, never assume the default.
14. **WP Mail SMTP is installed and active but not configured for real delivery** (Phase 7) — mailer is still native PHP `mail()`, From Email is a local placeholder (`dev-email@wpengine.local`). Mentra's own `wp_mail()` header architecture (no From header, only Content-Type + Reply-To) already matches the recommended design and needs no further code change. Owner must select a provider and enter credentials in wp-admin — see `docs/mail-configuration.md`. Do not build custom SMTP transport.
15. **Legal-policy bodies** (Privacy/Terms/Shipping/Refund) still require human Vietnam-specific legal review before content changes; routing/slug fixes are fine.
16. **`/tai-ung-dung/` app-download page** (added during the owner/Antigravity finishing pass): a new static-source-mirror page (`templates/source/get.html`, source key `get`), replacing the old `/get-mentra` behavior — that legacy path now 301-redirects to `/tai-ung-dung/` instead of to sales contact. No price/cart content.
17. **Frontend visual page-building milestone is CLOSED**, owner-approved. Any further frontend work should be scoped as a new, deliberate task, not assumed to be still-open Phase 4.x cleanup.
18. **Forms architecture (Phase 5) is CLOSED, PASS.** `docs/forms-inventory.md` is the field-level reference for every form; `docs/phase-5-report.md` has the full test matrix. `/lien-he/`'s `?topic=` query handling was fixed as part of this phase (see item 10 above and the Phase 5 entry in Completed Phases).
19. **Anti-spam security (Phase 6) is CLOSED, PASS.** `docs/form-security.md` is the architecture reference (fail-closed rules, rate-limit semantics, privacy/IP handling); `docs/phase-6-report.md` has the full test matrix. Real production reCAPTCHA keys still need to be entered by the owner before protection is actually active in production — see item 12 above.
20. **Mail delivery (Phase 7) is CLOSED, IMPLEMENTATION PASS / DELIVERY CONFIGURATION PENDING.** `docs/mail-configuration.md` is the owner-facing wp-admin setup guide; `docs/phase-7-report.md` has the full audit/test matrix. Real SMTP/API provider selection, credentials, and production DNS (SPF/DKIM/DMARC) are owner actions — see item 14 above.

## Current important IDs/data

- Mentra Live WooCommerce product ID is install-specific — always look up by SKU (`MENTRA-LIVE`) via `mentra_vn_get_product_by_sku()`, never hardcode.
- Variation SKUs: `MENTRA-LIVE-DEN` (instock), `MENTRA-LIVE-TRONG-SUOT` (outofstock).
- News category: `Bài viết`, slug `bai-viet` — look up by slug, do not hardcode the term ID.
- Article identity meta: `_mentra_vn_article=1`, `_mentra_vn_legacy_slug`, `_mentra_vn_article_authors` (array of `['name','role','avatar']`).
- Press data source: `wp-content/themes/mentra-vietnam/data/press.php` — never query `wp_posts` for press content.
- Contact-form recipient: WordPress option `mentra_vn_contact_email` (default `contact@domain.vn`), editable at Settings → Mentra Việt Nam — always read via `contact_email()` in the plugin, never hardcode the address. **Currently set to the owner's personal address for real-delivery testing** (set during the Forms Hotfix, DB option only, not in source) — replace with the real production recipient before launch.
- reCAPTCHA v2 keys: WordPress options `mentra_vn_recaptcha_site_key` (public) / `mentra_vn_recaptcha_secret_key` (server-only, never expose) — editable at Settings → Mentra Việt Nam. **Currently CONFIGURED** on this install with Google's official public v2 Checkbox test key pair (not a production credential; never written to any file, DB option only). Read via `Mentra_Vietnam_Core_99::recaptcha_enabled()`/`recaptcha_site_key()`, or the theme-layer bridge functions `mentra_vn_recaptcha_enabled()`/`mentra_vn_recaptcha_site_key()` — never hardcode or duplicate this check.

## Current news state (Phase 4 verified)

- 16/16 articles migrated, 16/16 translated, 16/16 verified (`docs/news-migration-manifest.md`).
- 6/6 press items static; 0 press items exist as WP Posts.
- Category `Bài viết` / `bai-viet` — count = 16.
- Zero `cdn.shopify.com` references remain in migrated content.
- Migration is idempotent (stress-tested 5x reruns + simulated partial-failure self-heal, no duplicates).

## Business decisions

- WooCommerce is catalog-data CMS only, forever.
- Only items with a real, confirmed source product object become WooCommerce products.
- Purchase CTA text/target fixed: "Liên hệ mua hàng" → `/lien-he/?topic=sales` (about to gain `&intent=purchase&product=<key>` per the Next Task above — the CTA text itself does not change).
- The 16 articles and 6 press items are permanently separate concepts; press items must never become WordPress Posts.
- Legal policy body content is not to be translated/rewritten without human review.
- Brand/product names are never translated.

### Must-not-regress checklist (re-verify after any forms/purchase work)

- WooCommerce remains catalog-only: no price, no quantity, no Add to Cart, no cart, no checkout — anywhere, including the new Purchase form.
- Mentra Live's stock status and gallery remain dynamically sourced from WooCommerce (`mentra_vn_get_product_by_sku()`), never hardcoded.
- Infinity Cable remains its own real product/accessory page (`/products/mentra-live-charging-cable/`) per the current architecture — not folded back into Mentra Live.
- News remains exactly 16 WordPress Posts + 6 static press items; press items must never become WP Posts.
- Legal content (Privacy/Terms/Shipping/Refund) still requires Vietnam-specific human review before any body-text change.

## Known deferred work

- ~~Contact form recipient unification to a single `contact@domain.vn`~~ — **DONE** in Phase 5 (`mentra_vn_contact_email` option).
- ~~Career application form backend~~ — **DONE** in Phase 5 (`mentra_vn_career_ajax`).
- ~~Google reCAPTCHA v2 + server-side verification~~ — **DONE** in Phase 6 (`enforce_recaptcha()`, fail-closed once configured). Real production keys are still not entered on this install — that's an owner/admin action, not further engineering work.
- WP Mail SMTP configuration — **implementation-side work DONE** in Phase 7 (architecture audited, confirmed correct, no code change needed). Real provider selection + credentials + From Email + production DNS are owner actions, not further engineering work — see `docs/mail-configuration.md`.
- **`contact@domain.vn` is a confirmed placeholder domain**, not yet the final production domain (noted explicitly during Phase 7's mail audit — `domain.vn` was never a real registered domain in this project). Before launch, replace it via the existing `mentra_vn_contact_email` option (Settings → Mentra Việt Nam) once the real production domain is finalized — do not silently invent a replacement in the meantime.
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

**Not Phase 8.** The immediate next task is the scoped "Final Forms / Purchase / Email Hotfix" documented above (nonce-bound context security, Purchase Sales form + product allowlist, product CTA wiring, Purchase-specific emails, CID email logo). Only after the owner explicitly approves that hotfix as complete should Phase 8 (whatever it turns out to be) begin. Beyond that hotfix, remaining known work is owner/admin configuration, not engineering: WP Mail SMTP provider selection + credentials + From Email (Phase 7, `docs/mail-configuration.md`), real reCAPTCHA production keys (Phase 6, Settings → Mentra Việt Nam), the final production domain to replace the `domain.vn` placeholder, and production DNS (SPF/DKIM/DMARC). No local branch in this lineage (`feature/forms-architecture` → `feature/recaptcha-security` → `feature/mail-delivery` → `fix/forms-captcha-500`) has been pushed to origin yet.

**DO NOT START PHASE 8 OR ANY NEW PHASE WITHOUT USER/PROJECT-MANAGER INSTRUCTION.**

## Required reading for next Claude context

1. `CLAUDE.md`
2. `docs/project-status.md` (this file — has the full current state, the known remaining security issue, and the fully-specified next task; sufficient on its own for most next-task work)
3. `docs/forms-hotfix-report.md` (most recent detailed report — root causes, fixes, and the full UX/branding/acknowledgement-email extension, both verified live)

Only read further if the task genuinely needs a specific historical detail:

- `docs/phase-7-report.md` (WP Mail SMTP audit detail), `docs/mail-configuration.md` (owner mail setup guide)
- `docs/form-security.md` (Phase 6 anti-spam architecture reference — fail-closed rules, rate-limit semantics)
- `docs/forms-inventory.md` (field-level reference for every form as of Phase 5)
- `docs/phase-4.6-report.md`, `docs/visual-fidelity-audit.md`, `docs/full-visual-route-audit.md`, `docs/owner-visual-bugs.md` (historical — frontend milestone is closed, their "pending" language is stale)

Do not reread every historical document unless the task genuinely requires it.
