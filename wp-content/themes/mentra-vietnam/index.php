<?php
if (!defined('ABSPATH')) { exit; }
$key = mentra_vn_get_source_key();
if ($key && mentra_vn_source_exists($key)) {
    mentra_vn_document_open($key);
    mentra_vn_render_source($key);
    mentra_vn_document_close();
    return;
}
get_header(); ?>
<main id="main-content" style="padding:120px var(--container-px);max-width:1100px;margin:auto">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<article <?php post_class(); ?> style="margin-bottom:50px">
<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
<?php the_excerpt(); ?>
</article>
<?php endwhile; else: ?><p>Không có nội dung.</p><?php endif; ?>
</main>
<?php get_footer();
