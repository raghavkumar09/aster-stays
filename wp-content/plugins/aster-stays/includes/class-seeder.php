<?php

defined( 'ABSPATH' ) || exit;

final class Aster_Stays_Seeder {
    private static $seed_option = 'aster_stays_demo_seeded';

    public static function maybe_seed() {
        self::remove_wordpress_starter_post();
        if ( ! get_option( self::$seed_option ) ) {
            self::seed();
            return;
        }
        self::ensure_products();
    }

    private static function remove_wordpress_starter_post() {
        $starter = get_page_by_path( 'hello-world', OBJECT, 'post' );
        if ( $starter && 'Hello world!' === $starter->post_title ) {
            wp_delete_post( $starter->ID, true );
        }
    }

    public static function seed() {
        self::register_terms();
        $destinations = self::destination_data();
        $index = 0;
        foreach ( self::stay_data() as $data ) {
            $existing = get_page_by_path( $data['slug'], OBJECT, 'stay' );
            $stay_id = $existing ? $existing->ID : wp_insert_post(
                array(
                    'post_type' => 'stay',
                    'post_status' => 'publish',
                    'post_title' => $data['title'],
                    'post_name' => $data['slug'],
                    'post_excerpt' => $data['summary'],
                    'post_content' => $data['description'],
                    'menu_order' => $index++,
                ),
                true
            );
            if ( is_wp_error( $stay_id ) ) {
                continue;
            }
            foreach ( $data['meta'] as $key => $value ) {
                if ( '' === get_post_meta( $stay_id, $key, true ) ) {
                    update_post_meta( $stay_id, $key, $value );
                }
            }
            wp_set_object_terms( $stay_id, $data['region'], 'stay_region' );
            wp_set_object_terms( $stay_id, $data['amenities'], 'stay_amenity' );
        }

        self::create_pages();
        self::ensure_products();
        update_option( self::$seed_option, 1 );
    }

    private static function register_terms() {
        foreach ( self::destination_data() as $slug => $label ) {
            if ( ! term_exists( $slug, 'stay_region' ) ) {
                wp_insert_term( $label, 'stay_region', array( 'slug' => $slug ) );
            }
        }
        foreach ( array( 'Sea view', 'Private pool', 'Breakfast', 'Fireplace', 'Mountain view', 'Pet friendly', 'Workspace', 'Chef service', 'Garden', 'Yoga deck' ) as $amenity ) {
            if ( ! term_exists( $amenity, 'stay_amenity' ) ) {
                wp_insert_term( $amenity, 'stay_amenity' );
            }
        }
    }

    private static function create_pages() {
        $pages = array(
            'home' => array( 'Home', '' ),
            'about' => array( 'Our story', '<p>Aster began with a simple idea: the best trips feel less like a booking and more like being welcomed in. We work with thoughtful local hosts and small stays that help you settle into a place, not just pass through it.</p><p>Every home in our collection is hand-picked for its character, its people and the little details that make a good weekend last longer in your memory.</p>' ),
            'contact' => array( 'Get in touch', '<p>Need help choosing a place, planning a slower itinerary, or managing a stay? Tell us a little about your trip and our local team will get back to you.</p>[aster_inquiry_form title="Let’s plan something good"]' ),
            'guest-guide' => array( 'A little guest guide', '<p>Arrive curious. Leave the space as you found it, respect the neighbourhood, and ask your host for the local favourites. Each property page contains the practical details for your stay.</p><p>Questions about dates or accessibility? Send an enquiry and we will help before you book.</p>' ),
            'privacy-policy' => array( 'Privacy', '<p>This demo stores enquiry details only in the local WordPress dashboard so an operator can reply. Do not enter real guest information on a public demo.</p>' ),
            'journal' => array( 'Journal', '' ),
        );
        $page_ids = array();
        foreach ( $pages as $slug => $page ) {
            $existing = get_page_by_path( $slug );
            $page_ids[ $slug ] = $existing ? $existing->ID : wp_insert_post(
                array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_title' => $page[0],
                    'post_name' => $slug,
                    'post_content' => $page[1],
                )
            );
        }
        if ( ! empty( $page_ids['home'] ) && ! is_wp_error( $page_ids['home'] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', $page_ids['home'] );
        }
        if ( ! empty( $page_ids['journal'] ) && ! is_wp_error( $page_ids['journal'] ) ) {
            update_option( 'page_for_posts', $page_ids['journal'] );
        }
        self::create_menu( $page_ids );
        self::create_journal_post();
    }

    private static function create_menu( $pages ) {
        $menu_name = 'Aster Main Navigation';
        $menu = wp_get_nav_menu_object( $menu_name );
        $menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
        if ( is_wp_error( $menu_id ) ) {
            return;
        }
        if ( ! wp_get_nav_menu_items( $menu_id ) ) {
            $items = array(
                array( 'type' => 'custom', 'title' => 'Stays', 'url' => get_post_type_archive_link( 'stay' ) ),
                array( 'type' => 'page', 'title' => 'Our story', 'id' => $pages['about'] ?? 0 ),
                array( 'type' => 'page', 'title' => 'Journal', 'id' => $pages['journal'] ?? 0 ),
                array( 'type' => 'page', 'title' => 'Contact', 'id' => $pages['contact'] ?? 0 ),
            );
            foreach ( $items as $item ) {
                $args = array(
                    'menu-item-title' => $item['title'],
                    'menu-item-status' => 'publish',
                );
                if ( 'custom' === $item['type'] ) {
                    $args['menu-item-type'] = 'custom';
                    $args['menu-item-url'] = $item['url'];
                } elseif ( ! empty( $item['id'] ) ) {
                    $args['menu-item-type'] = 'post_type';
                    $args['menu-item-object'] = 'page';
                    $args['menu-item-object-id'] = $item['id'];
                } else {
                    continue;
                }
                wp_update_nav_menu_item( $menu_id, 0, $args );
            }
        }
        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    private static function create_journal_post() {
        if ( get_page_by_path( 'the-art-of-a-slower-weekend', OBJECT, 'post' ) ) {
            return;
        }
        wp_insert_post(
            array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => 'The art of a slower weekend',
                'post_name' => 'the-art-of-a-slower-weekend',
                'post_excerpt' => 'A few small ways to make a short escape feel like a real reset.',
                'post_content' => '<p>Leave a little room in the itinerary. The best part of a weekend away is often the long breakfast, the walk your host recommends, or the hour you spend watching the light change across the hills.</p><h2>Choose fewer things</h2><p>Pick one place that feels right, stay an extra night if you can, and ask the people who live there what they love about an ordinary day. Those moments make a trip feel local.</p>',
            )
        );
    }

    private static function ensure_products() {
        if ( ! class_exists( 'WC_Product_Simple' ) ) {
            return;
        }
        $stays = get_posts( array( 'post_type' => 'stay', 'post_status' => 'publish', 'numberposts' => -1 ) );
        foreach ( $stays as $stay ) {
            $product_id = absint( get_post_meta( $stay->ID, '_aster_product_id', true ) );
            if ( $product_id && 'publish' === get_post_status( $product_id ) ) {
                continue;
            }
            $product = new WC_Product_Simple();
            $price = (float) get_post_meta( $stay->ID, '_aster_price', true );
            $product->set_name( $stay->post_title . ' · stay booking' );
            $product->set_status( 'publish' );
            $product->set_virtual( true );
            $product->set_catalog_visibility( 'hidden' );
            $product->set_regular_price( (string) $price );
            $product->set_price( (string) $price );
            $product->set_sold_individually( false );
            $product_id = $product->save();
            if ( $product_id ) {
                update_post_meta( $stay->ID, '_aster_product_id', $product_id );
            }
        }
    }

    private static function destination_data() {
        return array(
            'goa' => 'Goa',
            'coorg' => 'Coorg',
            'rishikesh' => 'Rishikesh',
            'jaipur' => 'Jaipur',
            'manali' => 'Manali',
            'udaipur' => 'Udaipur',
        );
    }

    private static function stay_data() {
        $image = get_template_directory_uri() . '/assets/images/';
        return array(
            array(
                'slug' => 'casa-serena',
                'title' => 'Casa Serena',
                'summary' => 'A sun-washed Goan home tucked between the palms and the sea.',
                'description' => '<p>Casa Serena is made for long, unhurried days. A shaded courtyard opens into a breezy living room, while the beach is an easy walk away. Your host can arrange a home-cooked breakfast and point you towards the quieter stretches of coast.</p><p>Spend the morning by the plunge pool, wander out for lunch, then come back in time for the light to turn gold across the garden.</p>',
                'region' => 'goa',
                'amenities' => array( 'Sea view', 'Private pool', 'Breakfast', 'Garden', 'Pet friendly' ),
                'meta' => array( '_aster_price' => 12800, '_aster_guests' => 4, '_aster_bedrooms' => 2, '_aster_bathrooms' => 2, '_aster_rating' => 4.96, '_aster_badge' => 'Guest favourite', '_aster_image' => $image . 'goa.jpg', '_aster_amenities' => 'Private plunge pool, Garden breakfast, Beach walk, Pet friendly', '_aster_featured' => 1 ),
            ),
            array(
                'slug' => 'mosswood-cabin',
                'title' => 'Mosswood Cabin',
                'summary' => 'A quiet wooden cabin in the coffee country of Coorg.',
                'description' => '<p>Set on a working coffee estate, Mosswood has wide windows, a reading nook and an outdoor deck that looks into the canopy. Wake up to birdsong, take a guided estate walk, and end the day around a small fire.</p>',
                'region' => 'coorg',
                'amenities' => array( 'Fireplace', 'Mountain view', 'Breakfast', 'Garden', 'Workspace' ),
                'meta' => array( '_aster_price' => 9400, '_aster_guests' => 3, '_aster_bedrooms' => 1, '_aster_bathrooms' => 1, '_aster_rating' => 4.91, '_aster_badge' => 'Estate hideaway', '_aster_image' => $image . 'coorg.jpg', '_aster_amenities' => 'Coffee estate walks, Fireplace, Breakfast basket, Fast Wi-Fi', '_aster_featured' => 1 ),
            ),
            array(
                'slug' => 'riverbend-house',
                'title' => 'Riverbend House',
                'summary' => 'A light-filled riverside stay just outside Rishikesh.',
                'description' => '<p>Riverbend House is a calm base for early walks, river dips and slow lunches. The generous terrace looks towards the foothills, and your host can help arrange a local guide or a private yoga session.</p>',
                'region' => 'rishikesh',
                'amenities' => array( 'River view', 'Yoga deck', 'Breakfast', 'Workspace' ),
                'meta' => array( '_aster_price' => 7600, '_aster_guests' => 5, '_aster_bedrooms' => 2, '_aster_bathrooms' => 2, '_aster_rating' => 4.89, '_aster_badge' => 'Riverside calm', '_aster_image' => $image . 'rishikesh.jpg', '_aster_amenities' => 'Private yoga deck, River-view terrace, Local guide, Breakfast', '_aster_featured' => 1 ),
            ),
            array(
                'slug' => 'the-peacock-room',
                'title' => 'The Peacock Room',
                'summary' => 'A restored haveli suite in the old lanes of Jaipur.',
                'description' => '<p>Warm pink plaster, hand-blocked linens and a small private terrace make this suite a lovely way to experience the old city. Breakfast is served downstairs in a quiet courtyard before the markets wake up.</p>',
                'region' => 'jaipur',
                'amenities' => array( 'Breakfast', 'Chef service', 'Garden', 'Workspace' ),
                'meta' => array( '_aster_price' => 6800, '_aster_guests' => 2, '_aster_bedrooms' => 1, '_aster_bathrooms' => 1, '_aster_rating' => 4.94, '_aster_badge' => 'Old-city charm', '_aster_image' => $image . 'jaipur.jpg', '_aster_amenities' => 'Courtyard breakfast, Walking tour, Rooftop dining, Wi-Fi', '_aster_featured' => 1 ),
            ),
            array(
                'slug' => 'pine-and-pause',
                'title' => 'Pine & Pause',
                'summary' => 'A glass-fronted mountain home above the Kullu valley.',
                'description' => '<p>Designed to bring the outdoors in, Pine & Pause looks over layers of forest and snow-bright ridgelines. The wood stove, deep window seats and long dining table make it just as inviting on a misty afternoon as on a blue-sky morning.</p>',
                'region' => 'manali',
                'amenities' => array( 'Fireplace', 'Mountain view', 'Garden', 'Workspace', 'Pet friendly' ),
                'meta' => array( '_aster_price' => 11200, '_aster_guests' => 6, '_aster_bedrooms' => 3, '_aster_bathrooms' => 2, '_aster_rating' => 4.97, '_aster_badge' => 'Mountain favourite', '_aster_image' => $image . 'manali.jpg', '_aster_amenities' => 'Wood stove, Forest trail, Chef on request, Fast Wi-Fi', '_aster_featured' => 1 ),
            ),
            array(
                'slug' => 'lakehouse-no-7',
                'title' => 'Lakehouse No. 7',
                'summary' => 'A heritage home with an intimate view of Lake Pichola.',
                'description' => '<p>This restored city home sits a few steps from the water. It pairs old stone details with quiet, thoughtful comforts. Ask the rooftop team to plan a sunset boat ride or save you a table at a neighbourhood favourite.</p>',
                'region' => 'udaipur',
                'amenities' => array( 'Lake view', 'Breakfast', 'Chef service', 'Workspace' ),
                'meta' => array( '_aster_price' => 15400, '_aster_guests' => 4, '_aster_bedrooms' => 2, '_aster_bathrooms' => 2, '_aster_rating' => 4.93, '_aster_badge' => 'Aster collection', '_aster_image' => $image . 'udaipur.jpg', '_aster_amenities' => 'Lake-view roof terrace, Sunset boat ride, Breakfast, Concierge', '_aster_featured' => 1 ),
            ),
        );
    }
}
