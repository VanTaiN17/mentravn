# Phase 5 — Forms Architecture — Report

Branch: `feature/forms-architecture` (from `fix/full-visual-fidelity`, the owner-approved frontend branch).

## Summary

Built the backend for all six business-contact form types (General, Sales, Support, Partnership, Media, Career) plus reviewed (unchanged) Newsletter, unified recipient routing to a single configurable `contact@domain.vn`, and fixed a real pre-existing routing gap (`/lien-he/?topic=sales`/`?topic=support` were silently ignoring the query string). Full field-level inventory: `docs/forms-inventory.md`.

## What changed

### `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php`

- Added `mentra_vn_contact_email` option (default `contact@domain.vn`), registered via `register_setting()`, editable at **Settings → Mentra Việt Nam**. Replaces the old `sales_email`/`support_email` split (confirmed via repo-wide grep to be referenced nowhere else — safe removal).
- Added `FORM_TYPES` (the six server-side subject prefixes), `CONTACT_SUBJECT_MAP` (whitelist mapping the existing `#contact-subject` dropdown values to one of GENERAL/SALES/SUPPORT/PARTNERSHIP/MEDIA), and `CAREER_EXPERTISE` (whitelist of the existing `#career-expertise` options).
- Rewrote `contact_ajax()`: whitelists the subject dropdown value (rejects anything unrecognized, HTTP 400) instead of the old fragile `stripos($subject, 'support')` keyword guess; validates/length-caps every field; routes through the new shared `send_form_mail()`.
- Added `career_ajax()`: new handler for a page that previously had zero backend. Validates against the real field set only (no invented fields, no file upload — the source form has none).
- Added `send_form_mail($type, $subject_line, $body_lines, $reply_name, $reply_email)`: the single funnel every form type goes through — resolves the recipient, builds a server-only subject (`{prefix} - {name}`, client can never supply a prefix), sends via `wp_mail()` with a hardened `Reply-To`, returns a standardized `wp_send_json_*` response. Contains one explicit comment marking where Phase 6 inserts reCAPTCHA verification + rate limiting, centrally, before `wp_mail()`.
- Added `safe_header_value()` (strips CR/LF/NUL) used on the Reply-To display name, on top of `sanitize_text_field()`'s own whitespace normalization — defense in depth against header injection.
- `newsletter()` left untouched (reviewed only — see below).

### `wp-content/themes/mentra-vietnam/functions.php`

- `mentra_vn_get_source_key()`: `/lien-he/?topic=sales` and `/lien-he/?topic=support` now actually render the matching pre-selected static file (`contact@topic=sales.html` / `contact@topic=support.html`), which already existed on disk from the original WGET capture but were dead/unreachable — `$_GET['topic']` was never read before. The query value is passed through `sanitize_key()` and matched against a two-entry whitelist; anything else falls back to the plain `/lien-he/` page. No static HTML edited.
- `MENTRA_VN_THEME_VERSION` bumped `3.14.0` → `3.15.0` (mentra.js changed).

### `wp-content/themes/mentra-vietnam/assets/js/mentra.js`

- Added `initCareer()`, wired into the existing `DOMContentLoaded` init list. Mirrors the exact UX pattern already used by `initContact()`/`initNewsletter()`: disables the submit button immediately, shows a "Đang gửi…" sending state, re-enables on a 1.8s timeout after a recoverable failure, resets the form only on success. Additionally fills the pre-existing but previously orphaned `.career-form-status` element (styled in `mentra.css`, present in no markup until now) with the success/error message — the closest fidelity completion available without inventing new UI, following the same precedent as the Even Realities dormant-CSS fix in Phase 4.6.
- No changes to `initContact()`/`initNewsletter()` — their existing payload shape already matches what the rewritten PHP handlers expect.

## Business-rule compliance

- WooCommerce catalog-only rule: untouched, not in scope this phase.
- No price/cart/checkout/payment introduced anywhere in Phase 5 code.
- No CRM / contact-submission CPT / DB table added — General/Sales/Support/Partnership/Media/Career all go straight to `wp_mail()`, nothing is persisted. Newsletter keeps its existing `mentra_subscriber` CPT storage, unchanged.
- Mail transport is `wp_mail()` only. No SMTP/PHPMailer configuration was added or touched.
- No reCAPTCHA implemented (Phase 6, as instructed) — one explicit insertion-point comment left in `send_form_mail()`.
- No frontend redesign — zero HTML/CSS authored; the one DOM insertion (`.career-form-status`) reuses classnames that already shipped in `mentra.css`.

## Validation / security

- `sanitize_text_field` / `sanitize_email` / `is_email` / `sanitize_textarea_field` / `esc_url_raw` used per WordPress convention on every field.
- Every field has a server-side max length (name 150, email 254, company 150, subject 100, message 5000, position 150, portfolio 500).
- Form type is always resolved via a fixed whitelist (`CONTACT_SUBJECT_MAP`, `CAREER_EXPERTISE`) — unrecognized values are rejected with HTTP 400, never guessed or silently accepted.
- Nonce (`mentra_vn_public`) required on both AJAX actions, reusing the existing `verify_public_nonce()` helper (`check_ajax_referer(..., false)` + standardized JSON 403 on failure).
- CRLF/header-injection: `sanitize_text_field()` already collapses `\r\n\t` sequences to a single space; `safe_header_value()` additionally strips any residual CR/LF/NUL before the value reaches the `Reply-To` header. Verified live (see Tests) — a `\r\nBcc: attacker@evil.com` payload in `name` was neutralized to a single-line display name, no extra header was created.
- No server paths or stack traces are ever returned to the client — all error responses are the fixed, translated `wp_send_json_error()` messages defined in the handlers.

## Tests

`php -l` on the modified plugin file and `functions.php`: pass. `node --check` on `mentra.js`: pass. A full `php -l` sweep of every theme PHP file: pass.

The local WordPress site (down during the prior handoff round) was up for this phase — all tests below ran against it directly via `curl`, plus a temporary `wp_mail`-intercepting mu-plugin (created only for this test run, removed immediately after — not part of the deliverable) to inspect recipient/subject/body/headers without depending on real SMTP delivery. `wp_mail()`'s return value was short-circuited to `true` during the intercept only so the absence of a locally-configured MTA didn't block the test; this does not change production behavior (`wp_mail()` still calls the real transport when the mu-plugin isn't present, and its return value continues to mean "WordPress accepted the send," not "delivery confirmed").

### Six-type test matrix

| Type | Recipient | Subject | Result |
|---|---|---|---|
| GENERAL | contact@domain.vn | `[MENTRA - LIÊN HỆ] - Nguyen Van A` | PASS |
| SALES | contact@domain.vn | `[MENTRA - KINH DOANH] - Tran Thi B` | PASS |
| SUPPORT | contact@domain.vn | `[MENTRA - HỖ TRỢ] - Le Van C` | PASS |
| PARTNERSHIP | contact@domain.vn | `[MENTRA - ĐỐI TÁC] - Pham Thi D` | PASS |
| MEDIA | contact@domain.vn | `[MENTRA - TRUYỀN THÔNG] - Hoang Van E` | PASS |
| CAREER | contact@domain.vn | `[MENTRA - TUYỂN DỤNG] - Vo Thi F` | PASS |

All six confirmed resolving to `contact@domain.vn`, with correct server-generated subject prefix, correct body content, and a clean single-line `Reply-To` header — captured directly from the intercepted `wp_mail()` payload.

### Invalid-input matrix

| Case | Expected | Result |
|---|---|---|
| Invalid/forged nonce | HTTP 403, generic error | PASS |
| Invalid email format | HTTP 400 | PASS |
| Missing required field (empty message) | HTTP 400 | PASS |
| Unrecognized subject/form type (`Completely Bogus Topic`) | HTTP 400, rejected | PASS |
| Oversized input (6000-char message, cap 5000) | HTTP 400 | PASS |
| Career: unrecognized expertise value | HTTP 400 | PASS |
| Header-injection attempt (`\r\nBcc: attacker@evil.com` in name) | Neutralized, single-line Reply-To, no extra header | PASS |

### Routing fix verification

`/lien-he/?topic=sales` now serves `contact@topic=sales.html` (confirmed: pre-selected "Sales" option, `john@example.com` placeholder). `/lien-he/?topic=support` serves `contact@topic=support.html` (pre-selected "Technical Support"). `/lien-he/?topic=bogus` safely falls back to the plain contact page. `/doi-tac/` and `/truyen-thong/` unaffected (single-file routes, no query variant needed).

### Newsletter (reviewed, not changed)

Nonce, `sanitize_email`/`is_email`, duplicate prevention (title-match lookup before insert), standardized JSON response, and duplicate-submit UX (`initNewsletter()`) all confirmed already correct. Two submissions of the same email both returned success with no duplicate created, per the existing idempotent design — this is the intended behavior, not a bug.

### Regression sweep

`/`, `/mentra-live/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, `/tin-tuc/`, `/lien-he/`, `/tuyen-dung/` — all HTTP 200, no visual/DOM changes made to any of these pages by this phase.

## Phase 5 gate

| Requirement | Status |
|---|---|
| General/Sales/Support/Partnership/Media/Career all work | PASS |
| All six use `contact@domain.vn` | PASS |
| Subject type correct (server-generated, whitelisted) | PASS |
| Server-side validation | PASS |
| Nonce | PASS |
| Reply-To / header hardening | PASS |
| `wp_mail()` only transport | PASS |
| Newsletter not regressed | PASS |
| Frontend design unchanged | PASS |
| No reCAPTCHA implemented | PASS (deferred to Phase 6) |
| No SMTP implementation added | PASS (deferred to Phase 7) |
| Phase 3/4/frontend regression checks | PASS |
| PHP/JS syntax | PASS |

**Phase 5 status: PASS.**

## Deferred

- **Phase 6**: Google reCAPTCHA v2 (checkbox) + server-side verification, rate limiting — both to be inserted centrally in `send_form_mail()` before the `wp_mail()` call (insertion point already commented in code).
- **Phase 7**: WP Mail SMTP configuration for actual transport reliability. `wp_mail()`'s return value has only ever meant "WordPress accepted the operation," never "delivered" — this was true before this phase and remains true after.
