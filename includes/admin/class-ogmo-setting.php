<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ogmo_Admin_Setting
{
    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'dbi_add_settings_page'));
        add_action( 'admin_init', array(__CLASS__, 'dbi_register_settings') );
    }

    public static function dbi_add_settings_page() {
        add_options_page( 'OGMO page', 'OGMO Menu', 'manage_options', 'dbi-example-plugin', array(__CLASS__, 'dbi_render_plugin_settings_page' ) );
    }
    
    public static function dbi_render_plugin_settings_page() {
        ?>
        <h2>OGMO Settings</h2>
        <form action="options.php" method="post">
            <?php 
            settings_fields( 'dbi_example_plugin_options' );
            do_settings_sections( 'dbi_example_plugin' ); ?>
            <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
        </form>
        <?php
    }

    public static function dbi_register_settings() {
        register_setting( 'dbi_example_plugin_options', 'dbi_example_plugin_options', array(__CLASS__,'dbi_example_plugin_options_validate' ) );
        add_settings_section( 'api_settings', 'OGMO Settings', array(__CLASS__, 'dbi_plugin_section_text' ), 'dbi_example_plugin' );
    
        add_settings_field( 'dbi_plugin_setting_api_key', 'OGMO Key', array(__CLASS__, 'dbi_plugin_setting_api_key' ), 'dbi_example_plugin', 'api_settings' );
        add_settings_field( 'dbi_plugin_setting_results_limit', 'OGMO Results Limit', array(__CLASS__, 'dbi_plugin_setting_results_limit' ), 'dbi_example_plugin', 'api_settings' );
        add_settings_field( 'dbi_plugin_setting_start_date', 'OGMO Start Date', array(__CLASS__, 'dbi_plugin_setting_start_date' ), 'dbi_example_plugin', 'api_settings' );
        add_settings_field( 'dbi_plugin_setting_button', 'Reconfigure button in cart', array(__CLASS__, 'dbi_plugin_setting_button' ), 'dbi_example_plugin', 'api_settings' );
        add_settings_field( 'dbi_plugin_setting_button_text', 'Reconfigure button text', array(__CLASS__, 'dbi_plugin_setting_button_text' ), 'dbi_example_plugin', 'api_settings' );
        add_settings_field( 'dbi_plugin_setting_append_hook', 'Append options to hook', array(__CLASS__, 'dbi_plugin_setting_append_hook' ), 'dbi_example_plugin', 'api_settings' );
    }
    
    
    function dbi_example_plugin_options_validate( $input ) {
        return $input;
    }
    
    function dbi_plugin_section_text() {
        echo '<p>Here you can set all the options for using the API</p>';
    }
    
    function dbi_plugin_setting_api_key() {
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<input id='dbi_plugin_setting_api_key' name='dbi_example_plugin_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
    }
    
    function dbi_plugin_setting_results_limit() {
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<input id='dbi_plugin_setting_results_limit' name='dbi_example_plugin_options[results_limit]' type='text' value='" . esc_attr( $options['results_limit'] ) . "' />";
    }
    
    function dbi_plugin_setting_start_date() {
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<input id='dbi_plugin_setting_start_date' name='dbi_example_plugin_options[start_date]' type='text' value='" . esc_attr( $options['start_date'] ) . "' />";
    }

    function dbi_plugin_setting_button(){
        $options = get_option( 'dbi_example_plugin_options' );
        echo '<input type="checkbox" id="dbi_plugin_checkbox_element" name="dbi_example_plugin_options[check_box]" value="1"' . checked( 1, $options['check_box'], false ) . '/>';
    }

    function dbi_plugin_setting_button_text(){
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<input id='dbi_plugin_setting_button_text' name='dbi_example_plugin_options[button_text]' type='text' value='" . esc_attr( $options['button_text'] ) . "' />";
    }

    function dbi_plugin_setting_append_hook(){
        $options = get_option( 'dbi_example_plugin_options' );

        $available_hooks = array(
            'woocommerce_before_add_to_cart_button',
            'woocommerce_after_add_to_cart_button'
        );

        echo '<select name="dbi_example_plugin_options[add_action]" style="width:100%;">';

        foreach ( $available_hooks as $key ) {
            $is_selected = ( $options['add_action'] === $key ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $is_selected ) . '>' . esc_attr( $key ) . '</option>';
        }
        echo '</select>';
    }

}

Ogmo_Admin_Setting::init();

