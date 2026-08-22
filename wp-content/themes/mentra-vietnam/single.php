<?php
if (!defined('ABSPATH')) { exit; }
if (!have_posts() || !is_singular('post')) {
    return;
}

the_post();
$authors = get_post_meta(get_the_ID(), '_mentra_vn_article_authors', true);
$authors = is_array($authors) ? $authors : [];
$hero_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';

mentra_vn_document_open('article');
mentra_vn_render_partial('site-header');
?>
<main class="content-shell-rounded font-sans overflow-x-hidden" id="main-content" style="position:relative;z-index:1;min-height:calc(100dvh + 1px);background-color:var(--surface-0)">
<article class="bg-surface-0" style="padding-top:calc(var(--height-nav) + 46px);padding-bottom:var(--section-md);padding-left:var(--container-px);padding-right:var(--container-px)">
<div class="mx-auto max-w-4xl">
<nav aria-label="Breadcrumb" class="py-3 mb-4 md:mb-6" style="font-size:var(--font-size-fine);color:var(--ink-tertiary)">
<ol class="flex flex-wrap items-center gap-1" role="list" style="list-style:none;margin:0;padding:0">
<li class="flex items-center gap-1"><a href="<?php echo esc_url(home_url('/')); ?>" style="color:var(--ink-tertiary);text-decoration:none">Trang chủ</a></li>
<li class="flex items-center gap-1"><span aria-hidden="true" style="color:var(--ink-disabled);font-size:0.75em">/</span><a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>" style="color:var(--ink-tertiary);text-decoration:none">Tin tức</a></li>
<li class="flex items-center gap-1"><span aria-hidden="true" style="color:var(--ink-disabled);font-size:0.75em">/</span><span aria-current="page" style="color:var(--ink-secondary);font-weight:500"><?php the_title(); ?></span></li>
</ol>
</nav>
<header class="mb-8">
<h1 class="text-[22px] md:text-5xl font-bold mb-3 md:mb-4 leading-tight" style="color:var(--ink-primary)"><?php the_title(); ?></h1>
<div class="flex items-center gap-2 md:gap-3 text-sm md:text-base mb-4 md:mb-6" style="color:var(--ink-secondary)"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d \t\h\á\n\g n, Y')); ?></time></div>
<?php if ($authors) { ?>
<div class="flex flex-wrap items-center gap-4 md:gap-6">
<?php foreach ($authors as $author) {
    $name = isset($author['name']) ? $author['name'] : '';
    $role = isset($author['role']) ? $author['role'] : '';
    $avatar = isset($author['avatar']) ? $author['avatar'] : '';
    if (!$name) { continue; } ?>
<div class="flex items-center gap-2 md:gap-3">
<?php if ($avatar) { ?><img alt="<?php echo esc_attr($name); ?>" class="w-9 h-9 md:w-12 md:h-12 rounded-full object-cover" src="<?php echo esc_url(mentra_vn_asset($avatar)); ?>"/><?php } ?>
<div><div class="font-medium text-sm md:text-base" style="color:var(--ink-primary)"><?php echo esc_html($name); ?></div><?php if ($role) { ?><div class="text-xs md:text-sm" style="color:var(--ink-secondary)"><?php echo esc_html($role); ?></div><?php } ?></div>
</div>
<?php } ?>
</div>
<?php } ?>
</header>
<?php if ($hero_url) { ?><div class="mb-8 overflow-hidden rounded-xl md:mb-12 md:rounded-2xl"><img alt="<?php the_title_attribute(); ?>" class="h-auto w-full" src="<?php echo esc_url($hero_url); ?>"/></div><?php } ?>
<div class="prose prose-lg max-w-none overflow-x-hidden">
<?php the_content(); ?>
</div>
</div>
</article>
<?php mentra_vn_render_partial('newsletter-cta'); ?>
</main>
<?php
mentra_vn_render_partial('site-footer');
mentra_vn_document_close();
