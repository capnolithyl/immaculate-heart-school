<?php
/**
 * Child-theme Header Links: five editable items to the right of the school
 * branding on desktop, and beneath the primary menu inside the mobile drawer.
 *
 * @package IHM_Grade_School
 */

$ihms_locations = get_nav_menu_locations();
$ihms_menu_id = absint( $ihms_locations['header'] ?? 0 );
if ( ! $ihms_menu_id ) {
    return;
}

$ihms_menu_items = wp_get_nav_menu_items( $ihms_menu_id );
if ( ! is_array( $ihms_menu_items ) || ! $ihms_menu_items ) {
    return;
}

$ihms_menu_items = array_values( array_filter( $ihms_menu_items, static function ( $item ) {
    return 0 === (int) $item->menu_item_parent;
} ) );
$ihms_limit = defined( 'IHM_HEADER_LINK_LIMIT' ) ? IHM_HEADER_LINK_LIMIT : 5;
$ihms_menu_items = array_slice( $ihms_menu_items, 0, $ihms_limit );
$ihms_mobile = ! empty( $args['mobile'] );
?>
<?php if ( $ihms_mobile ) : ?>
<nav class="site-nav-drawer__utility mt-7 border-t border-white/20 pt-5 lg:hidden"
     aria-label="<?php esc_attr_e( 'School quick links', 'ihm-school' ); ?>">
    <ul class="m-0 flex list-none flex-col gap-2 p-0">
        <?php foreach ( $ihms_menu_items as $ihms_item ) : ?>
            <?php $ihms_current = in_array( 'current-menu-item', (array) $ihms_item->classes, true ); ?>
            <li class="m-0 list-none p-0">
                <a class="block rounded-sm px-2 py-2 font-body text-base font-medium uppercase text-white hover:text-gold focus-visible:outline-2 focus-visible:outline-gold"
                   href="<?php echo esc_url( $ihms_item->url ); ?>"
                   <?php if ( $ihms_current ) : ?>aria-current="page"<?php endif; ?>
                   <?php if ( $ihms_item->target ) : ?>target="<?php echo esc_attr( $ihms_item->target ); ?>"<?php endif; ?>
                   <?php if ( $ihms_item->xfn || '_blank' === $ihms_item->target ) : ?>
                       rel="<?php echo esc_attr( trim( $ihms_item->xfn . ( '_blank' === $ihms_item->target ? ' noopener noreferrer' : '' ) ) ); ?>"
                   <?php endif; ?>>
                    <?php echo esc_html( $ihms_item->title ); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
<?php else : ?>
<nav class="ihms-header__links hidden min-w-0 items-center justify-end gap-5 lg:flex xl:gap-9"
     aria-label="<?php esc_attr_e( 'School quick links', 'ihm-school' ); ?>">
    <?php foreach ( $ihms_menu_items as $ihms_item ) : ?>
        <?php $ihms_current = in_array( 'current-menu-item', (array) $ihms_item->classes, true ); ?>
        <a class="whitespace-nowrap font-body text-base font-medium uppercase tracking-tight text-white no-underline transition-colors duration-150 xl:text-xl"
           href="<?php echo esc_url( $ihms_item->url ); ?>"
           <?php if ( $ihms_current ) : ?>aria-current="page"<?php endif; ?>
           <?php if ( $ihms_item->target ) : ?>target="<?php echo esc_attr( $ihms_item->target ); ?>"<?php endif; ?>
           <?php if ( $ihms_item->xfn || '_blank' === $ihms_item->target ) : ?>
               rel="<?php echo esc_attr( trim( $ihms_item->xfn . ( '_blank' === $ihms_item->target ? ' noopener noreferrer' : '' ) ) ); ?>"
           <?php endif; ?>>
            <?php echo esc_html( $ihms_item->title ); ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
