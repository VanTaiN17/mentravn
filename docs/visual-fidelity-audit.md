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
