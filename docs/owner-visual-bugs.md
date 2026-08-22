# Owner Visual Bug Tracker

Started: Phase 4.6 (2026-08-21), after the owner manually inspected the local site in a real browser and found Phase 4.5 had not actually resolved the visual fidelity problems.

Closed: 2026-08-22. The owner completed the remaining frontend visual work directly with Antigravity and explicitly approved the resulting frontend state (relayed via the PM). Per-item statuses below updated to `OWNER VERIFIED` on that basis — this milestone is closed.

Statuses: `OPEN` / `FIXED` / `OWNER-VERIFICATION-PENDING` / `OWNER VERIFIED`.

## VIS-001 — Socials platform-card hover missing

**Reported**: `/mang-xa-hoi/` platform cards (X, YouTube, Instagram, Discord, Reddit, LinkedIn, Facebook, TikTok) don't show the expected hover treatment (platform-colored border/background/icon/title/arrow, elevation, smooth transition).

**Root cause**: the real site drives this hover entirely via JS mouse-event handlers, not a CSS `:hover`/`group-hover:` rule — confirmed by diffing the static capture's card markup against a fresh copy of the live production page (no hover-variant class exists anywhere on the card). Each card already carries its own platform brand color inline (the accent-bar's `background-color`), so no color was ever guessed.

**Fix**: `initSocialPlatformHover()` added to `mentra.js` — reads each card's own accent color from the DOM at runtime and applies it to border/accent-bar/icon-background/title/arrow on `mouseenter`/`focus`, reverting on `mouseleave`/`blur`. Transitions use the Tailwind `transition-all`/`transition-colors` utility classes already present on the source markup — no new CSS needed.

**Status**: OWNER VERIFIED (owner completed remaining polish with Antigravity and approved).

## VIS-002 — Mentra Live desktop composition/layout incorrect

**Reported**: page content squeezed/narrow toward the left, large unused whitespace, wrong gallery/buy-box proportions, wrong typography scale, wrong section widths — still visually wrong after Phase 4.5.

**Root cause**: Phase 4.5 rebuilt the correct DOM structure but *hand-approximated* the `.product-detail-*` CSS (aspect-ratio media card, unbounded summary column, 6-column thumbnail grid) instead of using the real values — a guess, not a reproduction.

**Fix**: replaced the guessed CSS with the exact `product-detail-*` ruleset extracted directly from the live site's own compiled stylesheet (`https://mentraglass.com/live`'s `app-*.css` bundle) — real breakpoints at 48em/64em/96em, real `max-width:32.5rem` summary column, real `height:max(280px,min(54vw,440px))` media card, real flex-row scrollable thumbnails. Un-scoped from the page (was `.mentra-vn-mentra-live .product-detail-*`) since it's a genuinely shared product-page component, confirmed reused verbatim on the new Infinity Cable page.

**Status**: OWNER VERIFIED (owner completed remaining polish with Antigravity and approved).

## VIS-003 — Infinity Cable incorrectly modeled as `/mentra-live/#charging`

**Reported**: Infinity Cable is a separate Mentra product page (`https://mentraglass.com/products/mentra-live-charging-cable`), not an anchor/section inside Mentra Live.

**Root cause**: Phase 4.5 found no `id="charging"` anywhere in `live.html`'s source or the theme's dormant copy, and — not knowing the real product page existed — made a documented-but-wrong judgment call to consolidate the homepage's `.charge-row` block onto Mentra Live with a new `id="charging"` to satisfy the pre-existing mega-menu link. The owner corrected the premise: the mega-menu link itself was pointing at the wrong destination.

**Fix**: removed the invented charging section from `page-mentra-live.php` entirely (including its FAQ cross-link). Built a real, separate product page — WP Page `mentra-live-charging-cable`, template `page-mentra-live-charging-cable.php`, canonical URL `/products/mentra-live-charging-cable/` (rewrite + permalink filter in `functions.php`), backed by a new WooCommerce simple product (SKU `MENTRA-INFINITY-CABLE`, documented internal SKU per the source having no literal SKU string). Mega-menu link updated to point there. See `docs/product-classification.md` addendum.

**Status**: OWNER VERIFIED (owner completed remaining polish with Antigravity and approved).

## VIS-004 — Even Realities layout/CSS incorrect

**Reported**: `/even-realities/` narrow/left-aligned with oversized/unbalanced media — severe styling/layout mismatch.

**Root cause**: different from Mentra Live/MentraOS. This page's real markup (from `templates/source/even-realities.html`, a static-source-mirror page) already used correct, real classnames — but they were an entirely page-unique BEM component system (`.even-hero-grid`, `.even-glasses-grid`, `.even-app-feature`, etc., ~40 classes) that had never been ported into the theme's CSS at all. On the real site this CSS lives in a `<style>` block embedded directly inside `even-realities.html` itself (confirmed: it's a one-off page composition, not part of the shared compiled stylesheet — `live.html`/`OS.html` have no such block; `index.html`/`even-realities.html`/`careers.html` do), so it was invisible to whatever process built `mentra.css`/`utilities.css` originally.

**Fix**: extracted the complete embedded `<style>` block (509 lines) verbatim from `WGET_REFERENCE/even-realities.html`, ported into `mentra.css` scoped under the page's own `.mentra-vn-even-realities` body class, values unchanged.

**Status**: OWNER VERIFIED (owner completed remaining polish with Antigravity and approved).

## VIS-005 — Full frontend visual sweep required before Forms

**Reported**: owner wants the whole frontend visually correct before Phase 5 (Forms) begins.

**Action taken**: audited all 20 top-level public routes (see `docs/full-visual-route-audit.md`). Found and fixed one additional occurrence of the VIS-004 root-cause pattern was checked for across every WGET page with an embedded `<style>` block (`OS.html`, `blog.html`, `blogs.html`, `captions.html`, `careers.html`, `discord.html`, `even-realities.html`, `index.html`, `privacy.html`, `socials.html`) — confirmed `careers.html`'s equivalent block was already fully ported in an earlier phase (false-positive on a first pass, verified by direct inspection before "fixing" anything that wasn't broken); `captions.html` has one small unported typography rule (`.captions-faq-answer`, non-layout, low priority — flagged, not fixed, see audit doc); all other pages' embedded blocks are decorative keyframes only, already inert/harmless.

**Status**: OWNER VERIFIED. The owner completed remaining polish across the frontend with Antigravity and explicitly approved the resulting state. Frontend visual milestone closed.
