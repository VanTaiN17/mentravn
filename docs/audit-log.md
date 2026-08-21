# Audit Log — Nhật ký các phiên làm việc

Mục đích: ghi lại những gì đã làm/thay đổi trong từng phiên để phiên sau đọc lại và không làm sai / không lặp lại công việc đã xong.

---

## Phiên 1 — 2026-08-21 — PHASE 1: Full Site Audit

**Phạm vi:** Chỉ audit, không sửa code, không sửa nội dung, không xoá file, không đổi source wget. Đã tuân thủ nghiêm ngặt — **không có file WordPress hay source wget nào bị chỉnh sửa trong phiên này**, chỉ tạo mới thư mục `docs/`.

### Việc đã làm

1. Xác định `WORDPRESS_ROOT` = `D:\Local Sites\mentra-vn\app\public` (đây chính là working directory của session, khác với `D:\Workspace\website\mentra-vn` — thư mục đó chỉ chứa `mentraglass.com/` là source wget gốc).
2. Xác định `THEME_PATH` = `wp-content/themes/mentra-vietnam` (v3.9.0, active), `PLUGIN_PATH` = `wp-content/plugins/mentra-vietnam-core` (v3.0.0).
3. Kiểm tra môi trường: WP-CLI **không cài đặt**; PHP CLI có sẵn qua Local by Flywheel (`C:\Users\LENOVO\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe`); site local **reachable** tại `http://mentra-vn.local/` trong suốt phiên.
4. Chạy 3 agent song song (background) để audit: (a) toàn bộ 47 file HTML trong source wget, (b) toàn bộ code theme + plugin (bao gồm `php -l`), (c) crawl trực tiếp live WordPress site + audit dịch thuật/form. Kết quả thô lưu tại `docs/_raw/*.md` (không cần đọc lại — đã tổng hợp đầy đủ vào các file chính thức).
5. Tự bổ sung kiểm tra trực tiếp 4 route mà agent crawl bỏ sót (`/nimo/`, `/even-realities/`, `/legacy/`, `/privacy/`) — xác nhận cả 4 đều là soft-404.
6. Tổng hợp toàn bộ vào 4 file chính thức trong `docs/`:
   - `site-audit.md` — file tổng hợp chính, đầy đủ 29 mục theo yêu cầu gốc.
   - `route-map.csv` — bảng route đầy đủ (53 dòng), machine-readable.
   - `translation-audit.md` — chi tiết lỗi tiếng Anh còn sót.
   - `asset-audit.md` — chi tiết asset lỗi/thiếu/trùng lặp.
   - `link-audit.md` — chi tiết link gãy/nghi vấn.

### Kết quả chính (KHÔNG được coi là đã fix — chỉ audit)

- **PHASE 1 STATUS: FAIL** (chưa đủ điều kiện sang Phase 3/4 — xem `site-audit.md` mục "Phase 1 Gate" để biết lý do đầy đủ).
- **P0 — CRITICAL, chưa sửa:** `/mentra-live/` và `/mentra-os/` đang **HTTP 500** vì `page-mentra-live.php`/`page-mentra-os.php` gọi hàm `mentra_vn_asset()` không tồn tại (nhầm với `mentra_vn_assets()` — số nhiều — vốn là hook enqueue khác). Xác nhận bằng log thật `wp-content/uploads/wc-logs/fatal-errors-2026-08-21-*.log`.
- Kiến trúc theme: hầu hết route render qua cơ chế "static-source mirror" (`functions.php` → `mentra_vn_render_source()`) echo thẳng HTML tĩnh từ `templates/source/*.html`, không qua `header.php`/`footer.php`/WP Loop thật. 29 route bị "soft-404" (HTTP 200 nhưng title/body-class báo lỗi) vì WP Page tương ứng không tồn tại trong DB dù `mentra-vietnam-core.php`'s `create_pages()` có liệt kê.
- Xác nhận đúng **16 bài viết + 6 báo chí** trên `/tin-tuc/` (khớp business rule) — nhưng cả 16 bài đều dẫn tới soft-404 với nội dung tiếng Anh gốc chưa dịch; kiến trúc `/tin-tuc/` hiện là 100% HTML tĩnh, chưa có `post_type=post` nào backing (dù `single.php` đã sẵn sàng, chỉ chưa dùng tới).
- Sản phẩm: chỉ **Mentra Live** có SKU/giá thật ($449) trong nguồn; Even Realities G2, NIMO, Tròng kính độ là phụ kiện/đối tác không có SKU.
- Form: 3 loại (Newsletter — hoạt động, Contact dùng chung nhiều route — hoạt động nhưng recipient chưa hợp nhất, Career — **hỏng hoàn toàn**, không có handler nào).
- 4 trang pháp lý (privacy-policy/terms/shipping/refund) có bug **dual-slug**: 1 slug soft-404 chứa nội dung thật (chưa dịch, còn nhắc Shopify/Stripe), 1 slug khác là trang thật nhưng chỉ có placeholder rỗng.
- 21 file source hotlink ảnh trực tiếp từ `cdn.shopify.com` (16 bài viết + trang tin tức + Legacy).

### Việc CHƯA làm (để dành cho phiên sau / theo đúng scope Phase 1)

- Chưa sửa bug `mentra_vn_asset()` (dù đây là fix rất nhỏ, KHÔNG được tự ý sửa vì user yêu cầu "chỉ audit").
- Chưa xác nhận thủ công bằng trình duyệt: mega-menu desktop (CSS tồn tại nhưng markup dropdown không thấy trong crawl tĩnh), hover header, Prescription FAQ, news search, animation Mentra Live.
- Chưa chạy Playwright/screenshot responsive (thư mục `docs/audit-screenshots/` đã tạo sẵn, còn trống).
- Chưa audit trực tiếp DB (không có WP-CLI, không truy vấn DB trực tiếp) — mọi kết luận về "trang có tồn tại không" dựa trên HTTP crawl + đọc code, không phải `wp post list`.
- Trạng thái dịch của `/nimo/` và `/even-realities/` chưa xác minh đầy đủ (chỉ kiểm tra nhanh vài từ khoá đặc trưng).

### Ghi chú cho phiên sau

- Đừng giả định số liệu 16/6 — phiên này đã **đếm thực tế** và xác nhận đúng, có thể tin tưởng dùng lại.
- File `docs/_raw/*.md` là dữ liệu thô từ agent, có thể xoá sau khi xác nhận `site-audit.md` đã tổng hợp đầy đủ (chưa xoá trong phiên này vì thuộc phạm vi "docs/ và script audit").
- Nếu Phase 2 bắt đầu bằng việc sửa `mentra_vn_asset()`, đó là fix 1 dòng — không cần thay đổi kiến trúc.
- Route map (`route-map.csv`) là nguồn dữ liệu nên dùng để lập kế hoạch Phase 2 (import 16 post, publish 13+ trang còn thiếu, hợp nhất 4 trang pháp lý dual-slug).
