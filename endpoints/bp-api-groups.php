<?php

class BP_API_Groups extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/groups', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
			)
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/group/(?P<id>\d+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
			)
		) );

	}


	/**
	 * Get all the groups
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ){

		$response = $this->get_groups( $request->get_query_params() );

		return $response;
	}

	/**
	 * Get specific group
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$param = $request->get_param( 'id' );

		$filter['include'] = $param;

		$response = $this->get_groups( $filter );

		return $response;
	}


	/**
	 * Fetch BP groups
	 *
	 * @param $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_groups( $request ){

		if( bp_has_groups( $request ) ){

			global $groups_template;

			$total     = ceil( $groups_template->total_group_count / $groups_template->pag_num );
			$current   = $groups_template->pag_page;
			$has_more_items = $total - $current;

			$groups = array();

			while( bp_groups() ){

				bp_the_group();

				$group = array(
					'id'                => bp_get_group_id(),
					'name'              => bp_get_group_name(),
					'description'       => bp_get_group_description_excerpt(),
					'avatar'            => bp_core_fetch_avatar( array( 'html' => false, 'item_id' => bp_get_group_id(), 'object' => 'group', ) ),
					'last_active'       => bp_get_group_last_active(),
					'member_count'      => bp_get_group_member_count(),
					'user_status'       => '',  //todo get user action (join group/request membership/leave group)
					'user_action'       => '',
				);

				$groups[] = $group;
			}

			$data = array(
				'groups'    => $groups,
				'has_more_items' => (bool) $has_more_items,
			);

			$data = apply_filters( 'bp_rest_prepare_groups', $data );

		} else {
			return new WP_Error( 'bp_rest_groups', __( 'No Groups Found.', 'buddypress' ), array( 'status' => 200 ) );
		}

		$response = new WP_REST_Response();
		$response->set_data( $data );
		$response = rest_ensure_response( $response );

		return $response;
	}

}