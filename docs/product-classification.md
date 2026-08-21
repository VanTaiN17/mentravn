# Product Classification — Phase 3

Date: 2026-08-21
Method: inspected `WGET_REFERENCE` (`D:\Workspace\website\mentra-vn\mentraglass.com`) directly for embedded Shopify `__remixContext` product/loader JSON on each candidate page, cross-checked against the current WordPress templates (`page-mentra-live.php`, and the `templates/source/even-realities.html` / `nimo.html` / `prescriptions.html` static mirrors).

## Findings

| Item | Physical SKU owned by Mentra | Sold by this site | Inventory meaningful | Recommended system | Reason |
|---|---|---|---|---|---|
| **Mentra Live** | Yes | Yes (informationally — contact-to-order, no checkout) | Yes | **WOOCOMMERCE PRODUCT** | Confirmed real Shopify product object in `live.html`: `gid://shopify/Product/8963292102908`, handle `mentra-live-camera-glasses`, title "Mentra Live Camera Glasses". Two real variants with independently differing availability: Black (`availableForSale:true`) and Transparent (`availableForSale:false`). This is a genuine physical product Mentra manufactures and ships, with meaningful per-variant stock state — exactly the case WooCommerce-as-catalog-CMS is for. |
| **Even Realities G2** | No | No | No | **STATIC PAGE** (unchanged) | `even-realities.html` has zero `"product":{...}` object in its loader JSON (confirmed: 0 matches for `gid://shopify/Product/` in the file). The page's own CTA is **"Buy Even Realities G2"**, linking externally to `evenrealities.com` — not a Mentra checkout. Even Realities is a third-party hardware maker; Mentra sells compatibility/software (MentraOS support), not the physical glasses. There is no SKU, no stock state, nothing for Mentra's inventory to track. Modeling this as a WooCommerce product would misrepresent it as something Mentra stocks and could sell. |
| **NIMO** | No | No | No | **STATIC PAGE** (unchanged) | `nimo.html` has zero product object. Page state is "Coming soon" / pre-launch partner hardware with no purchase path of any kind yet (not even an external one). No SKU, no stock, nothing to track. Forcing this into WooCommerce would require inventing a fake "coming soon" product with no real data behind it. |
| **Prescription lenses** | No (add-on service, not separately stocked) | Yes, but as a custom/quote service, not a stocked SKU | No | **STATIC PAGE** (unchanged) | `prescriptions.html` has zero product object. Source copy is "Contact sales to add single-vision prescription lenses to your Mentra Live order" — a custom deployment/service add-on fulfilled through a sales conversation per order, not a stocked, orderable SKU with its own inventory count. The current WordPress page (`/trong-kinh/`) already implements exactly the right pattern: informational content + a "Contact Sales" CTA. Nothing here benefits from being modeled as a catalog item — there's no stock state to display. |

## Conclusion

Matches the expected default exactly: **Mentra Live → WooCommerce Product; Even Realities, NIMO, Prescription lenses → remain static pages.** No disagreement with the expected classification — the wget source data provides an unambiguous, mechanical signal (presence/absence of a Shopify `product` object) that lines up cleanly with which items have real, trackable inventory versus which are informational/partner/service pages.

No changes are made to `/even-realities/`, `/nimo/`, or `/trong-kinh/` in this phase — their existing design and content (fixed for routing/translation in Phase 2) are preserved as-is.
