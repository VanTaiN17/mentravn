# Phase 7 — WP Mail SMTP Integration + Delivery Verification — Report

Branch: `feature/mail-delivery` (from `feature/recaptcha-security`, Phase 6 PASS).

## Summary

WP Mail SMTP is installed and active but not yet pointed at a real provider (mailer is still the PHP-native `mail`, no SMTP/API credentials set, From Email is a local-environment placeholder). Audited every `wp_mail()` call site in the Mentra plugin and confirmed the existing From/Reply-To architecture already matches the recommended design — **no code change was needed or made this phase**. All six business forms, the reCAPTCHA/rate-limit gates from Phase 6, and Vietnamese UTF-8 subjects/bodies were verified live through the real AJAX pipeline. Phase 7 status: **IMPLEMENTATION PASS / DELIVERY CONFIGURATION PENDING** (see Phase 7 Gate below — this is the expected, documented outcome for an unconfigured provider, not a code failure).

## Git

Branched `feature/mail-delivery` from `feature/recaptcha-security`. No `fix:` commit was created — the audit (below) found the mail-header architecture already correct, and the instructions explicitly say not to create meaningless code changes just to produce a commit. Commits:

1. `docs: audit WP Mail SMTP integration state and add mail configuration guide` — `docs/mail-configuration.md`.
2. `docs: complete Phase 7 mail delivery report` — this report, `docs/project-status.md` update.

No separate `test:` commit, matching Phase 5/6 precedent: this project has no PHPUnit/test-framework scaffold, so the full test matrix below was run live against the local site (via a temporary `wp_mail`/`wp_mail_failed`-intercepting mu-plugin, deleted immediately after) and is documented here rather than committed as a script.

## Pre-existing external commits

Investigated the two external commits Phase 6 flagged, per this phase's explicit instruction to verify them before proceeding:

| Commit | Subject | Author | Files | Ancestor of HEAD |
|---|---|---|---|---|
| `02b9bcb` | fix: implement production-grade mobile menu drawer with collapsible submenus and Vietnamese translations | VanTaiN17 \<nguyenvantai9632@gmail.com\> | `mentra.css`, `mentra.js`, `functions.php` | Yes |
| `c24432a` | fix: resolve CSS specificity blocking submenu closure and ensure submenus are closed by default | VanTaiN17 \<nguyenvantai9632@gmail.com\> | `mentra.css`, `mentra.js`, `functions.php` | Yes |

Both authored under the project owner's own account (same address as this project's `userEmail`), consistent with the established owner+Antigravity pattern from the Phase 4.6→5 handoff. Findings:

- **Theme/frontend only** — neither commit touches `wp-content/plugins/mentra-vietnam-core/mentra-vietnam-core.php` or any Forms/Security/Mail code at all.
- Each commit's only `functions.php` change is the `MENTRA_VN_THEME_VERSION` cache-bust constant (`3.15.0`→`3.16.0`→`3.17.0`) — verified via `git show -- functions.php` for both; nothing else in that file was touched.
- Grepped both commits' JS diffs for secrets/debug code/temp-file patterns (`console.log`, `debugger`, `var_dump`, hardcoded local paths, API keys, etc.) — no matches.
- No new files, no `.env`, no credentials anywhere in either diff.
- No mail/security regression possible given the file scope — confirmed separately by this phase's own regression sweep (below), which re-verified the six-form pipeline, reCAPTCHA fail-closed behavior, and rate limiting all still work correctly with these commits in the branch's history.

**Not reverted** — out-of-scope but legitimate, owner-authored frontend work, per instruction.

## WP Mail SMTP state

| Field | Value |
|---|---|
| Installed | Yes (v4.9.0) |
| Active | Yes |
| Mailer | `mail` (PHP native — no SMTP/API provider selected) |
| From Email | `dev-email@wpengine.local` (local-environment placeholder, **not** a Mentra address) |
| From Email forced | Yes |
| From Name | `mentra-vn` |
| From Name forced | No |
| Credentials configured for any provider | No — the `smtp` section exists but only holds default/empty scaffold fields (`autotls`, `auth`); no host/user/password, no API key, no OAuth token for any mailer |
| Competing mail plugin | None found — only WP Mail SMTP is active among mail-related plugins |

**Configuration status: PARTIALLY CONFIGURED** (plugin active, From fields set but to a placeholder) — treated as **NOT CONFIGURED for real delivery purposes**, since no actual transport provider/credentials exist. No secrets were printed or recorded anywhere in this audit; only field presence/emptiness was checked (see the inspection script's own `EMPTY/NOT SET` vs `SET (value withheld)` pattern for anything credential-shaped).

## Mentra mail architecture

Every `wp_mail()` call goes through `send_form_mail()` in `mentra-vietnam-core.php`. Audited its `$headers` construction directly:

```php
$headers = [
    'Content-Type: text/plain; charset=UTF-8',
    self::build_reply_to($reply_name, $reply_email),
];
```

No `From:` header anywhere in the plugin. This already matches the recommended Phase 7 architecture exactly — WP Mail SMTP's forced From Email (once a real one is configured) becomes the sole sender for every message, and the visitor's email is only ever `Reply-To`. **No code change was required.** Phase 5's CRLF/NUL header hardening (`safe_header_value()`) is untouched and still in effect.

## Recipient

All six business forms still resolve through the unchanged `contact_email()` helper (Phase 5's `mentra_vn_contact_email` option, default `contact@domain.vn`). Verified individually in the live test matrix below — General, Sales, Support, Partnership, Media, and Career all delivered `[to] => contact@domain.vn`.

## From / Reply-To

**From**: not set by Mentra code at all (confirmed above) — controlled entirely by WP Mail SMTP once configured. **Reply-To**: `{sanitized name} <{validated email}>` from the submitted form, unchanged from Phase 5. No visitor email is ever used as From.

## UTF-8

All six Vietnamese subject prefixes were captured verbatim from live `wp_mail()` calls, with correct diacritics and no mangling:

```
[MENTRA - LIÊN HỆ]      [MENTRA - KINH DOANH]   [MENTRA - HỖ TRỢ]
[MENTRA - ĐỐI TÁC]      [MENTRA - TRUYỀN THÔNG] [MENTRA - TUYỂN DỤNG]
```

Message bodies (Vietnamese labels like "Họ tên", "Công ty", "Chủ đề đã chọn", "Lĩnh vực chuyên môn") and the Reply-To display name also captured correctly, `Content-Type: text/plain; charset=UTF-8` present on every message. No manual MIME-encoding was added — WordPress's own `wp_mail()`/PHPMailer handles UTF-8 subject/body encoding, and nothing in this codebase interferes with it.

## Delivery tests

Ran the full six-form matrix through the real AJAX pipeline (nonce + reCAPTCHA v2 with Google's official test key pair, temporary, DB-only, never committed + rate limiting all live and unbypassed), captured via a temporary `wp_mail` filter + `wp_mail_failed` action (mu-plugin, deleted after use — no short-circuiting of the real send this time, unlike Phase 5's harness, specifically so real `wp_mail()`/transport behavior could be observed).

| Form type | Recipient | Subject | `wp_mail()` result |
|---|---|---|---|
| GENERAL | contact@domain.vn | `[MENTRA - LIÊN HỆ] - P7 General` | `true` |
| SALES | contact@domain.vn | `[MENTRA - KINH DOANH] - P7 Sales` | `true` |
| SUPPORT | contact@domain.vn | `[MENTRA - HỖ TRỢ] - P7 Support` | `true` |
| PARTNERSHIP | contact@domain.vn | `[MENTRA - ĐỐI TÁC] - P7 Partner` | `true` |
| MEDIA | contact@domain.vn | `[MENTRA - TRUYỀN THÔNG] - P7 Media` | `true` |
| CAREER | contact@domain.vn | `[MENTRA - TUYỂN DỤNG] - P7 Career` | `true` |

`wp_mail_failed` never fired for any of the six. **Important caveat**: `wp_mail()` returning `true` here reflects PHP's native `mail()` function being invoked without a PHP-level error on this local dev box — it is **not** evidence of real external delivery, since no real SMTP/API provider is configured (see WP Mail SMTP state above). Per instruction, this is reported honestly rather than treated as proof of delivery.

### WP Mail SMTP's own Email Test feature

Available at **WP Mail SMTP → Tools → Email Test** in wp-admin. Not driven programmatically this phase — with the mailer still set to native `mail()`, running it would only re-confirm the same local-`mail()` behavior already observed above, not exercise a real provider. Documented in `docs/mail-configuration.md` as the owner's next concrete step once a provider is configured.

### Mailbox receipt

**MAILBOX RECEIPT REQUIRES OWNER VERIFICATION.** No real mailbox is reachable from this environment/tooling, and no real provider is configured to deliver anywhere external in the first place. This is stated explicitly rather than inferring or fabricating inbox receipt from the `wp_mail() === true` result above.

## Failure tests

Reproduced the "failed reCAPTCHA verification" case cleanly (deliberately wrong, non-empty secret against real Google infrastructure — a genuine, non-mocked failure): HTTP 400, `{"success":false,...}"Không thể xác minh reCAPTCHA..."}`, **zero** new `wp_mail()` calls logged. (An earlier attempt in this same test run was invalidated by a shell-scripting mistake that left the secret unswapped — caught, discarded, and cleanly re-run before being recorded here; noted for transparency, not treated as a finding.)

Frontend JSON error handling (`wp_send_json_error()` → fixed Vietnamese message, no provider/server internals) is unchanged from Phase 5/6 — this phase did not touch `contact_ajax()`/`career_ajax()`/`newsletter()`/`send_form_mail()` at all, so no new failure-path testing of the application layer was needed beyond re-confirming no regression (below).

## Security regression (Phase 6)

| Case | Result |
|---|---|
| Missing reCAPTCHA token | HTTP 400, correct message, **no `wp_mail()` call** |
| Failed reCAPTCHA verification (real Google, wrong secret) | HTTP 400, correct message, **no `wp_mail()` call** |
| Rate limit (6th `partnership` request from same client) | HTTP 429, correct message, **no `wp_mail()` call** |
| Valid reCAPTCHA (all six types) | Reaches `wp_mail()` correctly (see Delivery Tests table) |

Confirms Phase 6's fail-closed guarantees are fully intact after the external mobile-menu commits and this phase's own (docs-only) changes.

## Frontend regression

Routes: `/`, `/lien-he/`, `/lien-he/?topic=sales`, `/lien-he/?topic=support`, `/doi-tac/`, `/truyen-thong/`, `/tuyen-dung/`, `/mentra-live/`, `/mentra-os/`, `/even-realities/`, `/mang-xa-hoi/`, `/tin-tuc/` — all HTTP 200. Source-level check of the homepage confirmed the desktop header (`.site-header--transparent`) and mobile-menu trigger (`aria-label="Mở menu"`) markup introduced by the external commits are present and structurally intact; no browser/visual regression testing was performed (no browser automation available in this environment — same documented limitation as prior phases). No redesign was made or needed.

## Production DNS requirements

Documented conceptually in `docs/mail-configuration.md` (SPF/DKIM/DMARC alignment for whichever provider and domain are eventually chosen). No DNS was inspected or altered — this project has no access to the real production domain's DNS, and `contact@domain.vn` is a confirmed placeholder (see `docs/project-status.md`), not a live domain to check records against.

## Owner verification required

1. Choose a real transport provider in **WP Mail SMTP → Settings** and enter its credentials directly in wp-admin (never in this repository).
2. Replace the placeholder From Email (`dev-email@wpengine.local`) with a real, domain-owned address once the production domain is finalized.
3. Use **WP Mail SMTP → Tools → Email Test** to confirm real delivery, and check the destination inbox (including spam) directly.
4. Configure SPF/DKIM/DMARC DNS records for the chosen provider/domain in production.
5. Once the production domain is finalized, update `mentra_vn_contact_email` (Settings → Mentra Việt Nam) to the real recipient if it changes from the current placeholder.

## Syntax tests

`php -l` full sweep of every theme/plugin PHP file: pass. `node --check` on `mentra.js`: pass. No new warnings or fatals (no code was changed this phase, other than documentation).

## Phase 7 gate

| Requirement | Status |
|---|---|
| WP Mail SMTP installed and active | PASS |
| Provider configured | **NOT MET** — mailer still `mail`, no credentials |
| Custom Mentra code uses `wp_mail()` only | PASS |
| No custom SMTP transport exists | PASS |
| All six business forms route correctly | PASS |
| Recipient remains `contact@domain.vn` | PASS |
| Reply-To remains sanitized/hardened | PASS |
| From architecture is provider/domain-safe | PASS (no From header set by Mentra code) |
| Vietnamese subject/body survives | PASS |
| Valid submissions reach SMTP transport | PASS (reach `wp_mail()`; real external transport not configured) |
| Application failure path works safely | PASS |
| reCAPTCHA/rate limiting unchanged | PASS |
| Frontend unchanged | PASS |
| Syntax/regression checks pass | PASS |
| Actual delivery verified as far as tooling/access allows | **PENDING** — no real provider configured, no mailbox reachable |

**Phase 7 status: IMPLEMENTATION PASS / DELIVERY CONFIGURATION PENDING.** This is the expected outcome given WP Mail SMTP has no real provider configured yet — not a code defect. Every application-layer requirement (architecture, headers, recipient, security regression, UTF-8, syntax) passes; only the operational SMTP/API provider setup and its real-mailbox verification remain, and those are owner actions documented in `docs/mail-configuration.md`.
