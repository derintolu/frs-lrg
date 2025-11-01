<?php
/**
 * Marketing Controller
 *
 * Handles marketing materials API endpoints.
 *
 * @package LendingResourceHub\Controllers\Marketing
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Marketing;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles marketing materials-related actions.
 *
 * @package LendingResourceHub\Controllers\Marketing
 */
class Actions {

	/**
	 * Get marketing materials - Social & Print only, max 6 items.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_marketing_materials( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'userId' );

		// Get configured social/print materials (max 6)
		$materials_data = get_option( 'frs_default_canva_embeds', array() );

		$materials = array();

		// Process up to 6 materials
		$count = min( count( $materials_data ), 6 );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! empty( $materials_data[ $i ]['name'] ) && ! empty( $materials_data[ $i ]['embed_code'] ) ) {
				$materials[] = array(
					'id'           => 'material-' . $i,
					'title'        => $materials_data[ $i ]['name'],
					'type'         => 'social_print',
					'embed_code'   => $materials_data[ $i ]['embed_code'],
					'lastModified' => current_time( 'c' ),
					'category'     => 'marketing',
				);
			}
		}

		// Return empty array if no materials (frontend will show empty state)
		return rest_ensure_response( $materials );
	}
}
