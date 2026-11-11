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
    define( 'IHMS_VERSION', '0.3.0' );
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

/**
 * School-only options, header logo and social links.
 * All of this functionality lives in the school child theme.
 */
require_once get_stylesheet_directory() . '/inc/school-options.php';

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
 * Shared school header/footer social-link renderer using existing IHM options.
 */
function ihms_social_links( string $class = '' ): void {
    $items = function_exists( 'get_field' ) ? get_field( 'social_media_links', 'option' ) : array();

    if ( ! is_array( $items ) || ! $items ) {
        return;
    }

    echo '<ul class="' . esc_attr( trim( 'm-0 flex list-none items-center gap-3 p-0 ' . $class ) ) .
        '" aria-label="' . esc_attr__( 'Social media', 'ihm-school' ) . '">';

    foreach ( $items as $item ) {
        $link = $item['link'] ?? array();
        $url  = is_array( $link ) ? ( $link['url'] ?? '' ) : $link;
        $icon = (string) ( $item['icon'] ?? '' );
        if ( ! $url || ! $icon ) {
            continue;
        }
        $label  = is_array( $link ) ? ( $link['title'] ?? 'Social media' ) : 'Social media';
        $target = is_array( $link ) && ( $link['target'] ?? '' ) === '_blank' ? '_blank' : '_self';
        printf(
            '<li><a class="inline-flex size-8 items-center justify-center rounded-full border border-white/20 text-white hover:text-gold focus-visible:outline-2 focus-visible:outline-gold" href="%s" aria-label="%s" target="%s"%s>%s</a></li>',
            esc_url( $url ),
            esc_attr( $label ),
            esc_attr( $target ),
            '_blank' === $target ? ' rel="noopener noreferrer"' : '',
            wp_kses( $icon, array( 'i' => array( 'class' => true, 'aria-hidden' => true ) ) )
        );
    }
    echo '</ul>';
}
