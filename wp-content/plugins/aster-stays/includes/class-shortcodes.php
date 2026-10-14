<?php

defined( 'ABSPATH' ) || exit;

final class Aster_Stays_Shortcodes {
    public static function register() {
        add_shortcode( 'aster_stays', array( __CLASS__, 'render_grid' ) );
        add_shortcode( 'aster_search', array( __CLASS__, 'render_search' ) );
        add_shortcode( 'aster_inquiry_form', array( __CLASS__, 'render_inquiry_form' ) );
        add_shortcode( 'aster_booking_form', array( __CLASS__, 'render_booking_form' ) );
    }

    public static function render_search() {
        $regions = get_terms( array( 'taxonomy' => 'stay_region', 'hide_empty' => false ) );
        ob_start();
        ?>
        <form class="aster-search" action="<?php echo esc_url( get_post_type_archive_link( 'stay' ) ); ?>" method="get">
            <label class="aster-search__field"><span><?php esc_html_e( 'Where to?', 'aster-stays' ); ?></span>
                <select name="destination">
                    <option value=""><?php esc_html_e( 'Anywhere', 'aster-stays' ); ?></option>
                    <?php if ( ! is_wp_error( $regions ) ) : foreach ( $regions as $region ) : ?>
                        <option value="<?php echo esc_attr( $region->slug ); ?>"><?php echo esc_html( $region->name ); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </label>
            <label class="aster-search__field"><span><?php esc_html_e( 'Guests', 'aster-stays' ); ?></span>
                <select name="guests">
                    <option value=""><?php esc_html_e( 'Any size', 'aster-stays' ); ?></option>
                    <?php for ( $count = 1; $count <= 8; $count++ ) : ?>
                        <option value="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( sprintf( _n( '%s guest', '%s guests', $count, 'aster-stays' ), number_format_i18n( $count ) ) ); ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <label class="aster-search__field"><span><?php esc_html_e( 'Max price (₹)', 'aster-stays' ); ?></span>
                <input type="number" name="max_price" min="0" step="500" placeholder="<?php esc_attr_e( 'Any budget', 'aster-stays' ); ?>">
            </label>
            <button class="aster-button aster-button--dark" type="submit"><?php esc_html_e( 'Find a stay', 'aster-stays' ); ?> <span aria-hidden="true">↗</span></button>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function render_grid( $attributes = array() ) {
        $attributes = shortcode_atts( array( 'per_page' => 9, 'featured' => '', 'heading' => '', 'compact' => '' ), $attributes, 'aster_stays' );
        $paged = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
        $meta_query = array( 'relation' => 'AND' );
        $tax_query = array();
        $destination = isset( $_GET['destination'] ) ? sanitize_title( wp_unslash( $_GET['destination'] ) ) : '';
        $guests = isset( $_GET['guests'] ) ? absint( $_GET['guests'] ) : 0;
        $min_price = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : 0;
        $max_price = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : 0;

        if ( $destination ) {
            $tax_query[] = array(
                'taxonomy' => 'stay_region',
                'field' => 'slug',
                'terms' => $destination,
            );
        }
        if ( $guests ) {
            $meta_query[] = array( 'key' => '_aster_guests', 'value' => $guests, 'type' => 'NUMERIC', 'compare' => '>=' );
        }
        if ( $min_price ) {
            $meta_query[] = array( 'key' => '_aster_price', 'value' => $min_price, 'type' => 'NUMERIC', 'compare' => '>=' );
        }
        if ( $max_price ) {
            $meta_query[] = array( 'key' => '_aster_price', 'value' => $max_price, 'type' => 'NUMERIC', 'compare' => '<=' );
        }
        if ( '1' === (string) $attributes['featured'] ) {
            $meta_query[] = array( 'key' => '_aster_featured', 'value' => '1' );
        }

        $query = new WP_Query(
            array(
                'post_type' => 'stay',
                'post_status' => 'publish',
                'posts_per_page' => min( 24, max( 1, absint( $attributes['per_page'] ) ) ),
                'paged' => $paged,
                'meta_query' => $meta_query,
                'tax_query' => $tax_query,
                'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
            )
        );

        ob_start();
        if ( $attributes['heading'] ) {
            printf( '<h2 class="aster-grid-heading">%s</h2>', esc_html( $attributes['heading'] ) );
        }
        if ( ! is_singular() ) {
            self::render_filters( $destination, $guests, $min_price, $max_price );
        }
        if ( $query->have_posts() ) {
            echo '<div class="aster-stay-grid' . ( $attributes['compact'] ? ' aster-stay-grid--compact' : '' ) . '">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $stay_id = get_the_ID();
                include ASTER_STAYS_PATH . 'templates/stay-card.php';
            }
            echo '</div>';
            if ( ! is_singular() && $query->max_num_pages > 1 ) {
                $links = paginate_links(
                    array(
                        'total' => $query->max_num_pages,
                        'current' => $paged,
                        'type' => 'list',
                        'add_args' => array_filter(
                            array(
                                'destination' => $destination,
                                'guests' => $guests,
                                'min_price' => $min_price,
                                'max_price' => $max_price,
                            )
                        ),
                    )
                );
                if ( $links ) {
                    echo '<nav class="aster-pagination" aria-label="' . esc_attr__( 'Stay pages', 'aster-stays' ) . '">' . wp_kses_post( $links ) . '</nav>';
                }
            }
        } else {
            echo '<div class="aster-empty"><span class="aster-eyebrow">No stays found</span><h3>Try a different search</h3><p>Clear a filter or explore all of our little hideaways.</p><a class="aster-text-link" href="' . esc_url( get_post_type_archive_link( 'stay' ) ) . '">See every stay <span aria-hidden="true">↗</span></a></div>';
        }
        wp_reset_postdata();
        return ob_get_clean();
    }

    private static function render_filters( $destination, $guests, $min_price, $max_price ) {
        $regions = get_terms( array( 'taxonomy' => 'stay_region', 'hide_empty' => false ) );
        ?>
        <form class="aster-filters" action="<?php echo esc_url( get_post_type_archive_link( 'stay' ) ); ?>" method="get">
            <label><span><?php esc_html_e( 'Destination', 'aster-stays' ); ?></span>
                <select name="destination"><option value=""><?php esc_html_e( 'All destinations', 'aster-stays' ); ?></option>
                    <?php if ( ! is_wp_error( $regions ) ) : foreach ( $regions as $region ) : ?>
                        <option value="<?php echo esc_attr( $region->slug ); ?>" <?php selected( $destination, $region->slug ); ?>><?php echo esc_html( $region->name ); ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </label>
            <label><span><?php esc_html_e( 'Guests', 'aster-stays' ); ?></span>
                <select name="guests"><option value=""><?php esc_html_e( 'Any number', 'aster-stays' ); ?></option>
                    <?php for ( $count = 1; $count <= 8; $count++ ) : ?>
                        <option value="<?php echo esc_attr( $count ); ?>" <?php selected( $guests, $count ); ?>><?php echo esc_html( $count ); ?>+</option>
                    <?php endfor; ?>
                </select>
            </label>
            <label><span><?php esc_html_e( 'Price from (₹)', 'aster-stays' ); ?></span><input type="number" name="min_price" min="0" step="500" value="<?php echo esc_attr( $min_price ?: '' ); ?>" placeholder="Any"></label>
            <label><span><?php esc_html_e( 'Price to (₹)', 'aster-stays' ); ?></span><input type="number" name="max_price" min="0" step="500" value="<?php echo esc_attr( $max_price ?: '' ); ?>" placeholder="Any"></label>
            <div class="aster-filters__actions"><button class="aster-button aster-button--dark" type="submit"><?php esc_html_e( 'Apply filters', 'aster-stays' ); ?></button><a href="<?php echo esc_url( get_post_type_archive_link( 'stay' ) ); ?>"><?php esc_html_e( 'Reset', 'aster-stays' ); ?></a></div>
        </form>
        <?php
    }

    public static function render_inquiry_form( $attributes = array() ) {
        $attributes = shortcode_atts( array( 'stay_id' => 0, 'title' => 'Have a question?' ), $attributes, 'aster_inquiry_form' );
        $stay_id = absint( $attributes['stay_id'] );
        if ( ! $stay_id && is_singular( 'stay' ) ) {
            $stay_id = get_the_ID();
        }
        ob_start();
        if ( isset( $_GET['inquiry'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['inquiry'] ) ) ) {
            echo '<div class="aster-notice aster-notice--success" role="status">' . esc_html__( 'Thanks for reaching out. Our local host will be in touch soon.', 'aster-stays' ) . '</div>';
            return ob_get_clean();
        }
        if ( isset( $_GET['inquiry'] ) && 'error' === sanitize_key( wp_unslash( $_GET['inquiry'] ) ) ) {
            echo '<div class="aster-notice aster-notice--error" role="alert">' . esc_html__( 'Please check your name, email, and message, then try again.', 'aster-stays' ) . '</div>';
        }
        ?>
        <form class="aster-inquiry-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
            <input type="hidden" name="action" value="aster_stays_inquiry">
            <input type="hidden" name="stay_id" value="<?php echo esc_attr( $stay_id ); ?>">
            <?php wp_nonce_field( 'aster_stays_inquiry', 'aster_stays_nonce' ); ?>
            <div class="aster-honeypot" aria-hidden="true"><label>Leave blank<input type="text" name="aster_company" tabindex="-1" autocomplete="off"></label></div>
            <div class="aster-form-heading"><span class="aster-eyebrow"><?php esc_html_e( 'A human, not a bot', 'aster-stays' ); ?></span><h2><?php echo esc_html( $attributes['title'] ); ?></h2></div>
            <div class="aster-form-row">
                <label><span><?php esc_html_e( 'Your name', 'aster-stays' ); ?></span><input type="text" name="guest_name" required autocomplete="name"></label>
                <label><span><?php esc_html_e( 'Email address', 'aster-stays' ); ?></span><input type="email" name="guest_email" required autocomplete="email"></label>
            </div>
            <div class="aster-form-row">
                <label><span><?php esc_html_e( 'Phone (optional)', 'aster-stays' ); ?></span><input type="tel" name="guest_phone" autocomplete="tel"></label>
                <label><span><?php esc_html_e( 'Destination or stay', 'aster-stays' ); ?></span>
                    <select name="stay_id_select"><option value=""><?php esc_html_e( 'I am still exploring', 'aster-stays' ); ?></option>
                        <?php
                        $stays = get_posts( array( 'post_type' => 'stay', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
                        foreach ( $stays as $stay ) :
                            ?>
                            <option value="<?php echo esc_attr( $stay->ID ); ?>" <?php selected( $stay_id, $stay->ID ); ?>><?php echo esc_html( $stay->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label><span><?php esc_html_e( 'What would you like to know?', 'aster-stays' ); ?></span><textarea name="guest_message" rows="4" required></textarea></label>
            <button class="aster-button aster-button--dark" type="submit"><?php esc_html_e( 'Send enquiry', 'aster-stays' ); ?> <span aria-hidden="true">↗</span></button>
            <p class="aster-form-note"><?php esc_html_e( 'We only use your details to reply to this enquiry.', 'aster-stays' ); ?></p>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function render_booking_form( $attributes = array() ) {
        $stay_id = ! empty( $attributes['stay_id'] ) ? absint( $attributes['stay_id'] ) : get_the_ID();
        $product_id = absint( get_post_meta( $stay_id, '_aster_product_id', true ) );
        $price = (float) get_post_meta( $stay_id, '_aster_price', true );
        $max_guests = max( 1, absint( get_post_meta( $stay_id, '_aster_guests', true ) ) );
        if ( ! $product_id || ! function_exists( 'wc_get_cart_url' ) ) {
            return '<div class="aster-notice">' . esc_html__( 'Booking is being prepared. Please send an enquiry for now.', 'aster-stays' ) . '</div>';
        }
        ob_start();
        ?>
        <form class="aster-booking-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
            <?php wp_nonce_field( 'aster_book_stay_' . $stay_id, 'aster_booking_nonce' ); ?>
            <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>">
            <input type="hidden" name="aster_stay_id" value="<?php echo esc_attr( $stay_id ); ?>">
            <div class="aster-booking-form__price"><strong><?php echo wp_kses_post( wc_price( $price ) ); ?></strong><span><?php esc_html_e( 'per night', 'aster-stays' ); ?></span></div>
            <div class="aster-booking-form__dates">
                <label><span><?php esc_html_e( 'Check-in', 'aster-stays' ); ?></span><input type="date" name="aster_check_in" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required></label>
                <label><span><?php esc_html_e( 'Check-out', 'aster-stays' ); ?></span><input type="date" name="aster_check_out" min="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>" required></label>
            </div>
            <label><span><?php esc_html_e( 'Guests', 'aster-stays' ); ?></span><select name="aster_guests"><?php for ( $count = 1; $count <= $max_guests; $count++ ) : ?><option value="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?> <?php echo esc_html( _n( 'guest', 'guests', $count, 'aster-stays' ) ); ?></option><?php endfor; ?></select></label>
            <button class="aster-button aster-button--dark aster-button--full" type="submit"><?php esc_html_e( 'Add to booking', 'aster-stays' ); ?> <span aria-hidden="true">↗</span></button>
            <p class="aster-form-note"><?php esc_html_e( 'You can review your dates before checkout.', 'aster-stays' ); ?></p>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function handle_inquiry() {
        if ( ! isset( $_POST['aster_stays_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aster_stays_nonce'] ) ), 'aster_stays_inquiry' ) ) {
            wp_die( esc_html__( 'This enquiry could not be verified. Please go back and try again.', 'aster-stays' ), 403 );
        }
        if ( ! empty( $_POST['aster_company'] ) ) {
            self::redirect_after_inquiry( 'sent' );
        }

        $name = isset( $_POST['guest_name'] ) ? sanitize_text_field( wp_unslash( $_POST['guest_name'] ) ) : '';
        $email = isset( $_POST['guest_email'] ) ? sanitize_email( wp_unslash( $_POST['guest_email'] ) ) : '';
        $phone = isset( $_POST['guest_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['guest_phone'] ) ) : '';
        $message = isset( $_POST['guest_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['guest_message'] ) ) : '';
        $stay_id = ! empty( $_POST['stay_id_select'] ) ? absint( $_POST['stay_id_select'] ) : absint( $_POST['stay_id'] ?? 0 );

        if ( ! $name || ! is_email( $email ) || ! $message ) {
            self::redirect_after_inquiry( 'error' );
        }

        $stay_title = $stay_id ? get_the_title( $stay_id ) : __( 'General enquiry', 'aster-stays' );
        $inquiry_id = wp_insert_post(
            array(
                'post_type' => 'stay_inquiry',
                'post_status' => 'private',
                'post_title' => sprintf( '%1$s — %2$s', $name, $stay_title ),
                'post_content' => $message,
            ),
            true
        );
        if ( is_wp_error( $inquiry_id ) ) {
            self::redirect_after_inquiry( 'error' );
        }
        update_post_meta( $inquiry_id, '_aster_guest_email', $email );
        update_post_meta( $inquiry_id, '_aster_guest_phone', $phone );
        update_post_meta( $inquiry_id, '_aster_stay_id', $stay_id );
        update_post_meta( $inquiry_id, '_aster_inquiry_date', current_time( 'mysql' ) );
        self::redirect_after_inquiry( 'sent' );
    }

    private static function redirect_after_inquiry( $status ) {
        $target = wp_get_referer() ? wp_get_referer() : home_url( '/contact/' );
        wp_safe_redirect( add_query_arg( 'inquiry', sanitize_key( $status ), $target ) );
        exit;
    }
}
