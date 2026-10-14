<?php get_header(); ?>
<section class="aster-page-hero"><div class="aster-container"><div class="aster-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><span><?php the_title(); ?></span></div><span class="aster-eyebrow">Aster Stays</span><h1><?php the_title(); ?></h1></div></section>
<section class="aster-page-content aster-container"><?php while(have_posts()): the_post(); ?><div class="aster-prose"><?php the_content(); ?></div><?php endwhile; ?></section>
<?php get_footer(); ?>
