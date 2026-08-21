<?php
if (!defined('ABSPATH')) { exit; }

// Catalog data comes from WooCommerce (Products > Mentra Live Camera
// Glasses, SKU MENTRA-LIVE); the visual layout below stays theme-owned
// and static. Never falls back to a hardcoded price if the product is
// missing - only stock label, image, SKU, and per-color availability.
$mentra_live = mentra_vn_get_product_by_sku('MENTRA-LIVE');
$stock_label = $mentra_live ? mentra_vn_stock_label($mentra_live->get_stock_status()) : '';
$hero_image_id = $mentra_live ? $mentra_live->get_image_id() : 0;
$hero_image_url = $hero_image_id ? wp_get_attachment_image_url($hero_image_id, 'large') : '';
if (!$hero_image_url) { $hero_image_url = mentra_vn_asset('closed_mentra_live.webp'); }
$sku = $mentra_live ? $mentra_live->get_sku() : '';
$color_rows = '';
if ($mentra_live) {
    foreach ($mentra_live->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) { continue; }
        $attrs = $variation->get_attributes();
        $color = $attrs['mau-sac'] ?? '';
        if (!$color) { continue; }
        $color_rows .= '<dt>' . esc_html($color) . '</dt><dd>' . esc_html(mentra_vn_stock_label($variation->get_stock_status())) . '</dd>';
    }
}

mentra_vn_document_open('mentra-live');
mentra_vn_render_partial('site-header');
?>
<main id="main">
<section class="product-page-hero section section-soft"><div class="container split align-center"><div class="reveal"><p class="eyebrow">MENTRA LIVE</p><h1 class="product-title">Kính thông minh mở cho AI và công việc thực tế.</h1><p class="lead">Camera HD, loa stereo, ba microphone và SDK mở để xây dựng quy trình AI tùy chỉnh cho nhà phát triển và doanh nghiệp.</p><div class="product-price"><?php echo esc_html($stock_label ?: 'Liên hệ để biết tình trạng hàng'); ?></div><a class="btn btn-dark" href="<?php echo esc_url(home_url('/lien-he/?topic=sales')); ?>">Liên hệ mua hàng</a></div><div class="media-card reveal"><img src="<?php echo esc_url($hero_image_url); ?>" alt="Mentra Live"></div></div></section>
<section class="section"><div class="container"><div class="section-heading reveal"><p class="eyebrow">TỔNG QUAN</p><h2>Một nền tảng kính thông minh có thể tùy biến theo công việc của bạn.</h2></div><div class="cards-3"><article class="feature-card reveal"><h3>Camera & AI</h3><p>Ghi hình góc nhìn người dùng, hỗ trợ nhận diện ngữ cảnh và xây dựng trợ lý AI theo quy trình.</p></article><article class="feature-card reveal"><h3>Âm thanh rảnh tay</h3><p>Loa stereo và ba microphone cho cuộc gọi, hướng dẫn bằng giọng nói và ghi chú.</p></article><article class="feature-card reveal"><h3>SDK mở</h3><p>Xây dựng ứng dụng sử dụng camera, microphone, loa, touchpad và nút vật lý.</p></article></div></div></section>
<section class="section section-black"><div class="container split align-center"><div class="reveal"><p class="eyebrow">THIẾT KẾ</p><h2>43 gram, đủ nhẹ cho một ca làm việc dài.</h2><p>Mentra Live được thiết kế để có thể đeo lâu trong môi trường thực tế thay vì chỉ dùng cho những phiên demo ngắn.</p></div><img class="reveal" src="<?php echo mentra_vn_asset('product_photos/frame2.png'); ?>" alt="Khung kính Mentra Live"></div></section>
<section class="section specs"><div class="container"><div class="section-heading reveal"><p class="eyebrow">THÔNG SỐ</p><h2>Thông số Mentra Live.</h2></div><div class="spec-grid reveal"><div><h3>Khung & thiết kế</h3><dl><dt>Khối lượng</dt><dd>43 g</dd><dt>Kích thước</dt><dd>162 × 148 × 47 mm</dd><dt>Hoàn thiện</dt><dd>Đen mờ</dd><dt>Điều khiển</dt><dd>2 nút + thanh vuốt cảm ứng</dd><dt>Mã sản phẩm</dt><dd><?php echo esc_html($sku ?: '—'); ?></dd><?php echo $color_rows; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from esc_html()-escaped pieces only */ ?></dl></div><div><h3>Camera & âm thanh</h3><dl><dt>Camera</dt><dd>1080p, FOV 119°</dd><dt>Ảnh</dt><dd>3264 × 2448</dd><dt>Microphone</dt><dd>3 microphone</dd><dt>Loa</dt><dd>Stereo</dd></dl></div><div><h3>Pin & kết nối</h3><dl><dt>Pin kính</dt><dd>260 mAh</dd><dt>Hộp sạc</dt><dd>2.200 mAh</dd><dt>Thời lượng hỗn hợp</dt><dd>12+ giờ theo công bố</dd><dt>Nền tảng</dt><dd>MentraOS</dd></dl></div></div></div></section>
<section class="section section-green"><div class="container narrow reveal"><p class="eyebrow">DÀNH CHO DOANH NGHIỆP</p><h2>Bạn cần pilot hoặc triển khai số lượng lớn tại Việt Nam?</h2><p>Liên hệ để trao đổi về số lượng thiết bị, quy trình triển khai, tích hợp ứng dụng và hỗ trợ kỹ thuật.</p><a class="btn btn-dark" href="<?php echo esc_url(home_url('/lien-he/?chu-de=kinh-doanh')); ?>">Liên hệ kinh doanh</a></div></section>
</main>
<?php
mentra_vn_render_partial('site-footer');
mentra_vn_document_close();
