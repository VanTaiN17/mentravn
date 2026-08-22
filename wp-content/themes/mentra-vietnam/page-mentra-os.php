<?php
if (!defined('ABSPATH')) { exit; }

// Phase 4.5 rebuild: the previous version of this template used an invented
// classname vocabulary (product-page-hero/cards-3/feature-card/section-black)
// that only ever existed in an orphaned, never-enqueued main.css, so it
// rendered essentially unstyled and omitted 4 of the page's 8 real sections.
// This version reproduces the real WGET section order/backgrounds (see
// docs/visual-fidelity-audit.md): light hero -> compatibility grid -> soft
// miniapp store -> light overview list -> light apps grid -> gradient SDK
// panel -> the one genuinely dark section (download promo) -> newsletter.
mentra_vn_document_open('mentra-os');
mentra_vn_render_partial('site-header');
?>
<main id="main">

<section class="os-hero-section relative overflow-hidden bg-surface-0" style="padding-left:var(--container-px);padding-right:var(--container-px);padding-top:calc(var(--height-nav) + clamp(48px,7vh,72px));padding-bottom:0;min-height:100svh">
<div class="site-shell relative z-10 flex flex-col justify-between gap-8" style="min-height:calc(100svh - var(--height-nav) - clamp(48px,7vh,72px))">
<div class="os-hero-layout grid flex-1 items-center gap-8 border-b border-[var(--border-subtle)] lg:gap-14" style="grid-template-columns:repeat(auto-fit,minmax(min(100%, 420px),1fr))">
<div class="pb-0 pt-8 text-center md:py-14 md:text-left lg:py-0">
<div data-mentra-reveal><h1 class="text-[clamp(2.25rem,8vw,3.75rem)] font-semibold leading-[1.03] tracking-normal md:text-[48px] xl:text-[60px]" style="color:var(--ink-primary)">Nền tảng mở<br>cho <span style="color:var(--brand-text)">kính thông minh.</span></h1></div>
<div data-mentra-reveal><p class="mx-auto mt-6 max-w-[620px] text-base leading-[1.65] md:mx-0 md:text-xl" style="color:var(--ink-primary)">MentraOS mang hệ sinh thái ứng dụng, SDK dành cho nhà phát triển và trải nghiệm liền mạch đến kính thông minh của bạn — 100% mã nguồn mở.</p></div>
<div class="mt-8"><div class="os-badge-grid justify-center md:justify-start">
<a aria-label="Tải trên App Store (mở tab mới)" class="transition-opacity duration-150 hover:opacity-90" href="https://apps.apple.com/us/app/mentra-the-smart-glasses-app/id6747363193" rel="noopener noreferrer" target="_blank"><img alt="Tải trên App Store" class="h-10 md:h-11" src="<?php echo esc_url(mentra_vn_asset('badges/apple_badge.svg')); ?>"></a>
<a aria-label="Tải trên Google Play (mở tab mới)" class="transition-opacity duration-150 hover:opacity-90" href="https://play.google.com/store/apps/details?id=com.mentra.mentra" rel="noopener noreferrer" target="_blank"><img alt="Tải trên Google Play" class="h-10 md:h-11" src="<?php echo esc_url(mentra_vn_asset('badges/google_play_badge.png')); ?>"></a>
<a aria-label="Xem MentraOS trên GitHub (mở tab mới)" class="transition-opacity duration-150 hover:opacity-90" href="https://github.com/Mentra-Community/MentraOS/releases" rel="noopener noreferrer" target="_blank"><img alt="Xem trên GitHub" class="h-10 md:h-11" src="<?php echo esc_url(mentra_vn_asset('badges/github_badge.png')); ?>"></a>
</div></div>
</div>
<div class="w-full self-end">
<div class="relative mx-auto flex w-full max-w-[760px] items-end justify-center self-end lg:justify-end">
<div class="os-hero-phone-hand h-auto"><img alt="MentraOS Miniapp Store" class="block h-auto w-full" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('Mockup_OS_Phone_Hand.png')); ?>"></div>
</div>
</div>
</div>
</div>
</section>

<section class="os-compatibility-section bg-surface-0 section-md" style="padding-left:var(--container-px);padding-right:var(--container-px)">
<div class="mx-auto max-w-[1280px]">
<div data-mentra-reveal><div class="mb-7 md:mb-12"><p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] md:text-sm" style="color:var(--brand-text)">Khả năng tương thích</p><h2 class="text-3xl font-semibold tracking-normal md:text-5xl" style="color:var(--ink-primary)">Hoạt động với kính của bạn.</h2></div></div>
<div class="os-compatible-grid mx-auto grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-5">
<?php
$compatible = [
    ['mentra_live_sexy_1.png', 'Mentra Live', 'Được hỗ trợ', home_url('/mentra-live/')],
    ['G2_assets/assets__flutter_assets__assets__img__device_new__image_g2_b_grey_side_view_bright.png', 'Even Realities G2', 'Được hỗ trợ', home_url('/even-realities/')],
    ['VuzixZ100.png', 'Vuzix Z100', 'Được hỗ trợ', ''],
    ['even_g1_sexy_1.png', 'Even Realities G1', 'Được hỗ trợ', home_url('/even-realities/')],
    ['NIMO-glasses.png', 'NIMO', 'Sắp hỗ trợ', home_url('/nimo/')],
];
foreach ($compatible as $i => [$img, $name, $tag, $href]) :
    $is_nimo = $i === count($compatible) - 1;
    $tag_style = $is_nimo
        ? 'background-color:rgba(255,247,237,.96);border-color:rgba(194,65,12,.16);color:#B45309'
        : 'background-color:rgba(235,250,242,.96);border-color:rgba(0,145,90,.12);color:var(--brand-text)';
    $outer_class = 'h-full' . ($is_nimo ? ' col-span-2 mx-auto w-[calc(50%-6px)] lg:col-span-1 lg:w-auto' : '');
?>
<div class="<?php echo esc_attr($outer_class); ?>"><div class="h-full" data-mentra-reveal>
<?php if ($href) : ?><a class="block h-full no-underline" href="<?php echo esc_url($href); ?>"><?php endif; ?>
<div class="os-compatible-card relative flex h-full flex-col overflow-hidden rounded-xl border bg-surface-0 transition-all duration-300<?php echo $href ? ' cursor-pointer' : ''; ?>" style="border-color:var(--border-subtle);box-shadow:var(--shadow-xs)">
<div class="os-compatible-card__media relative flex aspect-[1.22/1] shrink-0 items-center justify-center overflow-hidden bg-surface-1 px-5 py-8">
<span class="os-compatible-card__tag absolute left-4 top-4 z-10 rounded-full border px-3 py-1 text-xs font-semibold shadow-sm" style="<?php echo esc_attr($tag_style); ?>"><?php echo esc_html($tag); ?></span>
<img alt="<?php echo esc_attr($name); ?>" class="transition-transform duration-500" loading="lazy" src="<?php echo esc_url(mentra_vn_asset($img)); ?>" style="width:100%;height:100%;object-fit:contain;display:block">
</div>
<div class="os-compatible-card__body flex min-h-[72px] flex-col justify-center border-t p-5" style="border-color:var(--border-subtle)"><h3 class="os-compatible-card__title text-base font-semibold leading-tight" style="color:var(--ink-primary)"><?php echo esc_html($name); ?></h3></div>
</div>
<?php if ($href) : ?></a><?php endif; ?>
</div></div>
<?php endforeach; ?>
</div>
</div>
</section>

<section class="os-miniapp-store-section relative overflow-hidden bg-surface-1" style="padding-left:var(--container-px);padding-right:var(--container-px);padding-top:clamp(24px,3vw,48px);padding-bottom:0">
<div class="site-shell os-miniapp-store-shell grid gap-10 lg:min-h-[640px] lg:grid-cols-[1fr_0.9fr] lg:items-end lg:gap-20">
<div class="self-center" data-mentra-reveal><div class="py-12 lg:py-0">
<h2 class="max-w-xl text-3xl font-semibold leading-[1.06] tracking-normal md:text-5xl" style="color:var(--ink-primary)">Mentra mang những ứng dụng tốt nhất đến kính thông minh của bạn</h2>
<p class="mt-5 max-w-xl text-base leading-[1.7] md:text-lg" style="color:var(--ink-secondary)">Dùng Mentra Miniapp Store để duyệt, cài đặt và chạy ứng dụng ngay từ điện thoại. Trải nghiệm phụ đề, ghi chú AI, AI chủ động, theo dõi vận động và nhiều tiện ích khác.</p>
<div aria-label="Mentra app logos" class="os-miniapp-logo-grid mt-7 grid max-w-md grid-cols-2 gap-4 sm:grid-cols-4">
<div class="flex flex-col items-center gap-2 text-center"><img alt="Camera app" class="h-11 w-11 rounded-xl object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('app_icons/Camera.svg')); ?>"><span class="text-xs font-semibold leading-tight" style="color:var(--ink-secondary)">Camera</span></div>
<div class="flex flex-col items-center gap-2 text-center"><img alt="Captions app" class="h-11 w-11 rounded-xl object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('app_icons/Captions.svg')); ?>"><span class="text-xs font-semibold leading-tight" style="color:var(--ink-secondary)">Phụ đề</span></div>
<div class="flex flex-col items-center gap-2 text-center"><img alt="Ghi chú app" class="h-11 w-11 rounded-xl object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('app_icons/Mentra_Notes.png')); ?>"><span class="text-xs font-semibold leading-tight" style="color:var(--ink-secondary)">Ghi chú</span></div>
<div class="flex flex-col items-center gap-2 text-center"><img alt="Livestream app" class="h-11 w-11 rounded-xl object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('app_icons/Streamer.svg')); ?>"><span class="text-xs font-semibold leading-tight" style="color:var(--ink-secondary)">Livestream</span></div>
</div>
<div class="mt-8"><a class="btn-base btn-primary whitespace-nowrap" href="<?php echo esc_url(home_url('/ung-dung/')); ?>">Khám phá Miniapp<svg aria-hidden="true" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a></div>
</div></div>
<div class="self-end"><div class="os-miniapp-visual flex min-h-[420px] items-end justify-end lg:min-h-[600px]" style="margin-right:calc(-1 * max(var(--container-px), (100vw - 1440px) / 2))"><div class="os-miniapp-phone flex w-full max-w-[440px] items-end justify-center lg:max-w-[760px]"><img alt="MentraOS Miniapp Store trên iPhone" class="block h-auto w-full object-contain object-bottom" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('Mockup_OS_Phone_Image2.png')); ?>"></div></div></div>
</div>
</section>

<section class="relative overflow-hidden bg-surface-0" style="padding-left:var(--container-px);padding-right:var(--container-px);padding-top:var(--section-md);padding-bottom:var(--section-md)">
<div class="site-shell">
<div data-mentra-reveal><div class="mb-10 max-w-3xl md:mb-12"><h2 class="text-3xl font-semibold tracking-normal md:text-5xl" style="color:var(--ink-primary)">Tổng quan MentraOS</h2></div>
<div class="divide-y border-y" style="border-color:var(--border-subtle)">
<?php
$overview = [
    ['01', 'MentraOS là gì?', 'MentraOS là hệ điều hành dành cho kính thông minh. Đây là nền tảng mã nguồn mở, được thiết kế để mang lại trải nghiệm tốt nhất cho người dùng, nhà phát triển và nhà sản xuất. Người dùng có ứng dụng và toàn quyền lựa chọn AI mình muốn dùng. Nhà phát triển có SDK mở trên nhiều dòng kính khác nhau. Nhà sản xuất có giải pháp hệ điều hành trọn gói, có thể tích hợp mà vẫn giữ thương hiệu và mối quan hệ khách hàng riêng.', null],
    ['02', 'Vì sao cần MentraOS?', 'Kính thông minh chỉ có thể trở nên phổ biến khi trước hết chúng vẫn giống một chiếc kính: nhẹ, đẹp và đủ pin cho cả ngày. Các hệ điều hành truyền thống không được thiết kế cho những giới hạn đó. MentraOS chạy ứng dụng trên điện thoại, còn kính xử lý đầu vào và đầu ra cơ bản, nhờ vậy vẫn mang lại trải nghiệm phần mềm mạnh mẽ trên phần cứng có thể đeo hằng ngày.', null],
    ['03', 'MentraOS hoạt động như thế nào?', 'Để tiết kiệm pin cho kính, các ứng dụng MentraOS chạy trên điện thoại của người dùng. Mỗi miniapp chạy trong một ứng dụng chủ, nhờ đó nhiều ứng dụng có thể hoạt động cùng lúc, như thông báo, dịch thuật và ghi chú. Nhà phát triển được cấp quyền truy cập SDK vào phần cứng kính, bao gồm điều khiển màn hình, truy cập micro và các lệnh camera. Nhà sản xuất có thể tích hợp Mentra Core Engine vào ứng dụng iPhone hoặc Android của riêng mình.', null],
    ['04', 'Làm thế nào để bắt đầu dùng MentraOS?', 'Nếu bạn đang dùng kính được hỗ trợ, có thể MentraOS đã có sẵn. Khi ứng dụng mặc định hiển thị "Powered by Mentra", MentraOS đang chạy bên trong. Nếu chưa, hãy kiểm tra tương thích, tải ứng dụng Mentra và kết nối kính để bắt đầu.', ['Tải ứng dụng Mentra', home_url('/tai-ung-dung/')]],
    ['05', 'Vì sao chúng tôi xây dựng MentraOS?', 'Chúng tôi tin kính thông minh sẽ trở thành nền tảng máy tính cá nhân tiếp theo được sử dụng rộng rãi. MentraOS mang đến hệ sinh thái ứng dụng mở, SDK cho nhà phát triển và lớp phần mềm có thể hoạt động trên những mẫu kính mà người dùng thực sự muốn đeo.', null],
];
foreach ($overview as [$num, $q, $a, $link]) :
?>
<div class="grid gap-5 py-8 md:grid-cols-[56px_minmax(180px,240px)_minmax(0,1fr)] md:gap-6 md:py-9 lg:grid-cols-[56px_minmax(200px,260px)_minmax(0,1fr)] lg:gap-8" style="border-color:var(--border-subtle)">
<div class="flex items-start gap-4 md:block"><div class="inline-flex h-10 w-10 items-center justify-center rounded-full border text-sm font-semibold" style="border-color:rgba(0,120,68,.18);background:rgba(0,120,68,.04);color:var(--brand-text)"><?php echo esc_html($num); ?></div></div>
<h3 class="type-card-title md:pt-2"><?php echo esc_html($q); ?></h3>
<div><p class="max-w-[960px] text-[17px] leading-[1.72] md:text-[18px]" style="color:var(--ink-secondary)"><?php echo esc_html($a); ?></p>
<?php if ($link) : ?><a class="mt-5 inline-flex items-center gap-2 text-sm font-semibold" href="<?php echo esc_url($link[1]); ?>" rel="noopener noreferrer" style="color:var(--brand-text)" target="_blank"><?php echo esc_html($link[0]); ?><svg aria-hidden="true" fill="none" height="15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="15"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>

<section class="relative overflow-hidden bg-surface-0" style="padding-left:var(--container-px);padding-right:var(--container-px);padding-top:var(--section-md);padding-bottom:var(--section-md)">
<div class="site-shell">
<div class="grid gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
<div data-mentra-reveal><div class="max-w-[620px]">
<h2 class="text-3xl font-semibold leading-[1.06] tracking-normal md:text-5xl" style="color:var(--ink-primary)">Ứng dụng được tạo cho kính của bạn.</h2>
<p class="mt-5 text-base leading-[1.7] md:text-lg" style="color:var(--ink-secondary)">Từ phụ đề trực tiếp, điều khiển camera, ghi chú đến dịch thuật — khám phá các ứng dụng được thiết kế để sử dụng ngay trước mắt bạn.</p>
<div class="mt-8"><a class="btn-base btn-primary whitespace-nowrap" href="<?php echo esc_url(home_url('/ung-dung/')); ?>">Khám phá Miniapp<svg aria-hidden="true" fill="none" height="16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a></div>
</div></div>
<div class="grid gap-3 sm:grid-cols-2">
<?php
$apps = [
    ['app_icons/Captions.svg', 'Captions app', 'Phụ đề'],
    ['app_icons/Camera.svg', 'Camera app', 'Camera'],
    ['app_icons/Mentra_Notes.png', 'Ghi chú app', 'Ghi chú'],
    ['app_icons/Translation.svg', 'Dịch thuật app', 'Dịch thuật'],
];
foreach ($apps as [$img, $alt, $label]) :
?>
<div class="h-full rounded-2xl border border-border-subtle bg-surface-1 p-5 md:p-6"><div class="flex items-center gap-4"><img alt="<?php echo esc_attr($alt); ?>" class="h-12 w-12 shrink-0 rounded-xl object-contain" loading="lazy" src="<?php echo esc_url(mentra_vn_asset($img)); ?>"><h3 class="text-lg font-semibold leading-tight" style="color:var(--ink-primary)"><?php echo esc_html($label); ?></h3></div></div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>

<section class="relative overflow-hidden" style="background:linear-gradient(180deg,var(--surface-0) 0%,var(--surface-1) 40%,var(--surface-1) 60%,var(--surface-0) 100%);padding-top:var(--section-md);padding-bottom:var(--section-md);padding-left:var(--container-px);padding-right:var(--container-px)">
<div class="mx-auto max-w-4xl overflow-hidden">
<div class="grid md:grid-cols-2 gap-10 md:gap-14 items-center">
<div class="min-w-0">
<p class="text-xs md:text-sm font-semibold tracking-[0.2em] uppercase mb-3" style="color:var(--brand-text)">SDK dành cho nhà phát triển</p>
<div data-mentra-reveal><h2 class="mb-5 text-3xl font-semibold leading-[1.08] tracking-normal md:text-4xl lg:text-[44px]" style="color:var(--ink-primary)">Xây dựng ứng dụng kính thông minh đầu tiên<br><span style="color:var(--brand-text)">chỉ trong vài phút.</span></h2></div>
<div data-mentra-reveal><p class="text-[14px] md:text-lg leading-[1.7] mb-6 md:mb-7" style="color:var(--ink-primary)">Phát hành một ứng dụng có thể hoạt động trên mọi mẫu kính được hỗ trợ. Mentra SDK cho phép điều khiển màn hình, truy cập micro, gửi lệnh camera và nhiều hơn nữa từ một API.</p></div>
<a aria-label="Đọc tài liệu (mở tab mới)" class="btn-base btn-primary" href="https://docs.mentraglass.com" rel="noopener noreferrer" target="_blank"><svg aria-hidden="true" fill="none" height="16" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="16"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>Đọc tài liệu</a>
</div>
<div data-mentra-reveal><div class="min-w-0 overflow-hidden"><div class="rounded-xl overflow-hidden" style="background-color:#0D1117;box-shadow:0 12px 40px rgba(0,0,0,.18),0 4px 12px rgba(0,0,0,.1)">
<div class="flex items-center gap-2 px-3 md:px-4 py-2.5" style="background-color:#161B22;border-bottom:1px solid #21262D">
<div class="flex gap-1.5"><div class="w-2.5 h-2.5 rounded-full" style="background-color:#FF5F57"></div><div class="w-2.5 h-2.5 rounded-full" style="background-color:#FEBC2E"></div><div class="w-2.5 h-2.5 rounded-full" style="background-color:#28C840"></div></div>
<span class="text-[10px] md:text-xs ml-2 font-mono" style="color:#8B949E">my-glasses-app.ts</span>
</div>
<div class="p-3 md:p-5 overflow-x-auto"><pre class="text-[10px] md:text-[12px] leading-[1.7] font-mono whitespace-pre-wrap break-words" style="color:#C9D1D9"><code><span style="color:#FF7B72">import</span> { <span style="color:#79C0FF">AppServer</span> } <span style="color:#FF7B72">from</span> <span style="color:#A5D6FF">'@mentra/sdk'</span>

<span style="color:#FF7B72">class</span> <span style="color:#FFA657">MyApp</span> <span style="color:#FF7B72">extends</span> <span style="color:#79C0FF">AppServer</span> {
  <span style="color:#FF7B72">protected async</span> <span style="color:#D2A8FF">onSession</span>(<span style="color:#FFA657">session</span>, <span style="color:#FFA657">sessionId</span>, <span style="color:#FFA657">userId</span>) {
    <span style="color:#8B949E">// Display "Hello World" on the glasses</span>
    <span style="color:#FFA657">session</span>.<span style="color:#79C0FF">layouts</span>.<span style="color:#D2A8FF">showTextWall</span>(<span style="color:#A5D6FF">"Hello World from MentraOS!"</span>);

    <span style="color:#8B949E">// Log when user speaks</span>
    <span style="color:#FFA657">session</span>.<span style="color:#79C0FF">events</span>.<span style="color:#D2A8FF">onTranscription</span>((<span style="color:#FFA657">data</span>) <span style="color:#FF7B72">=&gt;</span> {
      <span style="color:#79C0FF">console</span>.<span style="color:#D2A8FF">log</span>(<span style="color:#A5D6FF">`User said: ${</span><span style="color:#FFA657">data</span>.<span style="color:#79C0FF">text</span><span style="color:#A5D6FF">}`</span>);
    });
  }
}

<span style="color:#FF7B72">const</span> <span style="color:#79C0FF">app</span> = <span style="color:#FF7B72">new</span> <span style="color:#FFA657">MyApp</span>({
  <span style="color:#79C0FF">packageName</span>: <span style="color:#A5D6FF">'com.example.myapp'</span>,
  <span style="color:#79C0FF">apiKey</span>: <span style="color:#FFA657">process</span>.<span style="color:#79C0FF">env</span>.<span style="color:#79C0FF">MENTRA_API_KEY</span>,
  <span style="color:#79C0FF">port</span>: <span style="color:#79C0FF">3000</span>
});

<span style="color:#79C0FF">app</span>.<span style="color:#D2A8FF">start</span>();</code></pre></div>
</div></div></div>
</div>
</div>
</section>

<section class="green-grid-promo-section os-download-promo-section">
<div data-mentra-reveal>
<div class="site-shell">
<div class="green-grid-promo os-download-promo">
<div aria-hidden="true" class="green-grid-promo__grid"></div>
<div>
<h2 class="os-download-promo__title text-3xl font-semibold tracking-normal md:text-4xl" style="color:#f8fff9">Tải MentraOS ngay hôm nay</h2>
<p class="os-download-promo__body mt-4 max-w-md text-base leading-[1.7] md:text-lg" style="color:rgba(248,255,249,.84)">Tải ứng dụng, kết nối kính và khám phá Mentra Miniapp Store.</p>
</div>
<div class="os-download-promo__actions">
<div class="os-badge-grid justify-center md:justify-end">
<a aria-label="Tải trên App Store (mở tab mới)" class="inline-flex items-center transition-transform duration-200 hover:scale-105 active:scale-95" href="https://apps.apple.com/us/app/mentra-the-smart-glasses-app/id6747363193" rel="noopener noreferrer" target="_blank"><img alt="Tải trên App Store" class="h-10 md:h-11" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('badges/apple_badge.svg')); ?>"></a>
<a aria-label="Tải trên Google Play (mở tab mới)" class="inline-flex items-center transition-transform duration-200 hover:scale-105 active:scale-95" href="https://play.google.com/store/apps/details?id=com.mentra.mentra" rel="noopener noreferrer" target="_blank"><img alt="Tải trên Google Play" class="h-10 md:h-11" loading="lazy" src="<?php echo esc_url(mentra_vn_asset('badges/google_play_badge.png')); ?>"></a>
</div>
<a aria-label="Xem MentraOS trên GitHub (mở tab mới)" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold transition-colors duration-150 hover:text-white" href="https://github.com/Mentra-Community/MentraOS/releases" rel="noopener noreferrer" style="color:rgba(248,255,249,.78)" target="_blank">Nhà phát triển: xem MentraOS trên GitHub<svg aria-hidden="true" fill="none" height="14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="14"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg></a>
</div>
</div>
</div>
</div>
</section>

<section class="relative" style="padding-top:56px;padding-bottom:56px;padding-left:var(--container-px);padding-right:var(--container-px);background-color:var(--surface-0)">
<div aria-hidden="true" class="absolute top-0 left-1/2" style="transform:translateX(-50%);width:calc(100% - 2 * var(--container-px, 24px));max-width:1200px;height:1px;background-color:var(--border-subtle)"></div>
<div class="mx-auto" style="max-width:600px">
<div data-mentra-reveal>
<div class="text-center mb-7">
<h2 class="text-[22px] md:text-[26px] font-bold mb-2.5" style="color:var(--ink-primary);letter-spacing:-0.03em;line-height:1.2">Sẵn sàng phát triển cùng Mentra?</h2>
<p class="text-[14px] md:text-[15px]" style="color:var(--ink-secondary);line-height:1.6">Mã nguồn mở thay đổi rất nhanh. Hãy cập nhật những thông tin mới nhất từ nền tảng.</p>
</div>
<form action="#" data-mentra-newsletter="1" method="post">
<div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 mb-2.5">
<label class="sr-only" for="mailing-os-email">Địa chỉ email</label>
<input autocomplete="email" class="input-base input-pill flex-1" id="mailing-os-email" name="email" placeholder="Nhập email của bạn" required style="padding:10px 20px" type="email">
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
