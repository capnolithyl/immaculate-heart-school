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
    define( 'IHMS_VERSION', '0.2.0' );
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

    // Only the child's new utilities; the parent handles global utilities/reset.
    $school_css_path = get_stylesheet_directory() . '/assets/css/school.css';

    if ( is_readable( $school_css_path ) ) {
        wp_enqueue_style(
            'ihms-tailwind',
            get_stylesheet_directory_uri() . '/assets/css/school.css',
            array( 'ihms-style' ),
            (string) filemtime( $school_css_path )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'ihms_enqueue_styles', 20 );

/**
 * Make school-only utilities available to the block/classic editors as well.
 * The parent remains responsible for its own editor stylesheet.
 */
function ihms_register_editor_styles(): void {
    add_editor_style( 'assets/css/school.css' );
}
add_action( 'after_setup_theme', 'ihms_register_editor_styles', 20 );

// Add school-only hooks and overrides here without copying parent behavior.
