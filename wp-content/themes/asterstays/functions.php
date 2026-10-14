<?php
defined( 'ABSPATH' ) || exit;

function asterstays_setup() {
    load_theme_textdomain( 'asterstays', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'editor-styles' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) );
    add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 600, 'single_image_width' => 1200 ) );
    register_nav_menus( array( 'primary' => __( 'Primary navigation', 'asterstays' ), 'footer' => __( 'Footer navigation', 'asterstays' ) ) );
}
add_action( 'after_setup_theme', 'asterstays_setup' );

function asterstays_enqueue_assets() {
    $theme_path = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $style = $theme_path . '/assets/css/site.css';
    $script = $theme_path . '/assets/js/site.js';
    wp_enqueue_style( 'asterstays-theme', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
    wp_enqueue_style( 'asterstays-site', $theme_uri . '/assets/css/site.css', array( 'asterstays-theme' ), file_exists( $style ) ? filemtime( $style ) : wp_get_theme()->get( 'Version' ) );
    wp_enqueue_script( 'asterstays-site', $theme_uri . '/assets/js/site.js', array(), file_exists( $script ) ? filemtime( $script ) : wp_get_theme()->get( 'Version' ), true );
}
add_action( 'wp_enqueue_scripts', 'asterstays_enqueue_assets' );

function asterstays_fallback_menu() {
    echo '<ul class="aster-menu"><li><a href="' . esc_url( get_post_type_archive_link( 'stay' ) ) . '">' . esc_html__( 'Stays', 'asterstays' ) . '</a></li>';
    foreach ( array( 'about' => __( 'Our story', 'asterstays' ), 'journal' => __( 'Journal', 'asterstays' ), 'contact' => __( 'Contact', 'asterstays' ) ) as $slug => $label ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            echo '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( $label ) . '</a></li>';
        }
    }
    echo '</ul>';
}
