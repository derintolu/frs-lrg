<?php
/**
 * Announcements Controller
 *
 * Handles announcements API endpoints.
 *
 * @package LendingResourceHub\Controllers\Announcements
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Announcements;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles announcements-related actions.
 *
 * @package LendingResourceHub\Controllers\Announcements
 */
class Actions {

	/**
	 * Get announcements.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_announcements( WP_REST_Request $request ) {
		$announcements = get_posts(
			array(
				'post_type'   => 'frs_announcement',
				'post_status' => 'publish',
				'numberposts' => 10,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		$formatted_announcements = array();
		foreach ( $announcements as $announcement ) {
			$badge                     = get_post_meta( $announcement->ID, '_announcement_badge', true );
			$formatted_announcements[] = array(
				'id'        => $announcement->ID,
				'title'     => $announcement->post_title,
				'content'   => $announcement->post_content,
				'excerpt'   => wp_trim_words( $announcement->post_content, 20, '...' ),
				'date'      => $announcement->post_date,
				'badge'     => $badge ?: '',
				'thumbnail' => get_the_post_thumbnail_url( $announcement->ID, 'medium' ),
				'priority'  => get_post_meta( $announcement->ID, '_announcement_priority', true ) ?: 'normal',
			);
		}

		return rest_ensure_response( $formatted_announcements );
	}

	/**
	 * Get single announcement.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function get_announcement( WP_REST_Request $request ) {
		$announcement_id = $request['id'];
		$announcement    = get_post( $announcement_id );

		if ( ! $announcement || $announcement->post_type !== 'frs_announcement' ) {
			return new WP_Error( 'not_found', 'Announcement not found', array( 'status' => 404 ) );
		}

		return rest_ensure_response(
			array(
				'id'        => $announcement->ID,
				'title'     => $announcement->post_title,
				'content'   => $announcement->post_content,
				'date'      => $announcement->post_date,
				'thumbnail' => get_the_post_thumbnail_url( $announcement->ID, 'large' ),
				'priority'  => get_post_meta( $announcement->ID, '_announcement_priority', true ) ?: 'normal',
			)
		);
	}
}
