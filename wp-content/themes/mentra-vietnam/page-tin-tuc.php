<?php
if (!defined('ABSPATH')) { exit; }

require_once get_template_directory() . '/data/press.php';

function mentra_vn_vi_date($timestamp) {
    return date('j', $timestamp) . ' tháng ' . date('n', $timestamp) . ', ' . date('Y', $timestamp);
}

$articles_query = new WP_Query([
    'post_type' => 'post',
    'category_name' => 'bai-viet',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);
$press_items = mentra_vn_press_items();
$article_count = (int) $articles_query->post_count;
$press_count = count($press_items);
$all_count = $article_count + $press_count;

// Combined, chronologically sorted list backing the "Tất cả" tab. Articles
// and press items are normalized into the same shape so they render with
// the same card visual system, matching the source's chronological
// ordering (not "16 articles then 6 press").
$combined = [];
foreach ($articles_query->posts as $post) {
    $combined[] = ['kind' => 'blogs', 'timestamp' => strtotime($post->post_date), 'post' => $post];
}
foreach ($press_items as $press) {
    $combined[] = ['kind' => 'news', 'timestamp' => strtotime($press['date']), 'press' => $press];
}
usort($combined, function ($a, $b) { return $b['timestamp'] <=> $a['timestamp']; });

mentra_vn_document_open('blogs');
mentra_vn_render_partial('site-header');
?>
<main class="content-shell-rounded font-sans overflow-x-clip" id="main-content" style="position:relative;z-index:1;min-height:calc(100dvh + 1px);background-color:var(--surface-0)">
<section class="relative overflow-hidden" style="background:var(--surface-0);padding-left:var(--container-px);padding-right:var(--container-px);padding-top:var(--page-start-pt-tight);padding-bottom:var(--section-xs)"><div class="site-shell relative" style="max-width:1440px;z-index:2"><div class="border-b border-[var(--border-subtle)] pb-8"><div class="max-w-[780px]"><span class="mb-3 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color:var(--brand-text)"><span class="inline-block h-1.5 w-1.5 rounded-full" style="background:var(--brand)"></span>Tin tức</span><h1 class="max-w-[12ch] text-[30px] font-semibold leading-[1.08] tracking-normal text-ink-primary sm:max-w-[14ch] sm:text-[38px] md:max-w-[780px] md:text-[56px] md:tracking-[-0.02em]">Tin tức và bài viết về kính thông minh.</h1></div></div></div></section>
<section class="sticky top-[38px] xl:top-[44px] 2xl:top-[50px] z-30 py-3 md:py-5" style="background:color-mix(in srgb, var(--surface-0) 88%, transparent);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--border-subtle);padding-left:var(--container-px);padding-right:var(--container-px)"><div class="mx-auto w-full px-3 md:px-0 md:w-[81%] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 md:gap-4" style="max-width:1440px">
<div aria-label="Bộ lọc nội dung" class="flex items-center gap-1 md:gap-0 overflow-x-auto flex-shrink-0 -mb-px" role="tablist">
<button aria-selected="false" class="relative px-3 md:px-5 py-2 md:py-3 text-[13px] md:text-[15px] whitespace-nowrap flex items-center gap-1.5" data-newsroom-filter="all" id="blog-filter-tab-all" role="tab" style="color:var(--ink-tertiary);font-weight:400;background:transparent;cursor:pointer;border:none;transition:color 180ms ease" tabindex="-1">Tất cả<span class="text-[12px] font-semibold leading-none" style="color:var(--ink-tertiary)"><?php echo (int) $all_count; ?></span><span class="absolute bottom-0 left-3 right-3 md:left-5 md:right-5 rounded-full" style="height:2px;background:transparent"></span></button>
<button aria-selected="false" class="relative px-3 md:px-5 py-2 md:py-3 text-[13px] md:text-[15px] whitespace-nowrap flex items-center gap-1.5" data-newsroom-filter="news" id="blog-filter-tab-news" role="tab" style="color:var(--ink-tertiary);font-weight:400;background:transparent;cursor:pointer;border:none;transition:color 180ms ease" tabindex="-1">Báo chí<span class="text-[12px] font-semibold leading-none" style="color:var(--ink-tertiary)"><?php echo (int) $press_count; ?></span><span class="absolute bottom-0 left-3 right-3 md:left-5 md:right-5 rounded-full" style="height:2px;background:transparent"></span></button>
<button aria-selected="true" class="relative px-3 md:px-5 py-2 md:py-3 text-[13px] md:text-[15px] whitespace-nowrap flex items-center gap-1.5" data-newsroom-filter="blogs" id="blog-filter-tab-blogs" role="tab" style="color:var(--ink-primary);font-weight:600;background:transparent;cursor:pointer;border:none;transition:color 180ms ease" tabindex="0">Bài viết<span class="text-[12px] font-semibold leading-none" style="color:var(--brand)"><?php echo (int) $article_count; ?></span><span class="absolute bottom-0 left-3 right-3 md:left-5 md:right-5 rounded-full" style="height:2px;background:var(--brand)"></span></button>
</div>
<div class="relative w-full sm:w-auto" style="min-width:0;max-width:340px"><svg aria-hidden="true" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" fill="none" stroke="var(--ink-tertiary)" stroke-width="2" viewbox="0 0 24 24"><path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" stroke-linecap="round" stroke-linejoin="round"></path></svg><input aria-label="Tìm kiếm bài viết" class="w-full pl-10 pr-10 py-2.5 rounded-full text-sm font-medium" data-newsroom-search="1" placeholder="Tìm bài viết..." style="background:var(--surface-1);border:1px solid var(--border-subtle);color:var(--ink-primary);min-height:44px" type="search"/><span class="absolute right-3 top-1/2 -translate-y-1/2 hidden sm:inline-flex items-center gap-0.5 text-[12px] font-medium px-1.5 py-0.5 rounded" style="background:var(--border-subtle);color:var(--ink-tertiary);pointer-events:none"><span style="font-size:13px">⌘</span>K</span></div>
</div></section>
<section class="section-sm" style="background:var(--surface-1);min-height:60vh;padding-left:var(--container-px);padding-right:var(--container-px)"><div class="mx-auto w-full px-3 md:px-0 md:w-[81%]" style="max-width:1440px">
<div class="flex items-center gap-3 mb-5"><div class="w-1 h-6 rounded-full" style="background:var(--brand)"></div><h2 class="text-lg md:text-xl font-bold" style="color:var(--ink-primary)">Bài viết</h2><span class="text-[11px] font-bold px-2.5 py-1 rounded-full" style="background:rgba(0, 184, 105, 0.1);color:var(--brand-text)"><?php echo (int) $article_count; ?></span></div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5" data-newsroom-grid="1" id="mentra-newsroom-grid">
<?php foreach ($combined as $item) :
    if ($item['kind'] === 'blogs') :
        $post = $item['post'];
        $title = get_the_title($post);
        $excerpt = get_the_excerpt($post);
        $permalink = get_permalink($post);
        $thumb = get_the_post_thumbnail_url($post, 'medium_large');
        $date_label = mentra_vn_vi_date($item['timestamp']);
        $search_text = esc_attr(strtolower('tin tuc ' . $date_label . ' ' . wp_strip_all_tags($title) . ' ' . wp_strip_all_tags($excerpt)));
        ?>
<div class="h-full" data-newsroom-item="1" data-newsroom-kind="blogs" data-newsroom-search="<?php echo $search_text; ?>">
<a class="group block h-full" href="<?php echo esc_url($permalink); ?>" style="text-decoration:none">
<div class="group h-full flex flex-col rounded-xl md:rounded-2xl overflow-hidden cursor-pointer" style="background:var(--surface-0);border:1px solid var(--border-subtle);box-shadow:var(--shadow-sm)">
<div class="relative" style="aspect-ratio:16 / 9;overflow:hidden"><?php if ($thumb) : ?><img alt="<?php echo esc_attr($title); ?>" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105" loading="lazy" src="<?php echo esc_url($thumb); ?>"/><?php endif; ?></div>
<div class="p-3 md:p-5 flex flex-col flex-grow">
<div class="mb-1.5 md:mb-2 flex items-center gap-1.5 md:gap-2 flex-wrap"><span class="inline-block text-[11px] md:text-[12px] font-bold uppercase tracking-wider px-2 md:px-2.5 py-0.5 rounded-full" style="background:rgba(127, 184, 212, 0.12);color:#4A7C7E;letter-spacing:0.08em">Tin tức</span><span class="text-[12px] md:text-[13px] font-medium" style="color:var(--ink-tertiary)"><?php echo esc_html($date_label); ?></span></div>
<h3 class="text-[14px] md:text-[17px] font-semibold mb-1 md:mb-1.5 leading-snug group-hover:text-[#00864e] transition-colors duration-200" style="color:var(--ink-primary)"><span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?php echo esc_html($title); ?></span></h3>
<p class="text-[13px] md:text-sm leading-relaxed mb-1 md:mb-1.5 hidden md:block" style="color:var(--ink-secondary);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?php echo esc_html(wp_strip_all_tags($excerpt)); ?></p>
<div class="mt-auto pt-1.5 md:pt-2 flex items-center justify-between"><span class="text-[13px] md:text-[14px] font-semibold flex items-center gap-1" style="color:var(--brand-text)">Đọc thêm<svg aria-hidden="true" class="w-3 h-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24"><path d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" stroke-linecap="round" stroke-linejoin="round"></path></svg></span></div>
</div></div></a></div>
    <?php else :
        $press = $item['press'];
        $date_label = mentra_vn_vi_date($item['timestamp']);
        $search_text = esc_attr(strtolower(wp_strip_all_tags($press['headline']) . ' ' . $press['publisher'] . ' ' . $date_label));
        ?>
<div class="h-full" data-newsroom-item="1" data-newsroom-kind="news" data-newsroom-search="<?php echo $search_text; ?>" hidden>
<a class="group block h-full" href="<?php echo esc_url($press['url']); ?>" rel="noopener noreferrer" style="text-decoration:none" target="_blank">
<div class="group h-full flex flex-col rounded-xl md:rounded-2xl overflow-hidden cursor-pointer newsroom-press-card" style="background:var(--surface-0);border:1px solid var(--border-subtle);box-shadow:var(--shadow-sm)">
<div class="relative newsroom-press-media" style="aspect-ratio:16 / 9;overflow:hidden"><img alt="<?php echo esc_attr($press['publisher']); ?>" class="newsroom-press-logo" loading="lazy" src="<?php echo esc_url(mentra_vn_asset($press['image'])); ?>"/></div>
<div class="p-3 md:p-5 flex flex-col flex-grow">
<div class="mb-1.5 md:mb-2 flex items-center gap-1.5 md:gap-2 flex-wrap"><span class="inline-block text-[11px] md:text-[12px] font-bold uppercase tracking-wider px-2 md:px-2.5 py-0.5 rounded-full newsroom-press-badge">Báo chí</span><span class="text-[12px] md:text-[13px] font-medium" style="color:var(--ink-tertiary)"><?php echo esc_html($date_label); ?></span></div>
<h3 class="text-[14px] md:text-[17px] font-semibold mb-1 md:mb-1.5 leading-snug group-hover:text-[#00864e] transition-colors duration-200" style="color:var(--ink-primary)"><span style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden"><?php echo esc_html($press['headline']); ?></span></h3>
<div class="mt-auto pt-1.5 md:pt-2 flex items-center justify-between"><span class="text-[13px] md:text-[14px] font-semibold flex items-center gap-1" style="color:var(--brand-text)">Đọc bài viết<svg aria-hidden="true" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24"><path d="M7 17 17 7M9 7h8v8" stroke-linecap="round" stroke-linejoin="round"></path></svg></span></div>
</div></div></a></div>
    <?php endif;
endforeach; ?>
</div>
<p class="newsroom-empty-state" hidden id="mentra-newsroom-empty">Không tìm thấy bài viết phù hợp.</p>
</div></section>
<?php mentra_vn_render_partial('newsletter-cta'); ?>
</main>
<?php
mentra_vn_render_partial('site-footer');
mentra_vn_document_close();
