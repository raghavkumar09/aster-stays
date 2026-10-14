<?php
/**
 * Plugin Name: Aster Stays Core
 * Description: Stay listings, enquiry capture, filters, demo content and WooCommerce booking details for Aster Stays.
 * Version: 1.0.0
 * Requires at least: 6.1
 * Requires PHP: 8.1
 * Author: Aster Stays
 * Text Domain: aster-stays
 */

defined( 'ABSPATH' ) || exit;

define( 'ASTER_STAYS_VERSION', '1.0.0' );
define( 'ASTER_STAYS_FILE', __FILE__ );
define( 'ASTER_STAYS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ASTER_STAYS_URL', plugin_dir_url( __FILE__ ) );

require_once ASTER_STAYS_PATH . 'includes/class-post-types.php';
require_once ASTER_STAYS_PATH . 'includes/class-admin.php';
require_once ASTER_STAYS_PATH . 'includes/class-shortcodes.php';
require_once ASTER_STAYS_PATH . 'includes/class-booking.php';
require_once ASTER_STAYS_PATH . 'includes/class-seeder.php';

final class Aster_Stays {
    public static function boot() {
        add_action( 'init', array( 'Aster_Stays_Post_Types', 'register' ) );
        add_action( 'init', array( 'Aster_Stays_Admin', 'register' ), 20 );
        add_action( 'init', array( 'Aster_Stays_Shortcodes', 'register' ), 20 );
        add_action( 'init', array( 'Aster_Stays_Seeder', 'maybe_seed' ), 90 );
        Aster_Stays_Booking::register_hooks();
    }

    public static function activate() {
        Aster_Stays_Post_Types::register();
        Aster_Stays_Seeder::seed();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

add_action( 'plugins_loaded', array( 'Aster_Stays', 'boot' ) );
register_activation_hook( __FILE__, array( 'Aster_Stays', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Aster_Stays', 'deactivate' ) );
