<?php
/**
 * School header: Figma green contact strip and single navigation bar.
 *
 * Contact/social data is sourced from parent-managed ACF Theme Settings.
 * The Header Links menu is shared by desktop and the mobile drawer.
 *
 * @package IHM_Grade_School
 */

$ihms_address = function_exists( 'get_field' ) ? (string) get_field( 'parish_address', 'option' ) : '';
$ihms_phone   = function_exists( 'get_field' ) ? (string) get_field( 'parish_phone', 'option' ) : '';
$ihms_maps    = function_exists( 'get_field' ) ? (string) get_field( 'parish_directions_url', 'option' ) : '';
$ihms_tel     = $ihms_phone ? preg_replace( '/[^0-9+]/', '', $ihms_phone ) : '';
$ihms_menu    = ihms_navigation_location();
?>
<header id="masthead"
        class="ihms-header group sticky top-(--ihm-admin-bar-sticky-offset) z-50"
        data-site-header data-scrolled="false">
    <div class="ihms-header__bar h-10.5 text-white">
        <div class="ihms-header__contact-row mx-auto grid h-full w-full max-w-7xl grid-cols-[minmax(0,1fr)_auto] items-center gap-3 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] lg:px-10 xl:px-12">
            <?php if ( $ihms_address ) : ?>
                <div class="ihms-header__address hidden min-w-0 items-center gap-3 text-base leading-6 text-slate-300 lg:flex">
                    <i class="fa-solid fa-location-dot shrink-0" aria-hidden="true"></i>
                    <?php if ( $ihms_maps ) : ?>
                        <a class="truncate hover:text-white hover:underline" href="<?php echo esc_url( $ihms_maps ); ?>"><?php echo esc_html( preg_replace( '/\s+/', ' ', $ihms_address ) ); ?></a>
                    <?php else : ?>
                        <span class="truncate"><?php echo esc_html( preg_replace( '/\s+/', ' ', $ihms_address ) ); ?></span>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <span class="hidden lg:block" aria-hidden="true"></span>
            <?php endif; ?>

            <?php if ( $ihms_phone && $ihms_tel ) : ?>
                <a class="ihms-header__phone inline-flex min-w-0 items-center gap-2 text-xs leading-6 text-slate-300 hover:text-white hover:underline sm:text-sm lg:justify-self-center lg:text-base"
                   href="tel:<?php echo esc_attr( $ihms_tel ); ?>">
                    <i class="fa-solid fa-phone shrink-0" aria-hidden="true"></i>
                    <span class="whitespace-nowrap"><?php echo esc_html( $ihms_phone ); ?></span>
                </a>
            <?php else : ?>
                <span aria-hidden="true"></span>
            <?php endif; ?>
            <?php ihms_social_links( 'ihms-header__social justify-self-end gap-1 sm:gap-3' ); ?>
        </div>
    </div>

    <div class="ihms-header__nav h-19 lg:h-25.5">
        <div class="mx-auto flex h-full w-full max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-10 xl:px-12">
            <a class="block shrink-0" href="<?php echo esc_url( home_url( '/' ) ); ?>"
               rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                <?php ihms_school_logo(); ?>
            </a>

            <?php if ( $ihms_menu ) : ?>
                <?php get_template_part( 'template-parts/header/utility', 'nav' ); ?>

                <button type="button"
                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-sm text-white hover:text-gold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold lg:hidden"
                        aria-controls="primary-navigation-panel"
                        aria-expanded="false"
                        aria-label="<?php esc_attr_e( 'Open school navigation', 'ihm-school' ); ?>"
                        data-nav-toggle>
                    <span class="flex w-7 flex-col gap-1.5" aria-hidden="true">
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                    </span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php if ( $ihms_menu ) : ?>
        <?php get_template_part( 'template-parts/header/primary', 'nav' ); ?>
    <?php endif; ?>
</header>
