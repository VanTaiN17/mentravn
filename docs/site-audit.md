# Mentra Vietnam — Site Audit (Phase 1: Full Site Audit)

**Ngày audit:** 2026-08-21
**Phạm vi:** Chỉ audit — không redesign, không triển khai WooCommerce/form/reCAPTCHA, không import Posts, không refactor lớn, không xoá file, không đổi source wget.
**Tài liệu liên quan:** [`route-map.csv`](route-map.csv) (bảng route đầy đủ, machine-readable) · [`translation-audit.md`](translation-audit.md) (chi tiết lỗi tiếng Anh) · [`asset-audit.md`](asset-audit.md) (chi tiết asset lỗi/thiếu) · [`link-audit.md`](link-audit.md) (chi tiết link gãy) · Raw agent reports trong [`_raw/`](_raw/) (dữ liệu thô, không cần đọc lại — đã tổng hợp vào file này)

---

## 0. Workspace & đường dẫn thực tế

| Mục | Giá trị |
|---|---|
| **WORDPRESS_ROOT** | `D:\Local Sites\mentra-vn\app\public` |
| **THEME_PATH** (active) | `D:\Local Sites\mentra-vn\app\public\wp-content\themes\mentra-vietnam` — "Mentra Vietnam 99", version **3.9.0** |
| **PLUGIN_PATH** (custom) | `D:\Local Sites\mentra-vn\app\public\wp-content\plugins\mentra-vietnam-core` — "Mentra Vietnam Core 99", version 3.0.0 |
| **Plugin khác đã cài** | `woocommerce`, `wordpress-seo` (Yoast SEO 28.3), `wp-mail-smtp`, `prime-mover` |
| **SOURCE_ROOT (wget gốc)** | `D:\Workspace\website\mentra-vn\mentraglass.com` — 47 file `.html`, xác nhận không có `.htm` |
| **Website tham chiếu public** | `https://mentraglass.com/` (Shopify Hydrogen/Remix storefront, không được chỉnh sửa trong audit này) |
| **Website WordPress local** | `http://mentra-vn.local/` — **reachable, HTTP 200** trong suốt audit |
| **WP-CLI** | Không cài đặt trong môi trường này — audit dựa vào crawl live site + đọc code thay vì `wp post list`/`wp plugin list` |
| **PHP CLI** | Có (bundled với Local by Flywheel, PHP 8.2.29) — dùng để chạy `php -l` |
| **Stack local xác nhận** | nginx + PHP 8.2 + WordPress 7.1 + WooCommerce 11.0.1 + Yoast SEO 28.3 |

Đã xác nhận `SOURCE_ROOT` không bị chỉnh sửa trong quá trình audit (kiểm tra lại số lượng file `.html` = 47, không có file lạ/mới, không có file nào có mtime hôm nay).

---

## 1. Bối cảnh project (tóm tắt)

Website Mentra Vietnam là bản Việt hoá của storefront Shopify Hydrogen/Remix gốc (`mentraglass.com`), được wget về rồi chuyển thành theme WordPress tuỳ biến (`mentra-vietnam`). Mục tiêu: bám sát giao diện gốc ~99%, CSS/JS chạy local, không phụ thuộc runtime Shopify/Hydrogen, ngôn ngữ tiếng Việt, font Red Hat Display, header desktop ~60px, responsive đầy đủ. Tên thương hiệu (Mentra, Mentra Live, MentraOS, NIMO, Even Realities, GitHub, Android...) không dịch.

## 2. Business rules dùng để phân loại (tóm tắt, chưa triển khai trong Phase 1)

- **WooCommerce** sau này chỉ dùng làm Product Catalog CMS (product/SKU/ảnh/gallery/attributes/variations/stock) — **không** giá, cart, checkout, orders, coupons, my-account. CTA sản phẩm → "Liên hệ mua hàng".
- **Tin tức**: đúng 16 bài viết → `post_type=post`; 6 mục báo chí → static/external, không phải WP Post. Tab tương lai: Tất cả / Báo chí / Bài viết.
- **Forms**: mọi form (Liên hệ chung, Kinh doanh, Hỗ trợ, Đối tác, Truyền thông, Tuyển dụng...) sau này gửi về `contact@domain.vn` qua `wp_mail()` + WP Mail SMTP, nhưng vẫn phân loại riêng theo mục đích.
- **Captcha/Mail**: sau này dùng Google reCAPTCHA v2 Checkbox + `wp_mail()` + WP Mail SMTP — chưa triển khai.

---

## 3. Kiến trúc theme — phát hiện quan trọng cần biết trước khi đọc phần còn lại

Theme **không** dùng mô hình WordPress "nội dung trong DB" thông thường cho hầu hết trang. Thay vào đó:

- `functions.php` chứa một **slug map tĩnh** (`mentra_vn_source_map()`, ~50 slug tiếng Việt/Anh) ánh xạ sang các file HTML tĩnh trong `wp-content/themes/mentra-vietnam/templates/source/*.html` — đây là **bản sao gần như nguyên văn của 47 file wget gốc**, đã được dịch (một phần) trực tiếp trong file.
- `page.php`, `front-page.php`, `page-mentra-live.php`, `page-mentra-os.php`, và cả `404.php` đều gọi `mentra_vn_render_source($key)` — hàm này `file_get_contents()` file HTML tĩnh và `echo` thẳng ra (có thay thế 3 token `{{THEME_URI}}`/`{{HOME_URL}}`/`{{CART_URL}}`). **`header.php`/`footer.php`/WP Loop bị bỏ qua hoàn toàn** cho mọi trang có match trong slug map.
- `header.php`/`footer.php` thật (rất tối giản, không có nav) chỉ được dùng cho các trang **không** khớp slug map — tức là fallback qua `page.php`'s generic branch hoặc `404.php`.
- **Bug cốt lõi gây ra phần lớn lỗi trong audit này**: với 30 slug, không có WP Page nào thực sự tồn tại trong DB khớp slug đó, nên WordPress router rơi vào `404.php`. `404.php` lại tự nhận diện slug qua cùng map và render đúng nội dung tĩnh (đôi khi đã dịch, đôi khi chưa) — nhưng **trả về HTTP 200 kèm `<title>Page not found</title>` và body class `error404`**, không phải 404 thật. Đây là hiện tượng **soft-404**, ảnh hưởng tới 29 route (xem mục Broken Pages/Missing Pages).
- Chỉ có duy nhất **1 custom post type** thật (`mentra_subscriber`, lưu email đăng ký newsletter, `public=>false`). **Không có CPT cho "sản phẩm", "bài báo chí", hay "nhân sự"** — toàn bộ nội dung đó vẫn nằm trong file HTML tĩnh.
- `single.php` (template WP Post thật, dùng cho `post_type=post`) đã tồn tại và hoạt động đúng, nhưng **chưa có Post nào** feed vào nó — `/tin-tuc/` hiện là 100% HTML tĩnh, không phải WP_Query.

---

## 4-5. Inventory Source Wget & Crawl Internal Links

**Phương pháp:** Đây là site Shopify Hydrogen (Remix) SSR — mỗi file HTML nhúng đầy đủ `window.__remixContext` (loader data, route id, menu, JSON-LD, dữ liệu bài viết/báo chí) và `window.__remixManifest`. Route/canonical được xác định qua Remix route `"id"` (đáng tin hơn thẻ `<link rel=canonical>`, vốn không nhất quán) thay vì đoán theo tên file.

- **47/47 file HTML đã audit đầy đủ** (không chỉ index.html). Toàn bộ chi tiết title/meta/canonical/page type/internal links/images/videos/CSS/JS/forms/CTA/Shopify-Hydrogen refs cho từng file nằm trong [`_raw/source-inventory.md`](_raw/source-inventory.md), đã tổng hợp vào [`route-map.csv`](route-map.csv).
- **SOURCE ROUTES**: 47 route (44 route logic duy nhất sau khi gộp 3 biến thể query-string của `/contact`, `/contact?topic=sales`, `/contact?topic=support` — cùng 1 component). Toàn bộ 47 route được xác nhận có ít nhất 1 internal link trỏ tới từ file khác trong tập 47 — **không có file mồ côi** trong nguồn.
- **SOURCE INTERNAL LINKS**: đầy đủ trong `_raw/source-inventory.md` (bảng "Source Internal Links" + link ngoài theo domain).
- **Bất thường phát hiện trong nguồn** (giữ nguyên, không sửa source wget, nhưng cần biết khi port nội dung):
  1. `privacy.html` **byte-identical với `index.html`** — không phải nội dung Privacy Policy thật. Nội dung thật nằm ở `privacy-policy.html`. **Không nên port nội dung `privacy.html`.**
  2. **53 lỗi link case-sensitivity/malformed** trong chính source gốc (48/53 là cùng 1 lỗi: link `os.html` viết thường trong khi file thật là `OS.html`) — chi tiết `link-audit.md` mục 6. Đã xác nhận WordPress hiện tại **không** kế thừa lỗi này.
  3. Chỉ **1 sản phẩm Shopify thật** có SKU/giá: Mentra Live ($449). `even-realities.html`, `nimo.html`, `prescriptions.html` không có object `product` trong loader JSON — là trang thông tin/đối tác.
  4. `blog.html` (route `/blogs/blog`) và `blogs.html` (route `/blogs`) là 2 bản render khác nhau của trang Newsroom — chỉ `blog.html` có 6 thẻ báo chí.

---

## 6-7. Inventory WordPress & Crawl Live Site

**WP-CLI không khả dụng** — audit dùng crawl trực tiếp `http://mentra-vn.local/` (BFS từ nav/footer + sitemap Yoast: `/sitemap_index.xml` → `post-sitemap.xml`, `page-sitemap.xml`, `category-sitemap.xml`, `author-sitemap.xml`) cộng với đọc trực tiếp toàn bộ code theme (9 file PHP) và plugin (1 file PHP), kiểm tra `php -l` (100% pass — xem mục Code Health).

**Tổng route WordPress đã kiểm chứng qua HTTP: 58** (bao gồm cả route hạ tầng như `/wp-json/`, `/sitemap.xml` và các sub-sitemap). Trong đó, so sánh trực tiếp với 44 route nguồn + 9 route WP-only (tổng 53 route "nội dung" có thể so sánh), phân bổ:

| Trạng thái | Số lượng | Ghi chú |
|---|---|---|
| Route thật, nội dung đúng (200, real content) | 21 | Gồm cả một số route mặc định WordPress/WooCommerce cần dọn (`/shop/`, `/cart/`, `/my-account/`, `/hello-world/`...) |
| **Soft-404** (200 nhưng title/body-class báo lỗi, do bug routing) | 13 | Route nav/footer chính |
| **Soft-404** — bài viết `/blogs/blog/*` | 16 | 1 cho mỗi trong 16 bài |
| **Soft-404** — xác minh bổ sung trực tiếp (không có trong crawl gốc) | 4 | `/nimo/`, `/even-realities/`, `/legacy/`, `/privacy/` — tự kiểm tra thêm trong phiên audit này |
| **HTTP 500 (fatal error thật)** | 2 | `/mentra-live/`, `/mentra-os/` |
| **HTTP 404 thật** | 1 | `/get-mentra` |
| **Redirect (302, hành vi WooCommerce chuẩn)** | 1 | `/checkout/` → `/cart/` |

Chi tiết đầy đủ từng route nằm trong `_raw/wp-crawl-translation.md` và đã hợp nhất vào `route-map.csv`.

---

## 8. Bảng Route Chính

Bảng đầy đủ (53 dòng, gồm ghi chú chi tiết từng route) nằm trong **[`route-map.csv`](route-map.csv)**. Dưới đây là bản rút gọn theo đúng format yêu cầu; cột Notes đã rút gọn — xem CSV để có chi tiết đầy đủ.

| # | Source Route | VN Route | Page Type | Status | Vietnamese | CSS | JS | Assets | Future System |
|---|---|---|---|---|---|---|---|---|---|
| 1 | `/` | `/` | Homepage | DONE | PARTIAL | OK | OK | PARTIAL | STATIC PAGE |
| 2 | `/about` | `/ve-mentra/` | About | DONE | PARTIAL | OK | OK | OK | STATIC PAGE |
| 3 | `/os` | `/mentra-os/` | Product/Platform | **BROKEN (500)** | N/A | N/A | N/A | N/A | STATIC PAGE |
| 4 | `/live` | `/mentra-live/` | WooCommerce Product | **BROKEN (500)** | N/A | N/A | N/A | N/A | WOOCOMMERCE PRODUCT |
| 5 | `/devs` | `/nha-phat-trien/` | Developer | MISSING (soft-404) | PARTIAL | OK | OK | OK | STATIC PAGE |
| 6 | `/discord` | `/discord/` | Social | MISSING (soft-404) | PARTIAL | OK | OK | OK | STATIC PAGE |
| 7 | `/even-realities` | `/even-realities/` | WooCommerce Product | MISSING (soft-404) | UNKNOWN | OK | OK | OK | WOOCOMMERCE PRODUCT |
| 8 | `/legacy` | `/legacy/` | Legacy/Archive | MISSING (soft-404) | PARTIAL | OK | OK | OK | STATIC PAGE |
| 9 | `/nimo` | `/nimo/` | WooCommerce Product | MISSING (soft-404) | UNKNOWN | OK | OK | OK | WOOCOMMERCE PRODUCT |
| 10 | `/apps` | `/ung-dung/` | Landing Page | DONE | YES | OK | OK | OK | STATIC PAGE |
| 11 | `/blogs` (+`/blogs/blog`) | `/tin-tuc/` | Blog Index | DONE | YES | OK | OK | PARTIAL | WORDPRESS POST + PRESS STATIC |
| 12-27 | `/blogs/blog/{16 slugs}` | `/blogs/blog/{16 slugs}/` | Blog Article | MISSING (soft-404) ×16 | NO | OK | OK | PARTIAL | WORDPRESS POST |
| 28 | `/captions` | `/phu-de/` | Landing Page | MISSING (soft-404) | PARTIAL | OK | OK | OK | STATIC PAGE |
| 29 | `/careers` | `/tuyen-dung/` | Careers | PARTIAL (form chết) | YES | OK | OK | OK | FORM PAGE |
| 30 | `/compare` | `/so-sanh/` | Landing Page | DONE | PARTIAL | OK | OK | OK | STATIC PAGE |
| 31 | `/contact` (×3 nguồn) | `/lien-he/` | Contact | PARTIAL | PARTIAL | OK | OK | OK | FORM PAGE |
| 32 | `/media-inquiries` | `/truyen-thong/` | Press/Media form | MISSING (soft-404) | PARTIAL | OK | OK | OK | FORM PAGE |
| 33 | `/notes` | `/mentra-notes/` | Landing Page | MISSING (soft-404) | NO | OK | OK | **BROKEN** (ảnh thiếu) | STATIC PAGE |
| 34 | `/partnerships` | `/doi-tac/` | Partnerships form | MISSING (soft-404) | YES | OK | OK | OK | FORM PAGE |
| 35 | `/prescriptions` | `/trong-kinh/` | WooCommerce Product | DONE | YES | OK | OK | OK | WOOCOMMERCE PRODUCT |
| 36 | `/privacy` (anomaly) | `/privacy/` | Legal (bản sao lỗi) | **BROKEN** | N/A | N/A | N/A | N/A | REMOVE |
| 37 | `/privacy-policy` | `/chinh-sach-quyen-rieng-tu/` vs `/chinh-sach-bao-mat/` | Legal | **BROKEN** (dual-slug) | NO | OK | OK | OK | STATIC PAGE |
| 38 | `/recalls` | `/thu-hoi/` | Legal/Utility | MISSING (soft-404) | YES | OK | OK | OK | STATIC PAGE |
| 39 | `/refund-policy` | `/chinh-sach-doi-tra/` vs `/doi-tra/` | Legal | **BROKEN** (dual-slug) | NO | OK | OK | OK | STATIC PAGE |
| 40 | `/shipping-policy` | `/chinh-sach-van-chuyen/` vs `/van-chuyen/` | Legal | **BROKEN** (dual-slug) | NO | OK | OK | OK | STATIC PAGE |
| 41 | `/terms-of-service` | `/dieu-khoan-dich-vu/` vs `/dieu-khoan/` | Legal | **BROKEN** (dual-slug) | NO | OK | OK | OK | STATIC PAGE |
| 42 | `/accessibility` | `/kha-nang-tiep-can/` | Legal/Utility | MISSING (soft-404) | NO | OK | OK | OK | STATIC PAGE |
| 43 | `/socials` | `/mang-xa-hoi/` | Social | MISSING (soft-404) | PARTIAL | OK | **PARTIAL** (render 2 lần?) | OK | STATIC PAGE |
| 44 | `/support` | `/ho-tro/` | Support | DONE | PARTIAL | OK | OK | OK | STATIC PAGE |
| — | N/A | `/get-mentra` | Utility (CTA) | MISSING (404 thật) | N/A | N/A | N/A | N/A | FORM PAGE |
| — | N/A | `/shop/`, `/cart/`, `/my-account/` | WooCommerce mặc định | PARTIAL | NO | OK | OK | OK | REMOVE |
| — | N/A | `/checkout/` | WooCommerce mặc định | REDIRECT | N/A | N/A | N/A | N/A | REMOVE |
| — | N/A | `/category/uncategorized/`, `/author/admin/`, `/hello-world/`, `/sample-page/` | WP mặc định | PARTIAL | NO | OK | OK | OK | REMOVE |

---

## 9. Missing Pages

29 route được liên kết (nav/footer/trang tin tức) nhưng không reachable đúng cách. Nhóm theo priority:

**P0 — chặn (Blocking):**
- `/mentra-live/`, `/mentra-os/` — không phải "missing" theo nghĩa chưa map, mà **crash hoàn toàn** (xem Broken Pages).

**P1 — quan trọng:**
- Source: `/partnerships`, `/recalls` → VN: `/doi-tac/`, `/thu-hoi/` — Reason: nội dung **đã dịch xong**, chỉ thiếu bước publish WP Page/routing. Future: FORM PAGE / STATIC PAGE. Priority: P1 (dễ sửa nhất, giá trị cao).
- Source: 16 `/blogs/blog/*` → VN: `/blogs/blog/*/` — Reason: kiến trúc chưa có WP Post backing + nội dung chưa dịch. Future: WORDPRESS POST. Priority: P1.
- Source: `/privacy-policy`, `/terms-of-service`, `/shipping-policy`, `/refund-policy` → VN: 2 slug xung đột mỗi trang — Reason: dual-slug conflict + nội dung tiếng Anh gốc. Future: STATIC PAGE. Priority: P1.
- N/A (không có source route) → VN: `/get-mentra` — Reason: CTA "Tải ứng dụng" chính của site trỏ tới route không tồn tại dưới bất kỳ hình thức nào (404 thật). Future: FORM PAGE. Priority: P1.

**P2 — thứ yếu:**
- Source: `/devs`, `/media-inquiries`, `/accessibility`, `/socials`, `/notes`, `/captions`, `/discord` → VN tương ứng — Reason: thiếu WP Page + (một số) chưa dịch. Future: STATIC PAGE / FORM PAGE. Priority: P2.

**P3 — phụ/external liên quan:**
- Source: `/nimo`, `/even-realities`, `/legacy` → VN tương ứng — Reason: thiếu WP Page, trạng thái dịch chưa xác minh đầy đủ. Future: WOOCOMMERCE PRODUCT (2 mục đầu) / STATIC PAGE (legacy). Priority: P3 (trang phụ, ít quan trọng theo business context).

## 10. Routes To Remove

| Route | Lý do | Đề xuất |
|---|---|---|
| `/shop/` | WooCommerce shop archive, placeholder "coming soon" tiếng Anh, mâu thuẫn business rule (không checkout) | REMOVE khỏi routing công khai hoặc redirect |
| `/cart/` | Placeholder tiếng Anh, không có icon giỏ hàng nào trỏ tới nhưng vẫn reachable | REMOVE/redirect |
| `/checkout/` | Redirect về `/cart/` — checkout ngoài phạm vi vĩnh viễn | Vô hiệu hoá hẳn khi cấu hình WooCommerce catalog-only |
| `/my-account/` | Form đăng nhập mặc định, hoàn toàn tiếng Anh, ngoài phạm vi theo business rule | REMOVE/khoá route |
| `/category/uncategorized/`, `/author/admin/` | Route mặc định WordPress, không liên kết từ nav, `/author/admin/` để lộ username "admin" | REMOVE / ẩn |
| `/hello-world/`, `/sample-page/` | Nội dung demo mặc định WordPress còn sống, lọt vào sitemap Yoast | REMOVE |
| `/privacy` (route source `privacy.html`) | Nội dung nguồn là bản sao lỗi của trang chủ, không phải Privacy Policy thật | Không port nội dung; loại khỏi route map final |

**Chưa xoá gì trong Phase 1** — chỉ đánh dấu.

## 11. Broken Pages

| Route | File/Template | Error | Likely Cause | Severity | Suggested Fix (chưa thực hiện) |
|---|---|---|---|---|---|
| `/mentra-live/` | `page-mentra-live.php:3,5` | `Uncaught Error: Call to undefined function mentra_vn_asset()` (HTTP 500) | Hàm `mentra_vn_asset()` chưa từng được định nghĩa — có lẽ nhầm với `mentra_vn_assets()` (số nhiều, không liên quan) trong `functions.php:16`. Xác nhận bằng log thật: `wp-content/uploads/wc-logs/fatal-errors-2026-08-21-*.log`, 18 entry CRITICAL từ 03:09–07:36 | **CRITICAL / P0** | Định nghĩa hàm helper `mentra_vn_asset($path)` trả về URL asset, ví dụ `return MENTRA_VN_THEME_URI . '/assets/' . ltrim($path, '/');` |
| `/mentra-os/` | `page-mentra-os.php:3,4,5` | Cùng lỗi | Cùng nguyên nhân | **CRITICAL / P0** | Cùng fix |
| `/privacy-policy`, `/terms-of-service`, `/shipping-policy`, `/refund-policy` (4 trang) | `functions.php` slug map + `404.php` fallback, vs. 4 trang stub thật tại slug khác | Không phải lỗi PHP — là **routing conflict**: mỗi trang pháp lý có 2 slug khác nhau (1 slug soft-404 chứa nội dung gốc tiếng Anh, 1 slug là trang thật nhưng chỉ có placeholder rỗng), không slug nào correct/complete | Sai kiến trúc routing trong `mentra_vn_source_map()` không khớp với slug thật đã được publish trong DB (có thể do 2 lần setup khác nhau không đồng bộ) | **HIGH / P1** | Chọn 1 slug chuẩn cho mỗi trang, hợp nhất nội dung (dịch + publish), xoá slug thừa hoặc 301 redirect |
| 13 route khác (`nha-phat-trien`, `discord`, `doi-tac`, `kha-nang-tiep-can`, `mang-xa-hoi`, `mentra-notes`, `phu-de`, `thu-hoi`, `truyen-thong`, `nimo`, `even-realities`, `legacy`, `privacy`) | `404.php` fallback | Soft-404: HTTP 200, `<title>Page not found</title>`, body class `error404` — không có WP Page thật cho các slug này dù `mentra-vietnam-core.php`'s `create_pages()` liệt kê hầu hết trong danh sách 25 trang cần tạo | Có thể `register_activation_hook` chưa từng chạy lại sau khi map/slug được cập nhật, hoặc trang đã bị xoá sau khi tạo | **HIGH / P1-P2** | Chạy lại `create_pages()` (hoặc tạo thủ công) cho các slug còn thiếu, kiểm tra lại toàn bộ 25 trang trong danh sách |
| 16 route `/blogs/blog/{slug}/` | `404.php` fallback | Soft-404, và ngay cả khi published thì nội dung là tiếng Anh nguyên bản | Không có route/CPT/rewrite rule thật cho bài viết cá nhân — kiến trúc chưa hỗ trợ | **MEDIUM (kiến trúc) / P1** | Import 16 bài thành `post_type=post` thật (dùng `single.php` sẵn có), build lại listing `/tin-tuc/` bằng WP_Query |
| `/get-mentra` | Không có template/route nào | HTTP 404 thật | Route CTA "buy/get app" của site cũ (Shopify) chưa từng được tái tạo trong WordPress | **HIGH / P1** | Quyết định business: route này nên trỏ đi đâu (khuyến nghị: `/lien-he/` với topic "mua hàng") |
| `/tuyen-dung/` (form) | `templates/source/careers.html`, không có handler | Form không hoạt động: không `name` attr, không nonce, không `wp_ajax_*` nào khớp `data-career-form` trong `mentra.js` hay plugin | Form được build trong markup nhưng chưa bao giờ được wire JS/backend | **HIGH / P1** | Viết handler `wp_ajax_mentra_vn_career`/`wp_ajax_nopriv_mentra_vn_career` tương tự `contact_ajax()` đã có |

## 12. Shopify / Hydrogen Dependencies

| Nhóm | Chi tiết | Phân loại |
|---|---|---|
| Ảnh hotlink `cdn.shopify.com` | 21 file trong `templates/source/` (16 bài viết + `blog.html`/`blogs.html` + `Legacy.html`) — thumbnail bài viết, avatar tác giả, badge app-store. Live site cũng xác nhận `/tin-tuc/` hotlink 16 ảnh thumbnail từ CDN Shopify | **SHOULD REMOVE / LOCALIZE** — sẽ hỏng nếu store Shopify gốc bị huỷ |
| Text pháp lý lỗi thời | `privacy-policy.html` (30 lần), `terms-of-service.html` (27 lần), `accessibility.html` (1 lần) nhắc tới "Shopify Payments", "Stripe", "Our store is powered by Shopify", "thanh toán Shopify" | **SHOULD FIX** — không chỉ là link gãy mà là **nội dung pháp lý sai sự thật** khi site sẽ là catalog-only, cần rà soát pháp lý riêng, không chỉ find-replace |
| Subdomain Mentra hợp lệ | `docs.mentraglass.com`, `console.mentraglass.com`, `apps.mentraglass.com` — dùng trong `page-mentra-os.php` và `mentra.js` | **REQUIRED EXTERNAL** — sản phẩm/dịch vụ thật, độc lập với migration WordPress này |
| `mailto:support@mentraglass.com` | Rải rác trong `templates/source/*.html` | Cần xem lại theo business rule `contact@domain.vn` — hiện là mailto tĩnh, không qua form/plugin |
| `https://mentraglass.com/get-mentra` | `mentra.js:58` (nhãn "Tải ứng dụng") | Trỏ về flow mua hàng Shopify cũ — **SHOULD REMOVE**, thay bằng route liên hệ nội bộ theo business rule không-checkout |
| Code comment lỗi thời | `mentra.css:249`, `mentra.js:441` — comment nhắc tới "Hydrogen SSR" | **SHOULD REMOVE** (chỉ là comment, không ảnh hưởng chức năng; logic patch DOM ở `mentra.js:441` có thể vẫn cần thiết, chỉ nên sửa lại comment) |
| `__remixContext`/`__remixManifest`/runtime Hydrogen thật | Không tìm thấy trong theme/plugin (chỉ tồn tại trong `SOURCE_ROOT`, không được mang vào WordPress) | Không áp dụng — **WordPress hiện tại không phụ thuộc runtime Hydrogen**, đúng mục tiêu |

## 13. URL Hardcode Audit

- `mentra-vn.local`, `localhost`, `127.0.0.1`, `myshopify.com`: **0 kết quả** trong toàn bộ theme/plugin/static-source — không có rò rỉ URL môi trường local.
- `cdn.shopify.com`: xem mục 12 ở trên (21 file, nên localize).
- `mentraglass.com`: xuất hiện dày đặc trong `templates/source/*.html` (đặc biệt `privacy-policy.html`/`terms-of-service.html`, cần bản dịch/rà pháp lý riêng), và 2 vị trí code (`page-mentra-os.php`, `mentra.js`) — cả 2 vị trí code đều là **link ngoài hợp lệ** tới subdomain Mentra thật (docs/console/apps), ngoại trừ `get-mentra` (xem mục 12).
- Plugin `mentra-vietnam-core.php`: **0 hardcoded URL** — sạch, dùng `home_url()`/`admin_url()`/`get_option()` nhất quán.

## 14. Broken / Suspicious Links

Chi tiết đầy đủ: [`link-audit.md`](link-audit.md). Tóm tắt:
- **17 internal link nội bộ dẫn tới soft-404** (13 route + `/get-mentra` là 404 thật) từ nav/footer.
- **16 link "Đọc thêm"** trên `/tin-tuc/` dẫn tới soft-404 bài viết.
- **0** `href="#"`/`href=""`/`javascript:void(0)` trong navigation thật (chỉ có trong `<form action="#">`, là pattern JS-intercept chủ đích, không phải lỗi — nhưng thiếu fallback nếu JS lỗi).
- `/cart`, `/checkout`, `/my-account` **tồn tại và reachable** dù business rule đã bỏ ecommerce checkout — cần xử lý (xem mục 10).
- External links hợp lệ (GitHub, Discord, mạng xã hội, báo chí, App Store/Google Play, Zendesk, workatastartup.com...) **không** bị đánh dấu lỗi — đúng theo yêu cầu.

## 15. Asset Audit

Chi tiết đầy đủ: [`asset-audit.md`](asset-audit.md). Tóm tắt bảng:

| Asset | Referenced From | Exists | Status | Notes |
|---|---|---|---|---|
| `assets/app_icons/Mentra_Ghi chú.png` | `/mentra-notes/` | NO (file thật là `Mentra_Notes.png`) | **BROKEN** | Đổi tên tiếng Việt dở dang |
| 16 thumbnail bài viết + avatar tác giả | `/tin-tuc/`, 16 bài viết | EXTERNAL (Shopify CDN) | **EXTERNAL — nên localize** | Rủi ro nếu store Shopify gốc bị huỷ |
| Badge app-store (`Legacy.html`) | `/legacy/` | EXTERNAL (Shopify CDN) | **EXTERNAL — nên localize** | Theme đã có sẵn badge local tương đương, chưa dùng |
| `assets/media/` (44 MB) | Không tham chiếu ở đâu | YES nhưng trùng lặp | **DUPLICATE** | Bản sao y hệt `assets/`, ứng viên dọn dẹp (cần rà DB trước) |
| `shopping-bag.svg`, `shopping-bag-green.svg` | Không tham chiếu | YES nhưng không dùng | **UNUSED** | Đúng theo "không cần cart icon" |
| 2 video `*.mp4@v=N` | Trang chủ | YES, tên file khớp | **OK nhưng FRAGILE** | Tên file chứa `@v=N` thay vì query string, dễ vỡ với tooling khác |
| 2/3 video hero trang chủ | Trang chủ | YES | **PARTIAL** | Thiếu `poster` |

Logo: **không phát hiện double-render**. `.logo-for-light`/`.logo-for-dark` là dead CSS không dùng.

## 16. Font Audit

- Font chuẩn **Red Hat Display** — load qua Google Fonts CDN (`fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;600;700;900&display=swap`), **đủ cả 5 weight** yêu cầu (400/500/600/700/900).
- **Không self-host** — `assets/fonts/` rỗng, không có `@font-face` nào trong CSS. Rủi ro: nếu mạng chặn/chậm Google Fonts (có thể xảy ra ở một số môi trường mạng Việt Nam), fallback stack (`ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`) sẽ hiển thị tạm — có `&display=swap` nên là FOUT, không phải FOIT (chấp nhận được).
- Áp dụng nhất quán: `body`, heading (`.type-section-title`, `.type-page-title`, `.type-hero`), nav (`.type-nav`), card title, mobile-menu, trang prescriptions, policy-content đều dùng `var(--font-display)`.
- **Không cần đổi trong Phase 1** — chỉ ghi nhận rủi ro fallback để cân nhắc tự-host trong tương lai.

## 17. Header Audit

- **Chiều cao xác nhận đúng 60px** trên desktop — biến CSS `--site-header-height: 60px` (`mentra.css:39`), áp dụng nhất quán (`mentra.css:264-265`, `854/871/904`). Header fallback (`header.php`) cũng hardcode 60px khớp.
- **2 hệ thống header khác nhau tồn tại song song**: (1) `header.php` thật — tối giản, chỉ logo, không nav — chỉ dùng cho trang fallback qua WP Loop thường; (2) header nhúng trong từng file `templates/source/*.html` — đầy đủ nav desktop, mega-menu CSS, mobile hamburger — là header thật cho hầu hết trang.
- **Logo**: không phát hiện lỗi double-logo. 3 thẻ `<img class="mentra-header-logo">` loại trừ lẫn nhau theo breakpoint (mobile/desktop/footer).
- **Cart icon: xác nhận KHÔNG tồn tại** ở bất kỳ đâu trong header/nav — đúng yêu cầu business rule. 2 file `shopping-bag*.svg` không được tham chiếu.
- **Mega-menu**: CSS hỗ trợ dropdown/mega-menu tồn tại (`mentra.css:272-289`, class `.mentra-desktop-menu`, `.mentra-nav-scrim`), nhưng khi crawl live site, nav desktop hiện ra là **flat list 5 mục** (OS, Kính, Nhà phát triển, Công ty, Liên hệ), không tìm thấy markup dropdown panel thực tế trong HTML render. **Cần kiểm tra thủ công trên trình duyệt** để xác nhận mega-menu có hoạt động hay chỉ là CSS chưa dùng tới.
- **Untranslated text trong header**: không phát hiện text tiếng Anh trong chính header/nav (5 mục nav đã là tiếng Việt); vấn đề `<html lang="en-US">` là ở mức `<head>`, ghi nhận riêng trong Translation Audit.

## 18. Interaction Audit

| Interaction | Trạng thái | Ghi chú |
|---|---|---|
| Desktop mega menu | **UNVERIFIED/PARTIAL** | CSS tồn tại, markup dropdown thực tế không xác nhận được qua crawl tĩnh — cần kiểm tra browser thủ công |
| Mobile menu | OK | Hamburger button + `mentra.js` toggle `aria-expanded`/class `mentra-menu-open` — có wiring đầy đủ |
| Header hover | UNVERIFIED | Không kiểm tra được qua crawl tĩnh (cần browser) |
| FAQ tabs/accordion (trang chủ) | OK | `home-faq-question`/`home-faq-tabpanel-*` + logic tương ứng trong `mentra.js` |
| Prescription FAQ | UNVERIFIED | Không được agent nào kiểm tra riêng — cần audit bổ sung |
| News tabs (Tất cả/Bài viết/Báo chí) | OK | Xác nhận đúng số liệu 16+6=22 khi crawl `/tin-tuc/` |
| News search | UNVERIFIED | Không xác nhận có tồn tại tính năng tìm kiếm trên `/tin-tuc/` |
| Video autoplay/pause/mute/fullscreen | PARTIAL | Logic video có trong `mentra.js`; 2/3 video hero thiếu `poster` (xem Asset Audit) |
| Marquee | OK | `home-backed-marquee*` trong `mentra.css`, logic tương ứng trong JS |
| Scroll reveal | OK | `IntersectionObserver`-based reveal trong `mentra.js` |
| Mentra Live animation | UNVERIFIED | Không xác nhận riêng trong audit này |
| Careers form UI | **BROKEN** | Markup tồn tại nhưng **không có JS handler, không nonce, không AJAX action nào khớp** — form chết hoàn toàn |
| Contact/Newsletter form UI | OK | Cả 2 hoạt động, có nonce, có sanitize server-side |
| File JS thừa | `assets/js/main.js` tồn tại trên đĩa nhưng **không được enqueue**, không request bởi live site — trùng lặp logic newsletter đã có trong `mentra.js` | Nên xoá để tránh nhầm lẫn (chưa xoá trong Phase 1) |

## 19. Full Vietnamese Audit

Chi tiết đầy đủ (bảng English Text/Location/Suggested Vietnamese/Severity): **[`translation-audit.md`](translation-audit.md)**.

Tóm tắt theo mức độ dịch (44 route logic duy nhất từ nguồn):

| Mức độ | Số route | Ví dụ |
|---|---|---|
| YES (dịch xong hoàn toàn) | 6 | `/ung-dung/`, `/tin-tuc/` (index), `/tuyen-dung/`, `/trong-kinh/`, `/doi-tac/`, `/thu-hoi/` |
| PARTIAL (còn sót cụm từ tiếng Anh) | 11 | `/` (2 chuỗi), `/ve-mentra/`, `/so-sanh/` (7 chuỗi), `/lien-he/` (nhiều placeholder), `/ho-tro/` (aria-label), `/mang-xa-hoi/`, `/nha-phat-trien/`, `/discord/`, `/truyen-thong/`, `/phu-de/`, `/legacy/` |
| NO (chưa dịch, tiếng Anh nguyên bản) | 22 | 16 bài viết + `/mentra-notes/` + 4 trang pháp lý (privacy-policy/terms/shipping/refund) + `/accessibility/` |
| UNKNOWN (chưa xác minh đầy đủ) | 2 | `/nimo/`, `/even-realities/` |
| N/A (trang lỗi, không đánh giá được) | 3 | `/mentra-os/`, `/mentra-live/` (500), `/privacy` (anomaly) |

**Vấn đề site-wide**: `<html lang="en-US">` trên **mọi trang**, dù nội dung đã là tiếng Việt — nên sửa thành `vi-VN` (LOW severity nhưng ảnh hưởng SEO/accessibility toàn site).

## 20. News Audit

**Xác nhận bằng crawl trực tiếp `/tin-tuc/` trên live site (không giả định số liệu):**

- **Tất cả (All): 22** = 16 Bài viết + 6 Báo chí — khớp chính xác kỳ vọng business rule.
- Không có pagination trên trang, toàn bộ item render 1 lần.

### Blog Articles (16) — xác nhận đúng 16, khớp cả nguồn lẫn WordPress

| # | Title (nguồn) | VN Title (hiện tại trên `/tin-tuc/`) | Source Route | Current VN Route | Ngày | Featured Image | Translation | Future System |
|---|---|---|---|---|---|---|---|---|
| 1 | Mentra 3.0 - Local Miniapps, Full User Control, and Enterprise Smart Glasses | Mentra 3.0 - Miniapp cục bộ, toàn quyền kiểm soát và kính thông minh cho doanh nghiệp | `/blogs/blog/mentra-3-0-local-miniapps-full-user-control-and-enterprise-smart-glasses` | `/blogs/blog/mentra-3-0-local-miniapps-full-user-control-and-enterprise-smart-glasses/` (soft-404, body EN) | 17/8/2026 | Shopify CDN (cần localize) | Khung VN, thân bài EN | WORDPRESS POST |
| 2 | Mentra Roadmap Update: Moving to Miniapps on the Phone | Cập nhật lộ trình Mentra: Chuyển miniapp sang chạy trên điện thoại | `/blogs/blog/mentra-roadmap-update-moving-to-miniapps-on-the-phone` | soft-404 | 9/6/2026 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 3 | Real-Time Captions With MentraOS | Phụ đề thời gian thực với MentraOS | `/blogs/blog/real-time-captions-with-mentraos` | soft-404 | 6/2/2026 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 4 | Batch 1 Sold Out: Mentra Live Featured in Engadget, Gizmodo, NY Post | Đợt 1 đã bán hết: Mentra Live xuất hiện trên Engadget, Gizmodo và NY Post | `/blogs/blog/mentra-live-featured-in-engadget-gizmodo-new-york-post-and-more` | soft-404 | 29/1/2026 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 5 | Mentra Releases First Smart Glasses With An App Store | Mentra ra mắt kính thông minh đầu tiên có kho ứng dụng riêng | `/blogs/blog/our-first-press-release-mentra-releases-first-smart-glasses-with-an-app-store` | soft-404 | 15/1/2026 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 6 | Batch 1 Is Almost Sold out! Join the Mentra Founder's Club | Đợt 1 gần bán hết! Tham gia Câu lạc bộ Nhà sáng lập Mentra | `/blogs/blog/batch-1-almost-sold-out` | soft-404 | 5/1/2026 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 7 | Mentra Live Shipping Update | Cập nhật giao hàng Mentra Live | `/blogs/blog/mentra-live-shipping-update` | soft-404 | 21/12/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 8 | Making Mentra Live | Hành trình tạo nên Mentra Live | `/blogs/blog/making-mentra-live` | soft-404 | 21/12/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 9 | 1 Year of Mentra | Một năm của Mentra | `/blogs/blog/1-year-of-mentra` | soft-404 | 30/11/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 10 | Even Realities G2 Support Coming on MentraOS | MentraOS sắp hỗ trợ Even Realities G2 | `/blogs/blog/even-realities-g2-supported-on-mentraos` | soft-404 | 15/11/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 11 | MemCards: the First Third-Party App to Launch on Mentra Market | MemCards: ứng dụng bên thứ ba đầu tiên ra mắt trên Mentra Market | `/blogs/blog/memcards-the-first-third-party-app-to-launch-on-mentra-market` | soft-404 | 29/8/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 12 | Community Update - Reliability improvements, Mentra Live, Displays | Cập nhật cộng đồng - Cải thiện độ ổn định, Mentra Live và màn hình | `/blogs/blog/august-22-community-update` | soft-404 | 22/8/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 13 | Announcing MentraOS 2.0 and Our $8M Raise | Ra mắt MentraOS 2.0 và công bố vòng gọi vốn 8 triệu USD | `/blogs/blog/announcing-mentraos-2-0-and-our-8m-raise` | soft-404 | 6/7/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 14 | AugmentedChords Goes #1 on Hacker News - Sheet Music App build on MentraOS | AugmentedChords lên #1 Hacker News - Ứng dụng bản nhạc xây dựng trên MentraOS | `/blogs/blog/augmentedchords-goes-1-on-hacker-news-sheet-music-app-build-on-mentraos` | soft-404 | 6/5/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 15 | MentraOS 1.0 Launch Hackathon - Smart Glasses Hackathon - March 2025 | Hackathon ra mắt MentraOS 1.0 - Hackathon kính thông minh tháng 3/2025 | `/blogs/blog/mentraos-1-0-launch-hackathon-smart-glasses-hackathon-march-2-2025` | soft-404 | 4/3/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |
| 16 | Why We're Building MentraOS: The Smart Glasses Operating System | Vì sao chúng tôi xây dựng MentraOS: Hệ điều hành cho kính thông minh | `/blogs/blog/mentraos-the-smart-glasses-operating-system-app-store` | soft-404 | 20/2/2025 | Shopify CDN | Khung VN, thân bài EN | WORDPRESS POST |

**Lưu ý quan trọng cho migration:** Dù index `/tin-tuc/` trông "hoàn thành" (tiêu đề/ngày/thẻ đều tiếng Việt), **toàn bộ 16/16 bài chưa có nội dung khả dụng** — mỗi link dẫn tới soft-404 với thân bài tiếng Anh nguyên bản. Nên coi là "16/16 bài cần migrate", không phải "16/16 đã xong".

### Press Items (6) — xác nhận đúng 6, khớp cả nguồn lẫn WordPress

| # | Publisher | VN Headline (trên `/tin-tuc/`) | External URL | Future System |
|---|---|---|---|---|
| 1 | Digital Trends | "Lựa chọn thay thế Ray-Ban Meta này là mã nguồn mở — và điều đó thay đổi mọi thứ" (16/1/2026) | `digitaltrends.com` | PRESS STATIC |
| 2 | Engadget | "Kính thông minh đầu tiên của Mentra là mã nguồn mở và có kho ứng dụng riêng" (15/1/2026) | `engadget.com` | PRESS STATIC |
| 3 | 9to5Google | "Kính thông minh 349 USD cạnh tranh Ray-Ban Meta với livestream YouTube và kho ứng dụng" (15/1/2026) | `9to5google.com` | PRESS STATIC |
| 4 | Android Police | "Đối thủ của Ray-Ban Meta hướng đến trải nghiệm livestream hoàn chỉnh" (15/1/2026) | `androidpolice.com` | PRESS STATIC |
| 5 | Forbes | "Mentra huy động 8 triệu USD để ra mắt hệ điều hành mã nguồn mở cho kính thông minh" (1/7/2025) | `forbes.com` | PRESS STATIC |
| 6 | GamesBeat | "Mentra huy động 8 triệu USD và ra mắt MentraOS 2.0 mã nguồn mở cho kính thông minh" (27/6/2025) | `gamesbeat.com` | PRESS STATIC |

**Ghi chú phụ**: nguồn wget còn có 1 logo Gizmodo xuất hiện dạng "press-logo-strip" trên `/live` và `/` (không phải card đầy đủ như 6 mục trên, không xuất hiện trên trang News). Không tính vào 6 mục chính thức — ghi chú riêng để business quyết định có nên thêm làm mục báo chí thứ 7 hay không.

**Không trộn 16 bài với 6 báo chí** — đã giữ tách biệt trong toàn bộ audit này, đúng yêu cầu.

## 21. Product Inventory

| Product | Source Route | VN Route | Source HTML | Images/Gallery | SKU (nguồn) | Attributes/Variations | Current Stock | Current CTA | Price hiện ở đâu | Future System |
|---|---|---|---|---|---|---|---|---|---|---|
| **Mentra Live** (Camera Glasses) | `/live` | `/mentra-live/` (hiện đang 500) | `live.html` | 6 ảnh (`mentra_live_sexy_1.png`, `frame.png`, `frame2.png`, `micro_charge_cable_mentra_live.png`, `chargingcase.webp`, `closed_mentra_live.webp`) | `gid://shopify/Product/8963292102908`, handle `mentra-live-camera-glasses` | Màu: Black (còn hàng), Transparent (hết hàng) — biến thể `gid://shopify/ProductVariant/...` | "Ships in 1-3 days" (nguồn) | **"Order Now"** (nguồn) — chưa xác định CTA hiện tại trên WP do trang đang 500 | Class `product-detail-price`, hiển thị **$449** | WOOCOMMERCE PRODUCT — **phải bỏ giá & CTA mua hàng**, thay bằng "Còn hàng"/"Hết hàng" + "Liên hệ mua hàng" |
| **Even Realities G2** (kính tương thích) | `/even-realities` | `/even-realities/` (soft-404) | `even-realities.html` | 4+ ảnh (G2, G1, Caption, Notes) + app-store badges | Không có object Shopify product — trang đối tác/thông tin | Không có variant | Không áp dụng (mua ngoài) | **"Buy Even Realities G2"** — trỏ ngoài `evenrealities.com`, không phải checkout Mentra | Không có giá trong nguồn | WOOCOMMERCE PRODUCT (phụ kiện tương thích, không bán trực tiếp) |
| **NIMO** (kính đối tác) | `/nimo` | `/nimo/` (soft-404) | `nimo.html` | 1 ảnh hero (`NIMO-glasses.png`) | Không có object Shopify product — trang pre-launch | Không có variant | **"Coming soon"** | Không có CTA mua (chưa ra mắt) | Không có giá | WOOCOMMERCE PRODUCT (trạng thái "sắp ra mắt") |
| **Tròng kính độ** (phụ kiện Mentra Live) | `/prescriptions` | `/trong-kinh/` (DONE) | `prescriptions.html` | 1 ảnh hero (`Mentra_live_green_shirt.png`) | Không có object Shopify product — add-on theo yêu cầu | Loại tròng kính/photochromic (mô tả văn bản, không có variant picker) | Không áp dụng (custom/quote) | **"Contact Sales"** — đã trỏ nội bộ `/lien-he/` | Không có giá (custom/quote qua sales) | WOOCOMMERCE PRODUCT — CTA đã gần khớp mô hình "Liên hệ mua hàng" mong muốn |

**Loại trừ rõ ràng khỏi Product Inventory** (đã kiểm tra, không phải sản phẩm): `OS.html`/`/mentra-os/` (nền tảng phần mềm MentraOS, không phải SKU vật lý), `apps.html`/`/ung-dung/` (listing app store, không có giá/variant), `captions.html`/`notes.html` (tính năng phần mềm của MentraOS). `compare.html`/`/so-sanh/` là trang so sánh, không phải trang sản phẩm.

**Business requirement nhắc lại**: giá ($449 hiện có trong nguồn Mentra Live) và mọi CTA cart/buy/checkout **phải bị loại bỏ** khi triển khai — nhưng **chưa sửa gì trong Phase 1** này.

## 22. Form Inventory

3 loại form riêng biệt (không tính các route pre-filled khác nhau là form khác nhau — chúng dùng chung 1 component):

| Form | Route(s) | Purpose | Fields | Current Action | Current JS Handler | Recipient hiện tại | Captcha | Backend | Working? | Future Type |
|---|---|---|---|---|---|---|---|---|---|---|
| **Newsletter signup** | Gần như mọi trang (footer, trừ `careers`/`devs`/`even-realities`/`prescriptions`) | GENERAL | 1 field: email | `action="#"`, JS `fetch` → `admin-ajax.php?action=mentra_vn_newsletter` | `mentra.js` | Không gửi email — chỉ lưu thành CPT `mentra_subscriber` trong DB | Không có | `Mentra_Vietnam_Core_99::newsletter()` — có nonce, `is_email()`, dedupe | **CÓ hoạt động** | GENERAL — sau này cần quyết định có gửi thông báo `wp_mail()` hay không |
| **Contact/Sales/Support/Partnership/Media** (1 component dùng chung) | `/lien-he/`, `/doi-tac/`, `/truyen-thong/` (và các biến thể `?topic=` không còn hoạt động trên live) | GENERAL / SALES / SUPPORT / PARTNERSHIP / MEDIA (chọn qua dropdown) | Họ tên, Email, Công ty (tuỳ chọn), Chủ đề (dropdown), Nội dung | `action="#"`, `novalidate`, JS `fetch` → `admin-ajax.php?action=mentra_vn_contact_ajax` | `mentra.js` | `wp_mail()` tới `sales_email` mặc định, chuyển sang `support_email` nếu subject chứa "support"/"hỗ trợ"/"technical"; mặc định fallback `admin_email` — **chưa phải 1 địa chỉ `contact@domain.vn` duy nhất** | Không có | `Mentra_Vietnam_Core_99::contact_ajax()` — có nonce, sanitize; **`Reply-To` header chưa chống CRLF injection** | **CÓ hoạt động** (nhưng cần hardening + thống nhất recipient) | GENERAL/SALES/SUPPORT/PARTNERSHIP/MEDIA |
| **Career application** | `/tuyen-dung/` | CAREER | Họ tên, Email, Lĩnh vực chuyên môn, Vị trí ứng tuyển (tuỳ chọn), Portfolio/LinkedIn (tuỳ chọn), "Vì sao muốn gia nhập Mentra?" | Không có `action` attribute, `novalidate`, class `career-form`, `data-career-form` | **Không có** — 0 kết quả tìm `data-career-form`/`career` trong `mentra.js` và plugin | Không gửi đi đâu cả | Không có | **Không tồn tại** — không có JS handler, không có AJAX action đăng ký | **KHÔNG hoạt động (form chết)** | CAREER — cần build handler mới |

## 23. Legal Audit

| Trang | Tồn tại? | VN Route | Đã dịch? | Nội dung còn mang tính chính sách Mỹ/Shopify? |
|---|---|---|---|---|
| Privacy (Chính sách quyền riêng tư) | 2 bản: soft-404 (nội dung thật) + stub thật (placeholder) — không bản nào hoàn chỉnh | `/chinh-sach-quyen-rieng-tu/` (soft-404) vs `/chinh-sach-bao-mat/` (stub) | KHÔNG (bản soft-404 100% tiếng Anh) | **CÓ** — nhắc "Shopify Payments", "Stripe", luật California/EU (CCPA, GDPR-style ngôn ngữ Mỹ/EU), chưa phù hợp thị trường VN |
| Terms of Service (Điều khoản dịch vụ) | 2 bản, cùng vấn đề | `/dieu-khoan-dich-vu/` (soft-404) vs `/dieu-khoan/` (stub) | KHÔNG | **CÓ** — luật California (`p65warnings.ca.gov`), governing law Mỹ |
| Shipping Policy (Vận chuyển) | 2 bản, cùng vấn đề | `/chinh-sach-van-chuyen/` (soft-404) vs `/van-chuyen/` (stub) | KHÔNG | Khả năng cao — chưa audit riêng nội dung chi tiết, nhưng cùng nguồn gốc Shopify |
| Refund Policy (Đổi trả) | 2 bản, cùng vấn đề | `/chinh-sach-doi-tra/` (soft-404) vs `/doi-tra/` (stub) | KHÔNG | Khả năng cao, tương tự |
| Accessibility (Khả năng tiếp cận) | 1 bản (soft-404) | `/kha-nang-tiep-can/` | KHÔNG | Nhắc "thanh toán Shopify" như ví dụ dịch vụ bên thứ ba |
| Recalls (Thu hồi sản phẩm) | 1 bản (soft-404, nhưng nội dung đã dịch xong) | `/thu-hoi/` | **CÓ** | Không phát hiện nội dung đặc thù Mỹ trong bản dịch |

**Không tự viết lại nội dung pháp lý** — chỉ ghi nhận theo đúng yêu cầu. Đây là nhóm vấn đề **P1** cần con người (pháp lý) xử lý song song với kỹ thuật, vì đây không đơn thuần là "dịch" mà là thay đổi nội dung chính sách thực tế (Mỹ → Việt Nam, có checkout → không checkout).

## 24. Responsive Audit

Breakpoint tìm thấy trong `assets/css/*.css` (2 hệ thống không thống nhất):

- **Pixel-based** (`mentra.css`/`main.css`, hand-authored): `639px`, `650px`, `680px`, `720px`, `767px`, `782px` (offset admin bar), `1020px`, `1023px`, `1050px` (max-width); `640px`, `768px`, `1024px`, `1200px`, `1440px` (min-width); compound `1024-1199px`.
- **Rem-based** (`utilities.css`, Tailwind-generated): `30rem`(480px), `40rem`(640px), `42rem`(672px), `48rem`(768px), `64rem`(1024px), `80rem`(1280px), `96rem`(1536px).

**Nhận xét**: các mốc tham chiếu người dùng nêu (1920/1440/1024/768/390/375) chỉ khớp một phần — `1440px` tồn tại (min-width, tier desktop lớn), `768px`/`1024px` là ranh giới tablet/desktop chính (cũng là nơi mega-menu/desktop-nav-trigger kích hoạt). Không tìm thấy breakpoint riêng cho 390/375 (mobile nhỏ) hay 1920 (desktop lớn) — site có thể dùng cùng 1 style cho mọi mobile/large-desktop, chưa xác nhận có vấn đề hiển thị thực tế hay không (cần kiểm tra bằng trình duyệt/screenshot). Không tự động hoá Playwright/screenshot trong phiên audit này — nếu cần, `docs/audit-screenshots/` đã được tạo sẵn cho phiên sau.

## 25. Code Health

- **PHP syntax (`php -l`)**: **100% pass** — 0 lỗi cú pháp trong toàn bộ 9 file PHP theme + 1 file plugin (PHP 8.2.29).
- **Nhưng cú pháp sạch ≠ chạy được**: `page-mentra-live.php`/`page-mentra-os.php` pass `php -l` nhưng crash runtime vì gọi hàm không tồn tại (`mentra_vn_asset()`) — lỗi loại này vô hình với linter, chỉ phát hiện được qua log lỗi thực tế hoặc review thủ công.
- **TODO/FIXME/XXX**: 0 kết quả trong toàn bộ code.
- **Dead/leftover code**: CSS `.logo-for-light`/`.logo-for-dark` không dùng; 2 comment nhắc "Hydrogen" lỗi thời; `MENTRA_VN.cart`/`{{CART_URL}}` plumbing tính toán URL giỏ hàng nhưng không render ở đâu (leftover từ quá trình convert Shopify→WP); file `assets/js/main.js` không dùng, trùng logic với `mentra.js`.
- **Không phát hiện block code lớn bị comment-out**.
- Không chạy auto-format/refactor toàn project theo đúng yêu cầu.

## 26. Security Pre-Audit (chỉ ghi nhận, forms hiện có)

| Form | Nonce | Sanitize | Escape | Server validation | Captcha | Rate limiting | wp_mail() | AJAX permission |
|---|---|---|---|---|---|---|---|---|
| Newsletter | **Có** (`check_ajax_referer('mentra_vn_public', 'nonce', false)`) | **Có** (`sanitize_email`, `is_email()`) | N/A (không echo lại input) | **Có** (email hợp lệ, dedupe) | **Không** | **Không** | Không dùng cho form này (chỉ lưu CPT) | `wp_ajax_` + `wp_ajax_nopriv_` đều đăng ký (đúng, public form) |
| Contact/Sales/Support/Partnership/Media | **Có** | **Có** (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`, `wp_unslash`) | Đầu ra admin settings dùng `esc_attr`/`esc_html` đúng chuẩn | **Có** (required field + `is_email()`) | **Không** (đúng như kỳ vọng — chưa triển khai reCAPTCHA) | **Không** — có nonce nhưng nonce không single-use, dễ bị spam nếu bị scrape | **Có**, nhưng `Reply-To` header **chưa lọc CRLF injection** trên `$name`/`$email` — rủi ro thấp nhưng nên vá trước go-live | Đăng ký đúng cho cả public + logged-in |
| Career application | **Không có gì cả** — form chết hoàn toàn, không có handler nào để đánh giá | — | — | — | — | — | — | — |
| `mentra_vn_render_source()` (renderer HTML tĩnh, không phải form nhưng liên quan output) | N/A | N/A | **Echo KHÔNG escape** (`phpcs:ignore ... OutputNotEscaped`) — chấp nhận được vì input là file tĩnh trên đĩa do dev kiểm soát, không phải input người dùng, nhưng đáng lưu ý cho security review nghiêm ngặt | N/A | N/A | N/A | N/A | N/A |

**Không triển khai form/captcha mới trong Phase 1** — bảng trên chỉ để phục vụ Phase sau.

---

## 27. Audit Summary

| Chỉ số | Giá trị |
|---|---|
| Total source routes | 47 file (44 route logic duy nhất sau khi gộp 3 biến thể `/contact`) |
| Total WordPress routes kiểm chứng qua HTTP | 58 (bao gồm route hạ tầng: sitemap, wp-json) — **53 route so sánh trực tiếp được** (44 từ nguồn + 9 route chỉ có ở WP) |
| Missing | 29 |
| Broken | 7 (2 lỗi HTTP 500 thật + 5 route pháp lý/anomaly xung đột slug) |
| Partial | 9 (route-level: `careers`, `contact` + 7 route WooCommerce/WP mặc định) |
| Done | 7 |
| External | 0 trong phạm vi so sánh nội bộ (nhiều external link hợp lệ được catalogue riêng trong `link-audit.md`, không tính là lỗi) |
| Remove (đề xuất, chưa thực hiện) | 10 (9 route WP/WC mặc định + 1 route `privacy` anomaly từ nguồn) |
| **Total products** | 4 (1 sản phẩm thật có SKU/giá — Mentra Live; 3 phụ kiện/đối tác không có SKU trong nguồn — Even Realities G2, NIMO, Tròng kính độ) |
| **Total blog articles** | 16 (xác nhận 3 cách độc lập từ nguồn + xác nhận lại trên live site) |
| **Total press items** | 6 (xác nhận từ nguồn + xác nhận lại trên live site; +1 logo Gizmodo phụ ghi chú riêng, không tính vào 6) |
| **Total forms** | 3 loại (Newsletter — hoạt động; Contact dùng chung 5+ route theo topic — hoạt động nhưng chưa hardening đầy đủ; Career — hỏng hoàn toàn) |
| Pages fully translated (YES) | 6 / 44 |
| Pages partially translated (PARTIAL) | 11 / 44 |
| Pages not translated (NO) | 22 / 44 |
| Pages translation status unknown | 2 / 44 |
| Pages N/A (route lỗi, không đánh giá được) | 3 / 44 |
| Broken assets | 1 xác nhận (`Mentra_Notes.png`) + 21 file phụ thuộc CDN Shopify ngoài (rủi ro, chưa "broken" nhưng cần localize) |
| External Shopify dependencies | 21 file hotlink ảnh + 3 file có text pháp lý lỗi thời nhắc Shopify/Stripe + 1 link CTA cũ (`get-mentra`) |
| Suspicious/broken links | 17 internal link nội bộ trỏ soft-404 + 16 link bài viết trỏ soft-404 + 1 link 404 thật (`/get-mentra`) |

---

## 28. Priority Fix Queue

### P0 — Blocking/Critical

| ID | Route | Problem | File | Recommended Action | Future System |
|---|---|---|---|---|---|
| P0-001 | `/mentra-live/` | HTTP 500 — gọi hàm `mentra_vn_asset()` chưa định nghĩa | `page-mentra-live.php:3,5` | Định nghĩa hàm `mentra_vn_asset($path)` trong `functions.php` | WOOCOMMERCE PRODUCT |
| P0-002 | `/mentra-os/` | HTTP 500 — cùng nguyên nhân | `page-mentra-os.php:3,4,5` | Cùng fix như trên | STATIC PAGE |

### P1 — Important

| ID | Route | Problem | File | Recommended Action | Future System |
|---|---|---|---|---|---|
| P1-001 | `/chinh-sach-quyen-rieng-tu/`, `/dieu-khoan-dich-vu/`, `/chinh-sach-van-chuyen/`, `/chinh-sach-doi-tra/` (4 trang) | Dual-slug conflict: bản soft-404 có nội dung thật (chưa dịch) vs bản published chỉ có placeholder | `functions.php` (slug map) + 4 WP Page hiện có | Chọn 1 slug chuẩn/trang, hợp nhất + dịch nội dung pháp lý (rà lại nội dung Mỹ→VN, bỏ nhắc Shopify/Stripe) | STATIC PAGE |
| P1-002 | 16 route `/blogs/blog/*` | Soft-404 + nội dung 100% tiếng Anh; kiến trúc chưa có WP Post backing | `functions.php`, `templates/source/blogs/blog/*.html` | Dịch 16 bài, import thành `post_type=post` thật, dùng `single.php` sẵn có, viết lại listing `/tin-tuc/` bằng `WP_Query` | WORDPRESS POST |
| P1-003 | `/get-mentra` | 404 thật — CTA "Tải ứng dụng" chính không có đích đến | Không có file | Quyết định business rồi tạo route (khuyến nghị: redirect/trỏ `/lien-he/` với ngữ cảnh mua hàng) | FORM PAGE |
| P1-004 | `/tuyen-dung/` (form) | Form ứng tuyển hoàn toàn không hoạt động — không JS handler, không nonce, không AJAX action | `templates/source/careers.html`, `mentra.js`, `mentra-vietnam-core.php` | Viết `wp_ajax_mentra_vn_career`/`wp_ajax_nopriv_mentra_vn_career` theo mẫu `contact_ajax()` đã có | FORM PAGE |
| P1-005 | 13 route soft-404 khác (`nha-phat-trien`, `discord`, `doi-tac`, `kha-nang-tiep-can`, `mang-xa-hoi`, `mentra-notes`, `phu-de`, `thu-hoi`, `truyen-thong`, `nimo`, `even-realities`, `legacy`, `privacy`) | Không có WP Page thật cho slug dù đã có trong danh sách `create_pages()` của plugin | `mentra-vietnam-core.php:56-86` | Kiểm tra lại/tạo lại 25 WP Page theo danh sách, publish đúng slug khớp `mentra_vn_source_map()` | STATIC PAGE / FORM PAGE |
| P1-006 | `/tin-tuc/` + 16 bài viết | 21 file hotlink ảnh trực tiếp từ `cdn.shopify.com` | `templates/source/blog.html`, `blogs.html`, `Legacy.html`, 16 file `blogs/blog/*.html` | Tải ảnh về, host trong `wp-content/uploads/` hoặc theme assets, cập nhật lại reference | WORDPRESS POST / STATIC PAGE |

### P2 — Secondary

| ID | Route | Problem | File | Recommended Action | Future System |
|---|---|---|---|---|---|
| P2-001 | `/shop/`, `/cart/`, `/my-account/` | Route WooCommerce mặc định, tiếng Anh, mâu thuẫn business rule không-checkout | WooCommerce default templates | Redirect/ẩn khỏi routing công khai khi cấu hình catalog-only | REMOVE |
| P2-002 | Toàn site | `<html lang="en-US">` dù nội dung tiếng Việt | `header.php`, `mentra_vn_document_open()` trong `functions.php` | Đổi `language_attributes()` output hoặc filter `language_attributes` sang `vi-VN` | N/A |
| P2-003 | `/so-sanh/` và các trang DONE khác | Rải rác chuỗi tiếng Anh còn sót (xem `translation-audit.md`) | `templates/source/compare.html` và tương ứng | Dịch nốt các chuỗi còn thiếu | STATIC PAGE |
| P2-004 | Toàn theme | `assets/media/` 44MB trùng lặp hoàn toàn `assets/` | `wp-content/themes/mentra-vietnam/assets/media/` | Xác nhận không có tham chiếu DB rồi xoá | N/A |
| P2-005 | `/lien-he/` (form) | Chưa route về 1 địa chỉ `contact@domain.vn` duy nhất; `Reply-To` header chưa lọc CRLF | `mentra-vietnam-core.php:170-177` | Thống nhất recipient theo business rule tương lai; thêm `str_replace(["\r","\n"], '', $name)` trước khi build header | FORM PAGE |
| P2-006 | `/hello-world/`, `/sample-page/`, `/category/uncategorized/`, `/author/admin/` | Nội dung demo mặc định WordPress còn sống, lọt sitemap | WP default | Xoá/ẩn nội dung demo | REMOVE |
| P2-007 | Contact/Newsletter form | Không rate limiting, không reCAPTCHA (đúng như kỳ vọng ở Phase này) | `mentra-vietnam-core.php` | Đưa vào backlog Phase 2 security hardening theo đúng kế hoạch đã chốt | FORM PAGE |

### P3 — Optional/Cosmetic

| ID | Route | Problem | File | Recommended Action | Future System |
|---|---|---|---|---|---|
| P3-001 | Toàn theme | Dead CSS (`.logo-for-light`/`.logo-for-dark`), 2 file `shopping-bag*.svg` không dùng, comment lỗi thời nhắc Hydrogen | `mentra.css`, `mentra.js` | Dọn dẹp khi có dịp refactor CSS | N/A |
| P3-002 | Trang chủ | 2 file video tên `*.mp4@v=N` dễ vỡ với tooling khác | `assets/Herosectionvideo-trimmed.mp4@v=3`, `assets/mobile_hero_smol1-trimmed.mp4@v=2` | Đổi tên file + cập nhật reference | N/A |
| P3-003 | Trang chủ | 2/3 video hero thiếu `poster` | `templates/source/index.html` | Thêm `poster` cho 2 video còn thiếu | STATIC PAGE |
| P3-004 | `/mentra-notes/` | Ảnh `Mentra_Ghi chú.png` không tồn tại, file thật là `Mentra_Notes.png` | `templates/source/notes.html` | Sửa lại tên file tham chiếu | STATIC PAGE |
| P3-005 | `/mang-xa-hoi/` | Nghi ngờ nội dung hero render 2 lần | `templates/source/socials.html` | Kiểm tra trực quan trên trình duyệt, sửa nếu xác nhận | STATIC PAGE |
| P3-006 | Toàn site | Mega-menu CSS tồn tại nhưng không xác nhận được markup dropdown khi crawl tĩnh | `mentra.css`, header nhúng trong `templates/source/*.html` | Kiểm tra thủ công bằng trình duyệt để xác nhận hành vi thực tế | N/A |

---

## Phase 1 Gate

1. **Có route nào chưa được map không?** → **Có.** 29 route ở trạng thái MISSING (soft-404 hoặc 404 thật), chi tiết đầy đủ trong mục 9 và `route-map.csv`.
2. **Có page nào BROKEN không?** → **Có.** 7 route: 2 lỗi HTTP 500 thật (`/mentra-live/`, `/mentra-os/`) + 5 route xung đột slug/anomaly (4 trang pháp lý + `/privacy`).
3. **Có missing asset quan trọng không?** → **Có nhưng nhỏ**: 1 ảnh 404 xác nhận (`Mentra_Notes.png`); nghiêm trọng hơn là 21 file phụ thuộc CDN Shopify ngoài (chưa hỏng nhưng rủi ro cao).
4. **Có English visible text còn sót không?** → **Có.** 22/44 route hoàn toàn chưa dịch, 11/44 dịch một phần — chi tiết `translation-audit.md`.
5. **Có Shopify/Hydrogen dependency không cần thiết không?** → **Có.** 21 file hotlink ảnh CDN + text pháp lý lỗi thời nhắc Shopify/Stripe + 1 CTA link cũ (`get-mentra`) trỏ về flow mua hàng Shopify.
6. **Đã xác định chính xác product inventory chưa?** → **Đã xác định.** 1 sản phẩm thật (Mentra Live, SKU + giá rõ ràng) + 3 phụ kiện/đối tác (Even Realities G2, NIMO, Tròng kính độ) không có SKU trong nguồn.
7. **Đã xác định chính xác 16 blog posts chưa?** → **Đã xác định, đúng 16**, kiểm chứng độc lập cả từ nguồn (3 phương pháp) lẫn từ live WordPress site.
8. **Đã xác định chính xác 6 press items chưa?** → **Đã xác định, đúng 6**, kiểm chứng độc lập cả từ nguồn lẫn live WordPress site (+1 mục phụ Gizmodo ghi chú riêng, không gộp vào 6).
9. **Đã xác định toàn bộ forms chưa?** → **Đã xác định.** 3 loại form (Newsletter, Contact dùng chung nhiều route theo topic, Career) — Career form hỏng hoàn toàn, cần build handler mới ở phase sau.

### PHASE 1 STATUS: **FAIL**

**Giải thích:** FAIL ở đây có nghĩa là **website chưa đủ điều kiện bước sang Phase 3/4**, không phải audit thất bại — audit đã hoàn thành đầy đủ 29 mục theo yêu cầu, inventory (route/product/blog/press/form) đã chính xác và đầy đủ. Lý do FAIL: 2 trang quan trọng nhất (Mentra Live, MentraOS) đang crash hoàn toàn (P0), 29/53 route ở trạng thái missing/soft-404, và phần lớn nội dung pháp lý + toàn bộ 16 bài viết tin tức chưa có bản dịch khả dụng cho người dùng cuối. Cần xử lý tối thiểu toàn bộ P0 và phần lớn P1 trước khi cân nhắc chuyển giai đoạn tiếp theo.
