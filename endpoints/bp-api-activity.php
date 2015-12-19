<?php
/**
 * Activity endpoint for WordPress REST API
 *
 * @since  2.5
 *
 * @package   BuddyPress
 * @author    modemlooper
 * @license   GPL-2.0+
 * @link      http://buddypress.org
 */

class BP_API_Activity extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/activity', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/activity/(?P<id>\d+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
			),
			'schema' => array( $this, 'get_item_schema' ),
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/activity/schema', array(
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

		$response = $this->get_activity( $request->get_query_params() );

		return $response;
	}

	/**
	 * Get a specific activity
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error
	 */
	public function get_item( $request ) {

		$param = $request->get_param( 'id' );

		$filter['include'] = $param;

		$response = $this->get_activity( $filter );

		return $response;
	}


	/**
	 * get_activity function.
	 *
	 * @access public
	 * @param mixed $filter
	 * @return void
	 */
	public function get_activity( $request ) {

		if ( bp_has_activities( $request ) ) {

			while ( bp_activities() ) {

				bp_the_activity();

				$activity = array(
					'avatar'	 		=> bp_core_fetch_avatar( array( 'html' => false, 'item_id' => bp_get_activity_user_id() ) ),
					'action'	 		=> bp_get_activity_action(),
					'content'	  		=> bp_get_activity_content_body(),
					'activity_id'		=> bp_get_activity_id(),
					'activity_username' => bp_core_get_username( bp_get_activity_user_id() ),
					'user_id'	 		=> bp_get_activity_user_id(),
					'comment_count'  	=> bp_activity_get_comment_count(),
					'can_comment'	 	=> bp_activity_can_comment(),
					'can_favorite'	  	=> bp_activity_can_favorite(),
					'is_favorite'	 	=> bp_get_activity_is_favorite(),
					'can_delete'  		=> bp_activity_user_can_delete(),
					'user_displayname'  => bp_core_get_user_displayname( bp_get_activity_user_id() ),
					'activity_date'     => bp_get_activity_date_recorded(),
				);

				$activity = apply_filters( 'bp_rest_prepare_activity', $activity );

				$activities[] =	 $activity;

			}

			$data = array(
				'activity' => $activities,
				'has_more_items' => bp_activity_has_more_items()
			);

			$data = apply_filters( 'bp_rest_prepare_activities', $data );

		} else {
			return new WP_Error( 'bp_rest_activity', __( 'No Activity Found.', 'buddypress' ), array( 'status' => 200 ) );
		}

		$response = new WP_REST_Response();
		$response->set_data( $data );
		$response = rest_ensure_response( $response );

		return $response;

	}


	/**
	 * add_activity function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_activity() {

		//add activity code here

	}


	/**
	 * edit_activity function.
	 *
	 * @access public
	 * @return void
	 */
	public function edit_activity() {

		//edit activity code here

	}


	/**
	 * remove_activity function.
	 *
	 * @access public
	 * @return void
	 */
	public function remove_activity() {

		//remove activity code here

	}


	/**
	 * Check if a given request has access
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {

		$response = apply_filters( 'bp_activity_permission', true );

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
			'title'      => 'activity',
			'type'       => 'object',
			/*
			 * Base properties for each Activity
			 */
			'properties' => array(
				'activity_id' => array(
					'description' => 'Unique identifier for the activity.',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'activity_username' => array(
					'description' => 'Unique username for the activity',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'user_id' => array(
					'description' => 'Unique identifier for the user',
					'type'        => 'int',
					'context'     => array( 'view' ),
				),
				'avatar' => array(
					'description' => 'The Avatar URL',
					'type'        => 'uri',
					'context'     => array( 'view' ),
				),
				'action' => array(
					'description' => 'The action that took place in HTML format',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'content' => array(
					'description' => 'The content got the activity',
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'comment_count' => array(
					'description' => 'The number of comments on the activity',
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'can_comment' => array(
					'description' => 'A boolean value of whether the logged in user can comment on the activity',
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'can_favorite' => array(
					'description' => 'A boolean value of whether the logged in user can favorite the activity',
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'is_favorite' => array(
					'description' => 'A boolean value of whether the logged in user has favorited the activity',
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'can_delete' => array(
					'description' => 'A boolean value about whether the logged in user can delete the activity',
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );

	}

}
