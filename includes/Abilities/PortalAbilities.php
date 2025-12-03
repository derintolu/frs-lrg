<?php
/**
 * Portal Abilities
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

use LendingResourceHub\Models\PageAssignment;
use WP_Error;

/**
 * Class PortalAbilities
 *
 * Registers abilities for portal management.
 */
class PortalAbilities {

	/**
	 * Register all portal abilities
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_get_page_assignments();
		self::register_assign_page();
		self::register_unassign_page();
		self::register_get_portal_tools();
		self::register_get_portal_config();
	}

	/**
	 * Register get-page-assignments ability
	 *
	 * @return void
	 */
	private static function register_get_page_assignments(): void {
		wp_register_ability(
			'lrh/get-page-assignments',
			array(
				'label'       => __( 'Get Page Assignments', 'lending-resource-hub' ),
				'description' => __( 'Retrieves page assignments for users, showing which custom portal pages are assigned to specific loan officers or realtors.', 'lending-resource-hub' ),
				'category'    => 'portal-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by user ID.', 'lending-resource-hub' ),
						),
						'page_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by page ID.', 'lending-resource-hub' ),
						),
						'limit' => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of results to return.', 'lending-resource-hub' ),
							'default'     => 20,
							'minimum'     => 1,
							'maximum'     => 100,
						),
					),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array( 'type' => 'integer' ),
							'user_id'      => array( 'type' => 'integer' ),
							'page_id'      => array( 'type' => 'integer' ),
							'assigned_date' => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_page_assignments' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
					),
				),
			)
		);
	}

	/**
	 * Execute get-page-assignments ability
	 *
	 * @param array $input Input parameters.
	 * @return array List of page assignments.
	 */
	public static function execute_get_page_assignments( array $input ): array {
		$query = PageAssignment::query();

		if ( isset( $input['user_id'] ) ) {
			$query->where( 'user_id', absint( $input['user_id'] ) );
		}

		if ( isset( $input['page_id'] ) ) {
			$query->where( 'page_id', absint( $input['page_id'] ) );
		}

		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 20;
		$assignments = $query->limit( $limit )->get();

		return $assignments->map( function( $assignment ) {
			return array(
				'id'            => $assignment->id,
				'user_id'       => $assignment->user_id,
				'page_id'       => $assignment->page_id,
				'assigned_date' => $assignment->assigned_date ? $assignment->assigned_date->format( 'Y-m-d H:i:s' ) : null,
			);
		} )->toArray();
	}

	/**
	 * Register assign-page ability
	 *
	 * @return void
	 */
	private static function register_assign_page(): void {
		wp_register_ability(
			'lrh/assign-page',
			array(
				'label'       => __( 'Assign Page', 'lending-resource-hub' ),
				'description' => __( 'Assigns a custom portal page to a specific user (loan officer or realtor).', 'lending-resource-hub' ),
				'category'    => 'portal-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'The user ID to assign the page to.', 'lending-resource-hub' ),
						),
						'page_id' => array(
							'type'        => 'integer',
							'description' => __( 'The page ID to assign.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'user_id', 'page_id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'            => array( 'type' => 'integer' ),
						'user_id'       => array( 'type' => 'integer' ),
						'page_id'       => array( 'type' => 'integer' ),
						'assigned_date' => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_assign_page' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => false,
						'idempotent' => true,
					),
				),
			)
		);
	}

	/**
	 * Execute assign-page ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Assignment result or error.
	 */
	public static function execute_assign_page( array $input ) {
		$user_id = absint( $input['user_id'] );
		$page_id = absint( $input['page_id'] );

		// Check if user exists
		if ( ! get_user_by( 'id', $user_id ) ) {
			return new WP_Error(
				'user_not_found',
				__( 'User not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Check if page exists
		if ( ! get_post( $page_id ) ) {
			return new WP_Error(
				'page_not_found',
				__( 'Page not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Check if assignment already exists
		$existing = PageAssignment::where( 'user_id', $user_id )
			->where( 'page_id', $page_id )
			->first();

		if ( $existing ) {
			return array(
				'id'            => $existing->id,
				'user_id'       => $existing->user_id,
				'page_id'       => $existing->page_id,
				'assigned_date' => $existing->assigned_date ? $existing->assigned_date->format( 'Y-m-d H:i:s' ) : null,
			);
		}

		// Create new assignment
		$assignment = PageAssignment::create(
			array(
				'user_id'       => $user_id,
				'page_id'       => $page_id,
				'assigned_date' => current_time( 'mysql' ),
			)
		);

		return array(
			'id'            => $assignment->id,
			'user_id'       => $assignment->user_id,
			'page_id'       => $assignment->page_id,
			'assigned_date' => $assignment->assigned_date ? $assignment->assigned_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register unassign-page ability
	 *
	 * @return void
	 */
	private static function register_unassign_page(): void {
		wp_register_ability(
			'lrh/unassign-page',
			array(
				'label'       => __( 'Unassign Page', 'lending-resource-hub' ),
				'description' => __( 'Removes a page assignment from a user.', 'lending-resource-hub' ),
				'category'    => 'portal-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'The user ID.', 'lending-resource-hub' ),
						),
						'page_id' => array(
							'type'        => 'integer',
							'description' => __( 'The page ID.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'user_id', 'page_id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_unassign_page' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => false,
						'idempotent' => true,
					),
				),
			)
		);
	}

	/**
	 * Execute unassign-page ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public static function execute_unassign_page( array $input ) {
		$user_id = absint( $input['user_id'] );
		$page_id = absint( $input['page_id'] );

		$assignment = PageAssignment::where( 'user_id', $user_id )
			->where( 'page_id', $page_id )
			->first();

		if ( ! $assignment ) {
			return new WP_Error(
				'assignment_not_found',
				__( 'Page assignment not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$assignment->delete();

		return array( 'success' => true );
	}

	/**
	 * Register get-portal-tools ability
	 *
	 * @return void
	 */
	private static function register_get_portal_tools(): void {
		wp_register_ability(
			'lrh/get-portal-tools',
			array(
				'label'       => __( 'Get Portal Tools', 'lending-resource-hub' ),
				'description' => __( 'Retrieves a list of available tools and features for portal users including calculators, forms, and integrations.', 'lending-resource-hub' ),
				'category'    => 'portal-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_role' => array(
							'type'        => 'string',
							'description' => __( 'Filter tools by user role.', 'lending-resource-hub' ),
							'enum'        => array( 'loan_officer', 'realtor_partner', 'all' ),
							'default'     => 'all',
						),
					),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'id'          => array( 'type' => 'string' ),
							'title'       => array( 'type' => 'string' ),
							'description' => array( 'type' => 'string' ),
							'category'    => array( 'type' => 'string' ),
							'url'         => array( 'type' => 'string' ),
							'roles'       => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_portal_tools' ),
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
					),
				),
			)
		);
	}

	/**
	 * Execute get-portal-tools ability
	 *
	 * @param array $input Input parameters.
	 * @return array List of portal tools.
	 */
	public static function execute_get_portal_tools( array $input ): array {
		// Define available tools
		$tools = array(
			array(
				'id'          => 'mortgage-calculator',
				'title'       => __( 'Mortgage Calculator', 'lending-resource-hub' ),
				'description' => __( 'Calculate monthly payments and amortization schedules.', 'lending-resource-hub' ),
				'category'    => 'calculators',
				'url'         => home_url( '/tools/mortgage-calculator' ),
				'roles'       => array( 'loan_officer', 'realtor_partner' ),
			),
			array(
				'id'          => 'property-search',
				'title'       => __( 'Property Search', 'lending-resource-hub' ),
				'description' => __( 'Search and analyze property values using Rentcast API.', 'lending-resource-hub' ),
				'category'    => 'property-tools',
				'url'         => home_url( '/tools/property-search' ),
				'roles'       => array( 'loan_officer', 'realtor_partner' ),
			),
			array(
				'id'          => 'calendar-booking',
				'title'       => __( 'Calendar & Booking', 'lending-resource-hub' ),
				'description' => __( 'Schedule appointments with FluentBooking integration.', 'lending-resource-hub' ),
				'category'    => 'scheduling',
				'url'         => home_url( '/tools/calendar' ),
				'roles'       => array( 'loan_officer', 'realtor_partner' ),
			),
			array(
				'id'          => 'lead-forms',
				'title'       => __( 'Lead Forms', 'lending-resource-hub' ),
				'description' => __( 'Manage and embed lead capture forms.', 'lending-resource-hub' ),
				'category'    => 'forms',
				'url'         => home_url( '/tools/lead-forms' ),
				'roles'       => array( 'loan_officer', 'realtor_partner' ),
			),
		);

		// Filter by role if specified
		$role_filter = isset( $input['user_role'] ) ? sanitize_text_field( $input['user_role'] ) : 'all';

		if ( 'all' !== $role_filter ) {
			$tools = array_filter( $tools, function( $tool ) use ( $role_filter ) {
				return in_array( $role_filter, $tool['roles'], true );
			} );
		}

		return array_values( $tools );
	}

	/**
	 * Register get-portal-config ability
	 *
	 * @return void
	 */
	private static function register_get_portal_config(): void {
		wp_register_ability(
			'lrh/get-portal-config',
			array(
				'label'       => __( 'Get Portal Config', 'lending-resource-hub' ),
				'description' => __( 'Retrieves portal configuration and settings for a specific user including branding, permissions, and feature access.', 'lending-resource-hub' ),
				'category'    => 'portal-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'The user ID to get configuration for. Defaults to current user.', 'lending-resource-hub' ),
						),
					),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id'     => array( 'type' => 'integer' ),
						'portal_slug' => array( 'type' => 'string' ),
						'role'        => array( 'type' => 'string' ),
						'branding'    => array(
							'type'       => 'object',
							'properties' => array(
								'primary_color'   => array( 'type' => 'string' ),
								'secondary_color' => array( 'type' => 'string' ),
								'logo_url'        => array( 'type' => 'string' ),
							),
						),
						'features'    => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_portal_config' ),
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
					),
				),
			)
		);
	}

	/**
	 * Execute get-portal-config ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Portal configuration or error.
	 */
	public static function execute_get_portal_config( array $input ) {
		$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : get_current_user_id();

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return new WP_Error(
				'user_not_found',
				__( 'User not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$role = 'subscriber';
		if ( in_array( 'loan_officer', $user->roles, true ) ) {
			$role = 'loan_officer';
		} elseif ( in_array( 'realtor_partner', $user->roles, true ) ) {
			$role = 'realtor_partner';
		}

		return array(
			'user_id'     => $user_id,
			'portal_slug' => 'loan_officer' === $role ? 'lo' : 're',
			'role'        => $role,
			'branding'    => array(
				'primary_color'   => get_user_meta( $user_id, 'portal_primary_color', true ) ?: '#1e40af',
				'secondary_color' => get_user_meta( $user_id, 'portal_secondary_color', true ) ?: '#3b82f6',
				'logo_url'        => get_user_meta( $user_id, 'portal_logo_url', true ) ?: '',
			),
			'features'    => array( 'calculators', 'forms', 'calendar', 'property-search', 'leads' ),
		);
	}
}
