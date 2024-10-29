<?php
/**
 * Expivi core function
 *
 * @package Expivi
 */

defined( 'ABSPATH' ) || exit;

function ogmo_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	$template = ogmo_locate_template( $template_name, $template_path, $default_path );

	$action_args = array(
		'template_name' => $template_name,
		'template_path' => $template_path,
		'located'       => $template,
		'args'          => $args,
	);

	if ( ! empty( $args ) && is_array( $args ) ) {
		if ( isset( $args['action_args'] ) ) {
			unset( $args['action_args'] );
		}
		extract( $args );
	}

	include $action_args['located'];
}

function ogmo_locate_template( $template_name, $template_path = '', $default_path = '' ) {

    if ( ! $default_path ) {
        $default_path = realpath(dirname(__FILE__) . '/../templates/');
    }

	$template = trailingslashit( $default_path ) . $template_name;

    // Return what we found.
    return $template;
}

add_action('wp_trash_post', 'unlink_design_on_delete');

function unlink_design_on_delete($product_id){
	$design_id = get_post_meta( $product_id, '_select_field', true );
	$webservice = new Ogmo_Webservice();
    $response = $webservice->unlink_design($product_id, $design_id);
}