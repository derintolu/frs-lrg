<?php
/**
 * Partnership Abilities
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

use LendingResourceHub\Models\Partnership;
use WP_Error;

/**
 * Class PartnershipAbilities
 *
 * Registers abilities for partnership management.
 */
class PartnershipAbilities {

	/**
	 * Register all partnership abilities
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_get_partnerships();
		self::register_get_partnership();
		self::register_create_partnership();
		self::register_update_partnership();
		self::register_delete_partnership();
	}

	/**
	 * Register get-partnerships ability
	 *
	 * @return void
	 */
	private static function register_get_partnerships(): void {
		wp_register_ability(
			'lrh/get-partnerships',
			array(
				'label'       => __( 'Get Partnerships', 'lending-resource-hub' ),
				'description' => __( 'Retrieves a list of partnerships with optional filtering by status, loan officer, or agent.', 'lending-resource-hub' ),
				'category'    => 'partnership-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'status' => array(
							'type'        => 'string',
							'description' => __( 'Filter by partnership status.', 'lending-resource-hub' ),
							'enum'        => array( 'active', 'pending', 'declined', 'cancelled' ),
						),
						'loan_officer_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by loan officer user ID.', 'lending-resource-hub' ),
						),
						'agent_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by agent/realtor user ID.', 'lending-resource-hub' ),
						),
						'limit' => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of results to return.', 'lending-resource-hub' ),
							'default'     => 10,
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
							'id'                => array( 'type' => 'integer' ),
							'loan_officer_id'   => array( 'type' => 'integer' ),
							'agent_id'          => array( 'type' => 'integer' ),
							'partner_post_id'   => array( 'type' => 'integer' ),
							'partner_email'     => array( 'type' => 'string' ),
							'partner_name'      => array( 'type' => 'string' ),
							'status'            => array( 'type' => 'string' ),
							'created_date'      => array( 'type' => 'string' ),
							'updated_date'      => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_partnerships' ),
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
	 * Execute get-partnerships ability
	 *
	 * @param array $input Input parameters.
	 * @return array List of partnerships.
	 */
	public static function execute_get_partnerships( array $input ): array {
		$query = Partnership::query();

		if ( isset( $input['status'] ) ) {
			$query->where( 'status', sanitize_text_field( $input['status'] ) );
		}

		if ( isset( $input['loan_officer_id'] ) ) {
			$query->where( 'loan_officer_id', absint( $input['loan_officer_id'] ) );
		}

		if ( isset( $input['agent_id'] ) ) {
			$query->where( 'agent_id', absint( $input['agent_id'] ) );
		}

		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 10;
		$partnerships = $query->limit( $limit )->get();

		return $partnerships->map( function( $partnership ) {
			return array(
				'id'              => $partnership->id,
				'loan_officer_id' => $partnership->loan_officer_id,
				'agent_id'        => $partnership->agent_id,
				'partner_post_id' => $partnership->partner_post_id,
				'partner_email'   => $partnership->partner_email,
				'partner_name'    => $partnership->partner_name,
				'status'          => $partnership->status,
				'created_date'    => $partnership->created_date ? $partnership->created_date->format( 'Y-m-d H:i:s' ) : null,
				'updated_date'    => $partnership->updated_date ? $partnership->updated_date->format( 'Y-m-d H:i:s' ) : null,
			);
		} )->toArray();
	}

	/**
	 * Register get-partnership ability
	 *
	 * @return void
	 */
	private static function register_get_partnership(): void {
		wp_register_ability(
			'lrh/get-partnership',
			array(
				'label'       => __( 'Get Partnership', 'lending-resource-hub' ),
				'description' => __( 'Retrieves detailed information about a specific partnership by ID.', 'lending-resource-hub' ),
				'category'    => 'partnership-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The partnership ID.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'                => array( 'type' => 'integer' ),
						'loan_officer_id'   => array( 'type' => 'integer' ),
						'agent_id'          => array( 'type' => 'integer' ),
						'partner_post_id'   => array( 'type' => 'integer' ),
						'partner_email'     => array( 'type' => 'string' ),
						'partner_name'      => array( 'type' => 'string' ),
						'status'            => array( 'type' => 'string' ),
						'invite_token'      => array( 'type' => 'string' ),
						'invite_sent_date'  => array( 'type' => 'string' ),
						'accepted_date'     => array( 'type' => 'string' ),
						'custom_data'       => array( 'type' => 'object' ),
						'created_date'      => array( 'type' => 'string' ),
						'updated_date'      => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_partnership' ),
				'permission_callback' => function( $input ) {
					if ( ! current_user_can( 'edit_posts' ) ) {
						return false;
					}
					return true;
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
	 * Execute get-partnership ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Partnership details or error.
	 */
	public static function execute_get_partnership( array $input ) {
		$partnership = Partnership::find( absint( $input['id'] ) );

		if ( ! $partnership ) {
			return new WP_Error(
				'partnership_not_found',
				__( 'Partnership not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		return array(
			'id'               => $partnership->id,
			'loan_officer_id'  => $partnership->loan_officer_id,
			'agent_id'         => $partnership->agent_id,
			'partner_post_id'  => $partnership->partner_post_id,
			'partner_email'    => $partnership->partner_email,
			'partner_name'     => $partnership->partner_name,
			'status'           => $partnership->status,
			'invite_token'     => $partnership->invite_token,
			'invite_sent_date' => $partnership->invite_sent_date ? $partnership->invite_sent_date->format( 'Y-m-d H:i:s' ) : null,
			'accepted_date'    => $partnership->accepted_date ? $partnership->accepted_date->format( 'Y-m-d H:i:s' ) : null,
			'custom_data'      => $partnership->custom_data,
			'created_date'     => $partnership->created_date ? $partnership->created_date->format( 'Y-m-d H:i:s' ) : null,
			'updated_date'     => $partnership->updated_date ? $partnership->updated_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register create-partnership ability
	 *
	 * @return void
	 */
	private static function register_create_partnership(): void {
		wp_register_ability(
			'lrh/create-partnership',
			array(
				'label'       => __( 'Create Partnership', 'lending-resource-hub' ),
				'description' => __( 'Creates a new partnership between a loan officer and realtor agent.', 'lending-resource-hub' ),
				'category'    => 'partnership-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'loan_officer_id' => array(
							'type'        => 'integer',
							'description' => __( 'The loan officer user ID.', 'lending-resource-hub' ),
						),
						'agent_id' => array(
							'type'        => 'integer',
							'description' => __( 'The agent/realtor user ID.', 'lending-resource-hub' ),
						),
						'partner_post_id' => array(
							'type'        => 'integer',
							'description' => __( 'The partner portal custom post ID.', 'lending-resource-hub' ),
						),
						'partner_email' => array(
							'type'        => 'string',
							'description' => __( 'Partner email address.', 'lending-resource-hub' ),
							'format'      => 'email',
						),
						'partner_name' => array(
							'type'        => 'string',
							'description' => __( 'Partner name.', 'lending-resource-hub' ),
						),
						'status' => array(
							'type'        => 'string',
							'description' => __( 'Partnership status.', 'lending-resource-hub' ),
							'enum'        => array( 'active', 'pending', 'declined', 'cancelled' ),
							'default'     => 'pending',
						),
					),
					'required'             => array( 'loan_officer_id', 'partner_email', 'partner_name' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array( 'type' => 'integer' ),
						'loan_officer_id' => array( 'type' => 'integer' ),
						'agent_id'        => array( 'type' => 'integer' ),
						'partner_post_id' => array( 'type' => 'integer' ),
						'partner_email'   => array( 'type' => 'string' ),
						'partner_name'    => array( 'type' => 'string' ),
						'status'          => array( 'type' => 'string' ),
						'created_date'    => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_create_partnership' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => false,
						'idempotent' => false,
					),
				),
			)
		);
	}

	/**
	 * Execute create-partnership ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Created partnership or error.
	 */
	public static function execute_create_partnership( array $input ) {
		$partnership = Partnership::create(
			array(
				'loan_officer_id' => absint( $input['loan_officer_id'] ),
				'agent_id'        => isset( $input['agent_id'] ) ? absint( $input['agent_id'] ) : null,
				'partner_post_id' => isset( $input['partner_post_id'] ) ? absint( $input['partner_post_id'] ) : null,
				'partner_email'   => sanitize_email( $input['partner_email'] ),
				'partner_name'    => sanitize_text_field( $input['partner_name'] ),
				'status'          => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'pending',
				'created_date'    => current_time( 'mysql' ),
				'updated_date'    => current_time( 'mysql' ),
			)
		);

		if ( ! $partnership ) {
			return new WP_Error(
				'partnership_creation_failed',
				__( 'Failed to create partnership.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'id'              => $partnership->id,
			'loan_officer_id' => $partnership->loan_officer_id,
			'agent_id'        => $partnership->agent_id,
			'partner_post_id' => $partnership->partner_post_id,
			'partner_email'   => $partnership->partner_email,
			'partner_name'    => $partnership->partner_name,
			'status'          => $partnership->status,
			'created_date'    => $partnership->created_date ? $partnership->created_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register update-partnership ability
	 *
	 * @return void
	 */
	private static function register_update_partnership(): void {
		wp_register_ability(
			'lrh/update-partnership',
			array(
				'label'       => __( 'Update Partnership', 'lending-resource-hub' ),
				'description' => __( 'Updates an existing partnership\'s details including status, agent assignment, or other attributes.', 'lending-resource-hub' ),
				'category'    => 'partnership-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The partnership ID to update.', 'lending-resource-hub' ),
						),
						'agent_id' => array(
							'type'        => 'integer',
							'description' => __( 'Update the agent/realtor user ID.', 'lending-resource-hub' ),
						),
						'partner_post_id' => array(
							'type'        => 'integer',
							'description' => __( 'Update the partner portal custom post ID.', 'lending-resource-hub' ),
						),
						'status' => array(
							'type'        => 'string',
							'description' => __( 'Update partnership status.', 'lending-resource-hub' ),
							'enum'        => array( 'active', 'pending', 'declined', 'cancelled' ),
						),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array( 'type' => 'integer' ),
						'loan_officer_id' => array( 'type' => 'integer' ),
						'agent_id'        => array( 'type' => 'integer' ),
						'partner_post_id' => array( 'type' => 'integer' ),
						'status'          => array( 'type' => 'string' ),
						'updated_date'    => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_update_partnership' ),
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
	 * Execute update-partnership ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Updated partnership or error.
	 */
	public static function execute_update_partnership( array $input ) {
		$partnership = Partnership::find( absint( $input['id'] ) );

		if ( ! $partnership ) {
			return new WP_Error(
				'partnership_not_found',
				__( 'Partnership not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$update_data = array( 'updated_date' => current_time( 'mysql' ) );

		if ( isset( $input['agent_id'] ) ) {
			$update_data['agent_id'] = absint( $input['agent_id'] );
		}

		if ( isset( $input['partner_post_id'] ) ) {
			$update_data['partner_post_id'] = absint( $input['partner_post_id'] );
		}

		if ( isset( $input['status'] ) ) {
			$update_data['status'] = sanitize_text_field( $input['status'] );
		}

		$partnership->update( $update_data );

		return array(
			'id'              => $partnership->id,
			'loan_officer_id' => $partnership->loan_officer_id,
			'agent_id'        => $partnership->agent_id,
			'partner_post_id' => $partnership->partner_post_id,
			'status'          => $partnership->status,
			'updated_date'    => $partnership->updated_date ? $partnership->updated_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register delete-partnership ability
	 *
	 * @return void
	 */
	private static function register_delete_partnership(): void {
		wp_register_ability(
			'lrh/delete-partnership',
			array(
				'label'       => __( 'Delete Partnership', 'lending-resource-hub' ),
				'description' => __( 'Permanently deletes a partnership. This action cannot be undone.', 'lending-resource-hub' ),
				'category'    => 'partnership-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The partnership ID to delete.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'id'      => array( 'type' => 'integer' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_delete_partnership' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					),
				),
			)
		);
	}

	/**
	 * Execute delete-partnership ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public static function execute_delete_partnership( array $input ) {
		$partnership = Partnership::find( absint( $input['id'] ) );

		if ( ! $partnership ) {
			return new WP_Error(
				'partnership_not_found',
				__( 'Partnership not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$partnership_id = $partnership->id;
		$partnership->delete();

		return array(
			'success' => true,
			'id'      => $partnership_id,
		);
	}
}
