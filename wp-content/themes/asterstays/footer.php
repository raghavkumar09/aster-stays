<?php defined( 'ABSPATH' ) || exit; ?>
</main>
<footer class="aster-footer"><div class="aster-footer__top aster-container">
<div class="aster-footer__brand"><a class="aster-brand aster-brand--light" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="aster-brand__mark" aria-hidden="true">a</span><span class="aster-brand__word">aster<span>stays</span></span></a><p>Somewhere thoughtful to land.<br>Somewhere lovely to linger.</p></div>
<div><span class="aster-footer__label">Explore</span><a href="<?php echo esc_url( get_post_type_archive_link( 'stay' ) ); ?>">Our stays</a><a href="<?php echo esc_url( home_url( '/guest-guide/' ) ); ?>">Guest guide</a><a href="<?php echo esc_url( home_url( '/journal/' ) ); ?>">Journal</a></div>
<div><span class="aster-footer__label">Aster</span><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">Our story</a><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get in touch</a><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a></div>
<div class="aster-footer__note"><span class="aster-footer__label">The good stuff, occasionally.</span><p>Local places, little rituals and notes from the road.</p><a class="aster-footer__email" href="<?php echo esc_url( home_url( '/journal/' ) ); ?>">Read the journal <span aria-hidden="true">↗</span></a></div>
</div><div class="aster-footer__bottom"><div class="aster-container"><span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Aster Stays</span><span>Made for the long way round.</span><a href="#main-content">Back to top ↑</a></div></div></footer>
<?php wp_footer(); ?>
</body></html>
