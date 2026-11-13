<?php
/**
 * Desktop school navigation.
 *
 * The school has ONE editable menu, shared with its mobile drawer. The
 * existing Header Links location is preferred; Primary is a compatibility
 * fallback when the former has not yet been assigned on an older site.
 *
 * @package IHM_Grade_School
 */

$ihms_menu_location = ihms_navigation_location();
if ( ! $ihms_menu_location ) {
    return;
}
?>
<nav class="ihms-header__links hidden min-w-0 lg:block"
     aria-label="<?php esc_attr_e( 'School navigation', 'ihm-school' ); ?>">
    <?php
    wp_nav_menu(
        array(
            'theme_location' => $ihms_menu_location,
            'menu_class'     => 'ihms-header__menu',
            'container'      => false,
            'fallback_cb'    => false,
            'depth'          => 2,
        )
    );
    ?>
</nav>
