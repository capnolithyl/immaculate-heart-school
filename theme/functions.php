<?php
/**
 * IHM Grade School child-theme setup.
 *
 * Shared behavior and reusable blocks stay in the IHM parent theme.
 *
 * @package IHM_Grade_School
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'IHMS_VERSION' ) ) {
    define( 'IHMS_VERSION', '0.1.0' );
}

/**
 * Load school-only overrides after the parent's compiled Tailwind stylesheet.
 *
 * The parent must enqueue its own get_template_directory_uri() . '/style.css'
 * rather than get_stylesheet_uri(), which points at this child when active.
 */
function ihms_enqueue_styles(): void {
    wp_enqueue_style(
        'ihms-style',
        get_stylesheet_uri(),
        array( 'ihm-style' ),
        IHMS_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'ihms_enqueue_styles', 20 );

// Add school-only hooks and overrides here without copying parent behavior.
