<?php
/**
 * Landing Pages Controller
 *
 * Handles landing page-related API endpoints.
 *
 * @package LendingResourceHub\Controllers\LandingPages
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\LandingPages;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles landing page-related actions.
 *
 * @package LendingResourceHub\Controllers\LandingPages
 */
class Actions {

	/**
	 * Get landing pages for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_landing_pages_for_lo( WP_REST_Request $request ) {
		$user_id = $request['id'];
		$pages   = array();

		// Get biolink page for this loan officer
		$biolink_pages = get_posts(
			array(
				'post_type'   => 'frs_biolink',
				'meta_query'  => array(
					array(
						'key'     => '_frs_loan_officer_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status' => array( 'publish', 'draft' ),
				'numberposts' => -1,
			)
		);

		if ( ! empty( $biolink_pages ) ) {
			$biolink = $biolink_pages[0];
			$pages[] = array(
				'id'           => $biolink->ID,
				'title'        => $biolink->post_title,
				'type'         => 'biolink',
				'status'       => $biolink->post_status,
				'views'        => (int) get_post_meta( $biolink->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $biolink->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $biolink->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $biolink->ID, 'medium' ) ?: '',
				'isCoBranded'  => false,
				'ownerId'      => strval( $user_id ),
				'createdAt'    => $biolink->post_date,
				'lastModified' => $biolink->post_modified,
			);
		}

		// Get prequal pages for this loan officer
		$prequal_pages = get_posts(
			array(
				'post_type'   => 'frs_prequal',
				'meta_query'  => array(
					array(
						'key'     => '_frs_loan_officer_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status' => array( 'publish', 'draft' ),
				'numberposts' => -1,
			)
		);

		foreach ( $prequal_pages as $prequal ) {
			$pages[] = array(
				'id'           => $prequal->ID,
				'title'        => $prequal->post_title,
				'type'         => 'prequal',
				'status'       => $prequal->post_status,
				'views'        => (int) get_post_meta( $prequal->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $prequal->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $prequal->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $prequal->ID, 'medium' ) ?: '',
				'isCoBranded'  => true,
				'ownerId'      => strval( $user_id ),
				'createdAt'    => $prequal->post_date,
				'lastModified' => $prequal->post_modified,
			);
		}

		// Get open house pages for this loan officer
		$openhouse_pages = get_posts(
			array(
				'post_type'   => 'frs_openhouse',
				'meta_query'  => array(
					array(
						'key'     => '_frs_loan_officer_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status' => array( 'publish', 'draft' ),
				'numberposts' => -1,
			)
		);

		foreach ( $openhouse_pages as $openhouse ) {
			$pages[] = array(
				'id'           => $openhouse->ID,
				'title'        => $openhouse->post_title,
				'type'         => 'openhouse',
				'status'       => $openhouse->post_status,
				'views'        => (int) get_post_meta( $openhouse->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $openhouse->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $openhouse->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $openhouse->ID, 'medium' ) ?: '',
				'isCoBranded'  => true,
				'ownerId'      => strval( $user_id ),
				'createdAt'    => $openhouse->post_date,
				'lastModified' => $openhouse->post_modified,
			);
		}

		// Get mortgage landing pages for this loan officer
		$mortgage_pages = get_posts(
			array(
				'post_type'   => 'frs_mortgage_lp',
				'author'      => $user_id,
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'meta_value',
				'meta_key'    => '_frs_lp_template',
				'order'       => 'ASC',
			)
		);

		foreach ( $mortgage_pages as $mortgage ) {
			$template      = get_post_meta( $mortgage->ID, '_frs_lp_template', true );
			$template_name = $template === 'rate-quote' ? 'Rate Quote' : 'Loan Application';

			$pages[] = array(
				'id'           => $mortgage->ID,
				'title'        => $mortgage->post_title,
				'type'         => 'mortgage_' . $template,
				'status'       => $mortgage->post_status,
				'views'        => (int) get_post_meta( $mortgage->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $mortgage->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $mortgage->ID ),
				'thumbnail'    => '',
				'isCoBranded'  => false,
				'ownerId'      => strval( $user_id ),
				'template'     => $template,
				'templateName' => $template_name,
				'createdAt'    => $mortgage->post_date,
				'lastModified' => $mortgage->post_modified,
			);
		}

		return rest_ensure_response( $pages );
	}

	/**
	 * Get co-branded pages for realtor.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_landing_pages_for_realtor( WP_REST_Request $request ) {
		$user_id = $request['id'];
		$pages   = array();

		// Get prequal pages where this realtor is the partner
		$prequal_pages = get_posts(
			array(
				'post_type'   => 'frs_prequal',
				'meta_query'  => array(
					array(
						'key'     => '_frs_partner_user_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status' => array( 'publish', 'draft' ),
				'numberposts' => -1,
			)
		);

		foreach ( $prequal_pages as $prequal ) {
			$pages[] = array(
				'id'           => $prequal->ID,
				'title'        => $prequal->post_title,
				'type'         => 'prequal',
				'status'       => $prequal->post_status,
				'views'        => (int) get_post_meta( $prequal->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $prequal->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $prequal->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $prequal->ID, 'medium' ) ?: '',
				'isCoBranded'  => true,
				'partnerId'    => strval( $user_id ),
				'createdAt'    => $prequal->post_date,
				'lastModified' => $prequal->post_modified,
			);
		}

		// Get open house pages where this realtor is the partner
		$openhouse_pages = get_posts(
			array(
				'post_type'   => 'frs_openhouse',
				'meta_query'  => array(
					array(
						'key'     => '_frs_partner_user_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status' => array( 'publish', 'draft' ),
				'numberposts' => -1,
			)
		);

		foreach ( $openhouse_pages as $openhouse ) {
			$pages[] = array(
				'id'           => $openhouse->ID,
				'title'        => $openhouse->post_title,
				'type'         => 'openhouse',
				'status'       => $openhouse->post_status,
				'views'        => (int) get_post_meta( $openhouse->ID, '_frs_page_views', true ) ?: 0,
				'conversions'  => (int) get_post_meta( $openhouse->ID, '_frs_page_conversions', true ) ?: 0,
				'url'          => get_permalink( $openhouse->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $openhouse->ID, 'medium' ) ?: '',
				'isCoBranded'  => true,
				'partnerId'    => strval( $user_id ),
				'createdAt'    => $openhouse->post_date,
				'lastModified' => $openhouse->post_modified,
			);
		}

		return rest_ensure_response( $pages );
	}
}
