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
use LendingResourceHub\Controllers\Biolinks\Blocks as BiolinkBlocks;
use LendingResourceHub\Controllers\Prequal\Blocks as PrequalBlocks;
use LendingResourceHub\Controllers\OpenHouse\Blocks as OpenHouseBlocks;
use LendingResourceHub\Core\MortgageLandingGenerator;
use LendingResourceHub\Controllers\PartnerPortals\Blocks as PartnerPortalBlocks;

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

	/**
	 * Get landing page templates.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_templates( WP_REST_Request $request ) {
		$templates = array();

		// Get all mortgage landing pages marked as templates
		$template_pages = get_posts(
			array(
				'post_type'   => 'frs_mortgage_lp',
				'meta_query'  => array(
					array(
						'key'     => '_lrh_is_template',
						'value'   => '1',
						'compare' => '=',
					),
				),
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
			)
		);

		foreach ( $template_pages as $template ) {
			$template_type = get_post_meta( $template->ID, '_lrh_lp_template', true );

			$templates[] = array(
				'id'           => $template->ID,
				'title'        => $template->post_title,
				'type'         => $template_type ?: 'unknown',
				'template'     => $template_type,
				'status'       => $template->post_status,
				'url'          => get_permalink( $template->ID ),
				'thumbnail'    => get_the_post_thumbnail_url( $template->ID, 'medium' ) ?: '',
				'isTemplate'   => true,
				'createdAt'    => $template->post_date,
				'lastModified' => $template->post_modified,
			);
		}

		return rest_ensure_response( $templates );
	}

	/**
	 * Generate biolink page.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_biolink( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' ) ?? get_current_user_id();

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Check if biolink already exists for this user
		$existing = get_posts(
			array(
				'post_type'   => 'frs_biolink',
				'meta_query'  => array(
					array(
						'key'     => '_frs_loan_officer_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'numberposts' => 1,
			)
		);

		if ( ! empty( $existing ) ) {
			return new WP_Error( 'biolink_exists', __( 'Biolink page already exists for this user.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Generate the page
		$result = BiolinkBlocks::generate_biolink_page( $user_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate biolink page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Generate prequal page.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_prequal( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' ) ?? get_current_user_id();
		$realtor_id      = $request->get_param( 'realtor_id' );
		$partnership_id  = $request->get_param( 'partnership_id' );

		// Validate required params
		if ( empty( $realtor_id ) ) {
			return new WP_Error( 'missing_param', __( 'Realtor ID is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Generate the page
		$result = PrequalBlocks::generate_prequal_page( $loan_officer_id, $realtor_id, $partnership_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate prequal page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Generate open house page.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_openhouse( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' ) ?? get_current_user_id();
		$realtor_id      = $request->get_param( 'realtor_id' );
		$property_address = $request->get_param( 'property_address' );
		$property_data   = $request->get_param( 'property_data' ) ?? array();

		// Validate required params
		if ( empty( $realtor_id ) ) {
			return new WP_Error( 'missing_param', __( 'Realtor ID is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		if ( empty( $property_address ) ) {
			return new WP_Error( 'missing_param', __( 'Property address is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Generate the page
		$result = OpenHouseBlocks::generate_openhouse_page( $loan_officer_id, $realtor_id, $property_address, $property_data );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate open house page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Generate mortgage landing pages.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_mortgage( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' ) ?? get_current_user_id();

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Check if mortgage pages already exist for this user
		$existing = get_posts(
			array(
				'post_type'   => 'frs_mortgage_lp',
				'author'      => $user_id,
				'numberposts' => 1,
			)
		);

		if ( ! empty( $existing ) ) {
			return new WP_Error( 'mortgage_exists', __( 'Mortgage landing pages already exist for this user.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Generate the pages (creates 2 pages: loan-app and rate-quote)
		$result = MortgageLandingGenerator::generate_pages_for_user( $user_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate mortgage pages.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'pages'   => $result,
			)
		);
	}

	/**
	 * Generate tools landing page (calculator + property valuation).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_tools( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' ) ?? get_current_user_id();

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Generate the tools landing page
		$result = MortgageLandingGenerator::generate_tools_landing_page( $user_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate tools landing page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Generate calculator landing page.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_calculator( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' ) ?? get_current_user_id();

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Generate the calculator landing page
		$result = MortgageLandingGenerator::generate_calculator_landing_page( $user_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate calculator landing page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Generate property valuation landing page.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function generate_valuation( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'user_id' ) ?? get_current_user_id();

		// Check permissions
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have permission to create pages.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		// Generate the valuation landing page
		$result = MortgageLandingGenerator::generate_valuation_landing_page( $user_id );

		if ( ! $result ) {
			return new WP_Error( 'generation_failed', __( 'Failed to generate valuation landing page.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'page'    => $result,
			)
		);
	}

	/**
	 * Check if user has permission to generate pages.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return bool True if user can generate pages.
	 */
	public function check_generation_permissions( $request = null ) {
		error_log('=== Landing Page Generation Permission Check ===');
		error_log('User logged in: ' . (\is_user_logged_in() ? 'YES' : 'NO'));
		error_log('Current user ID: ' . \get_current_user_id());
		error_log('Can edit posts: ' . (\current_user_can('edit_posts') ? 'YES' : 'NO'));

		$result = \is_user_logged_in() && \current_user_can( 'edit_posts' );
		error_log('Permission result: ' . ($result ? 'TRUE' : 'FALSE'));

		return $result;
	}
}
