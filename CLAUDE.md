# Mentra Vietnam — Claude Code Instructions

## Project paths

WORDPRESS_ROOT:
D:\Local Sites\mentra-vn\app\public

ACTIVE_THEME:
wp-content/themes/mentra-vietnam

MENTRA_PLUGIN:
wp-content/plugins/mentra-vietnam-core

WGET_REFERENCE:
D:\Workspace\website\mentra-vn\mentraglass.com

LOCAL_SITE:
http://mentra-vn.local/

GITHUB:
https://github.com/VanTaiN17/mentravn.git

## Communication

- Work and report in English.
- The project manager communicates results to the owner in Vietnamese.
- Be concise and technical.

## Source of truth hierarchy

1. Current business decisions in CLAUDE.md
2. docs/project-status.md
3. Latest phase report
4. docs/route-map.csv
5. docs/site-audit.md
6. Original WGET source for visual/content reference

If older documentation conflicts with a later phase report, the later phase wins.

## Git rules

- Always inspect git status, current branch, log and remote first.
- Never discard user changes.
- Never use git reset --hard.
- Never use git clean -fd.
- Never force push.
- Never push unless the owner explicitly requests it.
- Use one branch per phase.
- Use small logical commits.
- Working tree should be clean at phase boundaries.
- origin is:
  https://github.com/VanTaiN17/mentravn.git

## Visual rules

- Preserve Mentra source design as closely as possible.
- No unsolicited redesign.
- Red Hat Display.
- Desktop header approximately 60px.
- No cart icon.
- Vietnamese frontend.
- Brand/product names remain unchanged.
- WGET source must never be modified.

## WooCommerce business rules

WooCommerce is catalog CMS only.

Allowed:
- product data
- SKU
- images/gallery
- attributes
- variations where necessary
- inventory
- stock status

Not allowed:
- frontend prices
- sales prices
- Add to Cart
- Cart flow
- Checkout
- Payment
- Coupons
- customer My Account flow

Stock frontend:
- Còn hàng
- Hết hàng

Purchase CTA:
- Liên hệ mua hàng
- /lien-he/?topic=sales

Current classification:
- Mentra Live = WooCommerce Product
- Even Realities G2 = static page
- NIMO = static page
- Prescription lenses = static page

## News rules

/tin-tuc/ contains exactly:
- 16 owned blog articles
- 6 external press items

Future architecture:
- 16 articles -> WordPress post_type=post
- 6 press items -> static/external data
- Never import the 6 press items as WordPress Posts

## Form rules

Current form categories include:
- General
- Sales
- Support
- Partnership
- Media
- Career
- Newsletter where applicable

All business-contact form mail currently goes to:
contact@domain.vn

Later:
- Google reCAPTCHA v2 Checkbox
- server-side verification
- wp_mail()
- WP Mail SMTP handles SMTP transport

Do not build custom SMTP transport.

## Legal content

Do not invent or silently rewrite legal policies.

Privacy / Terms / Shipping / Refund source content requires Vietnam-specific legal/business review.

Technical routing may be fixed independently.

## Phase discipline

Do not automatically begin the next phase.

At the end of each phase:
- test
- commit
- update docs/project-status.md
- create/update docs/phase-N-report.md
- report
- STOP
