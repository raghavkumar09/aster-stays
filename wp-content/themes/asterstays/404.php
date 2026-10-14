<?php get_header(); ?>
<section class="aster-error-page aster-container"><span class="aster-eyebrow">Looks like we took the scenic route</span><h1>Not quite <em>there.</em></h1><p>That page has wandered off. Let’s find you somewhere lovely instead.</p><a class="aster-button aster-button--dark" href="<?php echo esc_url(get_post_type_archive_link('stay')); ?>">Explore our stays <span>↗</span></a></section>
<?php get_footer(); ?>
