<?php
/**
 * School header: Figma green contact strip + dark translucent navigation.
 * The parish's real sliding-drawer navigation JavaScript is reused unchanged.
 *
 * @package IHM_Grade_School
 */

$ihms_address = function_exists( 'get_field' ) ? get_field( 'parish_address', 'option' ) : '';
$ihms_phone   = function_exists( 'get_field' ) ? get_field( 'parish_phone', 'option' ) : '';
$ihms_maps    = function_exists( 'get_field' ) ? get_field( 'parish_directions_url', 'option' ) : '';
$ihms_tel     = $ihms_phone ? preg_replace( '/[^0-9+]/', '', $ihms_phone ) : '';
?>
<header id="masthead"
        class="ihms-header group sticky top-(--ihm-admin-bar-sticky-offset) z-50"
        data-site-header data-scrolled="false">
    <div class="ihms-header__bar h-10.5 text-white">
        <div class="mx-auto flex h-full w-full max-w-7xl items-center justify-between gap-3 px-4 text-xs sm:px-6 lg:px-10 xl:px-12">
            <div class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-1">
                <?php if ( $ihms_address ) : ?>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <?php if ( $ihms_maps ) : ?>
                            <a class="hover:underline" href="<?php echo esc_url( $ihms_maps ); ?>"><?php echo esc_html( preg_replace( '/\s+/', ' ', $ihms_address ) ); ?></a>
                        <?php else : ?>
                            <span><?php echo esc_html( preg_replace( '/\s+/', ' ', $ihms_address ) ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ( $ihms_phone && $ihms_tel ) : ?>
                    <a class="hidden items-center gap-2 hover:underline sm:inline-flex"
                       href="tel:<?php echo esc_attr( $ihms_tel ); ?>">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <?php echo esc_html( $ihms_phone ); ?>
                    </a>
                <?php endif; ?>
            </div>
            <?php ihms_social_links( 'hidden sm:flex' ); ?>
        </div>
    </div>
    <div class="ihms-header__nav h-19 lg:h-25.5">
        <div class="mx-auto flex h-full w-full max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-10 xl:px-12">
            <div class="flex shrink-0 items-center gap-3 lg:gap-5">
                <button type="button"
                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-sm text-white hover:text-gold lg:order-2"
                        aria-controls="primary-navigation-panel"
                        aria-expanded="false"
                        aria-label="<?php esc_attr_e( 'Open main navigation', 'ihm-school' ); ?>"
                        data-nav-toggle>
                    <span class="flex w-8 flex-col gap-1.5" aria-hidden="true">
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                        <span class="h-0.5 w-full bg-current"></span>
                    </span>
                </button>
                <a class="block shrink-0 lg:order-1" href="<?php echo esc_url( home_url( '/' ) ); ?>"
                   rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    <?php ihms_school_logo(); ?>
                </a>
            </div>

            <?php get_template_part( 'template-parts/header/utility', 'nav' ); ?>
        </div>
    </div>
    <?php get_template_part( 'template-parts/header/primary', 'nav' ); ?>
</header>
