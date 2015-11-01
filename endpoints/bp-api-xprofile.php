<?php
/**
 * xProfile endpoint for WordPress REST API
 *
 * @since  2.5
 *
 * @package   BuddyPress
 * @author    modemlooper
 * @license   GPL-2.0+
 * @link      http://buddypress.org
 */

class BP_API_xProfile extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/xprofile', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/xprofile/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_schema' ),
		) );
	}

	/**
	 * Get all public activity
	 *
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function get_items( $request ) {

		$response = $this->get_xprofile();

		return $response;
	}



	/**
	 * get_xprofile function.
	 *
	 * @access public
	 * @param mixed $filter
	 * @return array
	 */
	public function get_xprofile() {

		$field_groups = bp_xprofile_get_groups( array( 'fetch_fields' => true ) );

		$xprofile_data = array();
		$xprofile_fields_data = array();

		foreach( $field_groups as $key => $group ) {

			$xprofile_data['xprofile_groups'][$key]['group_name'] = $group->name;
			$xprofile_data['xprofile_groups'][$key]['group_id'] = $group->id;

			foreach( $group->fields as $keys => $field ) {

				$xprofile_fields_data[$keys]['id'] = $field->id;
				$xprofile_fields_data[$keys]['name'] = $field->name;
				$xprofile_fields_data[$keys]['type'] = $field->type;
				$xprofile_fields_data[$keys]['description'] = $field->description;
				$xprofile_fields_data[$keys]['can_delete'] = $field->can_delete;
				$xprofile_fields_data[$keys]['field_order'] = $field->field_order;

				$xprofile_fields_data[$keys]['is_required'] = $field->is_required;
				$xprofile_fields_data[$keys]['option_order'] = $field->option_order;
				$xprofile_fields_data[$keys]['order_by'] = $field->order_by;
				$xprofile_fields_data[$keys]['order_by'] = $field->field_order;

			}

			$xprofile_data['xprofile_groups'][$key]['fields'] = $xprofile_fields_data;
		}

		$response = new WP_REST_Response();
		$response->set_data( $xprofile_data );
		$response = rest_ensure_response( $response );

		return $response;

	}


	/**
	 * Check if a given request has access
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {

		$response = apply_filters( 'bp_xprofile_permission', true );

		return $response;
	}


	/**
	 * Get the activity schema conforming to JSON Schema
	 *
	 * @return array
	 */
	public function get_schema(){
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'xprofile',
			'type'       => 'object',
			/*
			 * Base properties for each Activity
			 */
			'properties' => array(
				'field_id' => array(
					'description' => 'Unique identifier for the activity.',
					'type'        => 'int',
					'context'     => array( 'view' ),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );

	}

}
