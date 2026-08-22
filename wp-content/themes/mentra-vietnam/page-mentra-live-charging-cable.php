<?php
if (!defined('ABSPATH')) { exit; }

// Phase 4.6: Infinity Cable is a real Mentra product page
// (gid://shopify/Product/9286483280124, handle mentra-live-charging-cable),
// not the /mentra-live/#charging anchor Phase 4.5 mistakenly invented - see
// docs/product-classification.md and docs/phase-4.6-report.md. Rendered at
// /products/mentra-live-charging-cable/ (rewrite in functions.php). Reuses
// the same real product-detail-* structure/CSS as page-mentra-live.php,
// since that's the site's actual shared product-page template.
$product = mentra_vn_get_product_by_sku('MENTRA-INFINITY-CABLE');
$stock_label = $product ? mentra_vn_stock_label($product->get_stock_status()) : 'Liên hệ để biết tình trạng hàng';
$sku = $product ? $product->get_sku() : '';

$thumbs = [];
if ($product) {
    $ids = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());
    foreach ($ids as $attachment_id) {
        if (!$attachment_id) { continue; }
        $url = wp_get_attachment_image_url($attachment_id, 'large');
        if ($url) { $thumbs[] = ['url' => $url, 'alt' => 'Infinity Cable']; }
    }
}
if (!$thumbs) {
    $thumbs = [
        ['url' => mentra_vn_asset('infinitycable.png'), 'alt' => 'Infinity Cable'],
        ['url' => mentra_vn_asset('micro_charge_cable_mentra_live.png'), 'alt' => 'Infinity Cable đang kết nối với Mentra Live'],
    ];
}
$hero_image_url = $thumbs[0]['url'];

$spec_rows = [
    ['Tương thích với', 'Mentra Live'],
    ['Công dụng', 'Cáp sạc'],
    ['Hỗ trợ thêm', 'Đồng bộ dữ liệu và lập trình'],
];
if ($sku) { $spec_rows[] = ['Mã sản phẩm', $sku]; }

$mentra_vn_infinity_summary = function ($mobile) use ($stock_label, $spec_rows) {
    $wrap_class = $mobile ? 'product-detail-mobile-summary lg:hidden' : 'product-detail-summary hidden lg:block order-2 lg:order-2';
    ?>
    <div class="<?php echo esc_attr($wrap_class); ?>">
        <h1 class="product-detail-title mb-4">Infinity Cable cho Mentra Live</h1>
        <div class="product-detail-price mb-2"><?php echo esc_html($stock_label); ?></div>
        <p class="mb-4 max-w-lg text-[13px]" style="color:var(--ink-tertiary)">Dùng để sạc, đồng bộ dữ liệu và lập trình cho Mentra Live.</p>
        <p class="product-detail-description mb-4">Sạc Mentra Live ngay cả khi đang di chuyển với Infinity Cable. Kéo dài thời gian sử dụng kính thông minh gần như không giới hạn.</p>
        <p class="product-detail-description mb-5">Dùng Infinity Cable để đồng bộ dữ liệu hoặc lập trình cho Mentra Live.</p>
        <div class="mb-5">
            <a class="btn-base btn-lg w-full btn-primary" style="display:flex" href="<?php echo esc_url(add_query_arg(['topic' => 'sales', 'intent' => 'purchase', 'mentra_product' => 'mentra-live-charging-cable'], home_url('/lien-he/'))); ?>">Liên hệ mua hàng</a>
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

mentra_vn_document_open('mentra-live-charging-cable');
mentra_vn_render_partial('site-header');
?>
<main id="main">
<section class="product-detail-section bg-surface-0">
<div class="site-shell">
<div class="product-detail-grid">
<div class="order-1 md:order-1">

<div class="product-detail-media-card mb-5" style="background-color:var(--surface-1)">
<img alt="Infinity Cable" class="product-detail-media-image" src="<?php echo esc_url($hero_image_url); ?>">
</div>
<div class="product-detail-thumbnails">
<?php foreach ($thumbs as $i => $t) : ?>
<button type="button" aria-label="<?php echo esc_attr('Xem ' . $t['alt']); ?>" aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>" class="product-detail-thumb transition-colors duration-150 ease-out active:scale-95<?php echo $i === 0 ? ' is-active' : ''; ?>"><img alt="<?php echo esc_attr($t['alt']); ?>" class="product-detail-thumb-image transition-opacity duration-150" src="<?php echo esc_url($t['url']); ?>"></button>
<?php endforeach; ?>
</div>

<?php $mentra_vn_infinity_summary(true); ?>

</div>

<?php $mentra_vn_infinity_summary(false); ?>

</div>
</div>
</section>

<section class="relative overflow-hidden bg-surface-0" style="padding-left:var(--container-px);padding-right:var(--container-px);padding-top:var(--section-sm);padding-bottom:var(--section-sm)">
<div class="site-shell">
<div class="text-center mb-8" data-mentra-reveal>
<p class="eyebrow" style="color:var(--brand-text)">SẢN PHẨM LIÊN QUAN</p>
<h2 class="font-semibold text-[22px] md:text-[28px]" style="color:var(--ink-primary)">Có thể bạn cũng quan tâm</h2>
</div>
<div class="grid gap-4 sm:grid-cols-2 max-w-2xl mx-auto">
<a class="block rounded-2xl border transition-all duration-300" href="<?php echo esc_url(home_url('/mentra-live/')); ?>" style="border-color:var(--border-subtle);overflow:hidden">
<div style="aspect-ratio:16/10;background:var(--surface-1);display:flex;align-items:center;justify-content:center;overflow:hidden"><img alt="Mentra Live" src="<?php echo esc_url(mentra_vn_asset('product_photos/frame.png')); ?>" style="max-width:70%;max-height:70%;object-fit:contain"></div>
<div class="p-4"><h3 class="font-semibold" style="color:var(--ink-primary)">Mentra Live</h3><p class="text-sm mt-1" style="color:var(--ink-tertiary)">Xem sản phẩm</p></div>
</a>
</div>
</div>
</section>

<section class="relative" style="padding-top:56px;padding-bottom:56px;padding-left:var(--container-px);padding-right:var(--container-px);background-color:var(--surface-0)">
<div aria-hidden="true" class="absolute top-0 left-1/2" style="transform:translateX(-50%);width:calc(100% - 2 * var(--container-px, 24px));max-width:1200px;height:1px;background-color:var(--border-subtle)"></div>
<div class="mx-auto" style="max-width:600px">
<div data-mentra-reveal>
<div class="text-center mb-7">
<h2 class="text-[22px] md:text-[26px] font-bold mb-2.5" style="color:var(--ink-primary);letter-spacing:-0.03em;line-height:1.2">Luôn cập nhật</h2>
<p class="text-[14px] md:text-[15px]" style="color:var(--ink-secondary);line-height:1.6">Nhận thông tin về phụ kiện, phần mềm và tình trạng triển khai của Mentra Live.</p>
</div>
<form action="#" data-mentra-newsletter="1" method="post">
<div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 mb-2.5">
<label class="sr-only" for="mailing-infinity-email">Địa chỉ email</label>
<input autocomplete="email" class="input-base input-pill flex-1" id="mailing-infinity-email" name="email" placeholder="Nhập email của bạn" required style="padding:10px 20px" type="email">
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
