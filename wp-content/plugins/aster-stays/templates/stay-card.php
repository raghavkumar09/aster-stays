<?php
$stay_id = absint( $stay_id );
$title = get_the_title( $stay_id );
$image = get_post_meta( $stay_id, '_aster_image', true );
if ( ! $image && has_post_thumbnail( $stay_id ) ) {
    $image = get_the_post_thumbnail_url( $stay_id, 'large' );
}
$price = (float) get_post_meta( $stay_id, '_aster_price', true );
$rating = (float) get_post_meta( $stay_id, '_aster_rating', true );
$badge = get_post_meta( $stay_id, '_aster_badge', true );
$terms = get_the_terms( $stay_id, 'stay_region' );
$region = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';
?>
<article class="aster-stay-card">
    <a class="aster-stay-card__image" href="<?php echo esc_url( get_permalink( $stay_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View %s', 'aster-stays' ), $title ) ); ?>">
        <?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" width="720" height="520"><?php endif; ?>
        <?php if ( $badge ) : ?><span class="aster-stay-card__badge"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
        <span class="aster-stay-card__heart" aria-hidden="true">♡</span>
    </a>
    <div class="aster-stay-card__details">
        <div class="aster-stay-card__title-row">
            <div><span class="aster-eyebrow"><?php echo esc_html( $region ); ?></span><h3><a href="<?php echo esc_url( get_permalink( $stay_id ) ); ?>"><?php echo esc_html( $title ); ?></a></h3></div>
            <?php if ( $rating ) : ?><span class="aster-rating"><span aria-hidden="true">★</span> <?php echo esc_html( number_format_i18n( $rating, 2 ) ); ?></span><?php endif; ?>
        </div>
        <p><?php echo esc_html( get_the_excerpt( $stay_id ) ); ?></p>
        <div class="aster-stay-card__foot"><span><strong><?php echo esc_html( '₹' . number_format_i18n( $price ) ); ?></strong> / night</span><a class="aster-arrow-link" href="<?php echo esc_url( get_permalink( $stay_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'See details for %s', 'aster-stays' ), $title ) ); ?>">↗</a></div>
    </div>
</article>
