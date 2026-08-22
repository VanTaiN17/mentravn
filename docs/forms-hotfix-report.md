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

## Remaining owner action

- **Verify real mailbox receipt directly** — `wp_mail()`/WP Mail SMTP report success for the Gmail-relay sends made during this hotfix (recipient now `nguyenvantai9632@gmail.com`), but Claude has no mailbox access; per Phase 7's standing policy this is stated as **APPLICATION DELIVERY ACCEPTED / MAILBOX RECEIPT REQUIRES OWNER VERIFICATION**, not claimed as confirmed inbox delivery.
- **Decide the real production recipient** before launch — `mentra_vn_contact_email` is currently the owner's personal test address (a deliberate, disclosed, DB-only change made during this hotfix), not a final value.
- **Decide the real production SMTP identity** — the current WP Mail SMTP From Email/user is the owner's personal Gmail; confirm whether that's the intended long-term sender or a placeholder for a future business mailbox/provider.
- If 2FA is enabled on that Gmail account, confirm the configured password is a Gmail **App Password**, not the account's normal login password (SMTP auth otherwise fails even with a correct host).

## Hotfix status

**RESOLVED.** Both reported symptoms reproduced with a real, concrete root cause each, fixed/corrected, and verified through the real pipeline. No PHP fatal was ever occurring — the "500" was always a controlled response reacting to a real (now-corrected) SMTP configuration issue. Phase 8 may resume once the owner confirms the fix in their own browser.
