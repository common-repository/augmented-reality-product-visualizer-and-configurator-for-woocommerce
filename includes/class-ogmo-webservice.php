<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ogmo_Webservice {

	private $auth;

	public $integration;

	public function __construct() {
		$this->auth = new Ogmo_Auth();
		$this->integration = new Ogmo_Integration();
	}

	/**
	 * Performs the underlying HTTP request.
	 *
	 */
	public function request( $method, $resource, $args = array()) {
		$url = OGMO_API_ENDPOINT . $resource;

		global $wp_version;

		$id_token=$this->integration->get_option('id_token');

		$request_args = array(
			'method'      => $method,
			'redirection' => 5,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer '.$id_token,
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json'
			),
		);


		// attach arguments (in body or URL)
        if ( ! empty( $args ) ) {
            if ($method === 'GET') {
                $url = $url . '?' . http_build_query($args);
            } else {
                $request_args['body'] = json_encode($args);
            }
        }

		$raw_response = wp_remote_request( $url, $request_args );

		if ($raw_response['response']['code'] == 401 || $raw_response['response']['code'] == 403){
			$client_id = $this->integration->get_option('client_id');
			$refresh_token =$this->integration->get_option( 'refresh_token' );
			$id_token = $this->auth->generate_auth_token($this->integration);
			$request_args['headers']['Authorization'] = 'Bearer '.$id_token;
			$raw_response = wp_remote_request( $url, $request_args );
		}


		if ( is_wp_error( $raw_response )
		     || ( is_array( $raw_response )
		          && $raw_response['response']['code']
		          && floor( $raw_response['response']['code'] ) / 100 >= 4 )
		) {
            if ( is_wp_error( $raw_response ) ) {
                $error_message = $raw_response->get_error_message();
            } else {
                $error_message = print_r( $raw_response, true );
            }

			throw new Exception( 'Ogmo_Webservice::request failed' );
		}

		$json   = wp_remote_retrieve_body( $raw_response );
		$result = json_decode( $json, true );
		return $result;
	}

	public function dummmy_api() {

		$resource = 'dummmy_api';
		$data = array(
		    'dummmy' => 'dummmy',
			'dummmy' => 'dummmy',
        );

		$json = self::request( 'POST', $resource, $data );
		return $json;

	}

	public function get_priceplan_info() {

		$user_id = $this->integration->get_option('user_id');
		$resource = 'billing/pricePlans?userId='.$user_id;
		$data = array();
		$priceplan = self::request('GET', $resource, $data);

		$priceplan_info = array(
			'priceplan_name' => '',
			'priceplan_catogery' => '',
			'price' => 0,
			'design_count' => 0,
			'design_view_count' => 0,
		);

		$price_plandef_Id = $priceplan['data']['pricePlanDefId'];
		$resource = 'billing/pricePlanDefs/'.$price_plandef_Id;
		$data = array();
		$priceplandef = self::request('GET', $resource, $data);

		$priceplan_info['priceplan_name'] = $priceplandef['data']['pricePlanName'];
		$priceplan_info['priceplan_catogery'] = $priceplandef['data']['pricePlanCategory'];

		if ($priceplan['data']['pricePlanType'] == 'defined'){
			$priceplan_info['price'] = $priceplandef['data']['price'];
			$priceplan_info['design_count'] = $priceplandef['data']['designCount'];
			$priceplan_info['design_view_count'] = $priceplandef['data']['designViewCount'];
		}else{
			$priceplan_info['price'] = $priceplan['data']['price'];
			$priceplan_info['design_count'] = $priceplan['data']['designCount'];
			$priceplan_info['design_view_count'] = $priceplan['data']['designViewCount'];
		}
		return json_encode($priceplan_info);
	}

	public function get_design_summery() {

		$user_id = $this->integration->get_option('user_id');
		$resource = 'users/designsummery/'.$user_id;
		$data = array();
		$design_summery = self::request('GET', $resource, $data);

		$design_summery_info = array(
			'design_count' => $design_summery['data']['designCount'],
			'design_view_count' => $design_summery['data']['designViewCount']
		);

		return json_encode($design_summery_info);
	}

	public function update_plugin_status() {

		$platform_id = $this->integration->get_option('platform_id');
		$resource = 'platforms/'.$platform_id;
		$data = array(
			'platformStatus' => 'disconnected'
		);
		$platform = self::request('PUT', $resource, $data);

		$platform_info = array(
			'platform_id' => $platform['data']['platform_id']
		);

		return json_encode($platform_info);
	}

	public function get_design($design_id) {
		try {
			$resource = 'designs/platform/'.$design_id;
			$data = array();
			$design = self::request('GET', $resource, $data);
		
			$design_info = $design['data'];
			
			return json_encode($design_info);
		} catch (Exception $e) {
			// echo 'Message: ' .$e->getMessage();
			return "Error";
		}
	}

	public function unlink_design($product_id, $design_id) {
		try {
			$resource = 'designs/linkProductToDesign/'.$design_id;
			$data=array(
				'linkedProduct' => "",
				'unLinkedProduct'=>(string)$product_id
			);
			$unlink = self::request('PUT', $resource, $data);
					
		} catch (Exception $e) {
			echo 'Message: ' .$e->getMessage();
		}
	}
}