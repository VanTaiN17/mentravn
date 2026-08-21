<?php
if (!defined('ABSPATH')) { exit; }

// Phase 4.5 rebuild: the previous version of this template used an invented
// classname vocabulary (product-page-hero/cards-3/feature-card/spec-grid)
// that only ever existed in an orphaned, never-enqueued main.css, so it
// rendered essentially unstyled. This version reproduces the real WGET
// product-detail-page structure (see docs/visual-fidelity-audit.md), with
// WooCommerce still the sole data source for stock/SKU/gallery images -
// price, quantity, and Add to Cart are never rendered anywhere on this page,
// per the permanent catalog-only business rule.
$mentra_live = mentra_vn_get_product_by_sku('MENTRA-LIVE');
$stock_label = $mentra_live ? mentra_vn_stock_label($mentra_live->get_stock_status()) : 'Liên hệ để biết tình trạng hàng';
$sku = $mentra_live ? $mentra_live->get_sku() : '';

// Gallery/thumbnails: WooCommerce's featured image + gallery images when the
// product exists (DYNAMIC-WIRE, matches Phase 3's sideloaded 5-image set),
// falling back to the same static theme assets the source page's gallery
// uses if the product is ever missing.
$thumbs = [];
if ($mentra_live) {
    $ids = array_merge([$mentra_live->get_image_id()], $mentra_live->get_gallery_image_ids());
    foreach ($ids as $attachment_id) {
        if (!$attachment_id) { continue; }
        $url = wp_get_attachment_image_url($attachment_id, 'large');
        if ($url) { $thumbs[] = ['url' => $url, 'alt' => 'Mentra Live']; }
    }
}
if (!$thumbs) {
    $thumbs = [
        ['url' => mentra_vn_asset('mentra_live_sexy_1.png'), 'alt' => 'Mentra Live'],
        ['url' => mentra_vn_asset('product_photos/frame.png'), 'alt' => 'Gọng kính'],
        ['url' => mentra_vn_asset('product_photos/frame2.png'), 'alt' => 'Khung kính'],
        ['url' => mentra_vn_asset('micro_charge_cable_mentra_live.png'), 'alt' => 'Infinity Cable'],
        ['url' => mentra_vn_asset('product_photos/chargingcase.webp'), 'alt' => 'Hộp sạc'],
        ['url' => mentra_vn_asset('closed_mentra_live.webp'), 'alt' => 'Mentra Live gấp gọn'],
    ];
}
$hero_image_url = $thumbs[0]['url'];

// Base spec-table rows (static facts from the source), plus per-variation
// stock rows appended dynamically from live WooCommerce data.
$spec_rows = [
    ['Nền tảng', 'MentraOS'],
    ['Phần cứng', 'Camera, loa và ba micro'],
    ['Màn hình', 'Không có màn hình'],
    ['Tròng kính', 'Tròng trong không độ'],
    ['Giao hàng', 'Giao trong 1–3 ngày'],
    ['Đổi trả', 'Đổi trả trong 30 ngày'],
];
if ($sku) { $spec_rows[] = ['Mã sản phẩm', $sku]; }
if ($mentra_live) {
    foreach ($mentra_live->get_children() as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) { continue; }
        $attrs = $variation->get_attributes();
        $color = $attrs['mau-sac'] ?? '';
        if (!$color) { continue; }
        $spec_rows[] = ['Màu ' . $color, mentra_vn_stock_label($variation->get_stock_status())];
    }
}

$press_logos = [
    ['forbes.png', 'Forbes', 'https://www.forbes.com/sites/charliefink/2025/07/01/mentra-raises-8-million-to-launch-open-source-os-for-smart-glasses/'],
    ['games_beat.png', 'GamesBeat', 'https://gamesbeat.com/mentra-raises-8m-and-launches-mentraos-2-0-open-source-smartglasses-software/'],
    ['logos_press/9to5google.png', '9to5Google', 'https://9to5google.com/2025/01/07/mentra-live-smart-glasses/'],
    ['logos_press/gizmodo.png', 'Gizmodo', 'https://gizmodo.com/mentra-live-is-an-open-source-answer-to-ray-ban-meta-smart-glasses-2000549260'],
    ['logos_press/android_police.png', 'Android Police', 'https://www.androidpolice.com/mentra-live-smart-glasses-open-source-review/'],
];

$mentra_vn_summary = function ($mobile) use ($stock_label, $spec_rows, $press_logos) {
    $wrap_class = $mobile ? 'product-detail-mobile-summary lg:hidden' : 'product-detail-summary hidden lg:block order-2 lg:order-2';
    ?>
    <div class="<?php echo esc_attr($wrap_class); ?>">
        <h1 class="product-detail-title mb-4">Mentra Live</h1>
        <div class="product-detail-price mb-4"><?php echo esc_html($stock_label); ?></div>
        <p class="product-detail-description mb-5">Kính thông minh tích hợp camera, loa, micro và SDK mở cho các quy trình AI tùy chỉnh. Được thiết kế cho nhà phát triển và triển khai doanh nghiệp.</p>
        <div class="mb-5">
            <a class="btn-base btn-lg w-full btn-primary" style="display:flex" href="<?php echo esc_url(home_url('/lien-he/?topic=sales')); ?>">Liên hệ mua hàng</a>
        </div>
        <div class="mb-5 border-t py-4" style="border-color:var(--border-subtle)">
            <p class="mb-3 text-[12px] font-semibold uppercase tracking-[0.16em]" style="color:var(--ink-tertiary)">Được nhắc đến trên:</p>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
                <?php foreach ($press_logos as [$file, $name, $href]) : ?>
                <a aria-label="<?php echo esc_attr('Đọc bài viết từ ' . $name . ' (mở tab mới)'); ?>" class="inline-flex items-center rounded-sm transition-opacity duration-150 hover:opacity-80" href="<?php echo esc_url($href); ?>" rel="noopener noreferrer" target="_blank"><img alt="<?php echo esc_attr($name); ?>" class="h-4 max-w-[104px] object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset($file)); ?>"></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mb-5">
            <div class="product-detail-spec-table divide-y border-y" style="border-color:var(--border-subtle)">
                <?php foreach ($spec_rows as [$label, $value]) : ?>
                <div class="product-detail-spec-row grid grid-cols-[minmax(104px,0.42fr)_1fr] gap-4"><span style="color:var(--ink-tertiary)"><?php echo esc_html($label); ?></span><span class="font-semibold" style="color:var(--ink-primary)"><?php echo esc_html($value); ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
};

mentra_vn_document_open('mentra-live');
mentra_vn_render_partial('site-header');
?>
<main id="main">
<section class="product-detail-section bg-surface-0">
<div class="site-shell">
<div class="product-detail-grid">
<div class="order-1 md:order-1">

<div class="product-detail-media-card mb-5" style="background-color:var(--surface-1)">
<img alt="Mentra Live" class="product-detail-media-image" src="<?php echo esc_url($hero_image_url); ?>">
</div>
<div class="product-detail-thumbnails">
<?php foreach ($thumbs as $i => $t) : ?>
<button aria-label="<?php echo esc_attr('Xem ' . $t['alt']); ?>" aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>" class="product-detail-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"><img alt="<?php echo esc_attr($t['alt']); ?>" class="product-detail-thumb-image" src="<?php echo esc_url($t['url']); ?>"></button>
<?php endforeach; ?>
</div>

<?php $mentra_vn_summary(true); ?>

<div>
<div data-mentra-reveal>
<h2 class="text-2xl sm:text-3xl font-bold mb-8 sm:mb-12" style="color:var(--ink-primary);letter-spacing:-0.015em">Giới thiệu Mentra Live</h2>
</div>
<div class="grid lg:grid-cols-2 gap-6 sm:gap-12 mb-10 sm:mb-16">
<div class="space-y-6">
<div data-mentra-reveal><p style="color:var(--ink-primary);font-size:16px;line-height:1.65">Mentra Live là kính thông minh mã nguồn mở với SDK đầy đủ, camera, âm thanh và AI. Xây dựng, triển khai ứng dụng tùy chỉnh cho công việc hiện trường, kiểm tra, hỗ trợ từ xa và các quy trình rảnh tay.</p></div>
<div data-mentra-reveal><p style="color:var(--ink-primary);font-size:16px;line-height:1.65">Triển khai ứng dụng cho đội ngũ và cập nhật từ xa qua mạng.</p></div>
</div>
<div class="space-y-6">
<div data-mentra-reveal><p style="color:var(--ink-primary);font-size:16px;line-height:1.65">Với trọng lượng 43 g, Mentra Live đủ nhẹ để đeo cả ngày trong ca làm việc, công việc hiện trường và các triển khai thực tế. Chúng tôi đã thử nghiệm hàng chục nguyên mẫu gọng và dành hàng nghìn giờ để tạo ra chiếc kính mà người dùng thực sự muốn đeo lâu dài.</p></div>
</div>
</div>

<div data-mentra-reveal>
<div class="pdp-accordion-item is-open" id="mlive-features">
  <button aria-controls="mlive-features-panel" aria-expanded="true" class="pdp-accordion-trigger"><span>Tính năng</span><span aria-hidden="true" class="pdp-accordion-icon"><svg fill="none" height="16" viewbox="0 0 16 16" width="16"><path d="M8 1v14M1 8h14" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span></button>
  <div class="pdp-accordion-panel" id="mlive-features-panel"><div class="pb-6">
    <p class="mb-6" style="color:var(--ink-secondary);font-size:16px">Mentra Live là một nền tảng có khả năng tùy biến sâu theo từng trường hợp sử dụng và mô hình triển khai. Đây là những gì các đội ngũ đang xây dựng với Mentra Live:</p>
    <div class="grid gap-8">
      <div class="group"><h3 class="mb-3 font-semibold" style="color:var(--ink-primary);font-size:18px">Trợ lý AI cho kỹ thuật viên</h3><div class="overflow-hidden rounded-2xl bg-surface-2" style="aspect-ratio:16 / 9"><video aria-label="Trợ lý AI cho kỹ thuật viên" autoplay class="h-full w-full object-cover" controls loop muted playsinline preload="none" src="<?php echo esc_url(mentra_vn_asset('videos/Mechanic_MentraAI_B2B.mp4')); ?>"></video></div></div>
      <div class="group"><h3 class="mb-3 font-semibold" style="color:var(--ink-primary);font-size:18px">Cuộc gọi video rảnh tay</h3><div class="overflow-hidden rounded-2xl bg-surface-2" style="aspect-ratio:16 / 9"><video aria-label="Cuộc gọi video rảnh tay" autoplay class="h-full w-full object-cover" controls loop muted playsinline preload="none" src="<?php echo esc_url(mentra_vn_asset('videos/HVAC_Videocall_B2B.mp4')); ?>"></video></div></div>
      <div class="group"><h3 class="mb-3 font-semibold" style="color:var(--ink-primary);font-size:18px">Trợ lý AI cho tài xế giao hàng</h3><div class="overflow-hidden rounded-2xl bg-surface-2" style="aspect-ratio:16 / 9"><video aria-label="Trợ lý AI cho tài xế giao hàng" autoplay class="h-full w-full object-cover" controls loop muted playsinline preload="none" src="<?php echo esc_url(mentra_vn_asset('videos/Delivery_Navigation_B2B.mp4')); ?>"></video></div></div>
    </div>
  </div></div>
</div>

<div class="pdp-accordion-item" id="mlive-specs">
  <button aria-controls="mlive-specs-panel" aria-expanded="false" class="pdp-accordion-trigger"><span>Thông số kỹ thuật</span><span aria-hidden="true" class="pdp-accordion-icon"><svg fill="none" height="16" viewbox="0 0 16 16" width="16"><path d="M8 1v14M1 8h14" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span></button>
  <div class="pdp-accordion-panel" id="mlive-specs-panel"><div class="pb-6">
    <div class="space-y-5 text-[14px] leading-[1.55]">
      <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Trong hộp</h3><ul class="space-y-1" style="color:var(--ink-secondary)"><li>• Kính, hộp sạc, Infinity Cable và khăn microfiber</li></ul></div>
      <div class="grid md:grid-cols-2 gap-6">
        <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Gọng &amp; thiết kế</h3><ul class="space-y-1" style="color:var(--ink-secondary)"><li>• Trọng lượng: 43 g</li><li>• Hoàn thiện đen mờ</li><li>• 162mm D × 148mm R × 47mm C</li><li>• Hai nút bấm và thanh cảm ứng vuốt</li></ul></div>
        <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Camera</h3><ul class="space-y-1" style="color:var(--ink-tertiary)"><li>• Góc nhìn 119°, hướng ngang</li><li>• Video HD 1080p</li><li>• Ảnh HD 3264 × 2448</li></ul></div>
      </div>
      <div class="grid md:grid-cols-2 gap-6">
        <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Âm thanh</h3><ul class="space-y-1" style="color:var(--ink-tertiary)"><li>• Loa stereo, 3 micro</li><li>• Lệnh giọng nói và cuộc gọi</li></ul></div>
        <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Pin</h3><ul class="space-y-1" style="color:var(--ink-tertiary)"><li>• 260 mAh (kính), 2.200 mAh (hộp)</li><li>• Hơn 12 giờ sử dụng hỗn hợp</li><li>• Sạc qua Infinity Cable hoặc hộp sạc</li></ul></div>
      </div>
      <div><h3 class="font-semibold mb-3" style="color:var(--ink-primary);font-size:15px">Hệ điều hành &amp; kết nối</h3><ul class="space-y-1" style="color:var(--ink-tertiary)"><li>• Chạy MentraOS với đầy đủ hỗ trợ ứng dụng và SDK</li><li>• Wi-Fi + Bluetooth</li><li>• Tương thích iOS 15.1+ và Android 12+</li></ul></div>
    </div>
  </div></div>
</div>

<div class="py-6"><div>
  <div class="mb-5 flex items-center justify-between gap-4"><h2 class="text-[18px] font-semibold leading-tight" style="color:var(--ink-primary)">Đổi trả &amp; bảo hành</h2><a class="shrink-0 text-[13px] font-semibold underline underline-offset-4" href="<?php echo esc_url(home_url('/chinh-sach-doi-tra/')); ?>" style="color:var(--brand-text)">Xem chính sách đầy đủ</a></div>
  <!-- Vietnam-specific legal/business review required before this copy is treated as final policy text - see CLAUDE.md "Legal content". -->
  <div class="space-y-4">
    <p class="text-[15px] leading-[1.65]" style="color:var(--ink-tertiary)">Chúng tôi hỗ trợ đổi trả trong 30 ngày đối với đơn đủ điều kiện. Người mua chịu phí gửi trả, trừ trường hợp sản phẩm bị lỗi.</p>
    <p class="text-[15px] leading-[1.65]" style="color:var(--ink-tertiary)">Nếu sản phẩm phát sinh lỗi trong vòng 30 ngày kể từ khi giao, Mentra sẽ chi trả phí gửi trả và hoàn tiền sau khi xác nhận sự cố.</p>
    <p class="text-[15px] leading-[1.65]" style="color:var(--ink-tertiary)">Mentra Live đi kèm bảo hành giới hạn 1 năm cho các lỗi thuộc phạm vi bảo hành. Với yêu cầu được duyệt, Mentra hỗ trợ phí gửi trả và sản phẩm thay thế.</p>
  </div>
</div></div>

<div class="pdp-accordion-item" id="mlive-faq">
  <button aria-controls="mlive-faq-panel" aria-expanded="false" class="pdp-accordion-trigger"><span>Câu hỏi thường gặp</span><span aria-hidden="true" class="pdp-accordion-icon"><svg fill="none" height="16" viewbox="0 0 16 16" width="16"><path d="M8 1v14M1 8h14" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span></button>
  <div class="pdp-accordion-panel" id="mlive-faq-panel"><div class="pb-6">
    <div class="space-y-6">
      <div>
        <h3 class="text-lg font-semibold mb-3" style="color:var(--brand-text)">Sản phẩm &amp; tính năng</h3>
        <div class="space-y-3">
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Mentra Live có thể làm gì?</h4><p style="color:var(--ink-tertiary);font-size:15px">Mentra Live là kính thông minh có SDK mã nguồn mở cho phép xây dựng và triển khai ứng dụng trong môi trường thực tế. Kính có camera, ba micro, loa stereo và kho ứng dụng, nhưng không có màn hình. Ngay khi mở hộp, bạn có thể chụp ảnh, quay video, gọi điện, nghe nhạc, dùng phụ đề và dịch trực tiếp, ghi chú AI, livestream và chạy ứng dụng từ Mentra Miniapp Store.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Điều gì làm Mentra Live khác biệt?</h4><p style="color:var(--ink-tertiary);font-size:15px">Mentra Live nổi bật với SDK mã nguồn mở đầy đủ tính năng. Bạn có thể triển khai ứng dụng riêng trên nền tảng MentraOS, giữ quyền kiểm soát dữ liệu và hỗ trợ tùy chọn triển khai tại chỗ. Nhờ gọng nhẹ và dễ đeo, Mentra Live phù hợp sử dụng cả ngày để nhân viên tuyến đầu có thể đeo suốt ca làm việc.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Mentra Live có màn hình không?</h4><p style="color:var(--ink-tertiary);font-size:15px">Không. Đây là lựa chọn có chủ đích để kính nhẹ hơn, pin lâu hơn và thoải mái hơn. Màn hình làm tăng trọng lượng và tiêu hao pin; Mentra Live thuộc nhóm kính thông minh nhẹ trên thị trường.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Pin dùng được bao lâu?</h4><p style="color:var(--ink-tertiary);font-size:15px">Mentra Live được tối ưu cho thời lượng pin dài. Đeo cả ngày: hơn 10 giờ sử dụng hỗn hợp | Nghe nhạc: hơn 5 giờ | Livestream: hơn 40 phút | Quay video: hơn 1 giờ.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Mentra Live có chống nước không?</h4><p style="color:var(--ink-tertiary);font-size:15px">Không nên bơi khi đeo Mentra Live. Kính có thể chịu mưa nhẹ, mồ hôi và sử dụng hằng ngày, nhưng không nên dùng dưới mưa lớn.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Có thể dùng Mentra Live khi không có Internet không?</h4><p style="color:var(--ink-tertiary);font-size:15px">Có. Bạn vẫn có thể quay video, chụp ảnh, gọi điện, nghe nhạc và dùng một số chức năng khác khi không có Internet.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Trong hộp có gì?</h4><p style="color:var(--ink-tertiary);font-size:15px">Kính Mentra Live, hộp sạc, Infinity Cable và khăn microfiber.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Infinity Cable là gì?</h4><p style="color:var(--ink-tertiary);font-size:15px">Infinity Cable là cáp đặc biệt cắm trực tiếp vào kính để vừa đeo vừa sạc, giúp kéo dài thời gian sử dụng liên tục. Cáp có thể lấy nguồn từ pin dự phòng, điện thoại hoặc thiết bị USB-C. Xem phần <a href="#charging" style="color:var(--brand-text)">Sạc &amp; Infinity Cable</a> bên dưới.</p></div>
        </div>
      </div>
      <div>
        <h3 class="text-lg font-semibold mb-3" style="color:var(--brand-text)">Đặt hàng &amp; vận chuyển</h3>
        <div class="space-y-3">
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Làm sao để mua Mentra Live tại Việt Nam?</h4><p style="color:var(--ink-tertiary);font-size:15px">Trang này hiển thị tình trạng hàng theo từng màu trực tiếp từ hệ thống. Với nhu cầu đặt hàng, số lượng lớn hoặc triển khai thử nghiệm, hãy dùng nút "Liên hệ mua hàng" ở trên để được tư vấn.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Khi nào đơn hàng được gửi?</h4><p style="color:var(--ink-tertiary);font-size:15px">Giao trong 1–3 ngày sau khi đơn được xác nhận.</p></div>
        </div>
      </div>
      <div>
        <h3 class="text-lg font-semibold mb-3" style="color:var(--brand-text)">Phần mềm &amp; ứng dụng</h3>
        <div class="space-y-3">
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">MentraOS là gì?</h4><p style="color:var(--ink-tertiary);font-size:15px">MentraOS là hệ điều hành mã nguồn mở dành cho kính thông minh, được xây dựng cho Even Realities, Vuzix, Mentra Live và nhiều thiết bị khác. Mã nguồn mở nghĩa là mã nguồn được công khai, cho phép bất kỳ ai xem, chỉnh sửa và phân phối lại, thúc đẩy phát triển cộng tác và minh bạch.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Làm thế nào để xây dựng ứng dụng cho Mentra Live?</h4><p style="color:var(--ink-tertiary);font-size:15px">Ứng dụng Mentra Live sử dụng Mentra Bluetooth SDK. Bạn có thể viết ứng dụng Android hoặc iOS kết nối trực tiếp với Mentra Live và điều khiển camera, loa, micro, touchpad và nút bấm, hoạt động ngoại tuyến và không phụ thuộc hạ tầng cloud do Mentra lưu trữ. Xem <a href="https://docs.mentraglass.com" rel="noopener noreferrer" style="color:var(--brand-text)" target="_blank">tài liệu Mentra</a> để tìm hiểu thêm.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Mentra Live có hoạt động với iOS và Android không?</h4><p style="color:var(--ink-tertiary);font-size:15px">Có, thông qua ứng dụng Mentra. Tải ứng dụng từ <a href="https://apps.apple.com/us/app/mentra-the-smart-glasses-app/id6747363193" style="color:var(--brand-text)">App Store</a> hoặc <a href="https://play.google.com/store/apps/details?id=com.mentra.mentra" style="color:var(--brand-text)">Google Play</a>.</p></div>
          <div class="rounded-xl p-4 -mx-4 transition-colors duration-200 hover:bg-surface-1"><h4 class="font-semibold mb-1" style="color:var(--ink-primary);font-size:16px">Mentra Live có lưu dữ liệu của tôi không?</h4><p style="color:var(--ink-tertiary);font-size:15px">MentraOS chỉ lưu địa chỉ email và danh sách ứng dụng bạn đã cài. Người dùng có thể xem và xóa dữ liệu trực tiếp trong ứng dụng Mentra.</p></div>
        </div>
      </div>
    </div>
  </div></div>
</div>
</div>

</div>

<?php $mentra_vn_summary(false); ?>

</div>
</div>
</section>

<section id="charging" class="section-md bg-surface-0" style="padding-left:var(--container-px);padding-right:var(--container-px)">
<div class="mx-auto w-full" style="max-width:1200px">
<div data-mentra-reveal>
<div class="text-center mb-8 md:mb-12">
<p class="eyebrow" style="color:var(--brand-text)">SẠC &amp; INFINITY CABLE</p>
<h2 class="font-semibold mb-4 text-[22px] sm:text-[24px] md:text-[28px] xl:text-[32px] 2xl:text-[42px] leading-tight" style="color:var(--ink-primary)">Được thiết kế cho thời gian triển khai dài</h2>
<p class="text-base md:text-lg xl:text-xl max-w-2xl mx-auto leading-relaxed" style="color:var(--ink-tertiary)">Nhiều phương án sạc giúp Mentra Live hoạt động xuyên suốt quá trình thử nghiệm, làm việc hiện trường và sử dụng cả ngày.</p>
</div>
<div class="charge-row">
<div class="charge-col cursor-pointer">
<div class="charge-card-img" style="background-color:var(--surface-2)">
<img alt="Mentra Live infinity cable" class="charge-cable-img" src="<?php echo esc_url(mentra_vn_asset('micro_charge_cable_mentra_live.png')); ?>">
</div>
<div class="charge-text">
<h3 class="font-semibold text-[20px] md:text-[22px] xl:text-[26px] leading-tight mb-2" style="color:var(--ink-primary)">Cáp Infinity</h3>
<p class="text-[14px] md:text-[15px] xl:text-base leading-relaxed" style="color:var(--ink-tertiary)">Sạc kính ngay khi đang đeo. Cấp nguồn cho Mentra Live từ pin dự phòng, điện thoại hoặc bất kỳ nguồn USB-C nào để kéo dài thời gian hoạt động khi phát triển và triển khai.</p>
</div>
</div>
<div class="charge-col cursor-pointer">
<div class="charge-card-img" style="background-color:var(--surface-2)">
<img alt="Mentra Live charging case" src="<?php echo esc_url(mentra_vn_asset('charging_case.webp')); ?>">
</div>
<div class="charge-text">
<h3 class="font-semibold text-[20px] md:text-[22px] xl:text-[26px] leading-tight mb-2" style="color:var(--ink-primary)">Hộp sạc</h3>
<p class="text-[14px] md:text-[15px] xl:text-base leading-relaxed" style="color:var(--ink-tertiary)">Thêm 2.200 mAh năng lượng. Cất giữ, bảo vệ và sạc lại Mentra Live giữa các phiên sử dụng bằng hộp sạc đi kèm.</p>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="section-green"><div class="container narrow" data-mentra-reveal><p class="eyebrow">DÀNH CHO DOANH NGHIỆP</p><h2>Bạn cần pilot hoặc triển khai số lượng lớn tại Việt Nam?</h2><p>Liên hệ để trao đổi về số lượng thiết bị, quy trình triển khai, tích hợp ứng dụng và hỗ trợ kỹ thuật.</p><a class="btn btn-dark" href="<?php echo esc_url(home_url('/lien-he/?chu-de=kinh-doanh')); ?>">Liên hệ kinh doanh</a></div></section>

<section class="relative" style="padding-top:56px;padding-bottom:56px;padding-left:var(--container-px);padding-right:var(--container-px);background-color:var(--surface-0)">
<div aria-hidden="true" class="absolute top-0 left-1/2" style="transform:translateX(-50%);width:calc(100% - 2 * var(--container-px, 24px));max-width:1200px;height:1px;background-color:var(--border-subtle)"></div>
<div class="mx-auto" style="max-width:600px">
<div data-mentra-reveal>
<div class="text-center mb-7">
<h2 class="text-[22px] md:text-[26px] font-bold mb-2.5" style="color:var(--ink-primary);letter-spacing:-0.03em;line-height:1.2">Luôn cập nhật</h2>
<p class="text-[14px] md:text-[15px]" style="color:var(--ink-secondary);line-height:1.6">Nhận thông tin về tính năng mới, phụ kiện và các bản cập nhật phần mềm cho Mentra Live.</p>
</div>
<form action="#" data-mentra-newsletter="1" method="post">
<div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 mb-2.5">
<label class="sr-only" for="mailing-live-email">Địa chỉ email</label>
<input autocomplete="email" class="input-base input-pill flex-1" id="mailing-live-email" name="email" placeholder="Nhập email của bạn" required style="padding:10px 20px" type="email">
<button class="btn-base btn-primary" style="white-space:nowrap;display:inline-flex;align-items:center;gap:6px" type="submit">Đăng ký<svg aria-hidden="true" fill="none" height="14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" viewbox="0 0 24 24" width="14"><line x1="5" x2="19" y1="12" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></button>
</div>
<p class="text-sm text-center" style="color:var(--ink-secondary)">Không spam. Có thể hủy đăng ký bất cứ lúc nào.</p>
</form>
</div>
</div>
</section>

</main>
<?php
mentra_vn_render_partial('site-footer');
mentra_vn_document_close();
