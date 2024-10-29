<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Ogmo_Reconfigure_Btn
{
    public static function init()
    {
        $options = get_option( 'dbi_example_plugin_options' );
        $action = isset(  $options['add_action'] ) ?  $options['add_action'] : '';
        $check = isset(  $options['check_box'] ) ?  $options['check_box'] : '';
        if ($check === '1') {
            if ($action === 'woocommerce_before_add_to_cart_button') {
                add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'ogmo_before_add_to_cart_btn' ) );
            } elseif ($action === 'woocommerce_after_add_to_cart_button') {
                add_action( 'woocommerce_after_add_to_cart_button', array( __CLASS__, 'ogmo_after_add_to_cart_btn' ) );
            }
        } else {
            return;
        }
    }

    public static function ogmo_before_add_to_cart_btn(){
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<button>".esc_attr($options['button_text'])."</button>";
    }

    public static function ogmo_after_add_to_cart_btn(){
        $options = get_option( 'dbi_example_plugin_options' );
        echo "<button>".esc_attr($options['button_text'])."</button>";
    }
}

Ogmo_Reconfigure_Btn::init();