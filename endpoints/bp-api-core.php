<?php

class BP_API_Core {

	public function get_info( $request ) {
	
		global $bp;
		$core = array(
			'version'            => $bp->version,
			'active_components'  => $bp->active_components,
			'directory_page_ids' => bp_core_get_directory_page_ids(),
		);
		$response = new WP_JSON_Response();
		$response->set_data( $core );
		$response = json_ensure_response( $response );
		
		return $response;
	
	}

}
