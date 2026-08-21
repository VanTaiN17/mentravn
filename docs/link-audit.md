# Link Audit — Mentra Vietnam

## 1. Link nội bộ WordPress bị gãy (soft-404 / redirect sai)

Đây là nhóm lỗi lớn nhất phát hiện được trong audit này. 17 route được liên kết trực tiếp từ nav/footer/trang tin tức nhưng dẫn tới soft-404 (HTTP 200, `<title>Page not found</title>`, body class `error404`, nhưng vẫn render nội dung do cơ chế fallback trong `functions.php`/`404.php`) — cộng thêm 16 route bài viết `/blogs/blog/*` cùng cơ chế lỗi. Xem chi tiết root-cause trong `site-audit.md` mục Broken Pages.

| Link hiện tại | Nơi xuất hiện | Trỏ tới | Vấn đề |
|---|---|---|---|
| `/nha-phat-trien/` | Top nav (mục "Nhà phát triển", 1 trong 5 mục nav chính) | soft-404 | Không có WP Page cho slug này |
| `/discord/` | Footer | soft-404 | — |
| `/nimo/` | (không liên kết hiện tại — xác minh trực tiếp) | soft-404 | — |
| `/even-realities/` | (không liên kết hiện tại — xác minh trực tiếp) | soft-404 | — |
| `/legacy/` | (không liên kết hiện tại — xác minh trực tiếp) | soft-404 | — |
| `/privacy/` | (không liên kết hiện tại — xác minh trực tiếp) | soft-404 | Nội dung nguồn vốn đã là bản sao lỗi của trang chủ — khuyến nghị bỏ hẳn route này, không map |
| `/chinh-sach-doi-tra/` | Footer ("Đổi trả") | soft-404 | Sai slug — trang thật (dạng stub) nằm ở `/doi-tra/`, không được liên kết ở đâu cả |
| `/chinh-sach-quyen-rieng-tu/` | Footer ("Quyền riêng tư") | soft-404 | Sai slug — trang thật (dạng stub) nằm ở `/chinh-sach-bao-mat/`, không được liên kết ở đâu cả |
| `/chinh-sach-van-chuyen/` | Footer ("Vận chuyển") | soft-404 | Sai slug — trang thật (dạng stub) nằm ở `/van-chuyen/`, không được liên kết ở đâu cả |
| `/dieu-khoan-dich-vu/` | Footer ("Điều khoản") | soft-404 | Sai slug — trang thật (dạng stub) nằm ở `/dieu-khoan/`, không được liên kết ở đâu cả |
| `/doi-tac/` | Footer ("Đối tác") | soft-404 | Chỉ thiếu bước publish/routing — nội dung đã dịch xong, form hoạt động |
| `/kha-nang-tiep-can/` | Footer ("Khả năng tiếp cận") | soft-404 | Nội dung gốc tiếng Anh, chưa dịch |
| `/mang-xa-hoi/` | Footer ("Mạng xã hội") | soft-404 | Kèm nghi vấn render nội dung 2 lần |
| `/mentra-notes/` | Footer/trang chủ ("Mentra Notes") | soft-404 | Nội dung tiếng Anh phần lớn |
| `/phu-de/` | Footer/trang chủ ("Phụ đề") | soft-404 | — |
| `/thu-hoi/` | Footer ("Thu hồi") | soft-404 | Chỉ thiếu bước publish/routing — nội dung đã dịch xong |
| `/truyen-thong/` | Footer ("Liên hệ truyền thông") | soft-404 | — |
| `/blogs/blog/{16 slug}/` | Toàn bộ 16 thẻ "Đọc thêm" trên `/tin-tuc/` | soft-404 | Nội dung bài viết hoàn toàn tiếng Anh (nguyên bản Shopify) |
| `/get-mentra` | Footer ("Tải ứng dụng") | **404 thật** (không phải soft-404) | Không có fallback nào — trang không tồn tại dưới bất kỳ hình thức nào |

## 2. Route thương mại điện tử không nên public theo business rule

| Route | Trạng thái | Vấn đề |
|---|---|---|
| `/shop/` | 200, tiếng Anh, placeholder "coming soon" | Business rule: không triển khai checkout. Route công khai, không có icon giỏ hàng dẫn tới nhưng vẫn reachable trực tiếp. |
| `/cart/` | 200, tiếng Anh, placeholder "coming soon" | Tương tự. |
| `/checkout/` | 302 → `/cart/` | Hành vi mặc định WooCommerce khi giỏ trống — cần vô hiệu hoá hẳn khi triển khai catalog-only. |
| `/my-account/` | 200, hoàn toàn tiếng Anh (form đăng nhập mặc định) | Nằm ngoài phạm vi theo business rule, nên gỡ/khoá route. |

## 3. Nội dung mặc định WordPress còn sót (nên dọn dẹp, đánh dấu REMOVE)

`/category/uncategorized/`, `/author/admin/`, `/hello-world/`, `/sample-page/` — không liên kết từ nav nhưng vẫn được index trong sitemap Yoast (`post-sitemap.xml`, `page-sitemap.xml`). `/author/admin/` còn để lộ username "admin" công khai — vấn đề vệ sinh bảo mật nhỏ, nên cân nhắc đổi tên user hoặc ẩn author archive.

## 4. Href/JS đáng ngờ

- `href="#"`, `href=""`, `javascript:void(0)`: **không tìm thấy** trong bất kỳ trang nào đã crawl (đã kiểm tra trang chủ, `/lien-he/`, `/tin-tuc/`) hoặc trong mã theme/plugin.
- `<form action="#">`: xuất hiện trên cả 3 loại form (newsletter, contact, careers) — đây là pattern chủ đích (JS `fetch()` chặn submit event), không phải link gãy. Tuy nhiên **không có fallback progressive-enhancement**: nếu JS lỗi/chưa load kịp, form sẽ submit về `action="#"` (chỉ reload trang, không gửi được gì, không báo lỗi cho người dùng). Ghi nhận là rủi ro UX cần cân nhắc khi hardening.

## 5. Link ngoài hợp lệ (không tính là lỗi)

Các link ngoài sau đây là **hợp lệ theo thiết kế**, không nên gắn cờ là lỗi chỉ vì external: `github.com/Mentra-Community/*`, `docs.mentraglass.com`, `console.mentraglass.com`, `apps.mentraglass.com`, `discord.gg/*`, trang mạng xã hội (`twitter.com`/`x.com`, `instagram.com`, `linkedin.com`, `facebook.com`, `tiktok.com`, `reddit.com`, `youtube.com`/mentraglass), các trang báo chí (Forbes, GamesBeat, Engadget, 9to5Google, Android Police, Digital Trends, Gizmodo), `apps.apple.com`, `play.google.com`, `testflight.apple.com`, `mentrahelp.zendesk.com`, `evenrealities.com` (external product link cho phụ kiện Even Realities), `workatastartup.com`.

## 6. Bug link trong chính source wget gốc (không phải lỗi WordPress, nhưng cần biết để KHÔNG tái tạo lại khi dịch/port nội dung)

53 lỗi casing/malformed URL trong 47 file HTML gốc:
- **48/53** là cùng 1 lỗi gốc lặp lại: link `os.html` (viết thường) trong khi file thật là `OS.html` (viết hoa) — xuất hiện trong markup header/footer dùng chung trên gần như mọi trang. Trên server phân biệt hoa-thường (Linux/Nginx — giống môi trường WordPress hiện tại) sẽ gây 404 nếu tái tạo y hệt.
- `Discord.html` vs `discord.html`, `legacy.html` vs `Legacy.html` — 2 cặp mismatch tương tự, ít trang hơn.
- 1 URL hỏng: `https://mentraglass.com/blogs/blog/MentraGlass.com/Discord` trong `blogs/blog/august-22-community-update.html` (domain string bị lồng sai vào path).

**Lưu ý tích cực:** WordPress hiện tại **không** kế thừa các lỗi case-sensitivity này (theme dùng route WordPress chuẩn hoá qua `home_url()`, không dùng lại literal `.html` links) — chỉ cần không copy nguyên văn các đường link này khi hoàn thiện nội dung 16 bài viết / trang tĩnh còn lại.
