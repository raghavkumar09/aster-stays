<?php

defined( 'ABSPATH' ) || exit;

final class Aster_Stays_Booking {
    public static function register_hooks() {
        add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate' ), 10, 5 );
        add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 10, 4 );
        add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'display_cart_data' ), 10, 2 );
        add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'set_booking_price' ) );
        add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'save_order_meta' ), 10, 4 );
    }

    public static function validate( $passed, $product_id, $quantity, $variation_id = 0, $variations = array() ) {
        if ( empty( $_POST['aster_stay_id'] ) ) {
            return $passed;
        }
        $stay_id = absint( $_POST['aster_stay_id'] );
        $nonce = isset( $_POST['aster_booking_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['aster_booking_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'aster_book_stay_' . $stay_id ) ) {
            wc_add_notice( __( 'Please refresh the stay page and try again.', 'aster-stays' ), 'error' );
            return false;
        }
        $check_in = self::valid_date( $_POST['aster_check_in'] ?? '' );
        $check_out = self::valid_date( $_POST['aster_check_out'] ?? '' );
        $guests = absint( $_POST['aster_guests'] ?? 0 );
        $max_guests = max( 1, absint( get_post_meta( $stay_id, '_aster_guests', true ) ) );
        $today = current_time( 'Y-m-d' );

        if ( ! $check_in || ! $check_out || $check_in <= $today || $check_out <= $check_in ) {
            wc_add_notice( __( 'Choose a future check-in date and a later check-out date.', 'aster-stays' ), 'error' );
            return false;
        }
        if ( $guests < 1 || $guests > $max_guests ) {
            wc_add_notice( sprintf( __( 'This stay allows up to %d guests.', 'aster-stays' ), $max_guests ), 'error' );
            return false;
        }
        if ( 'publish' !== get_post_status( $stay_id ) || 'stay' !== get_post_type( $stay_id ) ) {
            wc_add_notice( __( 'This stay is not currently available for booking.', 'aster-stays' ), 'error' );
            return false;
        }
        return $passed;
    }

    public static function cart_item_data( $data, $product_id, $variation_id, $quantity ) {
        if ( empty( $_POST['aster_stay_id'] ) ) {
            return $data;
        }
        $stay_id = absint( $_POST['aster_stay_id'] );
        $check_in = self::valid_date( $_POST['aster_check_in'] ?? '' );
        $check_out = self::valid_date( $_POST['aster_check_out'] ?? '' );
        if ( ! $check_in || ! $check_out ) {
            return $data;
        }
        $nights = (int) ( ( strtotime( $check_out ) - strtotime( $check_in ) ) / DAY_IN_SECONDS );
        $data['aster_booking'] = array(
            'stay_id' => $stay_id,
            'stay_name' => get_the_title( $stay_id ),
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => absint( $_POST['aster_guests'] ?? 1 ),
            'nights' => max( 1, $nights ),
            'nightly_price' => (float) get_post_meta( $stay_id, '_aster_price', true ),
        );
        $data['aster_booking_key'] = md5( $stay_id . $check_in . $check_out . wp_rand() );
        return $data;
    }

    public static function display_cart_data( $item_data, $cart_item ) {
        if ( empty( $cart_item['aster_booking'] ) ) {
            return $item_data;
        }
        $booking = $cart_item['aster_booking'];
        $item_data[] = array( 'key' => __( 'Stay', 'aster-stays' ), 'value' => esc_html( $booking['stay_name'] ) );
        $item_data[] = array( 'key' => __( 'Check-in', 'aster-stays' ), 'value' => esc_html( wp_date( get_option( 'date_format' ), strtotime( $booking['check_in'] ) ) ) );
        $item_data[] = array( 'key' => __( 'Check-out', 'aster-stays' ), 'value' => esc_html( wp_date( get_option( 'date_format' ), strtotime( $booking['check_out'] ) ) ) );
        $item_data[] = array( 'key' => __( 'Guests', 'aster-stays' ), 'value' => esc_html( (string) $booking['guests'] ) );
        $item_data[] = array( 'key' => __( 'Nights', 'aster-stays' ), 'value' => esc_html( (string) $booking['nights'] ) );
        return $item_data;
    }

    public static function set_booking_price( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
        foreach ( $cart->get_cart() as $cart_item ) {
            if ( ! empty( $cart_item['aster_booking'] ) ) {
                $booking = $cart_item['aster_booking'];
                $cart_item['data']->set_price( (float) $booking['nightly_price'] * (int) $booking['nights'] );
            }
        }
    }

    public static function save_order_meta( $item, $cart_item_key, $values, $order ) {
        if ( empty( $values['aster_booking'] ) ) {
            return;
        }
        $booking = $values['aster_booking'];
        $item->add_meta_data( __( 'Stay', 'aster-stays' ), $booking['stay_name'], true );
        $item->add_meta_data( __( 'Check-in', 'aster-stays' ), $booking['check_in'], true );
        $item->add_meta_data( __( 'Check-out', 'aster-stays' ), $booking['check_out'], true );
        $item->add_meta_data( __( 'Guests', 'aster-stays' ), $booking['guests'], true );
        $item->add_meta_data( __( 'Nights', 'aster-stays' ), $booking['nights'], true );
    }

    private static function valid_date( $value ) {
        $value = sanitize_text_field( wp_unslash( $value ) );
        $date = DateTime::createFromFormat( '!Y-m-d', $value );
        return $date && $date->format( 'Y-m-d' ) === $value ? $value : '';
    }
}
