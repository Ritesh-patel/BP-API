<?php

class BP_API_Activity {

	public function get_items( $filter = array() ) {

		$response = $this->get_activity( $filter['filter'] );

		return $response;

	}

	public function get_item( $request ) {

		$response = 'a single activity item';

		return $response;

	}


	/*
	* Helper functions to process data requests
	*
	*/

	public function get_activity( $filter ) {

		$args = $filter;

		if ( bp_has_activities( $args ) ) {

			while ( bp_activities() ) {

				bp_the_activity();

				$activity = array(
					'avatar'	 		=> bp_core_fetch_avatar( array( 'html' => false, 'item_id' => bp_get_activity_id() ) ),
					'action'	 		=> bp_get_activity_action(),
					'content'	  		=> bp_get_activity_content_body(),
					'activity_id'		=> bp_get_activity_id(),
					'activity_username' => bp_core_get_username( bp_get_activity_user_id() ),
					'user_id'	 		=> bp_get_activity_user_id(),
					'comment_count'  	=> bp_activity_get_comment_count(),
					'can_comment'	 	=> bp_activity_can_comment(),
					'can_favorite'	  	=> bp_activity_can_favorite(),
					'is_favorite'	 	=> bp_get_activity_is_favorite(),
					'can_delete'  		=> bp_activity_user_can_delete()
				);

				$activity = apply_filters( 'bp_json_prepare_activity', $activity );

				$activities[] =	 $activity;

			}

			$data = array(
				'activity' => $activities,
				'has_more_items' => bp_activity_has_more_items()
			);

			$data = apply_filters( 'bp_json_prepare_activities', $data );

		} else {
			return new WP_Error( 'bp_json_activity', __( 'No Activity Found.', 'buddypress' ), array( 'status' => 200 ) );
		}

		$response = new WP_JSON_Response();
		$response->set_data( $data );
		$response = json_ensure_response( $response );

		return $response;

	}

	public function add_activity() {

		//add activity code here

	}

	public function edit_activity() {

		//edit activity code here

	}

	public function remove_activity() {

		//remove activity code here

	}

}

function filter_activity( $activity ){

	$activity['action'] = 'wwwwww';

	return $activity;

}
add_filter( 'bp_json_prepare_activity', 'filter_activity' );