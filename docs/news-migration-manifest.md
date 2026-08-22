# Phase 4 — News Migration Manifest

Source of truth for the 16 owned articles and 6 press items being migrated in Phase 4.
Verified independently against `wp-content/themes/mentra-vietnam/templates/source/blogs/blog/*.html` (16 files, confirmed by direct directory listing) and `templates/source/blogs.html` (confirmed 22 cards: 16 article cards `data-newsroom-kind="blogs"` + 6 press cards `data-newsroom-kind="news"`).

Status values: `PENDING` → `MIGRATED` (post created) → `TRANSLATED` (body confirmed Vietnamese) → `VERIFIED` (rendered output reviewed).

## 16 owned articles → `post_type=post`

| # | Source title | Vietnamese title | Source slug | New WP slug | Source date | Featured image | Author | Status |
|---|---|---|---|---|---|---|---|---|
| 1 | Mentra 3.0 - Local Miniapps, Full User Control, and Enterprise Smart Glasses | Mentra 3.0 - Miniapp cục bộ, toàn quyền kiểm soát và kính thông minh cho doanh nghiệp | mentra-3-0-local-miniapps-full-user-control-and-enterprise-smart-glasses | (same) | 2026-08-17 | Mockup_OS_Phone_Image2.png | Cayden Pierce (CEO) | VERIFIED |
| 2 | 1 Year of Mentra | Một năm của Mentra | 1-year-of-mentra | (same) | 2025-11-30 | news/1_year_anniversary_at_Mentra_post_Nov_30_2025_new_7f14de88-c1d5-4613-b26d-2182002270f3.png | Cayden Pierce (CEO) | VERIFIED |
| 3 | Announcing MentraOS 2.0 and Our $8M Raise | Ra mắt MentraOS 2.0 và công bố vòng gọi vốn 8 triệu USD | announcing-mentraos-2-0-and-our-8m-raise | (same) | 2025-07-06 | news/blog-cover_e2d454c1-36f5-48e0-81ca-8e05a76b1d35.png | Cayden Pierce (CEO) | VERIFIED |
| 4 | AugmentedChords Goes #1 on Hacker News | AugmentedChords lên #1 Hacker News - Ứng dụng bản nhạc xây dựng trên MentraOS | augmentedchords-goes-1-on-hacker-news-sheet-music-app-build-on-mentraos | (same) | 2025-05-06 | news/Kevin_Lin_Sheet_Music_Smart_Glasses_May3_2025.jpg | Cayden Pierce (CEO) | VERIFIED |
| 5 | Community Update - August 22 | Cập nhật cộng đồng - Cải thiện độ ổn định, Mentra Live và màn hình | august-22-community-update | (same) | 2025-08-22 | news/nex_is_cooking_613cb673-6493-4ecc-ad5d-a2c91ab1cbac.png | Cayden Pierce (CEO) | VERIFIED |
| 6 | Batch 1 Is Almost Sold Out | Đợt 1 gần bán hết! Tham gia Câu lạc bộ Nhà sáng lập Mentra | batch-1-almost-sold-out | (same) | 2026-01-05 | news/1_commerical_bbb38391-aafe-406f-878d-dc3db4dd37b0.png | Cayden Pierce (CEO) | VERIFIED |
| 7 | Even Realities G2 Support Coming on MentraOS | MentraOS sắp hỗ trợ Even Realities G2 | even-realities-g2-supported-on-mentraos | (same) | 2025-11-15 | news/G2_display_86b44e9b-26f7-403f-9c9a-43e2dec74e86.png | Cayden Pierce (CEO) | VERIFIED |
| 8 | Making Mentra Live | Hành trình tạo nên Mentra Live | making-mentra-live | (same) | 2025-12-21 | news/mentra_live_history_93b94f1b-e7e9-479d-b617-4b45963ee804.jpg | Alex Israelov (CTO) + Cayden Pierce (CEO) | VERIFIED |
| 9 | MemCards: the First Third-Party App to Launch on Mentra Market | MemCards: ứng dụng bên thứ ba đầu tiên ra mắt trên Mentra Market | memcards-the-first-third-party-app-to-launch-on-mentra-market | (same) | 2025-08-29 | news/MemCards.jpg | Cayden Pierce (CEO) | VERIFIED |
| 10 | Mentra Live Featured in Engadget, Gizmodo, New York Post, and More | Đợt 1 đã bán hết: Mentra Live xuất hiện trên Engadget, Gizmodo và NY Post | mentra-live-featured-in-engadget-gizmodo-new-york-post-and-more | (same) | 2026-01-29 | news/News_Montage_556772c1-63cb-482b-a58b-f55f41361213.png | Cayden Pierce (CEO) | VERIFIED |
| 11 | Mentra Live Shipping Update | Cập nhật giao hàng Mentra Live | mentra-live-shipping-update | (same) | 2025-12-21 | news/mentra_live_sexy.jpg | Alex Israelov (CTO) + Cayden Pierce (CEO) | VERIFIED |
| 12 | Mentra Roadmap Update: Moving to Miniapps on the Phone | Cập nhật lộ trình Mentra: Chuyển miniapp sang chạy trên điện thoại | mentra-roadmap-update-moving-to-miniapps-on-the-phone | (same) | 2026-06-09 | Mockup_OS_Phone_Hand.png | Cayden Pierce (CEO) | VERIFIED |
| 13 | MentraOS 1.0 Launch Hackathon | Hackathon ra mắt MentraOS 1.0 - Hackathon kính thông minh tháng 3/2025 | mentraos-1-0-launch-hackathon-smart-glasses-hackathon-march-2-2025 | (same) | 2025-03-04 | news/Screenshot_from_2025-11-29_17-22-44.png | Cayden Pierce (CEO) | VERIFIED |
| 14 | Why We're Building MentraOS: The Smart Glasses Operating System | Vì sao chúng tôi xây dựng MentraOS: Hệ điều hành cho kính thông minh | mentraos-the-smart-glasses-operating-system-app-store | (same) | 2025-02-20 | news/MentraOS_store_1_captions_crop_64ce2518-9ccf-41f3-8337-be7fed2e99a1.png | Cayden Pierce (CEO) | VERIFIED |
| 15 | Mentra Releases First Smart Glasses With An App Store | Mentra ra mắt kính thông minh đầu tiên có kho ứng dụng riêng | our-first-press-release-mentra-releases-first-smart-glasses-with-an-app-store | (same) | 2026-01-15 | news/WomanWearingMentraLive_78ff9fd3-f995-45b4-9377-c79b8c26577c.png | Cayden Pierce (CEO) | VERIFIED |
| 16 | Real-Time Captions With MentraOS | Phụ đề thời gian thực với MentraOS | real-time-captions-with-mentraos | (same) | 2026-02-06 | news/Caption_Blogs_6240a85f-4b0c-434f-949c-65c09cb099e4.png | Cayden Pierce (CEO) | VERIFIED |

All 16 WP slugs are identical to the source slugs — no collisions found, so legacy `/blogs/blog/{slug}/` maps 1:1 to canonical `/tin-tuc/{slug}/`.

Category: **Bài viết** (slug `bai-viet`) applied to all 16.
Marker meta: `_mentra_vn_article = 1` on all 16.
Legacy slug meta: `_mentra_vn_legacy_slug` = source slug (same value as new slug in every case) — kept anyway so the redirect logic never depends on slug equality by coincidence.

## 6 press items — PRESS STATIC (never WordPress Posts)

| # | Publisher | Headline (VN) | Date | External URL | Image |
|---|---|---|---|---|---|
| 1 | Digital Trends | "Lựa chọn thay thế Ray-Ban Meta này là mã nguồn mở — và điều đó thay đổi mọi thứ" | 2026-01-16 | https://www.digitaltrends.com/wearables/your-ray-ban-meta-alternative-is-open-source-and-that-changes-everything/ | logos_press/Digital_Trends_logo.svg.png | PRESS STATIC |
| 2 | Engadget | "Kính thông minh đầu tiên của Mentra là mã nguồn mở và có kho ứng dụng riêng" | 2026-01-15 | https://www.engadget.com/wearables/mentras-first-smart-glasses-are-open-source-and-come-with-their-own-app-store-150021126.html | logos_press/Engadget-logo.svg.png | PRESS STATIC |
| 3 | 9to5Google | "Kính thông minh 349 USD cạnh tranh Ray-Ban Meta với livestream YouTube và kho ứng dụng" | 2026-01-15 | https://9to5google.com/2026/01/15/mentra-live-smart-glasses-youtube-livestream/ | logos_press/9to5google.png | PRESS STATIC |
| 4 | Android Police | "Đối thủ của Ray-Ban Meta hướng đến trải nghiệm livestream hoàn chỉnh" | 2026-01-15 | https://tech.yahoo.com/ar-vr/articles/ray-ban-meta-challengers-fulfill-150010023.html | logos_press/android_police.png | PRESS STATIC |
| 5 | Forbes | "Mentra huy động 8 triệu USD để ra mắt hệ điều hành mã nguồn mở cho kính thông minh" | 2025-07-01 | https://www.forbes.com/sites/charliefink/2025/07/01/mentra-raises-8-million-to-launch-open-source-os-for-smart-glasses/ | forbes.png | PRESS STATIC |
| 6 | GamesBeat | "Mentra huy động 8 triệu USD và ra mắt MentraOS 2.0 mã nguồn mở cho kính thông minh" | 2025-06-27 | https://gamesbeat.com/mentra-raises-8m-and-launches-mentraos-2-0-open-source-smartglasses-software/ | games_beat.png | PRESS STATIC |

Exactly these 6. Gizmodo appears only as a logo-strip mention on `/live` and `/`, never as a Newsroom card in the source — per Phase 1's note, it is explicitly NOT added as a 7th press item.

Implemented as a static PHP data array: `wp-content/themes/mentra-vietnam/data/press.php`. Never imported as WordPress Posts. Verification: 0 press items present in `wp_posts` after migration (see Phase 4 report, Tests section).
