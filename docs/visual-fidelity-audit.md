# Visual Fidelity Audit — Phase 4.5

Date: 2026-08-21
Scope: `/mentra-live/`, `/mentra-os/`, `/mang-xa-hoi/`
Method: DOM/section/class comparison between `WGET_REFERENCE` (`D:\Workspace\website\mentra-vn\mentraglass.com`, immutable, never modified), the theme's dormant localized copies (`templates/source/live.html`, `templates/source/OS.html`, `templates/source/socials.html`), the pre-Phase-4.5 WordPress templates, and the actually-enqueued CSS (`assets/css/mentra.css`, `assets/css/utilities.css` — confirmed via `wp_enqueue_style` calls in `functions.php`).

## Root cause (not "missing CSS")

`page-mentra-live.php` and `page-mentra-os.php` were hand-authored using an invented classname vocabulary (`product-page-hero`, `split`, `cards-3`, `feature-card`, `spec-grid`, `section-black/green/soft`, `.reveal`). Those classnames exist **only** in `wp-content/themes/mentra-vietnam/assets/css/main.css` — a file that is **never enqueued anywhere** (confirmed: `functions.php` only calls `wp_enqueue_style()` for `mentra-utilities`, `mentra-source` (mentra.css), and the Red Hat Display font). `main.css` also defines a completely different, generic design system (Inter font, `--bg:#f7f7f3`, etc.) unrelated to the site's real tokens. Result: both pages rendered as effectively unstyled browser-default HTML. Confirmed by direct grep — of 11 invented classnames spot-checked, 9 had zero rules anywhere in the real, enqueued stylesheets; the 2 coincidental hits (`.btn-dark`, `.media-card`) were unrelated single rules, not a working design.

Separately, both templates used `class="reveal"`, which has zero CSS rules and zero JS binding — the site's real scroll-reveal system is `[data-mentra-reveal]` + `initReveal()` in `mentra.js`. `class="reveal"` elements never animated and were never hidden either way (dead markup).

The real design system (`mentra.css` design tokens + `utilities.css`, a full compiled Tailwind v4 utility layer built by scanning the site's own source HTML) was already complete and correct — the WGET source's real markup uses real Tailwind utility classes plus a handful of BEM component classes, and is directly reproducible once the correct classnames are used.

## Mentra Live (`/mentra-live/`)

| Section (source order) | WGET exists | WP exists (before) | Markup match (before) | CSS match (before) | JS/effect match (before) | Assets | Action taken |
|---|---|---|---|---|---|---|---|
| Header / nav | YES | YES | YES (shared partial) | YES | YES | — | KEEP (shared `site-header` partial, out of scope) |
| Product gallery + thumbnails | YES | PARTIAL (1 static image, no gallery) | NO | NO | NO | present | RESTORE + DYNAMIC-WIRE (WooCommerce gallery images) |
| Buy box (title/price/CTA) | YES | PARTIAL (title+price+CTA, wrong layout) | NO | NO | NO | — | RESTORE layout, REMOVE-BY-BUSINESS-RULE (price/qty/Order Now → stock label + Liên hệ mua hàng) |
| "As seen in" press logo row | YES | NO | NO | NO | — | present | RESTORE |
| Spec table (Platform/Hardware/Display/Lenses/Fulfillment/Return) | YES | NO (different "spec-grid" content) | NO | NO | — | — | RESTORE + DYNAMIC-WIRE (SKU, per-color stock appended) |
| About Mentra Live copy | YES | PARTIAL (different 3-card content) | NO | NO | — | — | RESTORE |
| Accordion: Features (3 videos) | YES | NO | NO | NO | NO | present | RESTORE |
| Accordion: Specifications | YES | NO | NO | NO | NO | — | RESTORE |
| Returns & warranty | YES | NO | NO | NO | — | — | RESTORE (flagged for VN legal review per CLAUDE.md) |
| Accordion: FAQ | YES | NO | NO | NO | NO | — | RESTORE (price-mentioning answer rewritten per business rule) |
| Charging / Infinity Cable, `id="charging"` | NO on `live.html` itself (lives on `index.html`'s `.charge-row` block; no anchor exists on `live.html` in either WGET or the dormant copy) | NO | — | — | — | present | RESTORE (consolidated onto this page using the real `.charge-row` source markup/copy from `index.html`, with `id="charging"` added — matches the nav's existing `/mentra-live/#charging` link; see note below) |
| Enterprise/bulk CTA | YES (site's own established pattern) | YES | YES | N/A (real `.section-green` class already used elsewhere) | — | — | KEEP |
| Newsletter signup | YES | NO | NO | NO | NO | — | RESTORE (`data-mentra-newsletter="1"`, matches existing `initNewsletter()` contract) |
| Footer | YES | YES (shared partial) | YES | YES | YES | — | KEEP |

**Charging section decision**: `live.html` itself has no `id="charging"` anchor in either the WGET source or the localized copy — the real Infinity Cable/Charging Case content lives in a `.charge-row` block on the homepage (`index.html`). Since the site's own mega-menu already links to `/mentra-live/#charging` (an existing, pre-existing nav decision, not something this phase invented), and `.charge-row`/`.charge-col` CSS already exists in `mentra.css` (built for the homepage), the section was added to `page-mentra-live.php` using the homepage's real markup/Vietnamese copy verbatim, with `id="charging"` and `scroll-margin-top` added so the anchor lands correctly under the fixed header. This consolidates charging info onto the single-page catalog IA the VN site already uses instead of inventing new content.

**Inventory/other**: hero, media, typography, cards, video, hover effects — all restored via the real source markup above. No parallax/sticky-scroll effects exist on this page in the source (confirmed: no such classes in `live.html`). Tab/switch interactions = the 3 accordions (restored with a new `.pdp-accordion-item` component + `initPdpAccordion()`).

## MentraOS (`/mentra-os/`)

8 real sections exist in the WGET source; the pre-4.5 template had only 4, all using invented classnames, with an incorrect dark hero (real hero is light, `bg-surface-0`).

| Section (source order) | WGET exists | WP exists (before) | Markup match (before) | CSS match (before) | Background (real) | Action taken |
|---|---|---|---|---|---|---|
| Hero (headline, badges, phone mockup) | YES | PARTIAL (wrong bg, no download badges) | NO | NO | light | RESTORE |
| Compatibility grid (5 cards) | YES | PARTIAL (3 generic cards) | NO | NO | light | RESTORE (Mentra Live, Even Realities G2 & G1, Vuzix Z100, NIMO "Sắp hỗ trợ") |
| MiniApp Store (logo grid + phone mockup) | YES | PARTIAL (no logo grid, no CTA, wrong image) | NO | NO | soft | RESTORE |
| "Tổng quan MentraOS" (5-row numbered list) | YES | NO | NO | NO | light | RESTORE |
| "Apps built for your glasses" (4-card grid) | YES | NO | NO | NO | light | RESTORE |
| SDK / developer code panel | YES | PARTIAL (no code panel) | NO | NO | gradient light→soft→light | RESTORE |
| Download promo (dark CTA banner) | YES | NO | NO | NO (component never built anywhere in theme) | dark (only dark section on page) | RESTORE (new `.green-grid-promo`/`.os-download-promo*` CSS authored) |
| Newsletter signup | YES | NO | NO | NO | light | RESTORE |

Inventory: no parallax/sticky-media effects in source; scroll-reveal (`data-mentra-reveal`) staggers hero text, compatibility cards, and key headings — reproduced. Hover effects on compatibility cards (lift + image zoom) already covered by existing `mentra.css` rule (`.os-compatible-card:hover`). Two translation-quality bugs found in the dormant localized copy while porting and corrected: (1) the "Tổng quan MentraOS" list had partially untranslated English sentences and a broken "Nhà phát triểns" plural artifact — rewritten cleanly in Vietnamese; (2) the SDK code sample's dormant copy had a corrupted `data.text` identifier auto-translated into `của bạn.text`, which would break the code sample — restored to valid TypeScript.

## Socials (`/mang-xa-hoi/`)

Layout was already correct (owner's own assessment, confirmed by audit). The reported "missing effects" traced to a **site-wide** bug, not a Socials-specific gap:

| Finding | Detail |
|---|---|
| `data-mentra-reveal` attribute count | 13 in current `socials.html` (hero ×4, 8 platform cards, newsletter CTA) — a normal count for this site (`contact.html` 10, `live.html` 10, `index.html` 37); an earlier `grep -c` reading of "1" was a line-count artifact of the file being single-line-minified, not a real gap. |
| JS wiring | `initReveal()` confirmed invoked on `DOMContentLoaded` (`mentra.js:516`) — IntersectionObserver fires correctly and adds `.is-visible`. |
| Root cause of "no animation visible" | Every `data-mentra-reveal` element's static HTML — on **every** page site-wide, not just Socials — carries a baked-in inline `style="opacity:1;transform:none;transition:none"`, a leftover artifact from the original Hydrogen/Framer Motion capture (wget caught the DOM post-animation). An inline style always wins over an external stylesheet rule of equal/lower specificity that lacks `!important`, so `[data-mentra-reveal]{opacity:0;...}` in `mentra.css` could never actually apply — the reveal was a structural no-op everywhere it's used, not just here. |
| Fix | Added `!important` to the `[data-mentra-reveal]` base rule and its `.is-visible` counterpart in `mentra.css` (global fix, safe: it only completes an already-designed, already-used system; it cannot regress any page that wasn't animating before). |
| Hover/accent classes | Verified byte-for-byte identical between WGET source and current file (`social-icon-link`, `footer-social-icon`, per-platform colored accent bar) — nothing simplified in translation. |

Inventory: hero, platform grid, hover effects, footer — all confirmed intact structurally; only the reveal-on-scroll fade/slide-in was non-functional, now fixed.

## WooCommerce CSS/JS interference (site-wide finding, all pages)

Verified live (curl against rendered HTML) that every public page — not just Mentra Live/MentraOS — was loading `woocommerce-general.css`, `woocommerce-layout.css`, `woocommerce-smallscreen.css`, and `wc-blocks.css` (handle `wc-blocks-style`, unconditionally re-enqueued on every `wp_head` by WooCommerce Blocks' `Notices::enqueue_notice_styles()`, independent of the initial `wp_enqueue_scripts` dequeue), plus two order-attribution marketing/tracking scripts (`sourcebuster-js`, `wc-order-attribution`) that serve no purpose on a catalog-only site with no checkout. None of these are needed since this site never renders a WooCommerce shop/cart/checkout template. Dequeued in `functions.php`, scoped to `!is_admin()` so wp-admin's Products screens are unaffected. Full handle list documented in the code comment at the dequeue site.

## Reveal system audit (task section 13)

Compared initial opacity/transform/duration/easing/threshold/stagger against the source: the CSS/JS system itself (`[data-mentra-reveal]`, `.is-visible`, `initReveal()`) was already correctly built and matches the source's intent exactly (opacity 0→1, translateY(18px)→0, 0.65s cubic-bezier ease, IntersectionObserver threshold 0.08). The only defect was the inline-style specificity conflict described above (now fixed with `!important`), plus the two rebuilt pages using the disconnected `class="reveal"` convention instead of `data-mentra-reveal` (now fixed by rewriting both templates to use the real attribute).

---

# Phase 4.6 addendum — full visual fidelity correction

Date: 2026-08-21. Trigger: the owner visually inspected the local site in a real browser after Phase 4.5 and confirmed it had **not** actually fixed the reported problems. This addendum documents the corrected root-cause analysis; it supersedes the corresponding Phase 4.5 findings where they conflict (Phase 4.5's CSS approximations for Mentra Live/MentraOS were guesses, not verified reproductions).

## Re-audit method: two additional sources of truth

Per the phase instructions, this pass used the **current public production site** (`https://mentraglass.com/`) as a second source of truth alongside the immutable WGET capture, because the WGET capture is confirmed stale/incomplete for at least one route (Infinity Cable's product page did not exist at capture time). Specifically:

1. Downloaded the current public site's compiled CSS bundle directly (`https://mentraglass.com/live`'s single `<link rel="stylesheet">`, a Tailwind-based bundle at `cdn.shopify.com/oxygen-v2/.../app-*.css`, ~338KB) to a local scratch file and grepped it for the exact real rules of every classname in question — not hotlinked, not embedded in any shipped page, used only as a one-time reference during this session.
2. Downloaded current public HTML for `/live`, `/even-realities`, `/socials`, `/os`, and `/products/mentra-live-charging-cable` to a local scratch directory for structural/content reference (the last of these has no WGET equivalent at all).
3. Cross-checked every WGET source page for **embedded `<style>` blocks** (`grep -c "<style"`) — a previously-unexamined signal. Found 10 pages with one: `OS.html`, `blog.html`, `blogs.html`, `captions.html`, `careers.html`, `discord.html`, `even-realities.html`, `index.html`, `privacy.html`, `socials.html`. This directly explained Even Realities' failure (see below) and surfaced a second, already-fixed-in-an-earlier-phase instance (Careers).

## Root cause 1 (Mentra Live, MentraOS): Phase 4.5's CSS was a plausible-looking guess, not a reproduction

Phase 4.5 rebuilt both pages' DOM structurally correctly, but hand-authored the CSS for `.product-detail-*` and `.green-grid-promo`/`.os-download-promo*` by inference from context (padding/sizing values estimated to "look reasonable"), not from any real source. Compared against the real compiled `app.css`:

- `.product-detail-grid` was guessed as `display:grid;gap:40px` with a flat `1.05fr .95fr` desktop split. Real: `grid-template-columns:1fr` (mobile) → `minmax(0,1fr) minmax(0,1fr)` (48em–64em) → `minmax(0,1.1fr) minmax(25rem,.9fr)` (64em+) → `minmax(0,1.05fr) minmax(27rem,.86fr)` (96em+), each with its own gap formula. The guessed version had no 48em/96em breakpoints at all and no `minmax()` floor on the summary column, which is exactly the kind of gap that produces "squeezed left, wrong proportions" at real desktop widths.
- `.product-detail-media-card` was guessed as `aspect-ratio:1/1`. Real: `height:max(280px,min(54vw,440px))` (viewport-relative clamp, not a fixed aspect ratio) — a materially different sizing model.
- `.product-detail-summary` was guessed with no `max-width`, so it would stretch to fill its full grid track. Real: `max-width:32.5rem` (520px) at every breakpoint except the 48–64em tablet band.
- `.product-detail-thumbnails` was guessed as a `grid-template-columns:repeat(6,1fr)`. Real: `display:flex;overflow-x:auto` (a scrollable row, not a fixed 6-column grid) with fixed `5.25rem × 4.5rem` thumb sizing.
- `.green-grid-promo`/`.os-download-promo*` (MentraOS's download-promo banner) had **zero rule anywhere in the theme** in Phase 4.5, so this whole block was invented from scratch — guessed as a dark near-black gradient, centered/stacked layout. Real: a green radial+linear gradient (`#55b974` → `#25825f` → `#0d4d43`), a dot-grid overlay at `64px 64px`, and a two-column grid (`minmax(0,.9fr) minmax(320px,1fr)`) with the actions column right-aligned.

**Fix**: replaced every guessed value with the real extracted CSS (see the two commits `fix: restore Mentra Live desktop composition and layout` and `fix: restore MentraOS download-promo and responsive layout`). `.product-detail-*` was also un-scoped from the page-specific body class, since it's confirmed to be the site's real *shared* product-page template (verified identical on the new Infinity Cable page) — a rule already existed unscoped for `.product-detail-thumb` since Phase 3, so page-scoping it in Phase 4.5 had actually fragmented an intentionally shared component.

## Root cause 2 (Even Realities): a page-unique embedded `<style>` block was never ported

Unlike Mentra Live/MentraOS, `even-realities.html`'s real markup (rendered via the static-source mirror, `mentra_vn_render_source('even-realities')`) already used entirely correct, real classnames — but they belong to a page-unique BEM component system (`.even-hero-grid`, `.even-glasses-grid`, `.even-app-feature`, `.even-setup-list`, `.even-g2-grid`, `.even-final`, ~40 classes total) that has **zero rules anywhere** in `mentra.css`/`utilities.css`. On the real site this CSS is not part of the shared compiled bundle at all — it lives in a `<style>` block embedded directly in `even-realities.html` itself (confirmed: `live.html`/`OS.html` have no embedded style block; one-off marketing-page compositions like `index.html`/`even-realities.html`/`careers.html` do). Whatever process built `mentra.css`/`utilities.css` evidently scanned the shared compiled bundle and/or ran Tailwind's JIT scanner across the HTML content, but never extracted page-embedded `<style>` blocks — so this component was invisible to it, even though the page's own markup was 100% correct.

**Fix**: extracted the complete embedded `<style>` block verbatim from `WGET_REFERENCE/even-realities.html` (509 lines, values unchanged), ported into `mentra.css` scoped under `.mentra-vn-even-realities` (the page's own body class).

**Same pattern checked everywhere else**: every WGET page with an embedded `<style>` block was individually inspected. `careers.html` has an equivalent ~10KB block — but direct inspection of `mentra.css` confirmed it was **already fully ported** in an earlier phase (a first coverage-heuristic pass falsely flagged it as missing, due to a `grep -c` line-count-vs-occurrence-count artifact; verified directly against the file before concluding no action was needed — see `docs/full-visual-route-audit.md`). `captions.html`/`discord.html`/`OS.html`/`socials.html`/`index.html` have only decorative keyframe animations plus (captions.html only) one small unported non-layout typography rule, flagged as low-priority debt.

## Root cause 3 (Socials hover): confirmed JS-driven, not CSS-driven

Diffed the platform-card markup against a freshly-downloaded copy of the current public `/socials` page: no `:hover`/`group-hover:` Tailwind variant class exists anywhere on the card, the accent-bar, the icon box, the title, or the arrow. The real interaction is implemented via React mouse-event handlers mutating inline styles directly — there is no CSS rule to "find and port" for this one. Reproduced with `initSocialPlatformHover()` in `mentra.js` (vanilla JS, `mouseenter`/`mouseleave`/`focus`/`blur`), reading each card's own platform color from its existing accent-bar inline style rather than hardcoding a color table — the source markup's own Tailwind transition-utility classes (`transition-all duration-300` etc., already present pre-Phase-4.6) provide the smooth animation with no new CSS required.

## Root cause 4 (Infinity Cable): a real product page was misclassified as an anchor

Not a CSS root cause — a data/architecture correction. See `docs/owner-visual-bugs.md` VIS-003 and `docs/product-classification.md`'s Phase 4.6 addendum for the full writeup.
