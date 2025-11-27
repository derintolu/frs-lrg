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

		// Get group members with pagination
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/members',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_group_members' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

		// Get group activity
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/activity',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_group_activity' ),
				'permission_callback' => array( $this, 'check_loan_officer_permission' ),
			)
		);

		// Send group invite
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/invite',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'send_group_invite' ),
				'permission_callback' => array( $this, 'check_group_admin_permission' ),
			)
		);

		// Remove member from group
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/members/(?P<user_id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'remove_group_member' ),
				'permission_callback' => array( $this, 'check_group_admin_permission' ),
			)
		);

		// Change member role
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/members/(?P<user_id>\d+)/role',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'change_member_role' ),
				'permission_callback' => array( $this, 'check_group_admin_permission' ),
			)
		);

		// Get pending invites
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/invites',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_group_invites' ),
				'permission_callback' => array( $this, 'check_group_admin_permission' ),
			)
		);

		// Post activity to group
		register_rest_route(
			LRH_ROUTE_PREFIX,
			'/partner-companies/by-slug/(?P<slug>[a-zA-Z0-9-]+)/activity',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'post_group_activity' ),
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
	 * Check if user is a member of the group specified by slug.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error
	 */
	public function check_group_member_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_forbidden', __( 'You must be logged in.', 'lending-resource-hub' ), array( 'status' => 401 ) );
		}

		$slug    = $request->get_param( 'slug' );
		$user_id = get_current_user_id();

		// Admin has access to all
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Get group by slug
		$group = groups_get_group_by(
			array(
				'slug' => $slug,
			)
		);

		if ( ! $group || ! $group->id ) {
			return new WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		// Check if user is a member of this group
		if ( ! groups_is_user_member( $user_id, $group->id ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not a member of this partner company.', 'lending-resource-hub' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Check if user is an admin or moderator of the group specified by slug.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error
	 */
	public function check_group_admin_permission( $request ) {
		$permission_check = $this->check_loan_officer_permission();
		if ( is_wp_error( $permission_check ) ) {
			return $permission_check;
		}

		$slug  = $request->get_param( 'slug' );
		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$user_id = get_current_user_id();
		if ( ! groups_is_user_admin( $user_id, $group->id ) && ! groups_is_user_mod( $user_id, $group->id ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You do not have permission to manage this group.', 'lending-resource-hub' ), array( 'status' => 403 ) );
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
		$group_id     = intval( $request->get_param( 'buddypress_group_id' ) );

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

		if ( $group_id ) {
			carbon_set_post_meta( $post_id, 'pp_buddypress_group_id', $group_id );
		}

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
		$group_id     = $request->get_param( 'buddypress_group_id' );

		if ( $company_name ) {
			carbon_set_post_meta( $company_id, 'pp_company_name', sanitize_text_field( $company_name ) );
		}

		if ( $group_id !== null ) {
			carbon_set_post_meta( $company_id, 'pp_buddypress_group_id', intval( $group_id ) );
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
	 * Bulk add realtors to partner company.
	 *
	 * Expected format:
	 * {
	 *   "realtors": [
	 *     {"email": "realtor@example.com", "first_name": "John", "last_name": "Doe"},
	 *     ...
	 *   ]
	 * }
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_my_companies( $request ) {
		$user_id = get_current_user_id();

		if ( ! function_exists( 'groups_get_user_groups' ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'BuddyPress Groups is not active.', 'lending-resource-hub' ),
				),
				500
			);
		}

		// Get ALL partner-org groups (not just ones user is member of)
		// All loan officers and leadership should see all partner companies
		$all_groups = groups_get_groups(
			array(
				'type'     => 'alphabetical',
				'per_page' => 999,
			)
		);

		$companies = array();

		foreach ( $all_groups['groups'] as $group ) {
			// Check if this is a partner-org group
			$group_type = bp_groups_get_group_type( $group->id );
			if ( $group_type !== 'partner-org' ) {
				continue;
			}

			$group_id = $group->id;

			$group = groups_get_group( $group_id );
			if ( ! $group || ! $group->id ) {
				continue;
			}

			// Get user's role in the group (if they are a member)
			$is_member = groups_is_user_member( $user_id, $group_id );
			$is_admin  = groups_is_user_admin( $user_id, $group_id );
			$is_mod    = groups_is_user_mod( $user_id, $group_id );

			if ( ! $is_member ) {
				$role = 'non-member';
			} else {
				$role = $is_admin ? 'admin' : ( $is_mod ? 'mod' : 'member' );
			}

			// Get branding
			$branding = array(
				'primary_color'   => groups_get_groupmeta( $group_id, 'pp_primary_color' ) ?: '#2563eb',
				'secondary_color' => groups_get_groupmeta( $group_id, 'pp_secondary_color' ) ?: '#2dd4da',
				'button_style'    => groups_get_groupmeta( $group_id, 'pp_button_style' ) ?: 'rounded',
			);

			// Get stats
			$stats = array(
				'activity_count' => 0, // TODO: Get actual activity count
				'page_views'     => (int) groups_get_groupmeta( $group_id, '_pp_page_views' ) ?: 0,
			);

			// Get member count
			$member_count = groups_get_total_member_count( $group_id );

			$companies[] = array(
				'id'           => $group->id,
				'name'         => $group->name,
				'description'  => $group->description,
				'slug'         => $group->slug,
				'avatar_urls'  => array(
					'full'  => bp_core_fetch_avatar(
						array(
							'item_id' => $group_id,
							'object'  => 'group',
							'type'    => 'full',
							'html'    => false,
						)
					),
					'thumb' => bp_core_fetch_avatar(
						array(
							'item_id' => $group_id,
							'object'  => 'group',
							'type'    => 'thumb',
							'html'    => false,
						)
					),
				),
				'member_count' => $member_count,
				'user_role'    => $role,
				'branding'     => $branding,
				'stats'        => $stats,
			);
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
	 * Get single partner company by slug.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_company_by_slug( $request ) {
		$slug    = $request->get_param( 'slug' );
		$user_id = get_current_user_id();

		// Get group by slug using correct BuddyPress function
		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$group_id = $group->id;

		// Check if this is a partner-org group
		$group_type = bp_groups_get_group_type( $group_id );
		if ( $group_type !== 'partner-org' ) {
			return new WP_Error( 'invalid_group_type', __( 'This is not a partner company group.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Get user's role in the group
		$is_admin = groups_is_user_admin( $user_id, $group_id );
		$is_mod   = groups_is_user_mod( $user_id, $group_id );
		$role     = $is_admin ? 'admin' : ( $is_mod ? 'mod' : 'member' );

		// Get branding
		$branding = array(
			'primary_color'   => groups_get_groupmeta( $group_id, 'pp_primary_color' ) ?: '#2563eb',
			'secondary_color' => groups_get_groupmeta( $group_id, 'pp_secondary_color' ) ?: '#2dd4da',
			'button_style'    => groups_get_groupmeta( $group_id, 'pp_button_style' ) ?: 'rounded',
		);

		// Get stats
		$stats = array(
			'activity_count' => 0, // TODO: Get actual activity count
			'page_views'     => (int) groups_get_groupmeta( $group_id, '_pp_page_views' ) ?: 0,
		);

		// Get member count
		$member_count = groups_get_total_member_count( $group_id );

		// Get members (first 10 for preview)
		$members = groups_get_group_members(
			array(
				'group_id' => $group_id,
				'per_page' => 10,
				'page'     => 1,
			)
		);

		$members_data = array();
		if ( ! empty( $members['members'] ) ) {
			foreach ( $members['members'] as $member ) {
				$members_data[] = array(
					'id'         => $member->ID,
					'name'       => $member->display_name,
					'avatar_url' => get_avatar_url( $member->ID ),
					'role'       => groups_is_user_admin( $member->ID, $group_id ) ? 'admin' : ( groups_is_user_mod( $member->ID, $group_id ) ? 'mod' : 'member' ),
				);
			}
		}

		$company_data = array(
			'id'           => $group->id,
			'name'         => $group->name,
			'description'  => $group->description,
			'slug'         => $group->slug,
			'avatar_urls'  => array(
				'full'  => bp_core_fetch_avatar(
					array(
						'item_id' => $group_id,
						'object'  => 'group',
						'type'    => 'full',
						'html'    => false,
					)
				),
				'thumb' => bp_core_fetch_avatar(
					array(
						'item_id' => $group_id,
						'object'  => 'group',
						'type'    => 'thumb',
						'html'    => false,
					)
				),
			),
			'member_count' => $member_count,
			'user_role'    => $role,
			'branding'     => $branding,
			'stats'        => $stats,
			'members'      => $members_data,
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $company_data,
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

			// TODO: Add user to BuddyPress group if group_id exists
			$group_id = carbon_get_post_meta( $company_id, 'pp_buddypress_group_id' );
			if ( $group_id && function_exists( 'groups_join_group' ) ) {
				groups_join_group( $group_id, $user->ID );
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
	 * Get group members.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_group_members( $request ) {
		$slug  = $request->get_param( 'slug' );
		$page  = $request->get_param( 'page' ) ?? 1;
		$per_page = $request->get_param( 'per_page' ) ?? 20;

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$members = groups_get_group_members(
			array(
				'group_id' => $group->id,
				'page'     => $page,
				'per_page' => $per_page,
			)
		);

		$formatted_members = array();
		if ( ! empty( $members['members'] ) ) {
			foreach ( $members['members'] as $member ) {
				$formatted_members[] = array(
					'id'         => $member->ID,
					'name'       => $member->display_name,
					'avatar_url' => get_avatar_url( $member->ID ),
					'role'       => groups_is_user_admin( $member->ID, $group->id ) ? 'admin' : ( groups_is_user_mod( $member->ID, $group->id ) ? 'mod' : 'member' ),
				);
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $formatted_members,
				'total'   => $members['count'],
			),
			200
		);
	}

	/**
	 * Get group activity.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_group_activity( $request ) {
		$slug  = $request->get_param( 'slug' );
		$page  = $request->get_param( 'page' ) ?? 1;
		$per_page = $request->get_param( 'per_page' ) ?? 20;

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$activities = bp_activity_get(
			array(
				'object'      => 'groups',
				'primary_id'  => $group->id,
				'page'        => $page,
				'per_page'    => $per_page,
			)
		);

		$formatted_activities = array();
		if ( ! empty( $activities['activities'] ) ) {
			foreach ( $activities['activities'] as $activity ) {
				$formatted_activities[] = array(
					'id'         => $activity->id,
					'user_id'    => $activity->user_id,
					'user_name'  => bp_core_get_user_displayname( $activity->user_id ),
					'avatar_url' => get_avatar_url( $activity->user_id ),
					'content'    => $activity->content,
					'date'       => $activity->date_recorded,
					'type'       => $activity->type,
				);
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $formatted_activities,
				'total'   => $activities['total'],
			),
			200
		);
	}

	/**
	 * Send group invite.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function send_group_invite( $request ) {
		$slug    = $request->get_param( 'slug' );
		$user_id = $request->get_param( 'user_id' );
		$message = $request->get_param( 'message' ) ?? '';

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		if ( ! $user_id ) {
			return new \WP_Error( 'missing_user_id', __( 'User ID is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		// Check if user already a member
		if ( groups_is_user_member( $user_id, $group->id ) ) {
			return new \WP_Error( 'already_member', __( 'User is already a member.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		$inviter_id = get_current_user_id();
		$invite     = groups_invite_user(
			array(
				'user_id'       => $user_id,
				'group_id'      => $group->id,
				'inviter_id'    => $inviter_id,
				'send_invite'   => 1,
			)
		);

		if ( ! $invite ) {
			return new \WP_Error( 'invite_failed', __( 'Failed to send invite.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Invite sent successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Remove group member.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function remove_group_member( $request ) {
		$slug    = $request->get_param( 'slug' );
		$user_id = $request->get_param( 'user_id' );

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		if ( ! groups_is_user_member( $user_id, $group->id ) ) {
			return new \WP_Error( 'not_member', __( 'User is not a member.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		$removed = groups_remove_member( $user_id, $group->id );

		if ( ! $removed ) {
			return new \WP_Error( 'remove_failed', __( 'Failed to remove member.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Member removed successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Change member role.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function change_member_role( $request ) {
		$slug    = $request->get_param( 'slug' );
		$user_id = $request->get_param( 'user_id' );
		$role    = $request->get_param( 'role' );

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		if ( ! in_array( $role, array( 'admin', 'mod', 'member' ) ) ) {
			return new \WP_Error( 'invalid_role', __( 'Invalid role.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		$member = new \BP_Groups_Member( $user_id, $group->id );

		if ( ! $member->id ) {
			return new \WP_Error( 'not_member', __( 'User is not a member.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		if ( $role === 'admin' ) {
			$member->promote( 'admin' );
		} elseif ( $role === 'mod' ) {
			$member->promote( 'mod' );
		} else {
			$member->demote();
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Member role updated successfully.', 'lending-resource-hub' ),
			),
			200
		);
	}

	/**
	 * Get group invites.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_group_invites( $request ) {
		$slug  = $request->get_param( 'slug' );

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		$invites = groups_get_invites_for_group( get_current_user_id(), $group->id );

		$formatted_invites = array();
		if ( ! empty( $invites ) ) {
			foreach ( $invites as $invite ) {
				$formatted_invites[] = array(
					'id'          => $invite->id,
					'user_id'     => $invite->user_id,
					'user_name'   => bp_core_get_user_displayname( $invite->user_id ),
					'avatar_url'  => get_avatar_url( $invite->user_id ),
					'invited_by'  => bp_core_get_user_displayname( $invite->inviter_id ),
					'date'        => $invite->date_modified,
				);
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $formatted_invites,
			),
			200
		);
	}

	/**
	 * Post activity to group.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function post_group_activity( $request ) {
		$slug    = $request->get_param( 'slug' );
		$content = $request->get_param( 'content' );

		$group = bp_get_group_by( 'slug', $slug );

		if ( ! $group || ! $group->id ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'lending-resource-hub' ), array( 'status' => 404 ) );
		}

		if ( empty( $content ) ) {
			return new \WP_Error( 'empty_content', __( 'Activity content is required.', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}

		$activity_id = groups_record_activity(
			array(
				'user_id'   => get_current_user_id(),
				'group_id'  => $group->id,
				'type'      => 'activity_update',
				'content'   => wp_kses_post( $content ),
			)
		);

		if ( ! $activity_id ) {
			return new \WP_Error( 'activity_failed', __( 'Failed to post activity.', 'lending-resource-hub' ), array( 'status' => 500 ) );
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Activity posted successfully.', 'lending-resource-hub' ),
				'data'    => array( 'activity_id' => $activity_id ),
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
		$group_id      = carbon_get_post_meta( $post->ID, 'pp_buddypress_group_id' );

		// Get group member count
		$member_count = 0;
		if ( $group_id && function_exists( 'groups_get_group' ) ) {
			$group        = groups_get_group( $group_id );
			$member_count = $group->total_member_count ?? 0;
		}

		return array(
			'id'                  => $post->ID,
			'company_name'        => carbon_get_post_meta( $post->ID, 'pp_company_name' ),
			'title'               => $post->post_title,
			'slug'                => $post->post_name,
			'url'                 => get_permalink( $post->ID ),
			'edit_url'            => get_edit_post_link( $post->ID, 'raw' ),
			'buddypress_group_id' => $group_id,
			'member_count'        => $member_count,
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
