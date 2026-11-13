<?php
/**
 * School's mobile-only navigation drawer.
 *
 * WordPress uses the SAME menu location here and on desktop. We preserve
 * the shared parent navigation JavaScript's data attributes and menu IDs,
 * so nested submenus, keyboard focus, Escape and panel animations keep
 * working without copying the parent's behavior into the child.
 *
 * @package IHM_Grade_School
 */

$ihms_menu_location = ihms_navigation_location();
if ( ! $ihms_menu_location ) {
    return;
}

$ihms_search_id = wp_unique_id( 'ihms-menu-search-' );
?>
<div id="primary-navigation-panel"
     class="site-nav-drawer fixed inset-x-0 bottom-0 top-(--ihm-admin-bar-sticky-offset) z-100 font-body lg:hidden"
     data-nav-drawer
     data-nav-back-label="<?php esc_attr_e( 'Back to', 'ihm-school' ); ?>"
     data-nav-back-text="<?php esc_attr_e( 'Back', 'ihm-school' ); ?>"
     data-nav-open-submenu-label="<?php esc_attr_e( 'Open submenu for', 'ihm-school' ); ?>"
     aria-hidden="true"
     hidden>
    <button type="button"
            class="site-nav-drawer__backdrop absolute inset-0 h-full w-full cursor-pointer border-0 bg-black/55 p-0"
            aria-label="<?php esc_attr_e( 'Close school navigation', 'ihm-school' ); ?>"
            data-nav-close></button>

    <div class="site-nav-drawer__stack pointer-events-none absolute inset-0"
         role="dialog" aria-modal="true"
         aria-label="<?php esc_attr_e( 'School navigation', 'ihm-school' ); ?>"
         data-nav-stack>
        <nav class="site-nav-drawer__panel site-nav-drawer__panel--root pointer-events-auto absolute inset-y-0 left-0 z-10 flex min-h-0 flex-col bg-navy text-white"
             aria-label="<?php esc_attr_e( 'School menu', 'ihm-school' ); ?>"
             data-nav-root>
            <button type="button"
                    class="site-nav-drawer__close absolute top-3 right-5 z-20 flex size-9 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent p-0 text-gold hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-gold-light"
                    aria-label="<?php esc_attr_e( 'Close school navigation', 'ihm-school' ); ?>"
                    data-nav-close>
                <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
                    <path d="M5 5l14 14M19 5L5 19"></path>
                </svg>
            </button>

            <div class="site-nav-drawer__menu-scroll min-h-0 flex-1 overflow-x-hidden overflow-y-auto px-6 pb-4"
                 data-nav-menu>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => $ihms_menu_location,
                        'menu_id'        => 'primary-menu',
                        'menu_class'     => 'site-nav-drawer__list m-0 w-full list-none p-0',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'depth'          => 0,
                    )
                );
                ?>
            </div>

            <div class="site-nav-drawer__bottom flex shrink-0 flex-col items-center gap-5 px-6 pb-6">
                <form class="site-nav-drawer__search flex h-10 w-full min-w-0 items-center overflow-hidden rounded-full bg-white/10 focus-within:outline-2 focus-within:outline-gold-light"
                      role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <label class="screen-reader-text" for="<?php echo esc_attr( $ihms_search_id ); ?>">
                        <?php esc_html_e( 'Search the school site', 'ihm-school' ); ?>
                    </label>
                    <button type="submit"
                            class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-full border border-white/20 bg-white/10 p-0 text-white"
                            aria-label="<?php esc_attr_e( 'Search', 'ihm-school' ); ?>">
                        <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
                            <circle cx="10.5" cy="10.5" r="6.5"></circle>
                            <path d="m15.5 15.5 5 5"></path>
                        </svg>
                    </button>
                    <input id="<?php echo esc_attr( $ihms_search_id ); ?>"
                           class="h-10 w-full min-w-0 border-0 bg-transparent px-3 font-body text-base text-white outline-none placeholder:text-white/60"
                           type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
                           placeholder="<?php esc_attr_e( 'Search', 'ihm-school' ); ?>">
                </form>
                <?php ihms_social_links( 'justify-center gap-4' ); ?>
            </div>
        </nav>
        <div class="site-nav-drawer__submenus pointer-events-none absolute inset-0"
             data-nav-submenus></div>
    </div>
</div>
