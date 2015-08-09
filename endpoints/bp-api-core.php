<?php

class BP_API_Core extends WP_REST_Controller {


	public function __construct() {

	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
	
		register_rest_route( BP_API_SLUG, '/core/*', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'permission_callback' => array( $this, 'core_api_permissions_check' ),
		) );
	}



	/**
	 * get_info function.
	 * 
	 * @access public
	 * @param mixed $request
	 * @return void
	 */
	public function get_item( $request ) {
	
		global $bp;
		$core = array(
			'version'            => $bp->version,
			'active_components'  => $bp->active_components,
			'directory_page_ids' => bp_core_get_directory_page_ids(),
		);
		$response = new WP_REST_Response();
		$response->set_data( $core );
		$response = rest_ensure_response( $response );
		
		return $response;
	
	}
	

	/**
	 * core_api_permissions_check function.
	 *
	 * allow permission to access core info
	 * 
	 * @access public
	 * @return void
	 */
	public function core_api_permissions_check(  ) {
	
		$response = apply_filters( 'core_api_permissions_check', true );
		
		return $response;
	
	}

}
