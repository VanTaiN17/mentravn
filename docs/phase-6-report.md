# Phase 6 — reCAPTCHA v2 + Anti-Spam Security — Report

Branch: `feature/recaptcha-security` (from `feature/forms-architecture`, Phase 5 PASS).

## Summary

Added Google reCAPTCHA v2 Checkbox (never v3/Invisible/Enterprise), a centralized fail-closed server-side verification pipeline, and WP-transient-based rate limiting to all seven public forms (General, Sales, Support, Partnership, Media, Career, Newsletter). No SMTP, no new CRM/database, no visual redesign — see `docs/form-security.md` for the full architecture reference.

## Git

Branched `feature/recaptcha-security` from `feature/forms-architecture`. Commits (small, logical, in order):

1. `feat: add reCAPTCHA v2 configuration` — options, settings UI, admin notice, `recaptcha_enabled()`/`recaptcha_site_key()`, theme-layer bridge functions.
2. `feat: add centralized server-side captcha verification and rate limiting` — client identity hashing, rate limiter, Google verification, `enforce_security()`.
3. `feat: wire captcha and rate limiting into all seven public forms` — `contact_ajax()`, `career_ajax()`, `newsletter()` call sites.
4. `feat: load reCAPTCHA script only on pages with a protected form` — `mentra_vn_page_has_protected_form()`, conditional enqueue, theme version bump.
5. `feat: wire captcha into contact, career, and newsletter forms` — frontend widget module + form UX wiring in `mentra.js`, one CSS spacing rule.
6. `docs: complete Phase 6 security report` — this report, `docs/form-security.md`, `docs/project-status.md` update.

(The suggested list split verification/rate-limiting and contact/career/newsletter wiring into more commits each; consolidated slightly since they landed together as one coherent code change — still small and independently reviewable. No separate `test:` commit: this project has no PHPUnit/test-framework scaffold, so — matching Phase 5's precedent — the full test matrix below was run live against the local site and is documented here rather than committed as a script.)

**Note — concurrent external commits observed on this branch during Phase 6**: three commits not authored by this session landed directly on `feature/recaptcha-security` while this work was in progress — `02b9bcb` and `c24432a` (a mobile navigation drawer redesign, plus a CSS-specificity fix for it) and functions.php's `MENTRA_VN_THEME_VERSION` was independently bumped to `3.17.0` in the same wave. These are unrelated to Phase 6 scope, were left untouched per instruction not to revert legitimate concurrent work, and are visible in `git log`. This session's own version bump (commit 4 above) starts from `3.17.0` → `3.18.0`.

## reCAPTCHA configuration

`mentra_vn_recaptcha_site_key` / `mentra_vn_recaptcha_secret_key`, both editable at **Settings → Mentra Việt Nam**. Site key is public (safe in frontend markup/JS); secret is `sanitize_text_field`, `show_in_rest => false`, read only server-side. `Mentra_Vietnam_Core_99::recaptcha_enabled()` is true only when both are non-empty — the single switch controlling both the frontend script/widget and server-side enforcement. No real keys were committed; testing used Google's own publicly documented v2 Checkbox test key pair (`testkey.google.com`, always verifies `success:true` for any non-empty token) via a temporary local-only wp-admin option value, never written to any file.

## Frontend integration

Google's script (`recaptcha/api.js?onload=mentraVnRecaptchaOnLoad&render=explicit&hl=vi`) loads only when `mentra_vn_recaptcha_enabled()` **and** `mentra_vn_page_has_protected_form()` are both true — a real per-page content audit, not a route-name guess (full detail in `docs/form-security.md`). Verified live:

| Route | Has protected form | Script present |
|---|---|---|
| `/`, `/mentra-live/`, `/mentra-os/`, `/mang-xa-hoi/`, `/tin-tuc/`, `/lien-he/`, `/doi-tac/`, `/truyen-thong/`, `/tuyen-dung/` | Yes (Newsletter and/or Contact and/or Career) | Yes |
| `/even-realities/`, `/nha-phat-trien/`, `/trong-kinh/` | No — Newsletter footer form is genuinely absent from these pages' source HTML | No |

Widgets render explicitly (`grecaptcha.render()`), injected via JS before each form's submit button, reusing `mentra.css`'s existing spacing conventions plus one new `.mentra-recaptcha` margin rule — no layout redesign.

## Server-side verification

`verify_recaptcha_token()` → `wp_remote_post()` against `https://www.google.com/recaptcha/api/siteverify`, validated via `is_wp_error()` / `wp_remote_retrieve_response_code()` / `wp_remote_retrieve_body()` / `json_decode()` / `success === true`. Fails closed on every abnormal condition (see `docs/form-security.md` table) — never falls through to `wp_mail()`/`wp_insert_post()` on ambiguity.

## Rate limiting

WP transients, 5 accepted attempts / 10-minute sliding window, keyed by `wp_hash()`-salted client identity + whitelisted form type (`mentra_rl_<hash>`). No custom DB table. Exact semantics (including the sliding-window caveat) documented in `docs/form-security.md`.

## Privacy / IP handling

Only `REMOTE_ADDR` is read (never `X-Forwarded-For`/`CF-Connecting-IP`/`X-Real-IP` — no trusted-proxy is configured for this site). The raw IP is never persisted (not in transients, options, or logs) — only a `wp_hash()`-salted digest is ever used as key material. The raw IP is still passed to Google's `remoteip` verification parameter (per Google's API, single-request use only, not stored locally).

## Contact forms (General/Sales/Support/Partnership/Media)

`enforce_security($type, $token)` runs after form-type resolution (the `#contact-subject` whitelist from Phase 5) and before `send_form_mail()`. Recipient, server-generated subject prefix, and Reply-To CRLF hardening from Phase 5 are all untouched — confirmed by inspection and by the test matrix below.

## Career

Same `enforce_security('career', $token)` call, same `send_form_mail()` tail — no separate implementation. No redesign, no file upload added.

## Newsletter

`enforce_security('newsletter', $token)` added right after email validation, before the existing duplicate-check + `wp_insert_post()` into `mentra_subscriber`. Final action unchanged (CPT storage, not `wp_mail()`); duplicate prevention untouched and re-verified.

## Failure handling

Two frontend-facing messages only, both Vietnamese, both fixed strings — never Google's raw `error-codes`, never a stack trace or server path:

- Missing token: "Vui lòng xác nhận bạn không phải là robot."
- Verification/transport/malformed-response failure: "Không thể xác minh reCAPTCHA. Vui lòng thử lại."
- Rate limit: "Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ít phút." (HTTP 429)

## Tests

All run live against the local WordPress site (up and reachable throughout this phase). `php -l` across every modified file and a full theme/plugin sweep: pass. `node --check` on `mentra.js`: pass.

CAPTCHA testing used Google's official public v2 Checkbox **test** key pair, set via a temporary local-only wp-admin option (never committed) — the secret genuinely accepts any non-empty token as `success:true`, so the seven "valid captcha" cases below are real, non-mocked Google round-trips. "Failed verification" used a deliberately wrong (but non-empty) secret against real Google infrastructure — also a real, non-mocked failure. "Malformed response" and "HTTP transport failure" used a temporary `pre_http_request` filter (mu-plugin, deleted immediately after) scoped only to the siteverify URL, per the option-B guidance for cases Google's real infrastructure can't be made to produce on demand.

### CAPTCHA test matrix

| Case | Result |
|---|---|
| GENERAL valid captcha | PASS |
| SALES valid captcha | PASS |
| SUPPORT valid captcha | PASS |
| PARTNERSHIP valid captcha | PASS |
| MEDIA valid captcha | PASS |
| CAREER valid captcha | PASS |
| NEWSLETTER valid captcha | PASS |
| Missing captcha token (general/newsletter/career) | Rejected 400, "xác nhận bạn không phải là robot" — no `wp_mail()`/insert |
| Failed Google verification (real network call, wrong secret) | Rejected 400, "Không thể xác minh reCAPTCHA" |
| Malformed Google response (intercepted) | Rejected 400, same message |
| HTTP verification transport failure (intercepted) | Rejected 400, same message |

### Rate limit test matrix

| Case | Result |
|---|---|
| First requests (1st–5th) for a given type | All succeed |
| 5th (threshold) request | Succeeds |
| 6th request, same client + type | HTTP 429, rejected |
| Different type (`support`/`career`) from the same client while `general` is blocked | Succeeds — confirms per-type scoping |
| Response body | Never contains IP, hash, transient key, or counter |

### Security order test

| Case | Result |
|---|---|
| Invalid nonce | HTTP 403, generic message — no captcha message, no `wp_mail()` |
| Invalid email | HTTP 400, "email hợp lệ" — no captcha message reached |
| Invalid/unrecognized form type | HTTP 400, "Chủ đề không hợp lệ" — no captcha message reached |

Confirms the pipeline order: nonce → field validation → form-type whitelist all short-circuit *before* `enforce_security()` is ever called — none of them trigger a captcha check or an external Google request.

### Unconfigured-state sanity check

With both reCAPTCHA options cleared (the state this phase leaves the site in — see below), a contact submission with **no** `g_recaptcha_response` field at all still succeeds: `recaptcha_enabled()` is false, `enforce_recaptcha()` no-ops, forms work exactly as in Phase 5.

### Secret exposure check

Grepped the rendered HTML of `/`, `/lien-he/`, `/mentra-live/`, `/even-realities/`, `/tuyen-dung/`, `/doi-tac/`, `/truyen-thong/` for the test secret string — absent from all. Only the site key appears, inside `MENTRA_VN.recaptcha.siteKey` and the widget's `data-sitekey` attribute, as designed.

### Regression sweep

`/`, `/mentra-live/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, `/tin-tuc/`, `/lien-he/`, `/doi-tac/`, `/truyen-thong/`, `/tuyen-dung/` — all HTTP 200. Phase 3 (WooCommerce catalog-only) and Phase 4 (16 Posts + 6 static press) untouched by this phase — no code in this diff touches product/post logic. Phase 5's recipient (`contact@domain.vn`), subject prefixes, and Reply-To hardening all confirmed intact in the test matrix above.

## Post-test state

The two reCAPTCHA options were cleared back to empty after testing (`delete_option()`), and every temporary test artifact (the mu-plugin interceptor, the one-off wp-load.php bootstrap scripts) was deleted — none were ever committed. The site is left in the same "not yet configured" state Phase 6 started in; the owner enters real production keys at **Settings → Mentra Việt Nam** when ready, and the admin notice will guide that.

## Phase 6 gate

| Requirement | Status |
|---|---|
| reCAPTCHA v2 Checkbox implemented (not v3/Invisible/Enterprise) | PASS |
| Site/secret keys admin-configurable | PASS |
| Secret never reaches frontend | PASS (verified) |
| Server-side Google verification exists | PASS |
| Missing token fails | PASS |
| Failed verification fails | PASS |
| Google HTTP errors fail safely | PASS |
| Malformed response fails safely | PASS |
| Server-side rate limiting exists | PASS |
| Raw IP not persisted | PASS |
| All six business forms protected | PASS |
| Career protected | PASS |
| Newsletter protected | PASS |
| CAPTCHA failure cannot call `wp_mail()` | PASS |
| Rate-limit failure cannot call `wp_mail()` | PASS |
| Newsletter failure cannot insert subscriber | PASS |
| Recipient remains `contact@domain.vn` | PASS |
| Reply-To Phase 5 hardening remains | PASS |
| Frontend design unchanged | PASS (widget + one CSS rule only) |
| No SMTP setup added | PASS |
| Phase 3/4 regressions absent | PASS |
| PHP/JS checks pass | PASS |

**Phase 6 status: PASS.**

## Deferred to Phase 7

WP Mail SMTP configuration for actual transport reliability. `wp_mail()`'s return value still only means "WordPress accepted the operation," never "delivered" — unchanged by this phase.
