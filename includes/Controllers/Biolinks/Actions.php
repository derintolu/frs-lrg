<?php
/**
 * Biolink Actions Controller
 *
 * Handles REST API requests for biolink page generation and management.
 *
 * @package LendingResourceHub\Controllers\Biolinks
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Biolinks;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Biolink API endpoints controller
 */
class Actions {

	/**
	 * Generate biolink page for a user
	 *
	 * POST /wp-json/lrh/v1/biolinks/generate
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function generate_biolink( WP_REST_Request $request ) {
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to generate biolink pages.', 'lending-resource-hub' ),
				array( 'status' => 403 )
			);
		}

		$user_id = $request->get_param( 'user_id' );

		if ( empty( $user_id ) ) {
			return new WP_Error(
				'missing_user_id',
				__( 'User ID is required.', 'lending-resource-hub' ),
				array( 'status' => 400 )
			);
		}

		// Verify user exists
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return new WP_Error(
				'invalid_user',
				__( 'User not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Generate biolink page
		$post_id = Blocks::generate_biolink_page( $user_id );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Get the generated post
		$post = get_post( $post_id );

		return new WP_REST_Response(
			array(
				'success'  => true,
				'message'  => __( 'Biolink page generated successfully.', 'lending-resource-hub' ),
				'post_id'  => $post_id,
				'post_url' => get_permalink( $post_id ),
				'post'     => array(
					'id'      => $post->ID,
					'title'   => $post->post_title,
					'slug'    => $post->post_name,
					'status'  => $post->post_status,
					'url'     => get_permalink( $post_id ),
					'user_id' => $user_id,
				),
			),
			200
		);
	}

	/**
	 * Get biolink page for a user
	 *
	 * GET /wp-json/lrh/v1/biolinks/user/{user_id}
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_biolink_for_user( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' );

		if ( empty( $user_id ) ) {
			return new WP_Error(
				'missing_user_id',
				__( 'User ID is required.', 'lending-resource-hub' ),
				array( 'status' => 400 )
			);
		}

		// Find biolink page for user
		$args = array(
			'post_type'      => 'frs_biolink',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'frs_biolink_user',
					'value' => $user_id,
				),
			),
		);

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'No biolink page found for this user.', 'lending-resource-hub' ),
					'post'    => null,
				),
				200
			);
		}

		$post = $query->posts[0];

		return new WP_REST_Response(
			array(
				'success' => true,
				'post'    => array(
					'id'      => $post->ID,
					'title'   => $post->post_title,
					'slug'    => $post->post_name,
					'status'  => $post->post_status,
					'url'     => get_permalink( $post->ID ),
					'user_id' => $user_id,
					'views'   => get_post_meta( $post->ID, '_frs_page_views', true ) ?: 0,
					'conversions' => get_post_meta( $post->ID, '_frs_page_conversions', true ) ?: 0,
				),
			),
			200
		);
	}

	/**
	 * Update biolink page stats (views, conversions)
	 *
	 * PUT /wp-json/lrh/v1/biolinks/{id}/stats
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_biolink_stats( WP_REST_Request $request ) {
		$post_id = $request->get_param( 'id' );
		$stat_type = $request->get_param( 'type' ); // 'view' or 'conversion'

		if ( empty( $post_id ) ) {
			return new WP_Error(
				'missing_post_id',
				__( 'Biolink post ID is required.', 'lending-resource-hub' ),
				array( 'status' => 400 )
			);
		}

		// Verify post exists and is a biolink
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'frs_biolink' ) {
			return new WP_Error(
				'invalid_post',
				__( 'Biolink page not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Update stats
		if ( $stat_type === 'view' ) {
			$views = (int) get_post_meta( $post_id, '_frs_page_views', true );
			update_post_meta( $post_id, '_frs_page_views', $views + 1 );

			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Page view recorded.', 'lending-resource-hub' ),
					'views'   => $views + 1,
				),
				200
			);
		} elseif ( $stat_type === 'conversion' ) {
			$conversions = (int) get_post_meta( $post_id, '_frs_page_conversions', true );
			update_post_meta( $post_id, '_frs_page_conversions', $conversions + 1 );

			return new WP_REST_Response(
				array(
					'success'     => true,
					'message'     => __( 'Conversion recorded.', 'lending-resource-hub' ),
					'conversions' => $conversions + 1,
				),
				200
			);
		}

		return new WP_Error(
			'invalid_stat_type',
			__( 'Invalid stat type. Must be "view" or "conversion".', 'lending-resource-hub' ),
			array( 'status' => 400 )
		);
	}

	/**
	 * List all biolink pages
	 *
	 * GET /wp-json/lrh/v1/biolinks
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return WP_REST_Response
	 */
	public function list_biolinks( WP_REST_Request $request ) {
		$per_page = $request->get_param( 'per_page' ) ?: 20;
		$page = $request->get_param( 'page' ) ?: 1;

		$args = array(
			'post_type'      => 'frs_biolink',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query = new \WP_Query( $args );

		$biolinks = array();
		foreach ( $query->posts as $post ) {
			$user_id = get_post_meta( $post->ID, 'frs_biolink_user', true );

			$biolinks[] = array(
				'id'          => $post->ID,
				'title'       => $post->post_title,
				'slug'        => $post->post_name,
				'status'      => $post->post_status,
				'url'         => get_permalink( $post->ID ),
				'user_id'     => $user_id,
				'views'       => get_post_meta( $post->ID, '_frs_page_views', true ) ?: 0,
				'conversions' => get_post_meta( $post->ID, '_frs_page_conversions', true ) ?: 0,
				'created_at'  => $post->post_date,
			);
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'biolinks'   => $biolinks,
				'total'      => $query->found_posts,
				'pages'      => $query->max_num_pages,
				'page'       => $page,
				'per_page'   => $per_page,
			),
			200
		);
	}
}
