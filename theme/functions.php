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
    define( 'IHMS_VERSION', '0.3.2' );
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

/** Release the mobile drawer if the user resizes to a desktop viewport. */
function ihms_enqueue_navigation(): void {
    wp_enqueue_script(
        'ihms-navigation',
        get_stylesheet_directory_uri() . '/assets/js/school-navigation.js',
        array( 'ihm-script' ),
        IHMS_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'ihms_enqueue_navigation', 30 );

/**
 * Make school-only utilities available to the block/classic editors as well.
 * The parent remains responsible for its own editor stylesheet.
 */
function ihms_register_editor_styles(): void {
    add_editor_style( 'assets/css/school.css' );
}
add_action( 'after_setup_theme', 'ihms_register_editor_styles', 20 );

// Add school-only hooks and overrides here without copying parent behavior.

/**
 * School-only options, header logo and social links.
 * All of this functionality lives in the school child theme.
 */
require_once get_stylesheet_directory() . '/inc/school-options.php';

// The parish-only Mass Times settings page and popup should never appear in
// the school's admin/frontend. Keep the shared parent theme site-agnostic.
add_filter( 'ihm_enable_mass_times_options', '__return_false' );
add_filter( 'ihm_show_mass_schedule', '__return_false' );
/**
 * School-specific floating tour action. Keep the destination editor-managed.
 * Only show the action when a valid link has been configured.
 */
function ihms_render_tour_action(): void {
    if ( ! is_front_page() || ! function_exists( 'get_field' ) ) {
        return;
    }

    $link = get_field( 'school_tour_link', 'option' );
    if ( ! is_array( $link ) || empty( $link['url'] ) ) {
        return;
    }

    $target = ( $link['target'] ?? '' ) === '_blank' ? '_blank' : '_self';
    $label  = ! empty( $link['title'] ) ? $link['title'] : __( 'Schedule a Tour', 'ihm-school' );
    ?>
    <a class="ihms-tour-tab"
       href="<?php echo esc_url( $link['url'] ); ?>"
       target="<?php echo esc_attr( $target ); ?>"
       <?php if ( '_blank' === $target ) : ?>rel="noopener noreferrer"<?php endif; ?>>
       <?php echo esc_html( $label ); ?>
    </a>
    <?php
}
add_action( 'wp_footer', 'ihms_render_tour_action', 5 );



/**
 * Render the Figma logo with an optional school-specific ACF media override.
 */
function ihms_school_logo(): void {
    $image = function_exists( 'get_field' ) ? get_field( 'school_logo', 'option' ) : 0;
    $id    = function_exists( 'ihm_block_attachment_id' ) ? ihm_block_attachment_id( $image ) : absint( $image );

    if ( $id ) {
        $markup = wp_get_attachment_image( $id, 'full', false, array(
            'class'         => 'ihms-header__logo',
            'loading'       => 'eager',
            'fetchpriority' => 'high',
            'alt'           => get_bloginfo( 'name' ),
        ) );
        if ( $markup ) {
            echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            return;
        }
    }

    echo '<img class="ihms-header__logo" src="' .
        esc_url( get_stylesheet_directory_uri() . '/assets/images/school-logo.png' ) .
        '" alt="' . esc_attr( get_bloginfo( 'name' ) ) .
        '" width="209" height="87" fetchpriority="high" loading="eager">';
}

/**
 * One school menu on both screen sizes.
 *
 * Editors already manage the school's main links at Appearance -> Menus ->
 * Header Links. Primary is a compatibility fallback for older school installs;
 * never render both locations in the same header.
 */
function ihms_navigation_location(): string {
    if ( has_nav_menu( 'header' ) ) {
        return 'header';
    }

    return has_nav_menu( 'primary' ) ? 'primary' : '';
}

/**
 * Prefer recognizable, locally rendered brand icons, independent of whether
 * the ACF Font Awesome field has been filled or its icon font has loaded.
 * Unknown platforms use their validated icon markup (if supplied), then a
 * generic link icon. All links and labels still come from the ACF repeater.
 */
function ihms_social_icon_markup( string $url, string $label, string $icon = '' ): string {
    $identifier = strtolower( $url . ' ' . $label . ' ' . $icon );

    if ( false !== strpos( $identifier, 'facebook' ) || false !== strpos( $identifier, 'fb.com' ) ) {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false"><path d="M13.7 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.3-1.5 1.6-1.5H17V3.6a24 24 0 0 0-2.4-.1c-2.5 0-4.2 1.5-4.2 4.3v2.1H7.7V13h2.7v8h3.3z"></path></svg>';
    }

    if ( false !== strpos( $identifier, 'instagram' ) ) {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="2.5" y="2.5" width="19" height="19" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.75" cy="6.5" r="1" fill="currentColor" stroke="none"></circle></svg>';
    }

    if ( false !== strpos( $identifier, 'youtube' ) || false !== strpos( $identifier, 'youtu.be' ) ) {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true" focusable="false"><rect x="2" y="5.5" width="20" height="13" rx="4"></rect><path d="m10 9 5 3-5 3z" fill="currentColor" stroke="none"></path></svg>';
    }

    if ( '' !== $icon ) {
        return wp_kses( $icon, array( 'i' => array( 'class' => true, 'aria-hidden' => true ) ) );
    }

    return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M10 13a5 5 0 0 0 7 .5l3-3a5 5 0 0 0-7-7l-1.7 1.7"></path><path d="M14 11a5 5 0 0 0-7-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"></path></svg>';
}

/**
 * The same editor-managed social sources are used by school top bar, mobile
 * drawer and footer. No site-specific URLs or social accounts in the theme.
 */
function ihms_social_links( string $class = '' ): void {
    $items = function_exists( 'get_field' ) ? get_field( 'social_media_links', 'option' ) : array();

    if ( ! is_array( $items ) || ! $items ) {
        return;
    }

    $valid_items = array_filter( $items, static function ( $item ): bool {
        if ( ! is_array( $item ) ) {
            return false;
        }
        $link = $item['link'] ?? '';
        $url  = is_array( $link ) ? ( $link['url'] ?? '' ) : $link;
        return is_string( $url ) && '' !== trim( $url ) && '' !== esc_url( $url );
    } );

    if ( ! $valid_items ) {
        return;
    }
    ?>
    <ul class="<?php echo esc_attr( trim( 'ihms-social-links m-0 flex list-none items-center gap-3 p-0 ' . $class ) ); ?>"
        aria-label="<?php esc_attr_e( 'Social media', 'ihm-school' ); ?>">
        <?php foreach ( $valid_items as $item ) : ?>
            <?php
            $link   = $item['link'];
            $url    = is_array( $link ) ? $link['url'] : $link;
            $label  = is_array( $link ) && ! empty( $link['title'] ) ? (string) $link['title'] : __( 'Social media', 'ihm-school' );
            $target = is_array( $link ) && ( $link['target'] ?? '' ) === '_blank' ? '_blank' : '_self';
            $icon   = is_string( $item['icon'] ?? null ) ? $item['icon'] : '';
            ?>
            <li class="m-0 list-none p-0">
                <a class="ihms-social-links__link inline-flex size-8 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white hover:text-gold focus-visible:outline-2 focus-visible:outline-gold"
                   href="<?php echo esc_url( $url ); ?>"
                   aria-label="<?php echo esc_attr( $label ); ?>"
                   <?php if ( '_blank' === $target ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                    <?php echo ihms_social_icon_markup( $url, $label, $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static or sanitized icon ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}
