<?php
if (!defined('ABSPATH')) { exit; }
$key = mentra_vn_get_source_key();
if ($key && mentra_vn_source_exists($key)) {
    mentra_vn_document_open($key);
    mentra_vn_render_source($key);
    mentra_vn_document_close();
    return;
}
get_header();
while (have_posts()) : the_post(); ?>
<main id="main-content" style="padding:120px var(--container-px);max-width:1000px;margin:auto">
  <h1><?php the_title(); ?></h1>
  <div class="policy-content"><?php the_content(); ?></div>
</main>
<?php endwhile;
get_footer();
