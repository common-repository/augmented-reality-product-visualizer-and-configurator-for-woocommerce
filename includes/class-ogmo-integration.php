<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ogmo_Integration' ) ) :


	class Ogmo_Integration extends WC_Integration {

		public function __construct() {
			$this->id                 = 'ogmo';
			$this->method_title       = __( 'OGMO', 'ogmo' );
            $this->method_description = __( 'Integrate Ogmo into WooCommerce. These credentials will be used to allow the integration of Ogmo with your store. By entering your Ogmo credentials your store will be able to communicate with the Ogmo API',
                'ogmo' );

			// Define user set variables.
            $this->username = $this->get_option( 'username' );
            $this->password = $this->get_option( 'password' );
			$this->user_id = $this->get_option( 'user_id' );
			$this->platform_id = $this->get_option( 'platform_id' );
			$this->client_id = $this->get_option( 'client_id' );
			$this->id_token = $this->get_option( 'id_token' );
			$this->access_token = $this->get_option( 'access_token' );
			$this->refresh_token = $this->get_option( 'refresh_token' );
			$this->consumer_key = $this->get_option( 'consumer_key' );
			$this->consumer_secret = $this->get_option( 'consumer_secret' );

			//Actions.
			add_action( 'woocommerce_update_options_integration_' . $this->id,
				array( $this, 'process_admin_options' ) );
		}
	}

endif;