# Forms Inventory — Phase 5

Every form on the site, its real fields (as found in the markup — nothing invented), its AJAX action, and how it routes.

## 1. Newsletter (footer, site-wide)

- **Markup**: `form[data-mentra-newsletter="1"]`, present in the shared footer on every page (`mailing-ender-email`).
- **Fields**: `email` (required, `type="email"`).
- **AJAX action**: `mentra_vn_newsletter` (unchanged, Phase 1-era).
- **Storage**: `mentra_subscriber` CPT (title = email). Duplicate-safe: looked up by exact title match before insert.
- **Phase 5 change**: none. Reviewed only, per instruction — nonce (`mentra_vn_public`), email validation (`sanitize_email` + `is_email`), duplicate prevention, JSON response, and duplicate-submit UX (`initNewsletter()` disables the button on submit, re-enables after 1.8s) were all already correct.

## 2. Contact-style form (General / Sales / Support / Partnership / Media)

One shared form design (`form[data-mentra-contact="1"]`), reused byte-identical across five static source pages, only the pre-selected `#contact-subject` option and placeholder copy differ:

| Page (URL) | Source file | Pre-selected subject |
|---|---|---|
| `/lien-he/` | `contact.html` | (none — placeholder) |
| `/lien-he/?topic=sales` | `contact@topic=sales.html` | Sales |
| `/lien-he/?topic=support` | `contact@topic=support.html` | Technical Support |
| `/doi-tac/` | `partnerships.html` | Partnerships |
| `/truyen-thong/` | `media-inquiries.html` | Media Inquiries |

**Fields** (`#contact-name`, `#contact-email`, `#contact-company` [optional], `#contact-subject` [select, optional in the DOM], `#contact-message`).

**`#contact-subject` whitelist** (all 9 option values already present in the markup — none invented):

| Dropdown value | Resolved form type |
|---|---|
| *(empty/unselected)* | GENERAL |
| General Question | GENERAL |
| Sales | SALES |
| Order & Shipping | SALES |
| Technical Support | SUPPORT |
| Partnerships | PARTNERSHIP |
| Business & Partnerships | PARTNERSHIP |
| Media Inquiries | MEDIA |
| Phản hồi | GENERAL |
| Other | GENERAL |

Any value outside this whitelist is rejected (HTTP 400), never guessed.

**AJAX action**: `mentra_vn_contact_ajax` (existing action name kept, handler rewritten — see `docs/phase-5-report.md`).

### Routing fix required

Before this phase, `/lien-he/?topic=sales` and `/lien-he/?topic=support` silently rendered the exact same `contact.html` as plain `/lien-he/` — `mentra_vn_get_source_key()` never looked at `$_GET['topic']`, even though `contact@topic=sales.html` and `contact@topic=support.html` already existed on disk from the original WGET capture, unused. Fixed in `functions.php`: the `topic` query value is passed through `sanitize_key()` and checked against a small fixed whitelist (`sales`, `support`) before selecting the file; anything else — or no match — falls back to the plain contact page. No static HTML was edited; this is routing/dispatch logic only.

## 3. Career form

- **Page**: `/tuyen-dung/` (`careers.html`), previously had **no backend at all** — confirmed via `grep` on `mentra.js` and the plugin before this phase; the form submitted nowhere.
- **Markup**: `form[data-career-form]`.
- **Fields** (all pre-existing, none invented): `#career-name` (required), `#career-email` (required), `#career-expertise` (required `<select>`, 8 fixed options), `#career-position` (optional text), `#career-portfolio` (optional URL), `#career-why` (required textarea).
- **`#career-expertise` whitelist**: Software Engineering, Hardware / Electrical Engineering, AI / Machine Learning, Product Design / UX, Marketing / Growth, Operations / Business, Community / Content, Other. Any other value is rejected.
- **AJAX action**: new — `mentra_vn_career_ajax`.
- **Status element**: `mentra.css` already ships a fully-styled but orphaned `.career-form-status` / `.is-error` / `.is-success` rule (lines ~1474-1477, `.mentra-vn-careers` scope) with no corresponding element anywhere in `careers.html` — almost certainly ported from the real source's embedded `<style>` block in an earlier phase, the same pattern as VIS-004 (Even Realities). `initCareer()` now creates and fills that element on submit/response. This uses only pre-existing CSS classnames, adds no new visual language, and is not a redesign.

## 4. Recipient routing (all six business-contact types)

Single WordPress option, `mentra_vn_contact_email` (default `contact@domain.vn`), editable at **Settings → Mentra Việt Nam**. Replaces the old two-way `sales_email` / `support_email` split (which was never referenced anywhere else in the codebase — safe to remove).

Every submission still carries its resolved `$type` (`general` / `sales` / `support` / `partnership` / `media` / `career`) through the pipeline and into the mail body ("Loại: ..."), even though all six currently resolve to the same mailbox — so recipients can be split by type later purely in `contact_email()` / a future per-type option, with no frontend change required.

## 5. Mail subjects (server-generated only)

| Type | Prefix |
|---|---|
| GENERAL | `[MENTRA - LIÊN HỆ]` |
| SALES | `[MENTRA - KINH DOANH]` |
| SUPPORT | `[MENTRA - HỖ TRỢ]` |
| PARTNERSHIP | `[MENTRA - ĐỐI TÁC]` |
| MEDIA | `[MENTRA - TRUYỀN THÔNG]` |
| CAREER | `[MENTRA - TUYỂN DỤNG]` |

The client never supplies a prefix; the final subject is always `{server prefix} - {sanitized name}`.
