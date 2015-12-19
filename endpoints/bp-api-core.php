<?php

class BP_API_Core extends WP_REST_Controller {


	public function __construct() {

	}


	/**
	 * register_routes function.
	 *
	 * Register the routes for the objects of the controller.
	 *
	 * @access public
	 * @return void
	 */
	public function register_routes() {

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/core', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'permission_callback' => array( $this, 'core_api_permissions' ),
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/core/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_schema' ),
			'permission_callback' => array( $this, 'core_api_permissions' ),
		) );
	}



	/**
	 * get_item function.
	 *
	 * returns data about a BuddyPress site
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
			'component_page_ids' => bp_core_get_directory_page_ids(),
		);

		$core = apply_filters( 'core_api_data_filter', $core );

		$response = new WP_REST_Response();
		$response->set_data( $core );
		$response = rest_ensure_response( $response );

		return $response;

	}


	/**
	 * core_api_permissions function.
	 *
	 * allow permission to access core info
	 *
	 * @access public
	 * @return void
	 */
	public function core_api_permissions() {

		$response = apply_filters( 'core_api_permissions', true );

		return $response;

	}

	/**
	 * Get the core schema conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_schema(){
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'core',
			'type'       => 'object',
			/*
			 * Base properties for each Activity
			 */
			'properties' => array(
				'version' => array(
					'description' => 'BuddyPress plugin version.',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'active_components' => array(
					'description' => 'Active BuddyPress compontents.',
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
				'component_page_ids' => array(
					'description' => 'Component page ids.',
					'type'        => 'array',
					'context'     => array( 'view' ),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );

	}


}
