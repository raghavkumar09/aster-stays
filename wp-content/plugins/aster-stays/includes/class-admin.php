<?php

defined( 'ABSPATH' ) || exit;

final class Aster_Stays_Admin {
    private static $fields = array(
        '_aster_price' => array( 'label' => 'Nightly price (INR)', 'type' => 'number', 'step' => '1', 'min' => '0' ),
        '_aster_guests' => array( 'label' => 'Maximum guests', 'type' => 'number', 'step' => '1', 'min' => '1' ),
        '_aster_bedrooms' => array( 'label' => 'Bedrooms', 'type' => 'number', 'step' => '1', 'min' => '0' ),
        '_aster_bathrooms' => array( 'label' => 'Bathrooms', 'type' => 'number', 'step' => '1', 'min' => '0' ),
        '_aster_rating' => array( 'label' => 'Guest rating', 'type' => 'number', 'step' => '0.1', 'min' => '0', 'max' => '5' ),
        '_aster_badge' => array( 'label' => 'Card badge', 'type' => 'text' ),
        '_aster_image' => array( 'label' => 'Property image URL', 'type' => 'url' ),
        '_aster_featured' => array( 'label' => 'Feature on the homepage', 'type' => 'checkbox' ),
    );

    public static function register() {
        add_action( 'add_meta_boxes_stay', array( __CLASS__, 'add_box' ) );
        add_action( 'save_post_stay', array( __CLASS__, 'save' ) );
        add_filter( 'manage_stay_posts_columns', array( __CLASS__, 'columns' ) );
        add_action( 'manage_stay_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
        add_action( 'admin_post_aster_stays_inquiry', array( 'Aster_Stays_Shortcodes', 'handle_inquiry' ) );
        add_action( 'admin_post_nopriv_aster_stays_inquiry', array( 'Aster_Stays_Shortcodes', 'handle_inquiry' ) );
    }

    public static function add_box() {
        add_meta_box(
            'aster-stay-details',
            __( 'Stay details', 'aster-stays' ),
            array( __CLASS__, 'render_box' ),
            'stay',
            'normal',
            'high'
        );
    }

    public static function render_box( $post ) {
        wp_nonce_field( 'aster_save_stay', 'aster_stay_nonce' );
        echo '<div class="aster-admin-fields">';
        foreach ( self::$fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            $checked = 'checkbox' === $field['type'] && $value ? ' checked' : '';
            $attributes = '';
            foreach ( $field as $attribute => $attribute_value ) {
                if ( 'label' !== $attribute ) {
                    $attributes .= ' ' . esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }
            printf( '<p><label for="%1$s"><strong>%2$s</strong></label><br>', esc_attr( $key ), esc_html( $field['label'] ) );
            if ( 'checkbox' === $field['type'] ) {
                printf( '<input id="%1$s" name="%1$s" value="1" type="checkbox"%2$s>', esc_attr( $key ), $checked );
            } else {
                printf(
                    '<input class="widefat" id="%1$s" name="%1$s" value="%2$s"%3$s>',
                    esc_attr( $key ),
                    esc_attr( $value ),
                    $attributes
                );
            }
            echo '</p>';
        }
        echo '<p class="description">' . esc_html__( 'Choose a destination and amenities in the panels on the right. A featured image is optional when a property image URL is supplied.', 'aster-stays' ) . '</p>';
        echo '</div>';
    }

    public static function save( $post_id ) {
        if ( ! isset( $_POST['aster_stay_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aster_stay_nonce'] ) ), 'aster_save_stay' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        foreach ( self::$fields as $key => $field ) {
            if ( 'checkbox' === $field['type'] ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? 1 : 0 );
                continue;
            }
            if ( ! isset( $_POST[ $key ] ) ) {
                delete_post_meta( $post_id, $key );
                continue;
            }
            $raw = wp_unslash( $_POST[ $key ] );
            $value = 'number' === $field['type'] ? (float) $raw : ( 'url' === $field['type'] ? esc_url_raw( $raw ) : sanitize_text_field( $raw ) );
            update_post_meta( $post_id, $key, $value );
        }
    }

    public static function columns( $columns ) {
        $columns['aster_region'] = __( 'Destination', 'aster-stays' );
        $columns['aster_price'] = __( 'Nightly price', 'aster-stays' );
        return $columns;
    }

    public static function column_content( $column, $post_id ) {
        if ( 'aster_region' === $column ) {
            $terms = get_the_terms( $post_id, 'stay_region' );
            echo $terms && ! is_wp_error( $terms ) ? esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) : '—';
        }
        if ( 'aster_price' === $column ) {
            echo esc_html( '₹' . number_format_i18n( (float) get_post_meta( $post_id, '_aster_price', true ) ) );
        }
    }
}
