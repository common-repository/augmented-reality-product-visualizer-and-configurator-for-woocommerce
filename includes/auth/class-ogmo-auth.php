<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ogmo_Auth {

    private function _generate_auth_token( $integration) {
        global $wp_version;

        $client_id = $integration->get_option('client_id');
        $refresh_token = $integration->get_option( 'refresh_token' );

        $data = array(
            'grant_type' => 'refresh_token',
            'client_id' => $client_id,
            'refresh_token' => $refresh_token
        ) ;

        $request_args = array(
            'method'      => 'POST',
            'headers'     => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json'
            ),
            'body'        => http_build_query($data, null, '&')
        );


        $url = OGMO_AUTHSERVICE_URL;

        $raw_response = wp_remote_request( $url, $request_args );

        if ( is_wp_error( $raw_response )
            || ( is_array( $raw_response )
                && $raw_response['response']['code']
                && floor( $raw_response['response']['code'] ) / 100 >= 4 )
        ) {
            throw new Exception( 'Ogmo_Auth::generate_auth_token failed' );
        }

        $json   = wp_remote_retrieve_body( $raw_response );
        $result = json_decode( $json, true );

        $id_token = $result['id_token'];
        $access_token = $result['access_token'];

        $integration->update_option('id_token',  $id_token);
		$integration->update_option('access_token', $access_token);

        return $id_token;
    }

    public function generate_auth_token($integration) {
        return $this->_generate_auth_token($integration);
    }
}
