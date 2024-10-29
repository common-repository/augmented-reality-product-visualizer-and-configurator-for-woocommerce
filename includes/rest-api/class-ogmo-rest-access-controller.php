<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Ogmo_REST_Access_Controller extends WC_REST_Controller {


    protected $namespace = 'ogmo/v3';


    protected $rest_base = 'saveogmokeys';


    public function register_routes() {

        register_rest_route( $this->namespace, '/saveogmokeys' , array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'set_ogmokeys' ),
                'permission_callback' => array( $this, 'set_access_permissions_check' ),
                'args'                => array_merge( $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    array(
                        'client_id' => array(
                            'type'        => 'string',
                            'description' => __( 'Ogmo client_id.', 'ogmo' ),
                            'required'    => true
                        ),
                        'refresh_token' => array(
                            'type'        => 'string',
                            'description' => __( 'Ogmo refresh_token.', 'ogmo' ),
                            'required'    => true
                        ),
                    ) ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );

        register_rest_route( $this->namespace, '/updateIdToken' , array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'update_idtoken' ),
                'permission_callback' => array( $this, 'set_access_permissions_check' ),
                'args'                => array_merge( $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    array(
                        'id_token' => array(
                            'type'        => 'string',
                            'description' => __( 'Ogmo id_token.', 'ogmo' ),
                            'required'    => true
                        )
                    ) ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    


    function set_ogmokeys( $request ) {
        
        $user_id = $request['user_id'];
        $platform_id = $request['platform_id'];
        $client_id = $request['client_id'];
        $refresh_token = $request['refresh_token'];
        // these two be taken from not the body but in url params i guess
        $consumer_key = $request['consumer_key'];
        $consumer_secret = $request['consumer_secret'];

        $integration = new Ogmo_Integration();
        $integration->update_option( "user_id", $user_id);
        $integration->update_option( "platform_id", $platform_id);
        $integration->update_option( "client_id", $client_id);
        $integration->update_option( "refresh_token", $refresh_token);
        $integration->update_option( "consumer_key", $consumer_key);
        $integration->update_option( "consumer_secret", $consumer_secret);

        $auth = new Ogmo_Auth();
        $id_token = $auth->generate_auth_token($integration);
        $integration->update_option( "id_token", $id_token);

        $request->set_param('context', 'edit');
        $response = $this->prepare_item_for_response($request,$request);
        return $response;

    }

    function update_idtoken( $request ) {
        
        $id_token = $request['id_token'];

        $integration = new Ogmo_Integration();
        $integration->update_option( "id_token", $id_token);

        $request->set_param('context', 'edit');
        $response = $this->prepare_item_for_response($request,$request);
        return $response;

    }


    function test_access( $request ) {
        return  "you have access from ogmo to woocommerce ";
    }

    public function set_access_permissions_check( $request ) {
        if ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) || current_user_can( Ogmo::CAPABILITY ) ) {
            return true;
        }

        return new WP_Error( 'woocommerce_rest_cannot_create',
            __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ),
            array( 'status' => rest_authorization_required_code() )
        );
    }

    public function prepare_item_for_response($item, $request ) {

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        return $response;
    }

}
