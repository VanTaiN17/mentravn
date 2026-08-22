# Forms Hotfix — reCAPTCHA Invisible + AJAX 500 — Report

Branch: `fix/forms-captcha-500` (from `feature/mail-delivery`, which is downstream of Phase 6/7).

## Summary

Two independent, real, reproduced root causes — one genuine code bug, one live configuration mistake — combined to produce the owner's reported symptoms. Both are now fixed/corrected and verified.

1. **Code bug (fixed)**: `ensureRecaptchaWidget()` in `mentra.js` called `form.insertBefore(container, submit)`, which requires `submit` to be a **direct child** of `form`. The Newsletter form's submit button is nested inside a wrapper `<div>`, not a direct child — `insertBefore` threw `NotFoundError: The child can not be found in the parent.` Because every `init*()` call in the `DOMContentLoaded` handler ran in one unguarded chain, this single throw **silently aborted every initializer listed after it** — `initContact()` and `initCareer()` never ran at all on any page that also has the Newsletter footer form (which is nearly every page). This is why the reCAPTCHA widget never appeared and why Contact/Career's own submit-interception (and therefore their AJAX wiring) was never attached on those pages.
2. **Live configuration mistake (corrected, not a code issue)**: WP Mail SMTP's `smtp.host` field had been set to an email address (`nguyenvantai9632@gmail.com`) instead of a real SMTP server hostname while the owner was configuring real-provider testing. This made every real `wp_mail()` send fail at the transport layer, which Mentra's existing (already-correct, Phase 5) failure handling reported as a controlled `HTTP 500` JSON error — exactly per spec, not a PHP fatal, not a crash. By the time this was directly inspected, the host had already been corrected (independently, likely by the owner) to `smtp.gmail.com`; the rest of the SMTP config (user/pass/port 587/TLS/From) was already correctly filled in for a standard Gmail App Password relay.

A third, non-bug finding: `mentra_vn_contact_email` (Mentra's own recipient setting) had never actually been changed — the owner's attempt to "route test mail to a personal address" had gone into WP Mail SMTP's **From Email** field instead, which only changes the sender identity, not the recipient. The recipient was still the coded default `contact@domain.vn` (a confirmed placeholder, not a real mailbox). Corrected as a WordPress option only (see Recipient Setting below).

## Git

Branched `fix/forms-captcha-500` from `feature/mail-delivery`. Commits:

1. `fix: restore reCAPTCHA widget rendering and isolate form initializers` — the `insertBefore` fix plus a `runInit()` wrapper around the `DOMContentLoaded` initializer chain, so one initializer throwing can never again silently disable unrelated ones.
2. `docs: complete forms hotfix report` — this report, `docs/project-status.md` update.

No `fix:` commit was needed for the SMTP host — that was a live database configuration value (WP Mail SMTP's own option), not a file in this repository, and is documented here rather than "fixed" in code, since there was nothing wrong with the code path handling it.

## Reproduction (before the fix)

Direct `POST` to `/wp-admin/admin-ajax.php` with a valid nonce and reCAPTCHA token (Google's official public v2 Checkbox test key pair, configured locally, never committed — same pair used throughout Phase 6/7):

| Form | Result before fix |
|---|---|
| GENERAL | `HTTP 500`, `{"success":false,...}"Không thể gửi email..."}` |
| SALES | `HTTP 500`, same |
| CAREER | `HTTP 500`, same |
| NEWSLETTER | `HTTP 200`, success (doesn't call `wp_mail()`) |

`WP_DEBUG_LOG` was temporarily enabled in `wp-config.php` (gitignored, local-only, reverted to its original `false` value immediately after diagnosis — never committed) to check for PHP warnings/fatals underneath the controlled 500s. **`wp-content/debug.log` was never created during any of this testing** — confirming zero PHP warnings/notices/fatals at any point; the 500 was always the deliberate `wp_send_json_error(..., 500)` call in `send_form_mail()` reacting to a genuine `wp_mail() === false`, not a crash.

## CAPTCHA visibility audit

Executed the real, unmodified `mentra.js` against the real rendered HTML of `/lien-he/`, `/lien-he/?topic=sales`, `/doi-tac/`, `/truyen-thong/`, `/tuyen-dung/`, and `/mang-xa-hoi/` using a headless DOM (jsdom, installed only in the local scratch/test directory, never added to this project's dependencies) — this executes the actual JS logic and catches real runtime exceptions, rather than guessing from static reading.

| Stage | Before fix | After fix |
|---|---|---|
| A. CAPTCHA container/widget markup in rendered HTML | No (injected by JS only, as designed) | No change — still JS-injected by design |
| A′. Widget present after JS executes | **No** — `initContact()`/`initCareer()` never ran on pages with a Newsletter form; even Newsletter's own widget never got attached because the throw happened during its own insertion | **Yes**, on every page/form combination tested |
| B. `MENTRA_VN.recaptcha` contains a non-secret site key | Yes (`configured: true`, real site key) — this was never broken | Yes, unchanged |
| C. Google's script enqueued (`recaptcha/api.js`, `hl=vi`) | Yes — enqueue logic (Phase 6) was never broken | Yes, unchanged |
| D. `window.grecaptcha` exists after load | Not directly testable without a real browser+network; not the failing stage (Google's script itself loads independently of Mentra's own JS) | Unchanged |
| E. Widget renders | **No** — never reached, since the container was never inserted into the DOM | **Yes** — confirmed via `grecaptcha.render()` call path and `.g-recaptcha` presence in the executed DOM |
| F. Submission includes non-empty `g-recaptcha-response`/token | No — `initContact()`'s submit handler was never attached, so submissions fell through to native browser form POST (not the AJAX path) | Yes — confirmed via a simulated submit dispatch: `event.defaultPrevented === true` and `fetch()` correctly called against `/wp-admin/admin-ajax.php` |

**First failing stage (before fix): E (and its downstream consequence, F)** — the widget never rendered because the DOM insertion itself threw, which is a strictly earlier failure than "did the user complete it."

## Root cause detail

`mentra.js`, `ensureRecaptchaWidget()`:

```js
const submit=qs('.career-submit,button[type="submit"]',form);
if(submit) form.insertBefore(container,submit); else form.appendChild(container);
```

`Node.insertBefore(new, ref)` requires `ref` to be a child of the node it's called on. For Contact/Career forms the submit `<button>` is a direct child of `<form>`, so this worked. For Newsletter (`templates/parts` and every static page's footer), the button is nested inside `<div class="flex flex-col sm:flex-row ...">`, so `form.insertBefore(container, submit)` is invalid and throws.

Because the theme's single `DOMContentLoaded` listener called every `init*()` function in one unguarded chain (`initHeader();...;initNewsletter();initContact();initCareer();...`), this throw inside `initNewsletter()` propagated all the way out, and JavaScript never executes code appearing after a thrown, uncaught statement in the same synchronous call chain — so `initContact()` and `initCareer()`, listed after `initNewsletter()`, silently never ran on any page where a Newsletter form is also present (the large majority of routes).

### Fix

```js
if(submit) submit.parentNode.insertBefore(container,submit); else form.appendChild(container);
```

Inserts immediately before the submit button regardless of nesting depth — correct for both the already-working Contact/Career case (where `submit.parentNode === form`, unchanged behavior) and the previously-broken Newsletter case.

### Hardening (prevents recurrence of this class of bug)

```js
const runInit=fn=>{try{fn();}catch(e){if(window.console&&console.error) console.error('mentra.js init failed:',fn.name||'(anonymous)',e);}};
document.addEventListener('DOMContentLoaded',()=>{[initHeader,...,initProductGallery].forEach(runInit);});
```

Each initializer now runs in isolation. A future bug in any one of them will log to the console (still visible for debugging) but can never again silently prevent unrelated initializers — including the three form-security ones — from running.

## Admin setting regression (Settings API)

Tested directly (section 6/7's explicit sequence) via the real `update_option()`/`sanitize_option` code path — the same mechanism WordPress's `options.php` uses on a real settings-page submit:

| Step | site key | secret key | contact_email |
|---|---|---|---|
| Before | CONFIGURED | CONFIGURED | (unset, resolves to default) |
| After `update_option('mentra_vn_contact_email', ...)` | CONFIGURED | CONFIGURED | new value |
| After re-saving both reCAPTCHA options with their own current values (full-form-submit simulation) | CONFIGURED | CONFIGURED | unchanged |

**Saving the recipient email does not clear or affect the reCAPTCHA options.** This is also true by code inspection: `mentra_vn_contact_email`, `mentra_vn_recaptcha_site_key`, and `mentra_vn_recaptcha_secret_key` are three fully independent `register_setting()` calls, each with its own scalar sanitize callback — there is no shared array or code path by which saving one could touch another. The "Settings API bug" hypothesis is **refuted**. No code change was made or needed here.

## Recipient setting

`mentra_vn_contact_email` was found **unset** (falls back to the coded default `contact@domain.vn`, a confirmed placeholder domain — see `docs/mail-configuration.md`). The owner's WP Mail SMTP configuration shows clear, deliberate setup of their own Gmail (`nguyenvantai9632@gmail.com`) as SMTP user/From — but that only ever controls the **sender**, never the recipient. To actually fulfill the owner's stated goal ("real delivery testing" to a personal inbox), `mentra_vn_contact_email` was set to that same already-known, non-secret address, **as a WordPress option only** (never written to any file in this repository). All six business forms read this option dynamically via `contact_email()` — none of them hardcode a recipient. The owner can change or clear it at **Settings → Mentra Việt Nam → Email nhận liên hệ** at any time; it should be set to the real production `contact@domain.vn`-replacement address before launch (see `docs/mail-configuration.md`).

## Test matrix (after fix, real pipeline, nonce + reCAPTCHA + rate limiting all live)

| Form | Widget visible (jsdom) | Valid token → result | wp_mail() reached |
|---|---|---|---|
| GENERAL | Yes | `200`, success | Yes — WP Mail SMTP confirmed sent |
| SALES | Yes | `429` (rate-limited from this session's own prior test volume — correct, expected behavior, not a bug) | N/A (blocked before mail stage, as designed) |
| SUPPORT | Yes | `200`, success | Yes — confirmed sent |
| PARTNERSHIP | Yes | `200`, success | Yes — confirmed sent |
| MEDIA | Yes | `200`, success | Yes — confirmed sent |
| CAREER | Yes | `200`, success | Yes — confirmed sent |
| NEWSLETTER | Yes | `200`, success | N/A (CPT storage, not mail — unchanged) |

Negative-path matrix, all still correctly controlled (no PHP fatal, no leaked provider/server detail):

| Case | Result |
|---|---|
| Missing token | `400`, "xác nhận bạn không phải là robot" |
| Invalid/failed token (real Google, deliberately wrong secret) | `400`, "Không thể xác minh reCAPTCHA" |
| Invalid nonce | `403`, generic session message |
| Invalid email | `400`, "email hợp lệ" |
| Rate limited | `429`, "quá nhiều yêu cầu" |
| `wp_mail()` forced false (via a temporary `pre_wp_mail` filter, removed after use) | `500`, "Không thể gửi email..." — controlled JSON, no fatal |

Frontend UX (per section 12) re-confirmed unchanged from Phase 6: duplicate-submit guard, sending state, `resetRecaptcha()` on every server round-trip, form fields preserved on non-success (only `form.reset()` on success), no visual redesign.

## Security regression

Missing/failed CAPTCHA and rate-limiting all still correctly block before `wp_mail()`/subscriber-insert (table above). Nothing in this hotfix touched `mentra-vietnam-core.php` (the plugin file) at all — only `mentra.js` changed.

## Frontend regression

`/`, `/mentra-live/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, `/tin-tuc/`, `/lien-he/`, `/tuyen-dung/`, `/doi-tac/`, `/truyen-thong/` — all `HTTP 200`. No visual redesign; the fix is a one-line DOM-insertion correction plus a non-visual error-isolation wrapper.

## Syntax tests

`php -l` full theme/plugin sweep: pass (no PHP was changed this hotfix). `node --check` on `mentra.js`: pass.

---

# Extension — Branding, Locked Contact Context, Success State, Acknowledgement Email

Owner-approved frontend/UX requirements added on top of the hotfix above, same branch, no `Phase 8` work started.

## Logo / branding

Audited `wp-content/themes/mentra-vietnam/assets/` for an existing logo before touching anything — `mentra_logo.svg` and `white_mentra_logo.svg` already exist (the same file `header.php` uses site-wide); no new asset was downloaded or generated. Contact and Career forms now get a small header (`<img src="{theme}/assets/mentra_logo.svg" height:22px>` + a context-specific Vietnamese heading) inserted via JS immediately before the form — same "inject via JS, never edit the 40+ static-source HTML mirrors" pattern already established in this codebase (mobile menu, reCAPTCHA widget). Newsletter is untouched (section 21).

## Route → locked contact type mapping

| Route | `MENTRA_VN.contactType` | Badge label | Heading |
|---|---|---|---|
| `/lien-he/` | `general` | Chung | Liên hệ Mentra |
| `/lien-he/?topic=sales` | `sales` | Kinh doanh | Liên hệ kinh doanh |
| `/lien-he/?topic=support` | `support` | Hỗ trợ | Hỗ trợ Mentra |
| `/doi-tac/` | `partnership` | Đối tác | Hợp tác cùng Mentra |
| `/truyen-thong/` | `media` | Truyền thông | Liên hệ truyền thông |
| `/tuyen-dung/` (Career, no dropdown to lock) | n/a | — | Ứng tuyển tại Mentra |

All six confirmed live via the actual rendered page HTML (`mentra_vn_current_contact_type()` in `functions.php`). `/lien-he/?topic=<invalid>` confirmed falling back to `general` (same test), never displaying the raw invalid value anywhere.

**Locked context UI**: on all five contact-style pages, the `#contact-subject` `<select>` is removed from the DOM (via `submit.parentNode`-relative JS, not an HTML edit) and replaced with a non-interactive "Loại yêu cầu" badge — confirmed via jsdom that the `<select>` genuinely no longer exists in the executed DOM, not just visually disabled. This removes the duplicate-choice UX on **every** contact-style page, including plain `/lien-he/` (locked to "Chung"), per the brief's "do not duplicate ... both as route context and as another required form-type dropdown."

## Client tamper protection

The locked badge is a UX affordance only — the **security boundary is server-side**: `Mentra_Vietnam_Core_99::route_locked_type()` re-derives the authoritative type from the request's `Referer` header (`wp_get_referer()`, WP core's own helper), which a same-origin `fetch()` sends automatically and a client-side HTML/JS edit cannot override. Verified live: a request with `Referer: .../doi-tac/` but a spoofed `subject=Sales` POST field was still classified and mailed as **Partnership** — the tampered field had zero effect. When the Referer is absent or unrecognized (e.g. a privacy-blocking browser, or a direct API call), the pre-existing Phase 5 `CONTACT_SUBJECT_MAP` whitelist is the fallback, unchanged — never weaker than before, only more precise when Referer is available.

## Success interface

On a successful AJAX response, the form (including its reCAPTCHA widget) is hidden and replaced by a dedicated success card: check-icon, "Gửi thành công" heading, thank-you message, and a "Về trang chủ" primary CTA. No auto-redirect. Focus moves to the card (`tabindex="-1"`, `.focus()`), which also carries `role="status"`/`aria-live="polite"` so screen readers announce it; the message and heading text are additionally the primary signal (not just the green success styling), satisfying the "must be understandable without relying only on color" requirement. Confirmed via jsdom: the card exists in the DOM (hidden) on page load and un-hides correctly.

## Homepage CTA

`href` is `MENTRA_VN.home` — the same value already localized from `trailingslashit(home_url('/'))` in `functions.php` (Phase 1-era), confirmed resolving to `http://mentra-vn.local/` in this environment and will resolve to the real production URL automatically after deployment, with zero code change. No optional secondary "Gửi yêu cầu khác" action was added — kept to the one required primary CTA per the brief's "do not add unnecessary actions if they clutter the UI."

## Failure interface

On any non-success response the form stays visible, the reCAPTCHA widget resets (`resetRecaptcha()`, unchanged from Phase 6), the submit button re-enables after its existing timeout, and a new inline `.mentra-form-error` element shows the actual returned message text (previously the Contact form only changed its button label to "Gửi lại" with no visible message text - Career already had `.career-form-status` for this, now extended the same idea to Contact). Entered field values are untouched either way (`form.reset()` still only runs on success).

## Customer acknowledgement email (new feature this round - no prior implementation existed)

`send_form_mail()` now: generates a short reference ID (`generate_reference_id()`, e.g. `MT-TK5JMR-8G7E`) per submission; sends the **admin notification as HTML** (previously plain text) using a shared, table-based, email-client-safe template (`render_html_email()`) that includes the theme's own logo via `get_template_directory_uri()` (never a hardcoded `mentra-vn.local`, never a filesystem path) with `alt="Mentra"`; and — **only after that admin send succeeds** — best-effort sends a **customer HTML acknowledgement** to the submitter's own (validated) email, carrying the **same reference ID**. The acknowledgement's own success/failure is never checked and never affects the JSON response.

Verified live, both directions:

| Scenario | Result |
|---|---|
| Admin mail forced to fail (temporary `pre_wp_mail` filter, removed after use) | `500` controlled JSON error; customer acknowledgement **never attempted** (confirmed via mail log - only one send logged) |
| Admin succeeds, customer acknowledgement forced to fail | AJAX response still `{"success":true,...}` - the business inquiry was received, per the documented policy |
| Six business types, real pipeline, real Referer per route | Admin + customer email pair confirmed for all six, **same reference ID on both halves of each pair** (e.g. `MT-TK5JMR-8G7E` on both the admin notification and the "Đã nhận được yêu cầu của bạn" customer email for that one submission) |

Email body fields are limited to business-relevant information (name, email, company, message/career fields, form type) - no reCAPTCHA token, IP/hash, rate-limit transient, nonce, SMTP credential, or internal server path is ever included, matching the existing hardening. `Content-Type` changed from `text/plain` to `text/html` for both messages; the pre-existing CRLF/NUL header hardening (`safe_header_value()`/`build_reply_to()`) is untouched and applies identically.

## Newsletter

Not touched. Its compact footer UX (Phase 1-era button-label pattern) is unchanged, per the brief's explicit instruction.

## Responsive

No browser/screenshot tooling is available in this environment (a limitation documented consistently throughout this project). Reviewed the new CSS directly instead: no fixed pixel widths were introduced anywhere (the logo is a fixed *height*, not width; the badge and success card use `inline-flex`/`flex` with content-based sizing; the success message caps at `44ch`, a relative unit); every new element is a direct child of the same container the existing, already-responsive form/page layout already handles. The CTA reuses the existing `.btn-base.btn-primary` class verbatim (already responsive sitewide). This gives reasonable confidence at both 1440px and 390px, but actual rendered-pixel verification was not directly observable by Claude - recommend a quick owner visual pass, same as prior phases' documented visual-verification limitation.

## Extended test matrix

All six business types + Newsletter (unchanged) re-verified end-to-end through the real pipeline (nonce, live reCAPTCHA, live rate limiting, real WP Mail SMTP transport) after this extension's changes: security passes → admin HTML mail succeeds → customer acknowledgement attempted → AJAX success → (frontend, confirmed via jsdom) form hidden/success shown → CTA resolves to `home_url('/')`. Negative paths re-confirmed: missing token, invalid nonce, invalid `expertise` (Career) all correctly rejected with zero `wp_mail()` calls (mail-log line count unchanged across all three). Route regression (12 routes) and full `php -l`/`node --check` sweep: all pass, zero new warnings.

## Remaining owner action

- **Verify real mailbox receipt directly** — `wp_mail()`/WP Mail SMTP report success for every send made during this hotfix and its extension (recipient/customer address `nguyenvantai9632@gmail.com`), but Claude has no mailbox access; per Phase 7's standing policy this is stated as **APPLICATION DELIVERY ACCEPTED / MAILBOX RECEIPT REQUIRES OWNER VERIFICATION**, not claimed as confirmed inbox delivery. Also check that the new HTML admin/customer emails render acceptably in a real inbox, including with images blocked (alt text "Mentra" is present as the fallback).
- **Decide the real production recipient** before launch — `mentra_vn_contact_email` is currently the owner's personal test address (a deliberate, disclosed, DB-only change made during this hotfix), not a final value.
- **Decide the real production SMTP identity** — the current WP Mail SMTP From Email/user is the owner's personal Gmail; confirm whether that's the intended long-term sender or a placeholder for a future business mailbox/provider.
- If 2FA is enabled on that Gmail account, confirm the configured password is a Gmail **App Password**, not the account's normal login password (SMTP auth otherwise fails even with a correct host).
- **Do a quick real-browser visual pass** at ~1440px and ~390px on `/lien-he/`, `/lien-he/?topic=sales`, `/doi-tac/`, `/truyen-thong/`, `/tuyen-dung/` - the CSS review above gives reasonable confidence but was not visually observed by Claude.

## Hotfix status

**RESOLVED**, extension **COMPLETE**. Both originally reported symptoms (invisible reCAPTCHA, AJAX 500) were reproduced with a real, concrete root cause each, fixed/corrected, and verified through the real pipeline - no PHP fatal was ever occurring. The owner-approved branding/locked-context/success-state/acknowledgement-email extension is implemented and verified end-to-end for all six business form types, with client tamper-protection confirmed live. Phase 8 may resume once the owner confirms the fix and new UX in their own browser.

---

# Final Forms/Purchase/Email Hotfix — Nonce Security, Purchase Flow, CID Logo

Owner-approved scoped hotfix on top of everything above, same branch (`fix/forms-captcha-500`). Not Phase 8. Addresses the one flagged remaining issue (Referer-based security) plus two new owner requirements (Purchase flow, email logo).

## 1. Nonce security replaces Referer

`Mentra_Vietnam_Core_99::route_locked_type()` (Referer-based type resolution) is **removed**. `wp_get_referer()` is no longer read anywhere in the plugin.

Replacement mechanism, both in `mentra-vietnam-core.php` and `functions.php`:

- **At render** (`mentra_vn_assets()` in `functions.php`): the current request's form type is resolved directly from the route/query (`mentra_vn_current_form_type()`, extending the existing `mentra_vn_current_contact_type()` with Career), and for Sales, the Purchase intent/product (`mentra_vn_current_purchase_context()`). A scoped nonce is created for exactly that combination via `mentra_vn_create_contact_nonce($type, $intent, $product)` and localized as `MENTRA_VN.formType`/`formNonce`/`formIntent`/`formProduct`/`formProductName`.
- **At submission** (`contact_ajax()`/`career_ajax()`): the client-submitted `form_type` (and, for Sales, `intent`/`mentra_product`) is normalized/whitelisted, then used to recompute the exact same action string via `Mentra_Vietnam_Core_99::contact_nonce_action()`, and the submitted `nonce` is verified against **that** action via `check_ajax_referer()`. A nonce only verifies against the action it was created for - WordPress's own nonce hashing already guarantees this, so no new cryptography was written here, only new scoping around it.

Action-string convention: `mentra_vn_contact_{type}` for the five non-Purchase types and Career, `mentra_vn_contact_sales_purchase_{product}` for Purchase mode. Because the action string changes whenever type/intent/product changes, a nonce created for one context can never verify against another - this is what makes tampering fail, not any client-side check.

`CONTACT_SUBJECT_MAP` (the old client-`subject`-field whitelist fallback) is removed along with it - the client no longer submits a free-text `subject` at all; `form_type` is now the only type signal, and it is worthless without a matching nonce.

### Tamper test (live, real pipeline)

Rendered `/doi-tac/` (Partnership), captured its nonce, then POSTed to `/wp-admin/admin-ajax.php`:

| Case | Result |
|---|---|
| Correct `form_type=partnership` + its own nonce (control) | `200`, success, mail sent |
| `form_type=sales` + the Partnership nonce | `403`, generic session message, **no mail sent** |
| Missing `form_type` | `400`, "Loại yêu cầu không hợp lệ." |
| Invalid `form_type` (`bogus`) | `400`, same |
| Missing `nonce` | `403` |
| A *different valid* nonce (General's) used with `form_type=partnership` | `403` |

Same pattern independently confirmed for Career (`form_type=general` submitted with the Career nonce → `400`, rejected before nonce check even runs, since `career_ajax()` requires `form_type === 'career'` first).

## 2. Purchase-specific Sales flow

### URL model (deviates from the brief's literal example - see below)

Rendered form: `/lien-he/?topic=sales&intent=purchase&mentra_product=<key>`

The product query parameter is **`mentra_product`, not the brief's suggested `product`**. Confirmed live during implementation: WooCommerce registers `product` as its own public query var (for the `product` CPT's `/product/%postname%/` permalink structure), so a plain `?product=mentra-live` on `/lien-he/` collides with it - WordPress's `redirect_canonical()` 301-redirects the request to `/product/mentra-live-camera-glasses/?product=mentra-live` **before Mentra's own code ever runs**, silently breaking Purchase mode. Reproduced directly:

```
GET /lien-he/?product=mentra-live          -> 301 -> /product/mentra-live-camera-glasses/?product=mentra-live
GET /lien-he/?mentra_product=mentra-live   -> 200 (no redirect)
```

Renamed consistently everywhere the key is used - the CTA query args, `mentra_vn_current_purchase_context()`'s `$_GET` read, and `contact_ajax()`'s `$_POST` read - so the GET and POST field names match. This is a necessary implementation deviation from the brief's literal example URL, not a scope change; the security/allowlist property the brief requires is unaffected.

### Product allowlist

`Mentra_Vietnam_Core_99::PRODUCT_ALLOWLIST`:

```php
'mentra-live' => 'Mentra Live',
'mentra-live-charging-cable' => 'Infinity Cable cho Mentra Live',
```

`is_valid_product()`/`product_name()` are the only way either the frontend or mail body ever gets a product display name - the raw `mentra_product` query/POST value is never echoed anywhere, on either the render path or the AJAX path. No other product got a "Liên hệ mua hàng" CTA in this pass (Even Realities/NIMO/prescriptions are static pages with no such CTA in their source markup - not touched, per "do not invent products").

### CTA wiring

`page-mentra-live.php` and `page-mentra-live-charging-cable.php`'s existing "Liên hệ mua hàng" buttons now build their href via `add_query_arg(['topic' => 'sales', 'intent' => 'purchase', 'mentra_product' => '<key>'], home_url('/lien-he/'))` - no hardcoded `mentra-vn.local`, confirmed resolving correctly live. Every other existing plain `?topic=sales` link sitewide (footer, other static pages) is untouched and still opens the normal Sales form.

### Invalid/missing product and intent - chosen behavior

Both the render path and the AJAX path independently and consistently **silently degrade to a normal Sales context** whenever the product isn't a recognized allowlist key, or `intent` isn't exactly `purchase`, or `intent=purchase` is requested on a non-Sales type. Nothing is ever displayed or echoed from the invalid raw value. Verified live: `?topic=sales&intent=purchase&mentra_product=totally-invalid` renders the **plain Sales form** (`formIntent:""`, `formProduct:""`, heading "Liên hệ kinh doanh") with zero occurrences of the string `totally-invalid` anywhere in the response.

### Purchase form fields

Purchase mode is detected client-side purely from the server-localized `MENTRA_VN.formIntent`/`formProduct`/`formProductName` (never guessed from the URL by JS itself). When active, `initContact()` in `mentra.js`:

- Reuses the existing `#contact-company` field's DOM slot to show a locked **"Sản phẩm"** badge (`addLockedProduct()`, reusing the already-styled `.mentra-locked-type` class from the prior hotfix's locked-context UI) - `<company-wrapper>` is fully replaced, so `#contact-company` genuinely no longer exists in the DOM in Purchase mode (confirmed via jsdom), not just hidden.
- Injects **Phone** (`#mentra-purchase-phone`, `type="tel"`) and **Address** (`#mentra-purchase-address`) fields immediately before the message field, cloning the existing `#contact-name` input's classes/inline style so they match the static source markup exactly with no new CSS.
- Relabels the message field "Nội dung / Ghi chú" and the submit button "Gửi yêu cầu mua hàng".
- Skips the plain "Loại yêu cầu: Kinh doanh" badge (`addLockedType()`) - the Product badge and "Liên hệ mua hàng" heading already communicate the context, avoiding the duplicate-signal problem the original locked-context hotfix was fixing.

No new CSS was needed - Product/Phone/Address all reuse existing classes (`.mentra-locked-type`, the cloned input class string).

Verified live via jsdom against the real rendered `/lien-he/?topic=sales&intent=purchase&mentra_product=mentra-live` page, executing the real unmodified `mentra.js`:

| Check | Result |
|---|---|
| JS errors | 0 |
| Heading | "Liên hệ mua hàng" |
| Product badge | "Mentra Live 🔒" |
| `#contact-company` present | No |
| Phone field present | Yes |
| Address field present | Yes |
| Message label | "Nội dung / Ghi chú" |
| Submit button text | "Gửi yêu cầu mua hàng" |
| reCAPTCHA widget container | Present |

Same check against `/doi-tac/` (non-Purchase) confirmed zero regression: Company present, no Phone/Address, normal badge/heading/submit text.

### Server-side validation

`contact_ajax()` (Sales, Purchase branch): Name/Email/Message validated identically to every other type; **Phone required** via a new `sanitize_phone()` (digits/spaces/`+`/`-`/`()`, 6+ digits, ≤30 chars - deliberately not a Vietnam-only pattern, so a legitimate international customer isn't rejected); **Address required**, `sanitize_textarea_field()`, ≤500 chars, never treated as HTML (escaped via the existing `esc_html()`/`nl2br()` in `render_html_email()`). Company is not read/validated/sent in Purchase mode; conversely Phone/Address are not read/validated in non-Purchase mode - the two are mutually exclusive per submission.

Purchase submissions use `$type = 'sales'` for `enforce_security()`/rate-limiting (the same call already used for plain Sales) - **not** a per-product key - so a client cannot obtain a fresh rate-limit quota by varying `mentra_product`/`intent`.

### Purchase product tamper test (live, real pipeline)

Rendered the Mentra Live Purchase page, captured its nonce, then POSTed with the same nonce but a tampered context:

| Case | Result |
|---|---|
| Correct product + its own nonce (control) | `200`, success, mail sent |
| Product swapped `mentra-live` → `mentra-live-charging-cable`, same nonce | `403`, no mail |
| `mentra_product=nonexistent-key` | `403`, no mail (degrades to plain-sales action server-side, which still doesn't match the Purchase-scoped nonce that was actually issued) |
| `intent=garbage` | `403`, no mail |
| Missing `mentra_product` entirely | `403`, no mail |
| Missing nonce entirely | `403`, no mail |

Same result for the Infinity Cable product (verified independently with its own nonce/context).

## 3. Purchase mail

Admin email (`contact_ajax()`'s Purchase branch → `send_form_mail()`): subject `[MENTRA - KINH DOANH] - Yêu cầu mua {Product} - {Name}` (still the existing Sales prefix, per the brief - only the tail is more specific). Body rows: Loại ("Yêu cầu mua hàng"), Sản phẩm, Họ tên, Email, Số điện thoại, Địa chỉ, Nội dung, plus the existing Mã tham chiếu/footer. **No Công ty row** - the `$fields` array for Purchase never includes a Company key at all (not merely blanked).

Customer acknowledgement (`send_customer_acknowledgement()`, new `$context['intent'] === 'purchase'` branch): subject `[MENTRA - KINH DOANH] Chúng tôi đã nhận yêu cầu mua hàng của bạn`, body names the approved product, confirms receipt, states the team will follow up "trong thời gian sớm nhất" - no price, no stock/order state, no delivery date promised, explicitly not checkout/order completion, per the brief. Shares the same reference ID as the admin mail. Same failure policy as every other type: admin failure → submission fails, customer ack never attempted; admin success + customer ack failure → submission still reported successful.

Verified live: valid Purchase submissions for both Mentra Live and Infinity Cable returned `{"success":true}` through the real pipeline (nonce + live reCAPTCHA + live rate limiting + real WP Mail SMTP transport, same Gmail relay already configured from the prior hotfix).

## 4. Email logo - CID fix

### Root cause (confirmed)

`render_html_email()` previously built `<img src>` from `get_template_directory_uri()`, which resolves to `http://mentra-vn.local/...` on this local environment - unreachable by Gmail (or any external client) when fetching the image as a remote URL. Even in production this approach would depend on the recipient's mail client being willing to fetch a remote image at all, which many clients block by default.

### Fix

`render_html_email()` now always emits `<img src="cid:mentra-logo" alt="Mentra" width="120" ...>`. A new `Mentra_Vietnam_Core_99::mail_with_logo($to, $subject, $html, $headers)` wraps every admin/customer send: registers a `phpmailer_init` hook immediately before calling `wp_mail()`, which calls `$phpmailer->addEmbeddedImage($logo_path, 'mentra-logo', 'mentra_logo_email.png')`, then removes the hook immediately after - so it can never attach the logo to an unrelated WordPress email (Newsletter doesn't call `wp_mail()` at all; nothing else in this codebase calls `wp_mail()` outside `send_form_mail()`/`send_customer_acknowledgement()`). `wp_mail()` remains the only transport; this hook only embeds an image, it never touches SMTP/mailer configuration (WP Mail SMTP still owns that, per Phase 7). Embedding is wrapped in try/catch - a failure there never blocks the textual email from sending.

### Logo asset

Only an SVG wordmark existed (`assets/mentra_logo.svg`, used site-wide by the theme header - **not modified**). Generated a new, separate raster derivative for email use only: `assets/mentra_logo_email.png` (360×94px, transparent background, ~4.2 KB), rendered directly from the existing SVG via `sharp` (installed only in the local scratchpad for this one-off conversion, never added as a project dependency) at 3x the display size for retina clients. Visually verified as a faithful reproduction of the existing wordmark before use.

### Verification

Structural verification via an isolated PHPMailer test (same `addEmbeddedImage()` call, same file, same CID name as `mail_with_logo()`, no WordPress/DB dependency): the resulting MIME message contains a `Content-ID: <mentra-logo>` part with `image/png` content, and the HTML body's `cid:mentra-logo` reference matches it exactly - confirming the mechanism has zero dependency on any hostname, local or production.

Real end-to-end sends (General, Partnership, Career, Mentra Live Purchase, Infinity Cable Purchase - all run during this hotfix's live testing) went through the real WP Mail SMTP → Gmail relay already configured from the prior hotfix. Per the project's standing mailbox-verification policy (Claude has no inbox access), this is **APPLICATION DELIVERY ACCEPTED / MAILBOX RECEIPT AND VISUAL LOGO RENDERING REQUIRE OWNER VERIFICATION** - the owner should open one of these test emails in Gmail and confirm the logo now renders (previously it did not).

## 5. Regression

`php -l` full sweep (theme + plugin): pass. `node --check` on `mentra.js`: pass. Route sweep (`/`, `/mentra-live/`, `/products/mentra-live-charging-cable/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, `/tin-tuc/`, `/lien-he/`, `/tuyen-dung/`, `/doi-tac/`, `/truyen-thong/`): all `200`. Newsletter re-verified live with its own unchanged `mentra_vn_public` nonce - untouched by any change in this hotfix. General/Support/Partnership/Media/Career all re-verified live end-to-end through the real pipeline after these changes, no regression.

## 6. Files changed

- `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php` - removed `route_locked_type()`/`CONTACT_SUBJECT_MAP`; added `PRODUCT_ALLOWLIST`, `is_valid_product()`, `product_name()`, `normalize_type()`, `contact_nonce_action()`, `create_contact_nonce()`, `verify_scoped_nonce()`, `sanitize_phone()`, `mail_with_logo()`; rewrote `contact_ajax()`, `career_ajax()`, `send_form_mail()`, `send_customer_acknowledgement()`, `render_html_email()`; removed `email_logo_url()`; added bridge functions `mentra_vn_is_valid_product()`, `mentra_vn_product_name()`, `mentra_vn_create_contact_nonce()`.
- `wp-content/themes/mentra-vietnam/functions.php` - added `mentra_vn_current_form_type()`, `mentra_vn_current_purchase_context()`; extended `mentra_vn_assets()`'s localized `MENTRA_VN` object with `formType`/`formNonce`/`formIntent`/`formProduct`/`formProductName`.
- `wp-content/themes/mentra-vietnam/assets/js/mentra.js` - added `addLockedProduct()`, `buildPurchaseField()`, `addPurchaseFields()`; extended `addSuccessCard()` with Purchase-aware heading/message; rewrote `initContact()` for Purchase mode; `initCareer()` now sends `form_type`/the scoped nonce instead of the generic one.
- `wp-content/themes/mentra-vietnam/page-mentra-live.php`, `page-mentra-live-charging-cable.php` - "Liên hệ mua hàng" CTA now builds a Purchase-mode URL via `add_query_arg()`.
- `wp-content/themes/mentra-vietnam/assets/mentra_logo_email.png` - new email-only raster logo derivative (original SVG untouched).

## Hotfix status (final)

**COMPLETE.** All three owner requirements (nonce security replacing Referer, Purchase-specific Sales flow, CID email logo) implemented and verified live through the real pipeline, including full tamper-test matrices for both the general nonce-scoping mechanism and the Purchase product/intent context specifically. No regression found in any of the six existing business form types, Career, or Newsletter. Phase 8 remains paused pending explicit owner approval of this hotfix.
