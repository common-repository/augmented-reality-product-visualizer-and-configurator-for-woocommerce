<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ogmo_Product_Options
{
    public static function init()
    {
        add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_shipping_costs_product_data_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'add_shipping_costs_product_data_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'shipping_costs_process_product_meta_fields_save' ) );
        add_action( 'admin_head', array( __CLASS__, 'wcpp_custom_style' ) );
    }

    public static function wcpp_custom_style() {
        $css_path = plugin_dir_url( __DIR__ ) . 'assets/css';
        wp_enqueue_style('ogmo-product-options-icon', $css_path.'/ogmo-product-options-icon.css');
    }

    public static function add_shipping_costs_product_data_tab( $product_data_tabs ) {
        $product_data_tabs['shipping-costs'] = array(
            'label' => __( 'OGMO Options', 'my_theme_domain' ), // translatable
            'target' => 'shipping_costs_product_data', // translatable
        );
        return $product_data_tabs;
    }

    public static function add_shipping_costs_product_data_fields() {
        global $post;
        $post_id = $post->ID;

        $js_path = plugin_dir_url( __DIR__ ) . 'assets/js';
        wp_enqueue_script('crypto', $js_path.'/crypto-js.min.js');
        wp_enqueue_script('oauth', $js_path.'/oauth-1.0a.js');
        wp_enqueue_script('ogmo-api', $js_path.'/ogmo-api.js');
        wp_enqueue_script('jquery-min', $js_path.'/jquery.min.js');
        wp_enqueue_script('select2-min', $js_path.'/select2.min.js');
        wp_enqueue_script('select2_1-min', $js_path.'/select2_1.min.js');

        $css_path = plugin_dir_url( __DIR__ ) . 'assets/css';
        wp_enqueue_style('select2-min-css', $css_path.'/select2.min.css');
        wp_enqueue_style('ogmo-product-options', $css_path.'/ogmo-product-options.css');

        $integration = new Ogmo_Integration();
        $id_token=$integration->get_option('id_token');
        $user_id=$integration->get_option('user_id');
        $refresh_token=$integration->get_option('refresh_token');
        $ogmo_authservice_url = OGMO_AUTHSERVICE_URL;
        $consumer_key = $integration->get_option('consumer_key');
        $consumer_secret = $integration->get_option('consumer_secret');
        $ogmo_api_endpoint = OGMO_API_ENDPOINT;
        $client_id = $integration->get_option('client_id');
        $ogmo_dashboard_url = OGMO_DASHBOARD_URL;
        $select_option_id = get_post_meta( $post_id, '_select_field', true );
        $webservice = new Ogmo_Webservice();
        $design = $webservice->get_design($select_option_id);

        ogmo_get_template('admin/product-options.phtml', 
        array(
            'id_token' => $id_token, 
            'user_id' => $user_id, 
            'post_id' => $post_id, 
            'refresh_token' => $refresh_token, 
            'ogmo_authservice_url' => $ogmo_authservice_url,
            'consumer_key' => $consumer_key, 
            'consumer_secret' => $consumer_secret,
            'ogmo_api_endpoint' => $ogmo_api_endpoint,
            'client_id' => $client_id,
            'ogmo_dashboard_url' => $ogmo_dashboard_url,
        ));

        // Register the script
        wp_register_script( 'ogmo-product-options-js', $js_path.'/ogmo-product-options.js' );
        // Localize the script with new data
        $translation_array = array(
            'id_token' => $id_token, 
            'user_id' => $user_id, 
            'post_id' => $post_id, 
            'refresh_token' => $refresh_token, 
            'ogmo_authservice_url' => $ogmo_authservice_url,
            'consumer_key' => $consumer_key, 
            'consumer_secret' => $consumer_secret,
            'ogmo_api_endpoint' => $ogmo_api_endpoint,
            'client_id' => $client_id,
            'ogmo_dashboard_url' => $ogmo_dashboard_url,
            'select_option_id' => $select_option_id
        );
        wp_localize_script( 'ogmo-product-options-js', 'ogmo_data_object', $translation_array );
        // Enqueued script with localized data.
        wp_enqueue_script( 'ogmo-product-options-js' );
    }

    public static function shipping_costs_process_product_meta_fields_save( $post_id ){
        // save the selector field data
        if( isset( $_POST['_select_field'] ) )
            update_post_meta( $post_id, '_select_field', esc_attr( $_POST['_select_field'] ) );
    }

}

Ogmo_Product_Options::init();