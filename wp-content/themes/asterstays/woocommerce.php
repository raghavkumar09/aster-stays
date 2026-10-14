<?php get_header(); ?>
<section class="aster-archive-hero"><div class="aster-container"><div class="aster-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><span><?php woocommerce_page_title(); ?></span></div><span class="aster-eyebrow">Aster checkout</span><h1><?php woocommerce_page_title(); ?></h1></div></section>
<section class="aster-commerce-content aster-container"><?php woocommerce_content(); ?></section>
<?php get_footer(); ?>
