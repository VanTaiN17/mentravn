# Mentra Vietnam — Production Readiness

Compiled at the end of Phase 8 (Final QA / Pre-Production Audit). Categorizes every remaining known item as **READY** (nothing left to do), **OWNER ACTION** (a real wp-admin/business decision, not engineering), **LEGAL ACTION** (needs Vietnam-specific human legal review), or **PRODUCTION CONFIG** (a hosting/deployment-environment step, not a code change). Full technical detail behind each item is in `docs/phase-8-report.md` and the phase report it references.

## READY

- Static marketing pages, routing, redirects (including the Phase 8 English-alias/duplicate-content fixes)
- WooCommerce catalog-only enforcement (no price/cart/checkout anywhere, including the new Purchase form)
- Mentra Live + Infinity Cable product pages, dynamic stock/gallery, correct alt text
- News: 16 real WordPress Posts + 6 static press items, correct routing, correct legacy redirects
- Forms backend: General/Sales/Support/Partnership/Media/Career + Purchase, nonce-scoped security (no Referer dependency), server-side validation/sanitization, Reply-To hardening
- reCAPTCHA v2 Checkbox (fail-closed once configured) + rate limiting (hashed client identity, no raw IP persistence)
- HTML admin + customer acknowledgement email pipeline, CID-embedded logo (no `.local`-domain dependency)
- SEO: locale (`vi`/`vi_VN` consistent everywhere), sitemap (no duplicate/system routes), priority-page metadata (title/description/canonical/OG)
- Accessibility basics (alt text, empty buttons, heading order, aria-live/focus management on form success states)
- Syntax: `php -l`/`node --check` clean across theme + plugin
- Git hygiene: no tracked secrets, no stray dev artifacts

## OWNER ACTION (wp-admin, not engineering)

| Item | Where | Current state |
|---|---|---|
| Production reCAPTCHA keys | Settings → Mentra Việt Nam | Still Google's public v2 test key pair (local-testing only, not a production credential) |
| Production mail provider + credentials | WP Mail SMTP settings | Still the owner's personal Gmail relay, used for real-delivery testing during the hotfix/Purchase-flow verification |
| Real production From Email | WP Mail SMTP settings | Still the owner's personal Gmail address |
| Real production contact recipient | Settings → Mentra Việt Nam → Email nhận liên hệ (`mentra_vn_contact_email`) | Still the owner's personal address (set deliberately during hotfix testing, DB option only, never in source) |
| Final production domain | Replaces the `contact@domain.vn` placeholder | `domain.vn` was never a real registered domain in this project (confirmed during Phase 7's mail audit) |
| WordPress site language admin dropdown | Settings → General → Site Language | The underlying `WPLANG` option is now correctly `vi` (Phase 8 fix) — the admin dropdown will show this as already set; no action needed unless the owner wants to double check |

## PRODUCTION CONFIG (hosting/deployment environment)

| Item | Notes |
|---|---|
| HTTPS | Standard hosting/CDN concern, not applicable to the local install |
| `WP_DEBUG = false`, `display_errors` off | `wp-config.php` is gitignored/environment-specific — confirm on the production host before launch, not a repo change |
| Backups | Hosting-provider concern |
| SPF / DKIM / DMARC | Real production DNS records, once the final domain + mail provider are chosen (see Owner Action above) |
| Permalink flush | Recommended as a standard post-deploy step any time rewrite rules changed (Phase 8 did not change any rewrite structure, only `template_redirect`-level redirects and one new Page — a flush is still good practice after any migration) |
| Yoast XML sitemap | Verified clean this phase (`docs/phase-8-report.md` §13) — no further action, but re-submit to Google Search Console after the domain goes live |
| Cache purge | Standard pre-launch/post-deploy step on the real host |
| Security headers (HSTS, CSP, etc.) | Hosting-dependent, not evaluated in this local environment |

## LEGAL ACTION

| Item | Notes |
|---|---|
| Privacy Policy body content | Currently untranslated/placeholder-era body text at `/chinh-sach-quyen-rieng-tu/` — routing is correct, content needs Vietnam-specific legal review before launch |
| Terms of Service body content | Same — `/dieu-khoan-dich-vu/` |
| Shipping Policy body content | Same — `/chinh-sach-van-chuyen/` |
| Refund Policy body content | Same — `/chinh-sach-doi-tra/` |

None of the above block a technical PASS (per the Phase 8 gate definition) — they are explicitly owner/legal follow-ups, not engineering defects.

## OPTIONAL (non-blocking technical debt, deferred with reason)

| Item | Why deferred |
|---|---|
| Fragile `*.mp4@v=N` hero video filenames | Cosmetic risk only; touching an owner-approved homepage hero for a filename cleanup wasn't judged worth the risk this phase |
| 2 homepage hero videos missing `poster` attribute | No `ffmpeg`/frame-extraction tool available in this environment to generate a real poster image; a mismatched placeholder would risk a visible flash on the owner-approved homepage — needs either tooling or the owner supplying real still images |
| Yoast home-title length | The homepage `<title>` now correctly includes the site name + tagline (Phase 8 fix), but is longer than Google's typical SERP truncation point — cosmetic, not a functional defect; left as Yoast's stock template behavior |

## Summary

**Technical PASS.** Everything in this document outside "READY" is either an owner business/configuration decision, a legal review requirement, a hosting-environment step, or a deliberately-deferred low-risk cosmetic item — none of it is an unresolved engineering defect.
