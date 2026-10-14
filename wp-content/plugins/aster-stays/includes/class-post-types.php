<?php

defined( 'ABSPATH' ) || exit;

final class Aster_Stays_Post_Types {
    public static function register() {
        register_post_type(
            'stay',
            array(
                'labels' => array(
                    'name' => __( 'Stays', 'aster-stays' ),
                    'singular_name' => __( 'Stay', 'aster-stays' ),
                    'add_new_item' => __( 'Add a stay', 'aster-stays' ),
                    'edit_item' => __( 'Edit stay', 'aster-stays' ),
                    'view_item' => __( 'View stay', 'aster-stays' ),
                    'search_items' => __( 'Search stays', 'aster-stays' ),
                    'not_found' => __( 'No stays found', 'aster-stays' ),
                    'menu_name' => __( 'Stays', 'aster-stays' ),
                ),
                'public' => true,
                'show_in_rest' => true,
                'has_archive' => true,
                'rewrite' => array( 'slug' => 'stays', 'with_front' => false ),
                'menu_icon' => 'dashicons-admin-home',
                'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
            )
        );

        register_taxonomy(
            'stay_region',
            array( 'stay' ),
            array(
                'labels' => array(
                    'name' => __( 'Destinations', 'aster-stays' ),
                    'singular_name' => __( 'Destination', 'aster-stays' ),
                    'add_new_item' => __( 'Add destination', 'aster-stays' ),
                ),
                'public' => true,
                'show_in_rest' => true,
                'hierarchical' => true,
                'rewrite' => array( 'slug' => 'destination', 'with_front' => false ),
            )
        );

        register_taxonomy(
            'stay_amenity',
            array( 'stay' ),
            array(
                'labels' => array(
                    'name' => __( 'Amenities', 'aster-stays' ),
                    'singular_name' => __( 'Amenity', 'aster-stays' ),
                ),
                'public' => true,
                'show_in_rest' => true,
                'hierarchical' => false,
                'rewrite' => array( 'slug' => 'amenity', 'with_front' => false ),
            )
        );

        register_post_type(
            'stay_inquiry',
            array(
                'labels' => array(
                    'name' => __( 'Enquiries', 'aster-stays' ),
                    'singular_name' => __( 'Enquiry', 'aster-stays' ),
                    'menu_name' => __( 'Enquiries', 'aster-stays' ),
                ),
                'public' => false,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => false,
                'exclude_from_search' => true,
                'supports' => array( 'title', 'editor' ),
                'menu_icon' => 'dashicons-email-alt',
                'capability_type' => 'post',
                'map_meta_cap' => true,
            )
        );

        self::register_meta();
    }

    private static function register_meta() {
        $fields = array(
            '_aster_price' => array( 'type' => 'number', 'sanitize_callback' => function( $value ) { return is_numeric( $value ) ? (float) $value : 0; } ),
            '_aster_guests' => array( 'type' => 'integer', 'sanitize_callback' => function( $value ) { return absint( $value ); } ),
            '_aster_bedrooms' => array( 'type' => 'integer', 'sanitize_callback' => function( $value ) { return absint( $value ); } ),
            '_aster_bathrooms' => array( 'type' => 'integer', 'sanitize_callback' => function( $value ) { return absint( $value ); } ),
            '_aster_rating' => array( 'type' => 'number', 'sanitize_callback' => function( $value ) { return is_numeric( $value ) ? (float) $value : 0; } ),
            '_aster_badge' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
            '_aster_image' => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
            '_aster_amenities' => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
            '_aster_featured' => array( 'type' => 'boolean', 'sanitize_callback' => function( $value ) { return (bool) $value; } ),
            '_aster_product_id' => array( 'type' => 'integer', 'sanitize_callback' => function( $value ) { return absint( $value ); } ),
        );

        foreach ( $fields as $key => $schema ) {
            register_post_meta(
                'stay',
                $key,
                array_merge(
                    $schema,
                    array(
                        'single' => true,
                        'show_in_rest' => true,
                        'auth_callback' => function() {
                            return current_user_can( 'edit_posts' );
                        },
                    )
                )
            );
        }
    }
}
