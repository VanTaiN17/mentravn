<?php
if (!defined('ABSPATH')) { exit; }
$key = mentra_vn_get_source_key();
if ($key && mentra_vn_source_exists($key)) {
    status_header(200);
    mentra_vn_document_open($key);
    mentra_vn_render_source($key);
    mentra_vn_document_close();
    return;
}
get_header(); ?>
<main id="main-content" style="min-height:70vh;padding:160px var(--container-px) 100px;max-width:900px;margin:auto">
  <p class="type-label" style="color:var(--brand-text)">404</p>
  <h1 class="type-page-title" style="font-size:clamp(44px,7vw,84px);margin:18px 0">Không tìm thấy trang</h1>
  <p style="font-size:18px;color:var(--ink-tertiary)">Trang bạn đang tìm kiếm không tồn tại hoặc đã được di chuyển.</p>
  <p style="margin-top:28px"><a class="btn-base btn-primary btn-lg" href="<?php echo esc_url(home_url('/')); ?>">Về trang chủ</a></p>
</main>
<?php get_footer();
