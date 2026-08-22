<?php
if (!defined('ABSPATH')) { exit; }

/**
 * The 6 official Mentra press mentions. Static data, never WordPress Posts
 * (see Phase 4 report / docs/news-migration-manifest.md). Gizmodo appears
 * only as a logo-strip mention elsewhere in the source and is deliberately
 * not a 7th entry here.
 */
function mentra_vn_press_items() {
    return [
        [
            'publisher' => 'Digital Trends',
            'headline' => 'Lựa chọn thay thế Ray-Ban Meta này là mã nguồn mở — và điều đó thay đổi mọi thứ',
            'date' => '2026-01-16',
            'url' => 'https://www.digitaltrends.com/wearables/your-ray-ban-meta-alternative-is-open-source-and-that-changes-everything/',
            'image' => 'logos_press/Digital_Trends_logo.svg.png',
        ],
        [
            'publisher' => 'Engadget',
            'headline' => 'Kính thông minh đầu tiên của Mentra là mã nguồn mở và có kho ứng dụng riêng',
            'date' => '2026-01-15',
            'url' => 'https://www.engadget.com/wearables/mentras-first-smart-glasses-are-open-source-and-come-with-their-own-app-store-150021126.html',
            'image' => 'logos_press/Engadget-logo.svg.png',
        ],
        [
            'publisher' => '9to5Google',
            'headline' => 'Kính thông minh 349 USD cạnh tranh Ray-Ban Meta với livestream YouTube và kho ứng dụng',
            'date' => '2026-01-15',
            'url' => 'https://9to5google.com/2026/01/15/mentra-live-smart-glasses-youtube-livestream/',
            'image' => 'logos_press/9to5google.png',
        ],
        [
            'publisher' => 'Android Police',
            'headline' => 'Đối thủ của Ray-Ban Meta hướng đến trải nghiệm livestream hoàn chỉnh',
            'date' => '2026-01-15',
            'url' => 'https://tech.yahoo.com/ar-vr/articles/ray-ban-meta-challengers-fulfill-150010023.html',
            'image' => 'logos_press/android_police.png',
        ],
        [
            'publisher' => 'Forbes',
            'headline' => 'Mentra huy động 8 triệu USD để ra mắt hệ điều hành mã nguồn mở cho kính thông minh',
            'date' => '2025-07-01',
            'url' => 'https://www.forbes.com/sites/charliefink/2025/07/01/mentra-raises-8-million-to-launch-open-source-os-for-smart-glasses/',
            'image' => 'forbes.png',
        ],
        [
            'publisher' => 'GamesBeat',
            'headline' => 'Mentra huy động 8 triệu USD và ra mắt MentraOS 2.0 mã nguồn mở cho kính thông minh',
            'date' => '2025-06-27',
            'url' => 'https://gamesbeat.com/mentra-raises-8m-and-launches-mentraos-2-0-open-source-smartglasses-software/',
            'image' => 'games_beat.png',
        ],
    ];
}
