<?php
/**
 * FluentForms Integration
 *
 * Handles integration with FluentForms for lead capture and submission processing.
 * Automatically creates leads in the lead_submissions table when forms are submitted.
 *
 * @package LendingResourceHub\Integrations
 * @since 1.0.0
 */

namespace LendingResourceHub\Integrations;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Models\LeadSubmission;

defined( 'ABSPATH' ) || exit;

/**
 * Class FluentForms
 *
 * Manages FluentForms plugin integration and lead tracking.
 *
 * @package LendingResourceHub\Integrations
 */
class FluentForms {

	use Base;

	/**
	 * Initialize FluentForms integration.
	 *
	 * @return void
	 */
	public function init() {
		// Only initialize if FluentForms is active
		if ( ! self::is_active() ) {
			return;
		}

		// Hook into FluentForms submission
		add_action( 'fluentform_submission_inserted', array( $this, 'handle_submission' ), 10, 3 );

		// Add custom columns to forms if needed
		add_filter( 'fluentform_entry_lists_columns', array( $this, 'add_custom_columns' ), 10, 2 );
	}

	/**
	 * Check if FluentForms is active.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public static function is_active() {
		return defined( 'FLUENTFORM' ) || class_exists( 'FluentForm\\App\\Modules\\Form\\Form' );
	}

	/**
	 * Get form URL by form ID.
	 *
	 * @param int $form_id FluentForms form ID.
	 * @return string Form URL.
	 */
	public static function get_form_url( $form_id ) {
		return home_url( '/?fluent-form=' . intval( $form_id ) );
	}

	/**
	 * Get form URL with tracking parameters.
	 *
	 * @param int      $form_id FluentForms form ID.
	 * @param int|null $loan_officer_id Loan officer user ID.
	 * @param int|null $agent_id Agent user ID.
	 * @param int|null $partnership_id Partnership ID.
	 * @return string Form URL with query parameters.
	 */
	public static function get_tracking_form_url( $form_id, $loan_officer_id = null, $agent_id = null, $partnership_id = null ) {
		$args = array();

		if ( $loan_officer_id ) {
			$args['loan_officer_id'] = $loan_officer_id;
		}

		if ( $agent_id ) {
			$args['agent_id'] = $agent_id;
		}

		if ( $partnership_id ) {
			$args['partnership_id'] = $partnership_id;
		}

		$form_url = self::get_form_url( $form_id );

		return ! empty( $args ) ? add_query_arg( $args, $form_url ) : $form_url;
	}

	/**
	 * Handle form submission - create lead.
	 *
	 * @param int   $entry_id FluentForms entry ID.
	 * @param array $form_data Submitted form data.
	 * @param object $form FluentForms form object.
	 * @return void
	 */
	public function handle_submission( $entry_id, $form_data, $form ) {
		// Only process specific forms (prequal, contact, mortgage apps)
		$form_id       = $form->id;
		$tracked_forms = get_option( 'lrh_tracked_form_ids', array( 3, 4, 5 ) );

		if ( ! in_array( $form_id, $tracked_forms, true ) ) {
			return;
		}

		// Extract data from submission
		$first_name = $form_data['names']['first_name'] ?? $form_data['first_name'] ?? '';
		$last_name  = $form_data['names']['last_name'] ?? $form_data['last_name'] ?? '';
		$email      = $form_data['email'] ?? '';
		$phone      = $form_data['phone'] ?? '';
		$message    = $form_data['message'] ?? $form_data['description'] ?? '';

		// Get loan officer/agent from form hidden fields or URL params
		$loan_officer_id = $form_data['loan_officer_id'] ?? ( $_GET['loan_officer_id'] ?? null );
		$agent_id        = $form_data['agent_id'] ?? ( $_GET['agent_id'] ?? null );
		$partnership_id  = $form_data['partnership_id'] ?? ( $_GET['partnership_id'] ?? null );

		// Get property/loan information if available
		$property_address = $form_data['property_address'] ?? $form_data['address'] ?? '';
		$property_city    = $form_data['property_city'] ?? $form_data['city'] ?? '';
		$property_state   = $form_data['property_state'] ?? $form_data['state'] ?? '';
		$property_zip     = $form_data['property_zip'] ?? $form_data['zip'] ?? '';
		$loan_amount      = $form_data['loan_amount'] ?? $form_data['homePrice'] ?? '';
		$down_payment     = $form_data['down_payment'] ?? $form_data['downPayment'] ?? '';
		$credit_score     = $form_data['credit_score'] ?? '';
		$employment_status = $form_data['employment_status'] ?? '';
		$annual_income    = $form_data['annual_income'] ?? '';

		// Build complete property address
		$full_address = trim(
			implode(
				', ',
				array_filter(
					array(
						$property_address,
						$property_city,
						$property_state,
						$property_zip,
					)
				)
			)
		);

		// Determine lead source
		$form_title   = $form->title ?? 'Form ' . $form_id;
		$lead_source  = 'fluent_form_' . $form_id;
		$source_label = 'FluentForm: ' . $form_title;

		// Create lead in database using Eloquent
		try {
			if ( class_exists( 'LendingResourceHub\\Models\\LeadSubmission' ) ) {
				LeadSubmission::create(
					array(
						'first_name'        => sanitize_text_field( $first_name ),
						'last_name'         => sanitize_text_field( $last_name ),
						'email'             => sanitize_email( $email ),
						'phone'             => sanitize_text_field( $phone ),
						'message'           => sanitize_textarea_field( $message ),
						'loan_officer_id'   => $loan_officer_id ? intval( $loan_officer_id ) : null,
						'agent_id'          => $agent_id ? intval( $agent_id ) : null,
						'partnership_id'    => $partnership_id ? intval( $partnership_id ) : null,
						'lead_source'       => sanitize_text_field( $lead_source ),
						'source_label'      => sanitize_text_field( $source_label ),
						'status'            => 'new',
						'property_address'  => sanitize_text_field( $full_address ),
						'loan_amount'       => $loan_amount ? floatval( str_replace( array( '$', ',' ), '', $loan_amount ) ) : null,
						'down_payment'      => $down_payment ? floatval( str_replace( array( '$', ',' ), '', $down_payment ) ) : null,
						'credit_score'      => $credit_score ? intval( $credit_score ) : null,
						'employment_status' => sanitize_text_field( $employment_status ),
						'annual_income'     => $annual_income ? floatval( str_replace( array( '$', ',' ), '', $annual_income ) ) : null,
						'custom_data'       => wp_json_encode( $form_data ),
						'created_date'      => current_time( 'mysql' ),
					)
				);

				// Log success
				error_log( 'LRH: Lead created from FluentForm #' . $form_id . ' - ' . $email );
			}
		} catch ( \Exception $e ) {
			// Log error
			error_log( 'LRH: Failed to create lead from FluentForm #' . $form_id . ' - ' . $e->getMessage() );
		}
	}

	/**
	 * Add custom columns to FluentForms entry list.
	 *
	 * @param array $columns Existing columns.
	 * @param int   $form_id Form ID.
	 * @return array Modified columns.
	 */
	public function add_custom_columns( $columns, $form_id ) {
		$columns['loan_officer']   = __( 'Loan Officer', 'lending-resource-hub' );
		$columns['realtor_partner'] = __( 'Realtor Partner', 'lending-resource-hub' );

		return $columns;
	}

	/**
	 * Get configured prequal form ID.
	 *
	 * @return int Form ID.
	 */
	public static function get_prequal_form_id() {
		return intval( get_option( 'lrh_prequal_form_id', 3 ) );
	}

	/**
	 * Get configured contact form ID.
	 *
	 * @return int Form ID.
	 */
	public static function get_contact_form_id() {
		return intval( get_option( 'lrh_contact_form_id', 4 ) );
	}

	/**
	 * Get configured mortgage application form ID.
	 *
	 * @return int Form ID.
	 */
	public static function get_mortgage_form_id() {
		return intval( get_option( 'lrh_mortgage_form_id', 5 ) );
	}

	/**
	 * Update tracked form IDs.
	 *
	 * @param array $form_ids Array of form IDs to track.
	 * @return bool Update success.
	 */
	public static function set_tracked_forms( $form_ids ) {
		return update_option( 'lrh_tracked_form_ids', array_map( 'intval', $form_ids ) );
	}
}
