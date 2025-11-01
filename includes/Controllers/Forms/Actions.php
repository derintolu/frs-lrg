<?php
/**
 * Forms Controller
 *
 * Handles form submissions and webhook endpoints.
 *
 * @package LendingResourceHub\Controllers\Forms
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Forms;

use LendingResourceHub\Models\LeadSubmission;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles form submission and webhook-related actions.
 *
 * @package LendingResourceHub\Controllers\Forms
 */
class Actions {

	/**
	 * Handle form submissions from JetFormBuilder or other form plugins.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function handle_form_submission( WP_REST_Request $request ) {
		$params = $request->get_params();

		// Get field mappings configuration
		$field_mappings = get_option( 'frs_form_field_mappings', array() );

		// Extract lead data using field mappings
		$lead_data = $this->map_form_fields( $params, $field_mappings );

		// Determine partnership context
		$partnership_context = $this->determine_partnership_context( $request, $params );

		// Store lead submission
		$lead = LeadSubmission::create(
			array_merge(
				$lead_data,
				$partnership_context,
				array(
					'lead_source' => $params['source'] ?? 'form_submission',
					'form_data'   => wp_json_encode( $params ),
					'lead_data'   => wp_json_encode( $lead_data ),
				)
			)
		);

		if ( $lead ) {
			// Send notifications (will be implemented)
			do_action( 'lrh_lead_submitted', $lead->id, $partnership_context['partnership_id'] ?? null );

			// Return success response
			return rest_ensure_response(
				array(
					'success' => true,
					'lead_id' => $lead->id,
					'message' => __( 'Lead submitted successfully', 'lending-resource-hub' ),
				)
			);
		}

		return new WP_Error( 'submission_failed', __( 'Failed to submit lead', 'lending-resource-hub' ), array( 'status' => 500 ) );
	}

	/**
	 * Handle partnership webhooks.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function handle_partnership_webhook( WP_REST_Request $request ) {
		$params = $request->get_params();

		// Process webhook based on type
		switch ( $params['type'] ?? '' ) {
			case 'partnership_created':
				return $this->handle_partnership_created_webhook( $params );

			case 'partnership_updated':
				return $this->handle_partnership_updated_webhook( $params );

			case 'lead_assignment':
				return $this->handle_lead_assignment_webhook( $params );

			default:
				return new WP_Error( 'invalid_webhook_type', __( 'Invalid webhook type', 'lending-resource-hub' ), array( 'status' => 400 ) );
		}
	}

	/**
	 * Map form fields to database fields.
	 *
	 * @param array $params Form parameters.
	 * @param array $field_mappings Field mappings configuration.
	 * @return array Mapped data.
	 */
	private function map_form_fields( $params, $field_mappings ) {
		$mapped_data = array();

		// Default field mappings if none configured
		if ( empty( $field_mappings ) ) {
			$field_mappings = array(
				'first_name'       => 'first_name',
				'last_name'        => 'last_name',
				'email'            => 'email',
				'phone'            => 'phone',
				'loan_amount'      => 'loan_amount',
				'property_value'   => 'property_value',
				'property_address' => 'property_address',
			);
		}

		foreach ( $field_mappings as $form_field => $db_field ) {
			if ( isset( $params[ $form_field ] ) ) {
				$value = $params[ $form_field ];

				// Sanitize based on field type
				switch ( $db_field ) {
					case 'email':
						$mapped_data[ $db_field ] = sanitize_email( $value );
						break;
					case 'loan_amount':
					case 'property_value':
						$mapped_data[ $db_field ] = floatval( preg_replace( '/[^0-9.]/', '', $value ) );
						break;
					case 'phone':
						$mapped_data[ $db_field ] = preg_replace( '/[^0-9\+\-\(\)\s]/', '', $value );
						break;
					default:
						$mapped_data[ $db_field ] = sanitize_text_field( $value );
				}
			}
		}

		return $mapped_data;
	}

	/**
	 * Determine partnership context from form submission.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @param array           $params Form parameters.
	 * @return array Partnership context.
	 */
	private function determine_partnership_context( $request, $params ) {
		$context = array(
			'partnership_id'  => null,
			'loan_officer_id' => null,
			'agent_id'        => null,
		);

		// Check for partnership ID in params
		if ( ! empty( $params['partnership_id'] ) ) {
			$partnership = \LendingResourceHub\Models\Partnership::find( $params['partnership_id'] );
			if ( $partnership && $partnership->status === 'active' ) {
				$context['partnership_id']  = $partnership->id;
				$context['loan_officer_id'] = $partnership->loan_officer_id;
				$context['agent_id']        = $partnership->agent_id;
			}
		}

		// Check for user IDs in params
		if ( ! empty( $params['loan_officer_id'] ) ) {
			$context['loan_officer_id'] = intval( $params['loan_officer_id'] );
		}

		if ( ! empty( $params['agent_id'] ) ) {
			$context['agent_id'] = intval( $params['agent_id'] );
		}

		// Try to determine from referrer URL
		if ( empty( $context['loan_officer_id'] ) && empty( $context['agent_id'] ) ) {
			$referrer = $request->get_header( 'referer' );
			if ( $referrer ) {
				$context = array_merge( $context, $this->extract_context_from_url( $referrer ) );
			}
		}

		return $context;
	}

	/**
	 * Extract partnership context from URL.
	 *
	 * @param string $url The URL to extract context from.
	 * @return array Partnership context.
	 */
	private function extract_context_from_url( $url ) {
		$context = array();

		// Parse URL to get page slug
		$parsed_url = wp_parse_url( $url );
		$path       = trim( $parsed_url['path'], '/' );
		$path_parts = explode( '/', $path );
		$slug       = end( $path_parts );

		// Find page by slug
		$page = get_page_by_path( $slug );
		if ( $page ) {
			// Check meta fields for loan officer and agent IDs
			$loan_officer_id = get_post_meta( $page->ID, '_frs_assigned_user_id', true );
			$agent_id        = get_post_meta( $page->ID, '_frs_partner_user_id', true );

			if ( $loan_officer_id ) {
				$context['loan_officer_id'] = intval( $loan_officer_id );
			}
			if ( $agent_id ) {
				$context['agent_id'] = intval( $agent_id );
			}
		}

		return $context;
	}

	/**
	 * Handle partnership created webhook.
	 *
	 * @param array $params Webhook parameters.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	private function handle_partnership_created_webhook( $params ) {
		// Implementation placeholder
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Partnership created webhook received',
			)
		);
	}

	/**
	 * Handle partnership updated webhook.
	 *
	 * @param array $params Webhook parameters.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	private function handle_partnership_updated_webhook( $params ) {
		// Implementation placeholder
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Partnership updated webhook received',
			)
		);
	}

	/**
	 * Handle lead assignment webhook.
	 *
	 * @param array $params Webhook parameters.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	private function handle_lead_assignment_webhook( $params ) {
		// Implementation placeholder
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'Lead assignment webhook received',
			)
		);
	}
}
