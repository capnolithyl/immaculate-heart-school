<?php
/**
 * Desktop school navigation.
 *
 * The school has ONE editable menu, shared with its mobile drawer.
 * Visibility is controlled by unlayered school CSS. Avoid 'hidden lg:block'
 * here: the parent's unlayered .hidden utility overrides the layered
 * Tailwind v4 lg:block utility from the child stylesheet.
 *
 * @package IHM_Grade_School
 */

$ihms_menu_location = ihms_navigation_location();
if ( ! $ihms_menu_location ) {
    return;
}
?>
<nav class="ihms-header__links min-w-0"
     aria-label="<?php esc_attr_e( 'School navigation', 'ihm-school' ); ?>">
    <?php
    wp_nav_menu(
        array(
            'theme_location' => $ihms_menu_location,
            'menu_id'        => 'school-desktop-menu',
            'menu_class'     => 'ihms-header__menu',
            'container'      => false,
            'fallback_cb'    => false,
            'depth'          => 2,
        )
    );
    ?>
</nav>
