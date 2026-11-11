<?php
/**
 * School-specific footer. No Mass Schedule or parish-only sections.
 *
 * @package IHM_Grade_School
 */

$ihms_address = function_exists( 'get_field' ) ? get_field( 'parish_address', 'option' ) : '';
$ihms_phone = function_exists( 'get_field' ) ? get_field( 'parish_phone', 'option' ) : '';
$ihms_directions = function_exists( 'get_field' ) ? get_field( 'parish_directions_url', 'option' ) : '';
$ihms_schedule = function_exists( 'get_field' ) ? get_field( 'school_drop_off_schedule', 'option' ) : array();
$ihms_privacy = get_privacy_policy_url();
$ihms_tel = $ihms_phone ? preg_replace( '/[^0-9+]/', '', $ihms_phone ) : '';
?>
<footer id="colophon" class="ihms-school-footer not-prose relative isolate overflow-hidden">
    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/ihm-watermark.png' ); ?>"
         alt="" aria-hidden="true" loading="lazy"
         class="pointer-events-none absolute -bottom-32 -right-32 z-0 w-[42rem] max-w-none opacity-[0.045]">
    <div class="relative z-10 mx-auto w-full max-w-7xl px-6 py-12 lg:px-10 xl:px-12">
        <div class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1.35fr_1.5fr]">
            <div>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                   class="inline-block" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    <?php ihms_school_logo(); ?>
                </a>
            </div>
            <nav aria-label="<?php esc_attr_e( 'Quick links', 'ihm-school' ); ?>">
                <h2 class="ihms-school-footer__heading mb-4 font-body text-sm font-bold uppercase"><?php esc_html_e( 'Quick Links', 'ihm-school' ); ?></h2>
                <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <?php wp_nav_menu( array(
                        'theme_location' => 'footer',
                        'container' => false,
                        'menu_class' => 'ihms-school-footer__menu text-sm',
                        'depth' => 1,
                        'fallback_cb' => false,
                    ) ); ?>
                <?php endif; ?>
            </nav>
            <section>
                <h2 class="ihms-school-footer__heading mb-4 font-body text-sm font-bold uppercase"><?php esc_html_e( 'Drop Off Schedule', 'ihm-school' ); ?></h2>
                <?php if ( is_array( $ihms_schedule ) && $ihms_schedule ) : ?>
                    <ul class="m-0 list-none space-y-2 p-0 text-sm">
                        <?php foreach ( $ihms_schedule as $ihms_row ) : ?>
                            <?php if ( empty( $ihms_row['time'] ) && empty( $ihms_row['description'] ) ) { continue; } ?>
                            <li><span aria-hidden="true">• </span><?php echo esc_html( ( $ihms_row['time'] ?? '' ) . ' | ' . ( $ihms_row['description'] ?? '' ) ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
            <section>
                <h2 class="ihms-school-footer__heading mb-4 font-body text-sm font-bold uppercase"><?php esc_html_e( 'Contact Us', 'ihm-school' ); ?></h2>
                <address class="not-italic text-sm leading-relaxed">
                    <?php if ( $ihms_address ) : ?>
                        <p class="mb-3 flex gap-2">
                            <i class="fa-solid fa-location-dot mt-1 shrink-0" aria-hidden="true"></i>
                            <?php if ( $ihms_directions ) : ?>
                                <a href="<?php echo esc_url( $ihms_directions ); ?>"><?php echo nl2br( esc_html( $ihms_address ) ); ?></a>
                            <?php else : ?>
                                <span><?php echo nl2br( esc_html( $ihms_address ) ); ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( $ihms_phone && $ihms_tel ) : ?>
                        <p class="flex gap-2">
                            <i class="fa-solid fa-phone mt-1 shrink-0" aria-hidden="true"></i>
                            <a href="tel:<?php echo esc_attr( $ihms_tel ); ?>"><?php echo esc_html( $ihms_phone ); ?></a>
                        </p>
                    <?php endif; ?>
                </address>
            </section>
        </div>
        <div class="mt-10 flex flex-col items-center justify-between gap-5 border-t border-white/20 pt-5 text-center text-xs sm:flex-row sm:text-left">
            <div class="flex flex-col gap-2">
                <p>&copy; <?php echo esc_html( wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'ihm-school' ); ?></p>
                <?php if ( $ihms_privacy ) : ?><a href="<?php echo esc_url( $ihms_privacy ); ?>"><?php esc_html_e( 'Privacy Policy', 'ihm-school' ); ?></a><?php endif; ?>
            </div>
            <?php ihms_social_links(); ?>
            <p><?php esc_html_e( 'Made with', 'ihm-school' ); ?> <span class="text-gold" aria-label="<?php esc_attr_e( 'love', 'ihm-school' ); ?>">&#9829;</span>
                <?php esc_html_e( 'by', 'ihm-school' ); ?>
                <a class="underline" href="https://www.diocesan.com/" target="_blank" rel="noopener noreferrer">Diocesan</a>
            </p>
        </div>
    </div>
</footer>
