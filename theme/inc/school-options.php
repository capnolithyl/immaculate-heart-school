<?php
/**
 * School-only editable settings attached beneath the parent's Theme Settings.
 *
 * @package IHM_Grade_School
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ihms_register_school_settings(): void {
    if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_options_sub_page( array(
        'page_title' => __( 'School Header & Footer', 'ihm-school' ),
        'menu_title' => __( 'School', 'ihm-school' ),
        'menu_slug'  => 'ihms-school-settings',
        'parent_slug'=> 'theme-settings',
        'capability' => 'manage_options',
        'post_id'    => 'option',
    ) );

    acf_add_local_field_group( array(
        'key'      => 'group_ihms_settings',
        'title'    => __( 'School Header & Footer', 'ihm-school' ),
        'fields'   => array(
            array(
                'key' => 'field_ihms_logo',
                'label' => __( 'School logo (optional)', 'ihm-school' ),
                'name' => 'school_logo',
                'type' => 'image',
                'return_format' => 'id',
                'instructions' => __( 'When empty, use the logo from the Figma design bundled with the school theme.', 'ihm-school' ),
            ),
            array(
                'key' => 'field_ihms_tour_link',
                'label' => __( 'Schedule a Tour link', 'ihm-school' ),
                'name' => 'school_tour_link',
                'type' => 'link',
                'return_format' => 'array',
            ),
            array(
                'key' => 'field_ihms_dropoff',
                'label' => __( 'Drop Off Schedule', 'ihm-school' ),
                'name' => 'school_drop_off_schedule',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => __( 'Add schedule time', 'ihm-school' ),
                'sub_fields' => array(
                    array(
                        'key' => 'field_ihms_dropoff_time',
                        'label' => __( 'Time', 'ihm-school' ),
                        'name' => 'time',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_ihms_dropoff_label',
                        'label' => __( 'Description', 'ihm-school' ),
                        'name' => 'description',
                        'type' => 'text',
                        'required' => 1,
                    ),
                ),
            ),
        ),
        'location' => array( array( array(
            'param' => 'options_page',
            'operator' => '==',
            'value' => 'ihms-school-settings',
        ) ) ),
        'active' => true,
    ) );
}
add_action( 'acf/init', 'ihms_register_school_settings', 25 );
