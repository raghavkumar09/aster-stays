<?php
defined( 'ABSPATH' ) || exit;
$stays_url = get_post_type_archive_link( 'stay' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="aster-topline"><span>A little more local. A little less ordinary.</span><a href="<?php echo esc_url( $stays_url ); ?>">Thoughtful stays, found slowly <span aria-hidden="true">↗</span></a></div>
<header class="aster-header"><div class="aster-header__inner aster-container">
<a class="aster-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Aster Stays home"><span class="aster-brand__mark" aria-hidden="true">a</span><span class="aster-brand__word">aster<span>stays</span></span></a>
<button class="aster-menu-toggle" type="button" aria-expanded="false" aria-controls="aster-primary-nav"><span></span><span></span><span class="screen-reader-text">Open menu</span></button>
<nav class="aster-nav" id="aster-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'asterstays' ); ?>"><?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'aster-menu', 'fallback_cb' => 'asterstays_fallback_menu', 'depth' => 1 ) ); ?><a class="aster-header__cta" href="<?php echo esc_url( $stays_url ); ?>"><?php esc_html_e( 'Find your stay', 'asterstays' ); ?><span aria-hidden="true">↗</span></a></nav>
</div></header>
<main id="main-content">
