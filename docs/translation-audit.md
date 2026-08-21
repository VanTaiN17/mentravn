# Translation Audit — Mentra Vietnam

Nguồn dữ liệu: crawl trực tiếp `http://mentra-vn.local/` (toàn bộ route reachable, bao gồm các route soft-404 vẫn render nội dung) đối chiếu với nội dung gốc trong `templates/source/*.html`. Tên thương hiệu (Mentra, Mentra Live, MentraOS, Mentra Miniapp Store, NIMO, Even Realities, GitHub, Android, Discord, LinkedIn, X, Instagram...) **không** được tính là lỗi tiếng Anh.

Severity:
- **HIGH** = header/buttons/forms/main visible UI text
- **MEDIUM** = body content
- **LOW** = aria-label/title/meta/non-critical

## Bảng lỗi tiếng Anh còn sót

| Route | English Text | Location/File | Suggested Vietnamese | Severity |
|---|---|---|---|---|
| `/` | "Work economy split" | Body — chart section heading (`templates/source/index.html`) | "Cơ cấu nền kinh tế lao động" | HIGH |
| `/` | "Physical work is 70 percent of the economy and knowledge work is 30 percent." | Body — chart caption | "Lao động chân tay chiếm 70% nền kinh tế, lao động tri thức chiếm 30%." | HIGH |
| `/so-sanh/` | "Feature" (x2) | Body — bảng so sánh, header cột | "Tính năng" | HIGH |
| `/so-sanh/` | "Price" (x2) | Body — bảng so sánh, header cột | "Giá" | HIGH |
| `/so-sanh/` | "12+ hours" | Body — chỉ số pin trong bảng so sánh | "12+ giờ" | HIGH |
| `/so-sanh/` | "8 hours" | Body — chỉ số pin trong bảng so sánh | "8 giờ" | HIGH |
| `/so-sanh/` | "8+ platforms" (giữa câu tiếng Việt) | Body — "Livestream đến 8+ platforms đồng thời..." | "8+ nền tảng" | HIGH |
| `/so-sanh/` | "Comparison" | Body — nhãn tab/filter FAQ | "So sánh" | HIGH |
| `/so-sanh/` | "Privacy & Mã nguồn mở" (nửa dịch) | Body — nhãn tab/filter FAQ | "Quyền riêng tư & Mã nguồn mở" | HIGH |
| `/ve-mentra/` | "Advisors" | Body — tiêu đề mục nhóm cố vấn (nơi khác trên cùng trang đã dùng "Cố vấn") | "Cố vấn" | HIGH |
| `/ve-mentra/` | "Medical & Clinical Advisor" | Body — chức danh của Dr. Mila Huhtala (các cố vấn khác đã có chức danh tiếng Việt) | "Cố vấn Y khoa & Lâm sàng" | MEDIUM |
| `/lien-he/`, `?topic=sales` | "Business & Partnerships" | Body — option dropdown chủ đề liên hệ | "Kinh doanh & Đối tác" | HIGH |
| `/lien-he/` | "Other" | Body — option dropdown chủ đề liên hệ | "Khác" | HIGH |
| `/lien-he/` | "Follow us" | Body — tiêu đề khối sidebar | "Theo dõi chúng tôi" | HIGH |
| `/lien-he/` | "Reach Us" | Body — tiêu đề khối sidebar | "Liên hệ" | HIGH |
| `/lien-he/` | "Prescriptions" | Body — nhãn eyebrow phía trên link sidebar (link text "Tròng kính độ" đã dịch) | "Tròng kính độ" | HIGH |
| `/lien-he/` | "Nhà phát triển Docs" (nửa dịch) | Body — nhãn link sidebar | "Tài liệu cho nhà phát triển" | HIGH |
| `/lien-he/`, `/doi-tac/`, `/truyen-thong/` | "John Doe" | Form — placeholder trường Họ tên | "Nguyễn Văn A" | HIGH |
| `/lien-he/`, `/doi-tac/`, `/truyen-thong/` | "john@example.com" | Form — placeholder trường Email | "email@vidu.vn" | HIGH |
| `/lien-he/`, `/doi-tac/`, `/truyen-thong/` | "Acme Corp" | Form — placeholder trường Công ty | "Tên công ty" | HIGH |
| `/lien-he/`, `/doi-tac/`, `/truyen-thong/` | "Tell us what's on your mind..." | Form — placeholder textarea Nội dung | "Hãy cho chúng tôi biết nội dung bạn cần hỗ trợ..." | HIGH |
| `/tuyen-dung/` | "jane@example.com" | Form — placeholder email đơn ứng tuyển (các placeholder khác trên cùng form đã dùng "Nguyễn Văn A") | "email@vidu.vn" | MEDIUM |
| `/nha-phat-trien/` (soft-404) | "Tạo ứng dụng a free developer account." | Body — bước 1 hướng dẫn onboarding, câu bị dịch dở | "Tạo tài khoản nhà phát triển miễn phí." | HIGH |
| `/phu-de/` (soft-404) | "Download" | Body — nhãn nút | "Tải xuống" | HIGH |
| `/phu-de/` (soft-404) | "60+ languages" | Body — chỉ số tính năng | "60+ ngôn ngữ" | HIGH |
| `/mentra-notes/` (soft-404) | "Ghi chú from your smart glasses." | Body — tiêu đề phụ hero, dịch dở | "Ghi chú trực tiếp từ kính thông minh của bạn." | HIGH |
| `/mentra-notes/` (soft-404) | "Capture conversations, site context, and follow-ups while the work is happening." | Body — đoạn mô tả hero, toàn tiếng Anh | "Ghi lại hội thoại, bối cảnh công việc và các việc cần theo dõi ngay khi công việc đang diễn ra." | HIGH |
| `/mentra-notes/` (soft-404) | "Get Mentra Ghi chú" | Body — nút CTA chính, dịch dở | "Nhận Mentra Notes" | HIGH |
| `/mentra-notes/` (soft-404) | "Transcribe" | Body — tiêu đề tính năng | "Chuyển giọng nói thành văn bản" | HIGH |
| `/mang-xa-hoi/` (soft-404) | "Mentra channels" | Body — tiêu đề hero (khối này có dấu hiệu render 2 lần) | "Kênh của Mentra" | HIGH |
| `/mang-xa-hoi/` (soft-404) | "Follow Mentra." | Body — tiêu đề phụ hero | "Theo dõi Mentra." | HIGH |
| `/mang-xa-hoi/` (soft-404) | "All Platforms" | Body — nhãn tab/filter | "Tất cả nền tảng" | HIGH |
| `/discord/` (soft-404) | "Community" | Body — eyebrow/tiêu đề mục | "Cộng đồng" | HIGH |
| `/discord/` (soft-404) | "Join Discord" | Body — nút CTA (nơi khác trên cùng trang đã dùng "Tham gia Discord") | "Tham gia Discord" | HIGH |
| `/truyen-thong/` (soft-404) | "Media" | Body — eyebrow của trang | "Truyền thông" | HIGH |
| `/truyen-thong/` (soft-404) | "Media inquiries" | Body — tiêu đề phụ trang | "Liên hệ truyền thông" | HIGH |
| `/chinh-sach-quyen-rieng-tu/`, `/dieu-khoan-dich-vu/`, `/chinh-sach-van-chuyen/`, `/chinh-sach-doi-tra/`, `/kha-nang-tiep-can/` (tất cả soft-404) | Toàn bộ nội dung trang | Body — nguyên văn tiếng Anh từ Shopify, chưa migrate ("Our store is powered by Shopify", "Stripe, Shopify Payments"...) | Cần dịch toàn bộ + rà soát pháp lý (nội dung nhắc tới Shopify/Stripe sẽ sai sự thật khi site chỉ còn catalog, không checkout) | HIGH |
| `/blogs/blog/*` (16 route soft-404) | Toàn bộ nội dung bài viết | Body — nguyên văn tiếng Anh từ Shopify (tiêu đề/ngày/breadcrumb/vai trò tác giả đã có tiếng Việt, riêng nội dung bài thì chưa) | Cần dịch toàn bộ 16 bài trước khi import thành WordPress Post | HIGH |
| `/shop/`, `/cart/` | "Great things are on the horizon" / "Something big is brewing! Our store is in the works and will be launching soon!" | Body — placeholder mặc định WooCommerce | Không khuyến nghị dịch — nên loại route này khỏi luồng công khai theo business rule (không checkout) thay vì dịch nội dung "sắp ra mắt" | HIGH |
| `/my-account/` | "My account", "Login", "Username or email address", "Password", "Remember me", "Log in", "Lost your password?" | Body/Form — màn hình đăng nhập mặc định WooCommerce, chưa dịch | Không khuyến nghị dịch — route nằm ngoài phạm vi theo business rule | HIGH |
| `/category/uncategorized/`, `/author/admin/`, `/hello-world/` | "Hello world!", "Welcome to WordPress. This is your first post..." | Body — nội dung demo mặc định WordPress còn tồn tại | Không cần dịch — nên gỡ bỏ nội dung demo | MEDIUM |
| `/ho-tro/` | "Getting Started", "Using Your Glasses", "Troubleshooting", "Account & Orders", "Policies & Information", "Contact Us" | aria-label trên các link danh mục trợ giúp (link text hiển thị đã là tiếng Việt) | "Bắt đầu", "Sử dụng kính", "Khắc phục sự cố", "Tài khoản & Đơn hàng", "Chính sách & Thông tin", "Liên hệ" | LOW |
| `/ho-tro/` (liên kết ngoài) | Toàn bộ trung tâm trợ giúp tại `mentrahelp.zendesk.com/hc/en-us/...` | External — nằm ngoài phạm vi WordPress nhưng ảnh hưởng trải nghiệm hỗ trợ đầu-cuối | Cần quyết định business: dịch Zendesk hoặc thay bằng trang hỗ trợ nội bộ | MEDIUM (ghi nhận) |
| Tất cả trang | `<html lang="en-US">` | Meta — thuộc tính ngôn ngữ tài liệu sai trên toàn site dù nội dung đã là tiếng Việt | Đổi thành `<html lang="vi-VN">` (sửa tại `header.php` và mọi entrypoint `mentra_vn_document_open()`) | LOW (toàn site, ảnh hưởng SEO/accessibility) |
| Tất cả trang | Nhiều `aria-label`/`alt` tiếng Anh lặp lại: "Follow Mentra on X (Twitter) (opens in new tab)", "Follow Mentra on Instagram (opens in new tab)", "Join Mentra on Discord (opens in new tab)", "Follow Mentra on LinkedIn (opens in new tab)", "Read article from Forbes (opens in new tab)", "Download on the App Store (opens in new tab)", "Get it on Google Play (opens in new tab)", "Download on GitHub (opens in new tab)", "Learn more about prescription lenses", alt ảnh như "Smart Glasses", "Person sliding on Mentra glasses", "Woman wearing Mentra glasses", v.v. | aria-label / alt, lặp lại trên hầu hết trang (icon mạng xã hội + báo chí ở footer) | Dịch toàn bộ aria-label/alt sang tiếng Việt tương ứng | LOW |

## Trang đã dịch đầy đủ, không lỗi

`/ung-dung/`, `/trong-kinh/`, `/ho-tro/` (nội dung chính), `/tuyen-dung/` (nội dung chính, form riêng bị lỗi kỹ thuật — xem site-audit.md §22), `/tin-tuc/` (trang index — lưu ý phần thân 16 bài viết bên dưới vẫn chưa dịch), `/thu-hoi/` (nội dung xong, chỉ thiếu routing), `/doi-tac/` (nội dung xong, chỉ thiếu routing), trang 404, và các trang stub pháp lý (`/chinh-sach-bao-mat/`, `/dieu-khoan/`, `/van-chuyen/`, `/doi-tra/` — placeholder tiếng Việt có chủ đích, không phải lỗi dịch, nhưng chưa có nội dung thật).

## Trang chưa xác minh đầy đủ (cần audit thủ công bổ sung)

`/nimo/`, `/even-realities/` — cả hai đều là soft-404, trạng thái dịch chưa được xác minh chi tiết trong phiên audit này (không khớp các cụm từ tiếng Anh đặc trưng khi kiểm tra nhanh, nhưng chưa đọc toàn văn).

## Ghi chú tổng hợp

- **Không có trang nào hiển thị nhãn tiếng Anh ở mức nghiêm trọng do lỗi kiến trúc** — phần lớn lỗi HIGH nằm ở các trang soft-404 (nội dung gốc tiếng Anh chưa từng được dịch) hoặc các cụm từ ngắn còn sót lại giữa nội dung đã dịch phần lớn (dấu hiệu dịch thủ công bỏ sót, không phải lỗi hệ thống).
- 4 trang pháp lý (privacy-policy, terms-of-service, shipping-policy, refund-policy) + accessibility đều **hoàn toàn chưa dịch** (100% tiếng Anh nguyên bản Shopify) và đồng thời có bug slug kép — xem `site-audit.md` mục Broken Pages.
- 16/16 bài viết tin tức: phần khung (tiêu đề, ngày, breadcrumb) đã dịch nhưng **toàn bộ nội dung bài chưa dịch** — cần dịch trước khi import thành WordPress Post.
