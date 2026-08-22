# Form Security — Phase 6

Reference for the anti-spam layer added on top of Phase 5's form pipeline. See `docs/forms-inventory.md` for field-level form details and `docs/phase-6-report.md` for the full test matrix.

## reCAPTCHA v2 Checkbox — configuration

Two options, both registered in `mentra_vn_group` and editable at **Settings → Mentra Việt Nam**:

| Option | Purpose | Exposure |
|---|---|---|
| `mentra_vn_recaptcha_site_key` | Public reCAPTCHA v2 site key | Localized into frontend JS (`MENTRA_VN.recaptcha.siteKey`), echoed into the widget's `data-sitekey` attribute. Safe by design. |
| `mentra_vn_recaptcha_secret_key` | Server-side verification secret | Never localized, never echoed to the frontend, excluded from the REST API (`show_in_rest => false`). Only read server-side by `verify_recaptcha_token()`. |

`Mentra_Vietnam_Core_99::recaptcha_enabled()` is the single switch: true only when **both** keys are non-empty. v2 Checkbox only — no v3, Invisible, or Enterprise integration anywhere in this codebase.

## Unconfigured state (no keys set)

Public forms **keep working** — CAPTCHA is not enforced when it isn't configured (`enforce_recaptcha()` returns immediately). This is deliberate: it is the honest state during local/dev setup rather than breaking forms or pretending protection exists. What *does* happen:

- An inline warning notice on the plugin's own settings page.
- A dismissible warning on every other wp-admin screen (for `manage_options` users), linking to the settings page.

Neither notice is ever shown to a frontend visitor.

## Configured state — fail-closed

Once both keys are set, verification is mandatory for every request and fails closed on all of the following, with no exceptions:

| Condition | Result |
|---|---|
| Token missing/empty | Rejected, "Vui lòng xác nhận bạn không phải là robot." |
| Secret empty at verification time (should be unreachable given `recaptcha_enabled()`, checked anyway) | Rejected, verification-failure message |
| Google returns `success !== true` | Rejected, "Không thể xác minh reCAPTCHA. Vui lòng thử lại." |
| `wp_remote_post()` transport error (`is_wp_error()`) | Rejected, same message |
| HTTP response code ≠ 200 | Rejected, same message |
| Response body isn't valid JSON | Rejected, same message |

The two Vietnamese messages are the only two the frontend ever sees — Google's raw `error-codes` array, HTTP status details, and any internal state are never exposed to a visitor.

## Server-side verification

`verify_recaptcha_token()` calls `wp_remote_post()` against `https://www.google.com/recaptcha/api/siteverify` with `secret`, `response`, and (when available) `remoteip`. Uses `wp_remote_retrieve_response_code()`/`wp_remote_retrieve_body()`/`is_wp_error()` per WordPress convention — no raw `curl`.

## Rate limiting

WP transients only — no custom database table. Policy: **5 accepted attempts per 10-minute window, per client-identity-hash + form type.**

- "Accepted attempt" means a request that passed nonce + field validation + form-type normalization and reached the rate-limit check — not a final successful send. An invalid nonce/email/form-type never reaches this stage at all (see Pipeline order below).
- This is a **sliding window**: each attempt within the window resets the transient's 10-minute expiry. A client must go fully quiet for 10 minutes to reset, rather than a strict fixed calendar window. Documented here deliberately since it's a real, testable difference from a fixed-window limiter.
- Scoped by form type: hitting the limit on `general` does not block `sales`/`support`/`partnership`/`media`/`career`/`newsletter` for the same client — each type has its own transient.
- On limit: `wp_send_json_error(['message' => '...'], 429)`. Response never includes the IP, hash, transient key, or internal counter — only the fixed Vietnamese message ("Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ít phút.").

## Client identity / privacy

- Only `$_SERVER['REMOTE_ADDR']` is used, validated with `filter_var(..., FILTER_VALIDATE_IP)`. `X-Forwarded-For`, `CF-Connecting-IP`, and `X-Real-IP` are never read anywhere in this codebase — this site has no configured trusted-proxy in front of it, so those headers would be trivially spoofable.
- The raw IP is **never persisted** — not in transients, options, or logs. `client_hash()` runs it through `wp_hash()` (keyed off the site's own AUTH salts) before it's used as rate-limit key material (`rate_limit_key()` = `mentra_rl_` + a 20-char slice of a second `wp_hash()` over `client hash + form type`).
- The raw IP is still sent to Google as the optional `remoteip` verification parameter (explicitly allowed by Google's API for this single request) — it is not stored locally in that path either.

## Central pipeline

Every one of the seven public forms funnels through the same two functions, called once each, in this order:

```
request → nonce → normalize form type → whitelist fields → validate → sanitize
   → enforce_security($type, $token)   [ check_rate_limit() then enforce_recaptcha() ]
   → compose → wp_mail() / wp_insert_post()   → response
```

- `contact_ajax()` and `career_ajax()` (General/Sales/Support/Partnership/Media/Career) call `enforce_security()` then `send_form_mail()` → `wp_mail()`.
- `newsletter()` calls the same `enforce_security()`, then its existing duplicate-check + `wp_insert_post()` into the `mentra_subscriber` CPT — unchanged final action, no `wp_mail()` involved, no new database/CPT introduced.
- No Google-verification code is duplicated across handlers — `enforce_security()`/`enforce_recaptcha()`/`verify_recaptcha_token()` are the only place it exists.
- A form type is always a value from `FORM_TYPES` or the literal `'newsletter'` by the time `enforce_security()` sees it — never raw user input — so it can't be used to construct an arbitrary transient key.

## Frontend integration

- The Google v2 script (`https://www.google.com/recaptcha/api.js?onload=mentraVnRecaptchaOnLoad&render=explicit&hl=vi`) is enqueued only when `mentra_vn_recaptcha_enabled()` is true **and** `mentra_vn_page_has_protected_form()` returns true for the current request (a real per-page content check — see that function's docblock in `functions.php` for the exact audit).
- Widgets are always rendered explicitly via `grecaptcha.render()`, never the implicit `data-sitekey` auto-scan, so timing between the async Google script and the widget container's insertion never matters.
- `getRecaptchaResponse()`/`resetRecaptcha()`/`mountRecaptchaWidget()` in `mentra.js` are keyed per-container via a `WeakMap`, so multiple protected forms on one page (if that ever occurs) are handled safely and independently.
- A token is **never cached** anywhere client-side (`localStorage`/`sessionStorage`/cookies/WP options) — it lives only in the widget's own in-memory state until read at submit time.
- `resetRecaptcha()` is called after every server round-trip — success or failure alike — so a consumed/rejected token can never be resubmitted; the next attempt always requires a fresh checkbox completion. A pure client-side validation failure (e.g. empty name field, caught before `fetch()` is even called) does **not** reset the widget, since the token was never touched.

## What this phase does not do

- No SMTP/PHPMailer transport configuration (Phase 7).
- No external mail-delivery verification — `wp_mail()`'s return value still only means "WordPress accepted the operation."
- No new CRM/contact-submission database or CPT.
- No visual redesign — the only frontend addition is the reCAPTCHA widget itself (a required security element) plus one small CSS margin rule for it.
