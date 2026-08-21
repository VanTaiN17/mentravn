<?php get_header(); ?>
<main id="main" class="page-main">
<?php while(have_posts()): the_post(); ?>
<section class="page-hero"><div class="container narrow reveal"><p class="eyebrow">TIN TỨC</p><h1><?php the_title(); ?></h1><p><?php echo esc_html(get_the_date('d/m/Y')); ?></p></div></section>
<section class="section"><article class="container prose reveal"><?php if(has_post_thumbnail()) the_post_thumbnail('large',['class'=>'post-cover']); the_content(); ?></article></section>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
