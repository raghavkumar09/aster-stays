<?php get_header(); ?>
<section class="aster-archive-hero"><div class="aster-container"><div class="aster-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><span>Our stays</span></div><span class="aster-eyebrow">Collected with care</span><h1>Find your <em>somewhere.</em></h1><p>Homes, hideaways and little moments worth travelling for.</p></div></section>
<section class="aster-archive-content aster-container"><div class="aster-results-bar"><span><?php echo esc_html((string)wp_count_posts('stay')->publish); ?> thoughtful places to begin</span><span>All prices shown per night · INR</span></div><?php echo do_shortcode('[aster_stays per_page="9"]'); ?></section>
<?php get_footer(); ?>
