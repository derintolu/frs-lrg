<?php
/**
 * Lead Abilities
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

use LendingResourceHub\Models\LeadSubmission;
use WP_Error;

/**
 * Class LeadAbilities
 *
 * Registers abilities for lead management.
 */
class LeadAbilities {

	/**
	 * Register all lead abilities
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_get_leads();
		self::register_get_lead();
		self::register_create_lead();
		self::register_update_lead_status();
	}

	/**
	 * Register get-leads ability
	 *
	 * @return void
	 */
	private static function register_get_leads(): void {
		wp_register_ability(
			'lrh/get-leads',
			array(
				'label'       => __( 'Get Leads', 'lending-resource-hub' ),
				'description' => __( 'Retrieves a list of lead submissions with optional filtering by status, partnership, or source.', 'lending-resource-hub' ),
				'category'    => 'lead-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'status' => array(
							'type'        => 'string',
							'description' => __( 'Filter by lead status.', 'lending-resource-hub' ),
							'enum'        => array( 'new', 'contacted', 'qualified', 'converted', 'closed' ),
						),
						'partnership_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by partnership ID.', 'lending-resource-hub' ),
						),
						'loan_officer_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by loan officer user ID.', 'lending-resource-hub' ),
						),
						'agent_id' => array(
							'type'        => 'integer',
							'description' => __( 'Filter by agent/realtor user ID.', 'lending-resource-hub' ),
						),
						'lead_source' => array(
							'type'        => 'string',
							'description' => __( 'Filter by lead source.', 'lending-resource-hub' ),
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
							'id'              => array( 'type' => 'integer' ),
							'partnership_id'  => array( 'type' => 'integer' ),
							'loan_officer_id' => array( 'type' => 'integer' ),
							'agent_id'        => array( 'type' => 'integer' ),
							'lead_source'     => array( 'type' => 'string' ),
							'first_name'      => array( 'type' => 'string' ),
							'last_name'       => array( 'type' => 'string' ),
							'email'           => array( 'type' => 'string' ),
							'phone'           => array( 'type' => 'string' ),
							'status'          => array( 'type' => 'string' ),
							'created_date'    => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_leads' ),
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
	 * Execute get-leads ability
	 *
	 * @param array $input Input parameters.
	 * @return array List of leads.
	 */
	public static function execute_get_leads( array $input ): array {
		$query = LeadSubmission::query();

		if ( isset( $input['status'] ) ) {
			$query->where( 'status', sanitize_text_field( $input['status'] ) );
		}

		if ( isset( $input['partnership_id'] ) ) {
			$query->where( 'partnership_id', absint( $input['partnership_id'] ) );
		}

		if ( isset( $input['loan_officer_id'] ) ) {
			$query->where( 'loan_officer_id', absint( $input['loan_officer_id'] ) );
		}

		if ( isset( $input['agent_id'] ) ) {
			$query->where( 'agent_id', absint( $input['agent_id'] ) );
		}

		if ( isset( $input['lead_source'] ) ) {
			$query->where( 'lead_source', sanitize_text_field( $input['lead_source'] ) );
		}

		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 10;
		$leads = $query->orderBy( 'created_date', 'DESC' )->limit( $limit )->get();

		return $leads->map( function( $lead ) {
			return array(
				'id'              => $lead->id,
				'partnership_id'  => $lead->partnership_id,
				'loan_officer_id' => $lead->loan_officer_id,
				'agent_id'        => $lead->agent_id,
				'lead_source'     => $lead->lead_source,
				'first_name'      => $lead->first_name,
				'last_name'       => $lead->last_name,
				'email'           => $lead->email,
				'phone'           => $lead->phone,
				'status'          => $lead->status,
				'created_date'    => $lead->created_date ? $lead->created_date->format( 'Y-m-d H:i:s' ) : null,
			);
		} )->toArray();
	}

	/**
	 * Register get-lead ability
	 *
	 * @return void
	 */
	private static function register_get_lead(): void {
		wp_register_ability(
			'lrh/get-lead',
			array(
				'label'       => __( 'Get Lead', 'lending-resource-hub' ),
				'description' => __( 'Retrieves detailed information about a specific lead submission by ID.', 'lending-resource-hub' ),
				'category'    => 'lead-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The lead submission ID.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'               => array( 'type' => 'integer' ),
						'partnership_id'   => array( 'type' => 'integer' ),
						'loan_officer_id'  => array( 'type' => 'integer' ),
						'agent_id'         => array( 'type' => 'integer' ),
						'lead_source'      => array( 'type' => 'string' ),
						'first_name'       => array( 'type' => 'string' ),
						'last_name'        => array( 'type' => 'string' ),
						'email'            => array( 'type' => 'string' ),
						'phone'            => array( 'type' => 'string' ),
						'loan_amount'      => array( 'type' => 'string' ),
						'property_value'   => array( 'type' => 'string' ),
						'property_address' => array( 'type' => 'string' ),
						'status'           => array( 'type' => 'string' ),
						'created_date'     => array( 'type' => 'string' ),
						'updated_date'     => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_lead' ),
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
	 * Execute get-lead ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Lead details or error.
	 */
	public static function execute_get_lead( array $input ) {
		$lead = LeadSubmission::find( absint( $input['id'] ) );

		if ( ! $lead ) {
			return new WP_Error(
				'lead_not_found',
				__( 'Lead submission not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		return array(
			'id'               => $lead->id,
			'partnership_id'   => $lead->partnership_id,
			'loan_officer_id'  => $lead->loan_officer_id,
			'agent_id'         => $lead->agent_id,
			'lead_source'      => $lead->lead_source,
			'first_name'       => $lead->first_name,
			'last_name'        => $lead->last_name,
			'email'            => $lead->email,
			'phone'            => $lead->phone,
			'loan_amount'      => $lead->loan_amount,
			'property_value'   => $lead->property_value,
			'property_address' => $lead->property_address,
			'status'           => $lead->status,
			'created_date'     => $lead->created_date ? $lead->created_date->format( 'Y-m-d H:i:s' ) : null,
			'updated_date'     => $lead->updated_date ? $lead->updated_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register create-lead ability
	 *
	 * @return void
	 */
	private static function register_create_lead(): void {
		wp_register_ability(
			'lrh/create-lead',
			array(
				'label'       => __( 'Create Lead', 'lending-resource-hub' ),
				'description' => __( 'Creates a new lead submission from form data or external sources.', 'lending-resource-hub' ),
				'category'    => 'lead-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'partnership_id' => array(
							'type'        => 'integer',
							'description' => __( 'The partnership ID this lead belongs to.', 'lending-resource-hub' ),
						),
						'loan_officer_id' => array(
							'type'        => 'integer',
							'description' => __( 'The loan officer user ID.', 'lending-resource-hub' ),
						),
						'agent_id' => array(
							'type'        => 'integer',
							'description' => __( 'The agent/realtor user ID.', 'lending-resource-hub' ),
						),
						'lead_source' => array(
							'type'        => 'string',
							'description' => __( 'The source of this lead.', 'lending-resource-hub' ),
						),
						'first_name' => array(
							'type'        => 'string',
							'description' => __( 'Lead first name.', 'lending-resource-hub' ),
						),
						'last_name' => array(
							'type'        => 'string',
							'description' => __( 'Lead last name.', 'lending-resource-hub' ),
						),
						'email' => array(
							'type'        => 'string',
							'description' => __( 'Lead email address.', 'lending-resource-hub' ),
							'format'      => 'email',
						),
						'phone' => array(
							'type'        => 'string',
							'description' => __( 'Lead phone number.', 'lending-resource-hub' ),
						),
						'loan_amount' => array(
							'type'        => 'string',
							'description' => __( 'Desired loan amount.', 'lending-resource-hub' ),
						),
						'property_value' => array(
							'type'        => 'string',
							'description' => __( 'Property value.', 'lending-resource-hub' ),
						),
						'property_address' => array(
							'type'        => 'string',
							'description' => __( 'Property address.', 'lending-resource-hub' ),
						),
					),
					'required'             => array( 'first_name', 'last_name', 'email' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array( 'type' => 'integer' ),
						'partnership_id'  => array( 'type' => 'integer' ),
						'loan_officer_id' => array( 'type' => 'integer' ),
						'first_name'      => array( 'type' => 'string' ),
						'last_name'       => array( 'type' => 'string' ),
						'email'           => array( 'type' => 'string' ),
						'status'          => array( 'type' => 'string' ),
						'created_date'    => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_create_lead' ),
				'permission_callback' => function() {
					return current_user_can( 'read' ); // Public leads can be created
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
	 * Execute create-lead ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Created lead or error.
	 */
	public static function execute_create_lead( array $input ) {
		$lead = LeadSubmission::create(
			array(
				'partnership_id'   => isset( $input['partnership_id'] ) ? absint( $input['partnership_id'] ) : null,
				'loan_officer_id'  => isset( $input['loan_officer_id'] ) ? absint( $input['loan_officer_id'] ) : null,
				'agent_id'         => isset( $input['agent_id'] ) ? absint( $input['agent_id'] ) : null,
				'lead_source'      => isset( $input['lead_source'] ) ? sanitize_text_field( $input['lead_source'] ) : null,
				'first_name'       => sanitize_text_field( $input['first_name'] ),
				'last_name'        => sanitize_text_field( $input['last_name'] ),
				'email'            => sanitize_email( $input['email'] ),
				'phone'            => isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : null,
				'loan_amount'      => isset( $input['loan_amount'] ) ? sanitize_text_field( $input['loan_amount'] ) : null,
				'property_value'   => isset( $input['property_value'] ) ? sanitize_text_field( $input['property_value'] ) : null,
				'property_address' => isset( $input['property_address'] ) ? sanitize_text_field( $input['property_address'] ) : null,
				'status'           => 'new',
				'created_date'     => current_time( 'mysql' ),
				'updated_date'     => current_time( 'mysql' ),
			)
		);

		if ( ! $lead ) {
			return new WP_Error(
				'lead_creation_failed',
				__( 'Failed to create lead submission.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'id'              => $lead->id,
			'partnership_id'  => $lead->partnership_id,
			'loan_officer_id' => $lead->loan_officer_id,
			'first_name'      => $lead->first_name,
			'last_name'       => $lead->last_name,
			'email'           => $lead->email,
			'status'          => $lead->status,
			'created_date'    => $lead->created_date ? $lead->created_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}

	/**
	 * Register update-lead-status ability
	 *
	 * @return void
	 */
	private static function register_update_lead_status(): void {
		wp_register_ability(
			'lrh/update-lead-status',
			array(
				'label'       => __( 'Update Lead Status', 'lending-resource-hub' ),
				'description' => __( 'Updates the status of a lead submission (e.g., new, contacted, qualified, converted, closed).', 'lending-resource-hub' ),
				'category'    => 'lead-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id' => array(
							'type'        => 'integer',
							'description' => __( 'The lead submission ID to update.', 'lending-resource-hub' ),
						),
						'status' => array(
							'type'        => 'string',
							'description' => __( 'New lead status.', 'lending-resource-hub' ),
							'enum'        => array( 'new', 'contacted', 'qualified', 'converted', 'closed' ),
						),
					),
					'required'             => array( 'id', 'status' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'id'           => array( 'type' => 'integer' ),
						'status'       => array( 'type' => 'string' ),
						'updated_date' => array( 'type' => 'string' ),
					),
				),
				'execute_callback' => array( self::class, 'execute_update_lead_status' ),
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
	 * Execute update-lead-status ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Updated lead or error.
	 */
	public static function execute_update_lead_status( array $input ) {
		$lead = LeadSubmission::find( absint( $input['id'] ) );

		if ( ! $lead ) {
			return new WP_Error(
				'lead_not_found',
				__( 'Lead submission not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$lead->update(
			array(
				'status'       => sanitize_text_field( $input['status'] ),
				'updated_date' => current_time( 'mysql' ),
			)
		);

		return array(
			'id'           => $lead->id,
			'status'       => $lead->status,
			'updated_date' => $lead->updated_date ? $lead->updated_date->format( 'Y-m-d H:i:s' ) : null,
		);
	}
}
