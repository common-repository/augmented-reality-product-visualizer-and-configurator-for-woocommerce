<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ogmo_API {


	public function __construct() {
        // Init REST API routes.
        add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
        add_filter( 'woocommerce_rest_is_request_to_rest_api', array( __CLASS__, 'is_request_to_rest_api' ) );
	}


	public function rest_api_init() {
		$this->rest_api_includes();
		$this->register_rest_routes();
	}

    public static function is_request_to_rest_api($is_api_request) {
        if ($is_api_request) {
            return true;
        }

        $rest_prefix = trailingslashit( rest_get_url_prefix() );


        if (false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix . 'ogmo/' )) {
            return true;
        }
        return false;
    }


	private function rest_api_includes() {
        include_once( 'rest-api/class-ogmo-rest-access-controller.php' );
	}


	private function register_rest_routes() {
		$controllers = array(
            'Ogmo_REST_Access_Controller'
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}
}


