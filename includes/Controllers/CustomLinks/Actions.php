<?php
/**
 * Custom Links Controller
 *
 * Handles custom links API endpoints.
 *
 * @package LendingResourceHub\Controllers\CustomLinks
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\CustomLinks;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles custom links-related actions.
 *
 * @package LendingResourceHub\Controllers\CustomLinks
 */
class Actions {

	/**
	 * Get custom links.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_custom_links( WP_REST_Request $request ) {
		$links = get_posts(
			array(
				'post_type'   => 'frs_custom_link',
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			)
		);

		$formatted_links = array();
		foreach ( $links as $link ) {
			$formatted_links[] = array(
				'id'          => $link->ID,
				'title'       => $link->post_title,
				'description' => get_post_meta( $link->ID, '_link_description', true ),
				'url'         => function_exists( 'get_field' ) ? ( get_field( 'link_url', $link->ID ) ?: get_post_meta( $link->ID, '_link_url', true ) ) : get_post_meta( $link->ID, '_link_url', true ),
				'icon'        => get_post_meta( $link->ID, '_link_icon', true ) ?: 'link',
				'color'       => get_post_meta( $link->ID, '_link_color', true ) ?: '#3b82f6',
				'order'       => $link->menu_order,
			);
		}

		return rest_ensure_response( $formatted_links );
	}
}
