<?php

class BP_API_Members extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/members', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
			)
		) );

		register_rest_route( BP_API_NAMESPACE . '/' . BP_API_VERSION, '/member/(?P<id>\d+)', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
			)
		) );

	}

	/**
	 * Get all the members
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_items( $request ){

		$response = $this->get_members( $request->get_query_params() );

		return $response;
	}

	/**
	 * Get specific member
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_item( $request ){

		$param = $request->get_param( 'id' );

		$filter['include'] = $param;

		$response = $this->get_members( $filter );

		return $response;

	}

	/**
	 * Fetch BP Members
	 *
	 * @param $request
	 *
	 * @return mixed|WP_Error|WP_REST_Response
	 */
	public function get_members( $request ){

		if( bp_has_members( $request ) ){

			global $members_template;

			$total     = ceil( $members_template->total_member_count / $members_template->pag_num );
			$current   = $members_template->pag_page;
			$has_more_items = $total - $current;

			$members = array();

			while( bp_members() ){

				bp_the_member();

				$member = array(
					'id'            => bp_get_member_user_id(),
					'name'          => bp_core_get_user_displayname( bp_get_member_user_id() ),
					'avatar'        => bp_core_fetch_avatar( array( 'html' => false, 'item_id' => bp_get_member_user_id() ) ),
					'last_active'   => bp_get_member_last_active(),
					'user_action'   => '', // todo add friend / cancel friendship action
				);

				$members[] = $member;

			}

			$data = array(
				'members'    => $members,
				'has_more_items' => (bool) $has_more_items,
			);

			$data = apply_filters( 'bp_rest_prepare_members', $data );

		} else {
			return new WP_Error( 'bp_rest_members', __( 'No Members Found.', 'buddypress' ), array( 'status' => 200 ) );
		}

		$response = new WP_REST_Response();
		$response->set_data( $data );
		$response = rest_ensure_response( $response );

		return $response;
	}

}