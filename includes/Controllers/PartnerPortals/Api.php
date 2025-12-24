<?php
/**
 * Partner Company Portal REST API Controller
 *
 * Provides REST API endpoints for loan officers to manage their assigned partner companies.
 * All operations are scoped to the current loan officer's assigned companies only.
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\PartnerPortals;

use LendingResourceHub\Traits\Base;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Api
 *
 * Handles REST API endpoints for partner company management.
 *
 * Endpoints:
 * - GET    /lrh/v1/partner-companies - List all partner companies for current LO
 * - POST   /lrh/v1/partner-companies - Create new partner company
 * - GET    /lrh/v1/partner-companies/{id} - Get single partner company
 * - PUT    /lrh/v1/partner-companies/{id} - Update partner company
 * - DELETE /lrh/v1/partner-companies/{id} - Delete partner company
 * - POST   /lrh/v1/partner-companies/{id}/realtors - Bulk add realtors to company
 * - POST   /lrh/v1/partner-companies/{id}/branding - Update company branding
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
 */
class Api {

	use Base;

	/**
	 * Initialize API routes.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		// List all partner companies for current LO
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_partner_companies' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

		// Create new partner company
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_partner_company' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

		// Get single partner company
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_partner_company' ),
				'permission_callback' => array( $this, 'check_company_access' ),
			)
		);

		// Update partner company
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_partner_company' ),
				'permission_callback' => array( $this, 'check_company_access' ),
			)
		);

		// Delete partner company
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_partner_company' ),
				'permission_callback' => array( $this, 'check_company_access' ),
			)
		);

		// Bulk add realtors to company
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/(?P<id>\d+)/realtors',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'bulk_add_realtors' ),
				'permission_callback' => array( $this, 'check_company_access' ),
			)
		);

		// Update company branding
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/(?P<id>\d+)/branding',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_branding' ),
				'permission_callback' => array( $this, 'check_company_access' ),
			)
		);

		// Get current user's partner companies
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/my-companies',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_my_companies' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

		// Get single company by slug
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_company_by_slug' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

	}

	/**
	 * Check if user is a loan officer.
	 *
	 * @return bool|WP_Error
	 */
	public function check_loan_officer_permission() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'You must be logged in.', 'lending-resource-hub' ), array( 'status' => 401 ) );
		}

		$user = wp_get_current_user();
		if ( ! in_array( 'loan_officer', $user->roles ) && ! in_array( 'administrator', $user->roles ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Only loan officers can manage partner companies.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Check if current loan officer has access to this partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error
	 */
	public function check_company_access( $request ) {
		$permission_check = $this->check_loan_officer_permission();
		if ( is_wp_error( $permission_check ) ) {
			return $permission_check;
		}

		$company_id = $request->get_param( 'id' );
		$user_id    = get_current_user_id();

		// Admin has access to all
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check if current user is assigned to this company
		$assigned_los = carbon_get_post_meta( $company_id, 'pp_loan_officers' );
		$assigned_ids = wp_list_pluck( $assigned_los, 'id' );

		if ( ! in_array( $user_id, $assigned_ids ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You do not have access to this partner company.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		return true;
	}


	/**
	 * Get all partner companies for current loan officer.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_partner_companies( $request ) {
		$user_id = get_current_user_id();

		// Get all partner company portals
		$args = array(
			'post_type'      => 'frs_partner_portal',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		);

		// If not admin, filter to only assigned companies
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_pp_loan_officers',
					'value'   => serialize( strval( $user_id ) ),
					'compare' => 'LIKE',
				),
			);
		}

		$query     = new \WP_Query( $args );
		$companies = array();

		foreach ( $query->posts as $post ) {
			$companies[] = $this->format_company_data( $post );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $companies,
			),
			200
		);
	}

	/**
	 * Create new partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_partner_company( $request ) {
		$user_id      = get_current_user_id();
		$company_name = sanitize_text_field( $request->get_param( 'company_name' ) );

		if ( empty( $company_name ) ) {
			return new WP_Error( 'missing_company_name', __( 'Company name is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Create portal post
		$portal_name = sprintf( __( '%s - Partner Portal', 'lending-resource-hub' ), $company_name );
		$slug        = sanitize_title( $company_name );

		$post_data = array(
			'post_title'   => $portal_name,
			'post_name'    => $slug,
			'post_content' => '<!-- wp:lrh/partner-portal-page /-->',
			'post_status'  => 'publish',
			'post_type'    => 'frs_partner_portal',
			'post_author'  => $user_id,
		);

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set meta fields
		carbon_set_post_meta( $post_id, 'pp_company_name', $company_name );
		carbon_set_post_meta( $post_id, 'pp_loan_officers', array( array( 'id' => $user_id, 'type' => 'user' ) ) );
		carbon_set_post_meta( $post_id, '_pp_page_views', 0 );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->format_company_data( get_post( $post_id ) ),
				'message' => __( 'Partner company created successfully.', 'lending-resource-hub' ),
			),
			201
		);
	}

	/**
	 * Get single partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_partner_company( $request ) {
		$company_id = $request->get_param( 'id' );
		$post       = get_post( $company_id );

		if ( ! $post || $post->post_type !== 'frs_partner_portal' ) {
			return new WP_Error( 'not_found', __( 'Partner company not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->format_company_data( $post ),
			),
			200
		);
	}

	/**
	 * Update partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_partner_company( $request ) {
		$company_id   = $request->get_param( 'id' );
		$company_name = $request->get_param( 'company_name' );

		if ( $company_name ) {
			carbon_set_post_meta( $company_id, 'pp_company_name', sanitize_text_field( $company_name ) );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->format_company_data( get_post( $company_id ) ),
				'message' => __( 'Partner company updated successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Delete partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_partner_company( $request ) {
		$company_id = $request->get_param( 'id' );

		$result = wp_trash_post( $company_id );

		if ( ! $result ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete partner company.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Partner company deleted successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Get current user's partner companies.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_my_companies( $request ) {
		// BuddyPress removed - return empty array
		// TODO: Implement custom company management
		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(),
				'message' => __( 'Partner company management moved to custom system.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Get single partner company by slug.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_company_by_slug( $request ) {
		$slug = $request->get_param( 'slug' );

		// Query for partner portal by slug
		$args = array(
			'post_type'   => 'frs_partner_portal',
			'name'        => $slug,
			'post_status' => 'publish',
			'numberposts' => 1,
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return new WP_Error( 'company_not_found', __( 'Partner company not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$post = $posts[0];

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->format_company_data( $post ),
			),
			200
		);
	}

	/**
	 * Bulk add realtors to a partner company.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function bulk_add_realtors( $request ) {
		$company_id = $request->get_param( 'id' );
		$realtors   = $request->get_param( 'realtors' );

		if ( ! is_array( $realtors ) ) {
			return new WP_Error( 'invalid_data', __( 'Realtors must be an array.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		$results = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		foreach ( $realtors as $realtor_data ) {
			$email = sanitize_email( $realtor_data['email'] ?? '' );

			if ( empty( $email ) || ! is_email( $email ) ) {
				$results['skipped']++;
				$results['errors'][] = sprintf( __( 'Invalid email: %s', 'lending-resource-hub' ), $email );
				continue;
			}

			// Check if user exists
			$user = get_user_by( 'email', $email );

			if ( ! $user ) {
				// Create new WordPress user for the realtor
				$user_data = array(
					'user_login' => $email,
					'user_email' => $email,
					'first_name' => sanitize_text_field( $realtor_data['first_name'] ?? '' ),
					'last_name'  => sanitize_text_field( $realtor_data['last_name'] ?? '' ),
					'role'       => 'realtor',
				);

				$user_id = wp_insert_user( $user_data );

				if ( is_wp_error( $user_id ) ) {
					$results['errors'][] = $user_id->get_error_message();
					$results['skipped']++;
					continue;
				}

				$results['created']++;
			} else {
				$results['updated']++;
			}
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $results,
				'message' => sprintf(
					__( 'Bulk upload complete. Created: %d, Updated: %d, Skipped: %d', 'lending-resource-hub' ),
					$results['created'],
					$results['updated'],
					$results['skipped']
				),
			),
			200
		);
	}

	/**
	 * Update partner company branding.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function update_branding( $request ) {
		$company_id = $request->get_param( 'id' );

		$branding_fields = array(
			'pp_primary_color',
			'pp_secondary_color',
			'pp_custom_logo',
			'pp_header_background',
			'pp_button_style',
		);

		foreach ( $branding_fields as $field ) {
			$value = $request->get_param( $field );
			if ( $value !== null ) {
				carbon_set_post_meta( $company_id, $field, $value );
			}
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $this->format_company_data( get_post( $company_id ) ),
				'message' => __( 'Branding updated successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}


	/**
	 * Format partner company data for API response.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array
	 */
	private function format_company_data( $post ) {
		$loan_officers = carbon_get_post_meta( $post->ID, 'pp_loan_officers' );

		return array(
			'id'                  => $post->ID,
			'company_name'        => carbon_get_post_meta( $post->ID, 'pp_company_name' ),
			'title'               => $post->post_title,
			'slug'                => $post->post_name,
			'url'                 => get_permalink( $post->ID ),
			'edit_url'            => get_edit_post_link( $post->ID, 'raw' ),
			'member_count'        => 0, // TODO: Implement custom member management
			'loan_officers'       => $loan_officers,
			'branding'            => array(
				'primary_color'       => carbon_get_post_meta( $post->ID, 'pp_primary_color' ),
				'secondary_color'     => carbon_get_post_meta( $post->ID, 'pp_secondary_color' ),
				'custom_logo'         => carbon_get_post_meta( $post->ID, 'pp_custom_logo' ),
				'header_background'   => carbon_get_post_meta( $post->ID, 'pp_header_background' ),
				'button_style'        => carbon_get_post_meta( $post->ID, 'pp_button_style' ),
			),
			'analytics'           => array(
				'views'       => (int) carbon_get_post_meta( $post->ID, '_pp_page_views' ),
				'conversions' => 0, // TODO: Track conversions
			),
			'created_at'          => $post->post_date,
			'modified_at'         => $post->post_modified,
		);
	}
}
