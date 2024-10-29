<?php
/*
 * Plugin Name: Augmented Reality & 3D Visualizer
 * Plugin URI:  https://en-gb.wordpress.org/plugins/augmented-reality-product-visualizer-and-configurator-for-woocommerce
 * Description: Specially built for eCommerce, OGMO allows eCommerce users to easily examine digital products with the help of Augmented reality and 3D technology without having the physical product beside, allowing them to customize products according to their preference.
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Requires WooCommerce: 4.0
 * Version: 3.1.0
 * Author: OGMO
 * Author URI: https://ogmo.xyz/
 * License: GPLv2 or later
 * Text Domain: augmented-reality-product-visualizer-and-configurator-for-woocommerce
 *
 * Copyright 2021, by OGMO, Inc.
 * All rights reserved
 *
 * This file is part of Augmented Reality & 3D Visualizer.
 *
 * Augmented Reality & 3D Visualizer is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.
 *
 * Augmented Reality & 3D Visualizer is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with Augmented Reality & 3D Visualizer.  If not, see <https://www.gnu.org/licenses/>
 *
 * OGMO, Inc., hereby disclaims all copyright interest in the program “Augmented Reality & 3D Visualizer” (Woocommerce plugin enables online product visualization and configuration in augmented reality and 3D)  written by OGMO.

 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */

use OgmoEnv\Ogmo_Env;
defined( 'ABSPATH' ) or exit;

/**
 * Define path to plugin file.
 */
if ( ! defined( 'OGMO_PLUGIN_FILE' ) ) {
	define( 'OGMO_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'Ogmo' ) ) :


	class Ogmo {

		const CAPABILITY = 'manage_ogmo';

		public $version = '3.0';


		private $api;

		protected static $_instance = null;


		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function construct() {
            $this->define_constants();

            if ( self::woocommerce_did_load() ) {
                $this->includes();

                register_activation_hook( __FILE__, array( $this, 'activate' ) );
                register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
            }
        }

		public function __construct() {
		    if ( self::woocommerce_did_load() ) {
		        $this->construct();
				
            } else {
                add_action( 'plugins_loaded' , array( $this, 'construct' ) );
            }
		}


		public function activate() {
            GLOBAL $wp_rewrite;
			add_option('ogmo_do_activation_redirect', true);
            $wp_rewrite->flush_rules(false);
        }

        public function deactivate() {
			try {
				$webservice = new Ogmo_Webservice();
				$webservice->update_plugin_status();
			}
			catch(Exception $e) {
				// echo 'Message: ' .$e->getMessage();
				null;
			}

			$integration = new Ogmo_Integration();
			$integration->update_option('username', '');
            $integration->update_option('password', '');
			$integration->update_option('user_id', '');
			$integration->update_option('platform_id', '');
			$integration->update_option('client_id', '');
			$integration->update_option('id_token', '');
			$integration->update_option('access_token', '');
            $integration->update_option('refresh_token', '');
			$integration->update_option('consumer_key', '');
            $integration->update_option('consumer_secret', '');
        }

		private static function woocommerce_did_load() {
		    return function_exists( 'WC' ) && version_compare( WC()->version, 3.4, '>=' );
        }


		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}


		private function define_constants() {
			require __DIR__ . '/includes/class-ogmo-env.php';
			(new Ogmo_Env(__DIR__ . '/.env'))->load();

			$this->define( '3.1.0', $this->version );
            $this->define( 'OGMO_DASHBOARD_URL', getenv('OGMO_DASHBOARD_URL') );
            $this->define( 'OGMO_API_ENDPOINT', getenv('OGMO_API_ENDPOINT') );
			$this->define( 'OGMO_AUTHSERVICE_URL', getenv('OGMO_AUTHSERVICE_URL') );
			$this->define( 'OGMO_DOCS_URL', getenv('OGMO_DOCS_URL') );
			$this->define( 'OGMO_VIEWER_URL', getenv('OGMO_VIEWER_URL') );
		}
		
		public function includes() {
			include_once( 'includes/class-ogmo-api.php' );
			include_once( 'includes/class-ogmo-install.php' );
			include_once( 'includes/class-ogmo-webservice.php' );
			include_once( 'includes/class-ogmo-integration.php' );
			include_once( 'includes/auth/class-ogmo-auth.php' );
			include_once( 'includes/class-ogmo-product-options.php' );
			include_once( 'includes/class-ogmo-core-functions.php' );
			include_once( 'includes/class-ogmo-scene.php' );
			include_once( 'includes/class-ogmo-reconfigure-btn.php' );
			
			
			if ( is_admin() ) {
                include_once( 'includes/admin/class-ogmo-admin-home.php' );
				include_once( 'includes/admin/class-ogmo-setting.php' );
				include_once( 'includes/class-ogmo-app.php' );
			}

			$this->api = new Ogmo_API();
		}


		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		public function settings_url() {
			$settings_url = admin_url( 'admin.php?page=wc-settings&tab=integration&section=ogmo' );

			return $settings_url;
		}
	}

endif;

/**
 * Main instance of Ogmo.
 *
 * Returns the main instance of Ogmo to prevent the need to use globals.
 *
 * @return Ogmo
 */
function get_ogmo() {
	return Ogmo::instance();
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ) {
	return;
}else if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ) {
	$GLOBALS['ogmo'] = get_ogmo();
}else{
	deactivate_plugins(OGMO_PLUGIN_FILE);
	die(
		esc_html( __( 'To activate OGMO, Please make sure the WooCommerce plugin is active: ', 'ogmo' ) ) .
		'<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>'
	);
}
