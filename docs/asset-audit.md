# Asset Audit — Mentra Vietnam

Scope: `wp-content/themes/mentra-vietnam/assets/` (91 MB, 274 files) và các asset tham chiếu từ `templates/source/*.html` + `page-mentra-live.php`/`page-mentra-os.php`.

## Bảng asset lỗi/thiếu/đáng chú ý

| Asset | Referenced From | Exists | Status | Notes |
|---|---|---|---|---|
| `assets/app_icons/Mentra_Ghi chú.png` | `templates/source/notes.html` (route `/mentra-notes/`) | NO (file thực tế tên `Mentra_Notes.png`) | BROKEN | Ảnh 404 (đã kiểm tra cả dạng nguyên văn và percent-encoded). Có vẻ là đổi tên tiếng Việt dở dang, chưa áp dụng cho file thật trên đĩa. |
| 16 ảnh thumbnail bài viết (`https://cdn.shopify.com/s/files/1/0747/3505/9196/articles/*`) | `templates/source/blogs.html` (`/tin-tuc/`) và toàn bộ 16 file `templates/source/blogs/blog/*.html` | EXTERNAL (còn sống trên CDN Shopify, chưa kiểm tra tính bền vững lâu dài) | EXTERNAL — SHOULD LOCALIZE | Hotlink trực tiếp từ CDN Shopify. Nếu store Shopify gốc bị huỷ, toàn bộ ảnh bài viết + thumbnail trang tin tức sẽ hỏng ngay lập tức. Cần tải về và host trong `wp-content/uploads/` trước khi go-live. |
| Author headshot `Cayden_Headshot.png` (16 file blog) | `templates/source/blogs/blog/*.html` (tất cả 16 bài) | EXTERNAL (CDN Shopify) | EXTERNAL — SHOULD LOCALIZE | Cùng rủi ro như trên. |
| App-store / Google-Play badge images | `templates/source/Legacy.html` | EXTERNAL (CDN Shopify `s/files/.../badge_app_store.png`, `badge_google_play.png`) | EXTERNAL — SHOULD LOCALIZE | Theme đã có sẵn `assets/badges/apple_badge.svg`, `assets/badges/google_play_badge.png`, `assets/badges/github_badge.png` local — Legacy.html nên trỏ vào các badge local này thay vì hotlink CDN Shopify. |
| `assets/media/` (toàn bộ thư mục con) | Không có tham chiếu nào trong code theme/plugin (`{{THEME_URI}}/assets/...` chỉ dùng đường dẫn phẳng `assets/...`) | YES (44 MB, ~100 file, bản sao y hệt `assets/`) | DUPLICATE — khả năng REMOVE | Xác nhận bằng `diff -rq` là trùng khớp byte-for-byte với `assets/` (trừ `css/`/`js/`/`fonts/`). Ứng viên dọn dẹp an toàn, nhưng cần rà DB (post content/ACF) trước khi xoá — audit này chỉ kiểm tra code theme, chưa kiểm tra nội dung DB. |
| `assets/shopping-bag.svg`, `assets/shopping-bag-green.svg` | Không có tham chiếu nào trong `.php`/`.css`/`.js`/`templates/source/*.html` | YES nhưng KHÔNG dùng | UNUSED | Đúng theo business rule "không cần cart icon" — không render ở đâu cả. Có thể xoá an toàn hoặc giữ lại nếu dự định dùng cho CTA "Liên hệ mua hàng" trong tương lai. |
| `assets/Herosectionvideo-trimmed.mp4@v=3` | `templates/source/index.html` (và tương ứng) | YES (tên file khớp chính xác tham chiếu) | OK nhưng FRAGILE | Tên file chứa ký tự `@v=3` thay vì query string `?v=3` — là artefact từ quá trình scrape/export gốc. Tự nhất quán (không hỏng hiện tại) nhưng dễ vỡ với công cụ quản lý asset/CDN/Git LFS coi `@`/`=` là ký tự đặc biệt. Khuyến nghị đổi tên (vd. `hero-section-video-trimmed.mp4`) khi dọn dẹp. |
| `assets/mobile_hero_smol1-trimmed.mp4@v=2` | `templates/source/index.html` | YES | OK nhưng FRAGILE | Cùng vấn đề như trên. |
| 2/3 video hero trang chủ (`data-hero="true"`, bản desktop + mobile) | `/` (trang chủ) | YES (file tồn tại) | PARTIAL | Không có thuộc tính `poster` — có khoảng trắng/giật hình trước khi video phát. Video thứ 3 (`animation_website.mp4`) có `poster="mentra_hero_1.webp"` đúng chuẩn. |
| Toàn bộ ảnh local trên trang chủ (55 ảnh) | `/` | YES | OK | Tất cả trả về HTTP 200 khi kiểm tra trực tiếp, không có ảnh vỡ trên trang chủ. |
| Ảnh local trên các trang mẫu khác (`/so-sanh/`, `/ve-mentra/`, `/tuyen-dung/`, `/ho-tro/`, `/mentra-notes/`, `/mang-xa-hoi/`, `/thu-hoi/`, `/phu-de/`) | Các route tương ứng | YES (trừ mục Mentra_Ghi chú.png ở trên) | OK | Không phát hiện ảnh vỡ nào khác ngoài trường hợp đã liệt kê. |
| `assets/fonts/` | — | Thư mục rỗng | N/A | Không có font tự host — xem Font Audit trong `site-audit.md`. Không phải lỗi (Google Fonts CDN đang phục vụ), nhưng là rủi ro fallback nếu mạng chặn Google Fonts. |

## Logo — kiểm tra double-render

Không phát hiện lỗi double-logo. 3 thẻ `<img class="mentra-header-logo">` tồn tại trên trang chủ nhưng loại trừ lẫn nhau theo breakpoint responsive: 1 trong header mobile (`lg:hidden`), 1 trong nav desktop (`hidden lg:block`), 1 ở footer — không hiển thị đồng thời. CSS `.logo-for-light`/`.logo-for-dark` (mentra.css dòng 88-91, 285-286) tồn tại nhưng không HTML nào dùng — dead CSS, không gây lỗi hiển thị.

## Investor / Partner / Press logos (kiểm kê, không phải lỗi)

- `assets/investor_logos/`: Y Combinator, Amazon, Android, Pebble, Toyota Ventures, Hartmann, YouTube (7 logo)
- `assets/logos_press/`: 9to5Google, Digital Trends, Engadget, Android Police, Gizmodo (5 logo — tương ứng 5/6 mục báo chí trên `/tin-tuc/`; logo Forbes và GamesBeat dùng file riêng `assets/forbes.png`, `assets/games_beat.png`)
- `assets/partners/`: Dimenso, Elliptic Labs, MIT, Primary VC, Skillmaker, Unlimited IRL (6 logo)
- `assets/product_photos/`: chargingcase.webp, frame.png, frame2.png (ảnh sản phẩm Mentra Live)
- `assets/team/`: 12 ảnh nhân sự
- `assets/videos/`: 3 video B2B use-case (Delivery Navigation, HVAC Videocall, Mechanic MentraAI) + poster JPG

## Tổng kết

- **Broken/Missing:** 1 (ảnh `Mentra_Notes.png`)
- **External (Shopify CDN, cần localize):** 21 file HTML nguồn có hotlink (16 bài viết + blog.html/blogs.html + Legacy.html)
- **Duplicate (an toàn để dọn, chờ xác nhận DB):** `assets/media/` — 44 MB
- **Unused (an toàn để dọn):** 2 file `shopping-bag*.svg`
- **Fragile filename (không hỏng nhưng nên đổi tên):** 2 video `*.mp4@v=N`
- **Partial (thiếu poster):** 2 video hero trang chủ
