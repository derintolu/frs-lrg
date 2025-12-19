<?php
/**
 * Leads Controller
 *
 * Handles lead-related API endpoints.
 *
 * @package LendingResourceHub\Controllers\Leads
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Leads;

use LendingResourceHub\Models\LeadSubmission;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles lead-related actions.
 *
 * @package LendingResourceHub\Controllers\Leads
 */
class Actions {

	/**
	 * Get all leads with LO and Agent names.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_leads( WP_REST_Request $request ) {
		global $wpdb;

		// Get all leads with loan officer and agent names via JOIN
		$leads = $wpdb->get_results(
			"SELECT l.*,
				u1.display_name as lo_name,
				u1.user_email as lo_email,
				u2.display_name as agent_name,
				u2.user_email as agent_email
			FROM {$wpdb->prefix}lead_submissions l
			LEFT JOIN {$wpdb->users} u1 ON l.loan_officer_id = u1.ID
			LEFT JOIN {$wpdb->users} u2 ON l.agent_id = u2.ID
			ORDER BY l.created_date DESC"
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $leads,
			),
			200
		);
	}

	/**
	 * Get leads for specific loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_leads_for_lo( WP_REST_Request $request ) {
		$lo_id     = $request->get_param( 'id' );
		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );

		$query = LeadSubmission::where( 'loan_officer_id', $lo_id );

		if ( $date_from ) {
			$query->where( 'created_date', '>=', $date_from );
		}

		if ( $date_to ) {
			$query->where( 'created_date', '<=', $date_to );
		}

		$leads = $query->orderBy( 'created_date', 'desc' )->get()->toArray();

		// Also get leads from FluentForms (frs-lead-pages)
		$fluent_leads = self::get_fluent_form_leads( $lo_id, 'loan_officer', $date_from, $date_to );

		// Merge and sort by date
		$all_leads = array_merge( $leads, $fluent_leads );
		usort( $all_leads, function( $a, $b ) {
			$date_a = $a['created_date'] ?? $a['created_at'] ?? '';
			$date_b = $b['created_date'] ?? $b['created_at'] ?? '';
			return strtotime( $date_b ) - strtotime( $date_a );
		});

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $all_leads,
			),
			200
		);
	}

	/**
	 * Get leads for specific realtor/agent.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_leads_for_realtor( WP_REST_Request $request ) {
		$realtor_id = $request->get_param( 'id' );
		$date_from  = $request->get_param( 'date_from' );
		$date_to    = $request->get_param( 'date_to' );

		$query = LeadSubmission::where( 'agent_id', $realtor_id );

		if ( $date_from ) {
			$query->where( 'created_date', '>=', $date_from );
		}

		if ( $date_to ) {
			$query->where( 'created_date', '<=', $date_to );
		}

		$leads = $query->orderBy( 'created_date', 'desc' )->get()->toArray();

		// Also get leads from FluentForms (frs-lead-pages)
		$fluent_leads = self::get_fluent_form_leads( $realtor_id, 'realtor', $date_from, $date_to );

		// Merge and sort by date
		$all_leads = array_merge( $leads, $fluent_leads );
		usort( $all_leads, function( $a, $b ) {
			$date_a = $a['created_date'] ?? $a['created_at'] ?? '';
			$date_b = $b['created_date'] ?? $b['created_at'] ?? '';
			return strtotime( $date_b ) - strtotime( $date_a );
		});

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $all_leads,
			),
			200
		);
	}

	/**
	 * Get leads from FluentForms submissions (frs-lead-pages).
	 *
	 * @param int         $user_id   The user ID.
	 * @param string      $user_type 'loan_officer' or 'realtor'.
	 * @param string|null $date_from Start date filter.
	 * @param string|null $date_to   End date filter.
	 * @return array Array of lead data.
	 */
	private static function get_fluent_form_leads( $user_id, $user_type, $date_from = null, $date_to = null ) {
		global $wpdb;

		// Check if FluentForms tables exist
		$table_name = $wpdb->prefix . 'fluentform_submissions';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			return [];
		}

		// Build the JSON path based on user type
		$json_field = $user_type === 'realtor' ? 'realtor_id' : 'loan_officer_id';

		// Build query
		$query = "SELECT id, response, status, created_at, updated_at
				  FROM {$table_name}
				  WHERE JSON_EXTRACT(response, '$.{$json_field}') = %s";
		$params = [ (string) $user_id ];

		if ( $date_from ) {
			$query .= ' AND created_at >= %s';
			$params[] = $date_from;
		}

		if ( $date_to ) {
			$query .= ' AND created_at <= %s';
			$params[] = $date_to;
		}

		$query .= ' ORDER BY created_at DESC LIMIT 100';

		$results = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		if ( ! $results ) {
			return [];
		}

		$leads = [];
		foreach ( $results as $row ) {
			$response = json_decode( $row->response, true );
			if ( ! $response ) {
				continue;
			}

			// Map FluentForms status to our status
			$status_map = [
				'unread'    => 'new',
				'read'      => 'contacted',
				'contacted' => 'contacted',
				'converted' => 'closed',
			];

			// Get page type for lead source
			$page_type = $response['page_type'] ?? 'lead_page';
			$source_map = [
				'open_house'         => 'open_house',
				'customer_spotlight' => 'spotlight',
				'special_event'      => 'event',
			];

			$leads[] = [
				'id'           => 'ff_' . $row->id, // Prefix to distinguish from wp_lead_submissions
				'first_name'   => $response['first_name'] ?? '',
				'last_name'    => $response['last_name'] ?? '',
				'email'        => $response['email'] ?? '',
				'phone'        => $response['phone'] ?? '',
				'lead_source'  => $source_map[ $page_type ] ?? 'lead_page',
				'status'       => $status_map[ $row->status ] ?? 'new',
				'created_date' => $row->created_at,
				'updated_date' => $row->updated_at,
				'source_type'  => 'fluent_forms', // Mark the source
				'page_id'      => $response['lead_page_id'] ?? null,
				'page_type'    => $page_type,
			];
		}

		return $leads;
	}

	/**
	 * Create a new lead submission.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function create_lead( WP_REST_Request $request ) {
		$data = array(
			'partnership_id'   => $request->get_param( 'partnership_id' ),
			'loan_officer_id'  => $request->get_param( 'loan_officer_id' ),
			'agent_id'         => $request->get_param( 'agent_id' ),
			'lead_source'      => sanitize_text_field( $request->get_param( 'lead_source' ) ),
			'first_name'       => sanitize_text_field( $request->get_param( 'first_name' ) ),
			'last_name'        => sanitize_text_field( $request->get_param( 'last_name' ) ),
			'email'            => sanitize_email( $request->get_param( 'email' ) ),
			'phone'            => sanitize_text_field( $request->get_param( 'phone' ) ),
			'loan_amount'      => $request->get_param( 'loan_amount' ),
			'property_value'   => $request->get_param( 'property_value' ),
			'property_address' => sanitize_textarea_field( $request->get_param( 'property_address' ) ),
			'lead_data'        => $request->get_param( 'lead_data' ),
			'form_data'        => $request->get_param( 'form_data' ),
			'status'           => 'new',
			'created_date'     => current_time( 'mysql' ),
			'updated_date'     => current_time( 'mysql' ),
		);

		$lead = LeadSubmission::create( $data );

		// TODO: Send lead notification email

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $lead,
				'message' => 'Lead created successfully',
			),
			201
		);
	}

	/**
	 * Update lead status.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function update_lead_status( WP_REST_Request $request ) {
		$lead_id = $request->get_param( 'id' );
		$status  = sanitize_text_field( $request->get_param( 'status' ) );

		$lead = LeadSubmission::find( $lead_id );

		if ( ! $lead ) {
			return new WP_Error( 'lead_not_found', 'Lead not found', array( 'status' => 404 ) );
		}

		$lead->status       = $status;
		$lead->updated_date = current_time( 'mysql' );
		$lead->save();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $lead,
				'message' => 'Lead status updated successfully',
			),
			200
		);
	}

	/**
	 * Create calculator lead submission.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function create_calculator_lead( WP_REST_Request $request ) {
		$params = $request->get_params();

		// Sanitize all inputs
		$first_name       = sanitize_text_field( $params['first_name'] );
		$last_name        = sanitize_text_field( $params['last_name'] );
		$email            = sanitize_email( $params['email'] );
		$phone            = sanitize_text_field( $params['phone'] );
		$loan_officer_id  = intval( $params['loan_officer_id'] );
		$calculator_type  = sanitize_text_field( $params['calculator_type'] );
		$calculation_data = isset( $params['calculation_data'] ) ? wp_json_encode( $params['calculation_data'] ) : null;

		// Validate loan officer exists
		$loan_officer = get_user_by( 'id', $loan_officer_id );
		if ( ! $loan_officer ) {
			return new WP_Error( 'invalid_loan_officer', 'Invalid loan officer ID', array( 'status' => 400 ) );
		}

		// Create lead using Eloquent model
		$lead = LeadSubmission::create(
			array(
				'loan_officer_id'  => $loan_officer_id,
				'first_name'       => $first_name,
				'last_name'        => $last_name,
				'email'            => $email,
				'phone'            => $phone,
				'lead_source'      => 'mortgage_calculator',
				'calculator_type'  => $calculator_type,
				'status'           => 'new',
				'created_date'     => current_time( 'mysql' ),
			)
		);

		if ( ! $lead ) {
			return new WP_Error( 'database_error', 'Failed to save lead', array( 'status' => 500 ) );
		}

		// Store calculation data as option
		if ( $lead->id && $calculation_data ) {
			update_option( "frs_calculator_lead_{$lead->id}_data", $calculation_data, false );
		}

		// Send notification email to loan officer
		$loan_officer_email = $loan_officer->user_email;
		$subject            = sprintf( 'New %s Calculator Lead', ucwords( str_replace( '_', ' ', $calculator_type ) ) );
		$message            = sprintf(
			"New calculator lead from your landing page:\n\nName: %s %s\nEmail: %s\nPhone: %s\nCalculator: %s\n\nView all leads in your portal dashboard.",
			$first_name,
			$last_name,
			$email,
			$phone,
			ucwords( str_replace( '_', ' ', $calculator_type ) )
		);

		wp_mail( $loan_officer_email, $subject, $message );

		// Trigger action for integrations
		do_action( 'lrh_calculator_lead_created', $lead->id, $params );

		return new WP_REST_Response(
			array(
				'success' => true,
				'lead_id' => $lead->id,
				'message' => 'Lead submitted successfully',
			),
			201
		);
	}

	/**
	 * Delete a lead.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function delete_lead( WP_REST_Request $request ) {
		$lead_id = $request->get_param( 'id' );

		$lead = LeadSubmission::find( $lead_id );

		if ( ! $lead ) {
			return new WP_Error( 'lead_not_found', 'Lead not found', array( 'status' => 404 ) );
		}

		$lead->delete();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Lead deleted successfully',
			),
			200
		);
	}

	/**
	 * Add note to lead.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function add_lead_note( WP_REST_Request $request ) {
		$lead_id = $request->get_param( 'id' );
		$note    = sanitize_textarea_field( $request->get_param( 'note' ) );

		$lead = LeadSubmission::find( $lead_id );

		if ( ! $lead ) {
			return new WP_Error( 'lead_not_found', 'Lead not found', array( 'status' => 404 ) );
		}

		// Get existing notes
		$notes = $lead->notes ? json_decode( $lead->notes, true ) : array();

		// Add new note with timestamp and user
		$notes[] = array(
			'note'       => $note,
			'user_id'    => get_current_user_id(),
			'user_name'  => wp_get_current_user()->display_name,
			'created_at' => current_time( 'mysql' ),
		);

		// Update lead notes
		$lead->notes        = wp_json_encode( $notes );
		$lead->updated_date = current_time( 'mysql' );
		$lead->save();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $lead,
				'message' => 'Note added successfully',
			),
			200
		);
	}

	/**
	 * Create mortgage landing page lead submission.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function create_mortgage_lead( WP_REST_Request $request ) {
		$params = $request->get_params();

		// Sanitize all inputs
		$first_name      = sanitize_text_field( $params['firstName'] );
		$last_name       = sanitize_text_field( $params['lastName'] );
		$email           = sanitize_email( $params['email'] );
		$phone           = sanitize_text_field( $params['phone'] );
		$loan_officer_id = intval( $params['loanOfficerId'] );
		$page_id         = intval( $params['pageId'] );
		$template        = sanitize_text_field( $params['template'] );

		// Optional fields
		$property_zip  = isset( $params['propertyZip'] ) ? sanitize_text_field( $params['propertyZip'] ) : '';
		$home_price    = isset( $params['homePrice'] ) ? sanitize_text_field( $params['homePrice'] ) : '';
		$down_payment  = isset( $params['downPayment'] ) ? sanitize_text_field( $params['downPayment'] ) : '';
		$property_type = isset( $params['propertyType'] ) ? sanitize_text_field( $params['propertyType'] ) : '';
		$goal          = isset( $params['goal'] ) ? sanitize_text_field( $params['goal'] ) : '';
		$best_time     = isset( $params['bestTimeToContact'] ) ? sanitize_text_field( $params['bestTimeToContact'] ) : '';

		// Validate loan officer exists
		$loan_officer = get_user_by( 'id', $loan_officer_id );
		if ( ! $loan_officer ) {
			return new WP_Error( 'invalid_loan_officer', 'Invalid loan officer ID', array( 'status' => 400 ) );
		}

		// Validate page exists
		$page = get_post( $page_id );
		if ( ! $page || $page->post_type !== 'frs_mortgage_lp' ) {
			return new WP_Error( 'invalid_page', 'Invalid mortgage landing page ID', array( 'status' => 400 ) );
		}

		// Determine lead source based on template
		$lead_source = $template === 'rate-quote' ? 'mortgage_rate_quote' : 'mortgage_application';

		// Build additional data JSON
		$additional_data = array(
			'page_id'              => $page_id,
			'template'             => $template,
			'property_zip'         => $property_zip,
			'home_price'           => $home_price,
			'down_payment'         => $down_payment,
			'property_type'        => $property_type,
			'goal'                 => $goal,
			'best_time_to_contact' => $best_time,
		);

		// Create lead using Eloquent model
		$lead = LeadSubmission::create(
			array(
				'loan_officer_id' => $loan_officer_id,
				'first_name'      => $first_name,
				'last_name'       => $last_name,
				'email'           => $email,
				'phone'           => $phone,
				'lead_source'     => $lead_source,
				'status'          => 'new',
				'created_date'    => current_time( 'mysql' ),
			)
		);

		if ( ! $lead ) {
			return new WP_Error( 'database_error', 'Failed to save lead', array( 'status' => 500 ) );
		}

		// Store additional data as option
		if ( $lead->id ) {
			update_option( "frs_mortgage_lead_{$lead->id}_data", wp_json_encode( $additional_data ), false );
		}

		// Send notification email to loan officer
		$loan_officer_email = $loan_officer->user_email;
		$template_name      = $template === 'rate-quote' ? 'Rate Quote' : 'Loan Application';
		$subject            = sprintf( 'New %s Lead from Your Landing Page', $template_name );

		$message = sprintf(
			"New %s lead from your mortgage landing page:\n\nName: %s %s\nEmail: %s\nPhone: %s\nProperty Zip: %s\nHome Price: %s\nDown Payment: %s\nProperty Type: %s\nGoal: %s\nBest Time to Contact: %s\n\nView all leads in your portal dashboard.",
			$template_name,
			$first_name,
			$last_name,
			$email,
			$phone,
			$property_zip ?: 'Not provided',
			$home_price ?: 'Not provided',
			$down_payment ?: 'Not provided',
			$property_type ?: 'Not provided',
			$goal ?: 'Not provided',
			$best_time ?: 'Not provided'
		);

		wp_mail( $loan_officer_email, $subject, $message );

		// Trigger action for integrations
		do_action( 'lrh_mortgage_lead_created', $lead->id, $params );

		return new WP_REST_Response(
			array(
				'success' => true,
				'lead_id' => $lead->id,
				'message' => 'Lead submitted successfully',
			),
			201
		);
	}
}
