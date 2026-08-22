# Full Visual Route Audit — Phase 4.6

Date: 2026-08-21
Method: (1) the 4 owner-reported routes were fixed via direct comparison against the live production site's compiled stylesheet and/or embedded per-page `<style>` blocks (see `docs/owner-visual-bugs.md`); (2) every other public top-level route was checked for the same root-cause pattern — a classname-coverage heuristic (every `class="..."` token on the rendered page checked against `mentra.css`/`utilities.css`) plus a direct check of every WGET page's own embedded `<style>` block, since that is the newly-discovered failure mode (Even Realities, and previously-already-fixed Careers). No manual browser rendering was available in this environment — see `docs/phase-4.6-report.md`'s Manual Browser Verification section.

| Route | Classification | Notes |
|---|---|---|
| `/` | VISUALLY OK | No page-unique missing-class signal; homepage's embedded `<style>` block is a single decorative loader keyframe, already inert. |
| `/mentra-live/` | **NEEDS STRUCTURE + CSS → FIXED** | Root cause and fix: VIS-002, `docs/owner-visual-bugs.md`. |
| `/products/mentra-live-charging-cable/` | **NEW ROUTE → BUILT** | Root cause and fix: VIS-003. |
| `/mentra-os/` | NEEDS CSS (re-checked) → FIXED | Re-verified against the live production stylesheet: the guessed `.green-grid-promo`/`.os-download-promo*` component from Phase 4.5 was replaced with the real extracted CSS (real green gradient, real two-column grid, real mobile breakpoint block for hero/compatibility-grid/miniapp-store/badges). Structure itself (rebuilt in Phase 4.5 from real inline styles) was already sound. |
| `/even-realities/` | **NEEDS STRUCTURE + CSS → FIXED** | Root cause and fix: VIS-004. |
| `/nimo/` | VISUALLY OK | No page-unique missing-class signal. |
| `/trong-kinh/` | VISUALLY OK | No page-unique missing-class signal. |
| `/so-sanh/` | VISUALLY OK | No page-unique missing-class signal. |
| `/ung-dung/` | VISUALLY OK | No page-unique missing-class signal. |
| `/phu-de/` | **NEEDS CSS (minor)** | `captions.html`'s embedded `<style>` block is mostly decorative keyframes plus one small unported rule, `.captions-faq-answer p/li/ul` (line-height/margin only, no layout impact). Flagged, not fixed this phase — low priority, does not affect page structure/composition. |
| `/mentra-notes/` | VISUALLY OK | No page-unique missing-class signal. |
| `/nha-phat-trien/` | VISUALLY OK | No page-unique missing-class signal. |
| `/ve-mentra/` | VISUALLY OK | No page-unique missing-class signal. |
| `/mang-xa-hoi/` | **NEEDS EFFECT → FIXED** | Root cause and fix: VIS-001. Layout itself already confirmed correct. |
| `/discord/` | VISUALLY OK | Embedded `<style>` block is a single decorative pulse-ring keyframe, already inert. |
| `/ho-tro/` | VISUALLY OK | No page-unique missing-class signal. |
| `/lien-he/` | VISUALLY OK | No page-unique missing-class signal. |
| `/doi-tac/` | VISUALLY OK | No page-unique missing-class signal. |
| `/truyen-thong/` | VISUALLY OK | No page-unique missing-class signal. |
| `/tuyen-dung/` | VISUALLY OK (verified, not re-fixed) | Has the same category of page-unique BEM component system (`.career-hero-grid`, `.career-facts`, etc.) as Even Realities, but a direct check of `mentra.css` (lines ~1081+, "Careers /tuyen-dung — source-close CSS from supplied Mentra Careers page") confirmed the full block was **already ported in an earlier phase** — an initial coverage-heuristic pass flagged it as a false positive (grep line-count vs. occurrence-count artifact, the same class of mistake caught during the Phase 4 CSV incident); verified directly against the file before concluding no fix was needed. |
| `/tin-tuc/` | VISUALLY OK | Phase 4 WordPress-native news architecture; not touched this phase per instructions (no CSS regression found). |

## Method notes

- The classname-coverage heuristic works by extracting every `class="..."` token from the rendered HTML and checking whether it's covered by `mentra.css`/`utilities.css` (Tailwind-style tokens assumed covered by the compiled `utilities.css`, since that file is a full Tailwind v4 build scanned from this site's own source HTML). It is a **signal, not proof** — it flags pages likely to have unstyled custom component classes, matching exactly the pattern found on Mentra Live/MentraOS/Even Realities. Every route marked "VISUALLY OK" above returned only generic WordPress/Yoast/body-class noise (`page-template-default`, `wp-singular`, `woocommerce-no-js`, etc.) in its missing-class list, not a repeated page-unique custom prefix — the same kind of signal that correctly identified Even Realities as broken.
- This heuristic **cannot** detect wrong colors, wrong spacing values within an already-covered class, wrong image proportions from a correctly-styled-but-wrong-value rule, or animation/interaction quality — only "this classname has zero matching CSS rule anywhere." Routes marked VISUALLY OK here are not proven pixel-correct, only proven to not exhibit the specific "orphaned component CSS" failure this phase was tracking down. Real browser verification remains outstanding for all routes (see `docs/phase-4.6-report.md`).
- Every WGET page carrying an embedded `<style>` block was individually inspected (not just heuristically) since that is now a confirmed, repeatable root cause: `OS.html`, `blog.html`, `blogs.html`, `captions.html`, `careers.html`, `discord.html`, `even-realities.html`, `index.html`, `privacy.html`, `socials.html`. `blog.html`/`blogs.html`/`privacy.html` are dormant/superseded (Phase 4 news architecture, and privacy.html is flagged unreachable in `functions.php`) and were not re-checked for live rendering impact.
