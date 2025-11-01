<?php
/**
 * Partnerships Controller
 *
 * Handles partnership-related API endpoints.
 *
 * @package LendingResourceHub\Controllers\Partnerships
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Partnerships;

use LendingResourceHub\Models\Partnership;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles partnership-related actions.
 *
 * @package LendingResourceHub\Controllers\Partnerships
 */
class Actions {

	/**
	 * Get all partnerships for current user.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnerships( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$status  = $request->get_param( 'status' );

		$query = Partnership::where( 'loan_officer_id', $user_id )
			->orWhere( 'agent_id', $user_id );

		if ( $status ) {
			$query->where( 'status', $status );
		}

		$partnerships = $query->orderBy( 'created_date', 'desc' )->get();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnerships,
			),
			200
		);
	}

	/**
	 * Create a new partnership invitation.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function create_partnership( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' );
		$partner_email   = sanitize_email( $request->get_param( 'email' ) );
		$partner_name    = sanitize_text_field( $request->get_param( 'name' ) );
		$message         = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( ! $partner_email ) {
			return new WP_Error( 'invalid_email', 'Valid email is required', array( 'status' => 400 ) );
		}

		// Check if partnership already exists
		$existing = Partnership::where( 'loan_officer_id', $loan_officer_id )
			->where( 'partner_email', $partner_email )
			->first();

		if ( $existing ) {
			return new WP_Error( 'partnership_exists', 'Partnership already exists', array( 'status' => 409 ) );
		}

		// Create partnership
		$partnership = Partnership::create(
			array(
				'loan_officer_id'  => $loan_officer_id,
				'partner_email'    => $partner_email,
				'partner_name'     => $partner_name,
				'status'           => 'pending',
				'invite_token'     => wp_generate_password( 32, false ),
				'invite_sent_date' => current_time( 'mysql' ),
				'created_date'     => current_time( 'mysql' ),
				'updated_date'     => current_time( 'mysql' ),
			)
		);

		// TODO: Send invitation email with $message

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
				'message' => 'Partnership invitation sent successfully',
			),
			201
		);
	}

	/**
	 * Assign partnership directly (no invitation).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function assign_partnership( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' );
		$realtor_id      = $request->get_param( 'realtor_id' );

		if ( ! $loan_officer_id || ! $realtor_id ) {
			return new WP_Error( 'missing_params', 'Loan officer ID and realtor ID are required', array( 'status' => 400 ) );
		}

		// Get realtor user data
		$realtor = get_userdata( $realtor_id );
		if ( ! $realtor ) {
			return new WP_Error( 'invalid_realtor', 'Realtor not found', array( 'status' => 404 ) );
		}

		// Check if partnership already exists
		$existing = Partnership::where( 'loan_officer_id', $loan_officer_id )
			->where( 'agent_id', $realtor_id )
			->first();

		if ( $existing ) {
			return new WP_Error( 'partnership_exists', 'Partnership already exists', array( 'status' => 409 ) );
		}

		// Create active partnership
		$partnership = Partnership::create(
			array(
				'loan_officer_id' => $loan_officer_id,
				'agent_id'        => $realtor_id,
				'partner_email'   => $realtor->user_email,
				'partner_name'    => $realtor->display_name,
				'status'          => 'active',
				'accepted_date'   => current_time( 'mysql' ),
				'created_date'    => current_time( 'mysql' ),
				'updated_date'    => current_time( 'mysql' ),
			)
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
				'message' => 'Partnership assigned successfully',
			),
			201
		);
	}

	/**
	 * Get partnerships for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnerships_for_lo( WP_REST_Request $request ) {
		$lo_id = $request->get_param( 'id' );

		$partnerships = Partnership::where( 'loan_officer_id', $lo_id )
			->orderBy( 'created_date', 'desc' )
			->get();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnerships,
			),
			200
		);
	}

	/**
	 * Get partnership for realtor.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnership_for_realtor( WP_REST_Request $request ) {
		$realtor_id = $request->get_param( 'id' );

		$partnership = Partnership::where( 'agent_id', $realtor_id )
			->first();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
			),
			200
		);
	}

	/**
	 * Get partners (realtor users) for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partners_for_lo( WP_REST_Request $request ) {
		$lo_id = $request->get_param( 'id' );

		$partnerships = Partnership::where( 'loan_officer_id', $lo_id )
			->where( 'status', 'active' )
			->get();

		// Extract partner data
		$partners = array();
		foreach ( $partnerships as $partnership ) {
			if ( $partnership->agent_id ) {
				$agent = get_userdata( $partnership->agent_id );
				if ( $agent ) {
					$partners[] = array(
						'id'            => $agent->ID,
						'name'          => $agent->display_name,
						'email'         => $agent->user_email,
						'role'          => 'realtor',
						'status'        => 'active',
						'createdAt'     => $partnership->created_date,
						'partnershipId' => $partnership->id,
					);
				}
			}
		}

		return new WP_REST_Response( $partners, 200 );
	}

	/**
	 * Get all realtor partners grouped with their LO partnerships.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_realtor_partners( WP_REST_Request $request ) {
		global $wpdb;

		// Get all users with realtor_partner role
		$realtor_users = get_users( array( 'role' => 'realtor_partner' ) );

		$realtor_partners = array();
		foreach ( $realtor_users as $realtor ) {
			// Get all partnerships for this realtor with LO names
			$partnerships = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.*, u.display_name as lo_name, u.user_email as lo_email
					FROM {$wpdb->prefix}partnerships p
					LEFT JOIN {$wpdb->users} u ON p.loan_officer_id = u.ID
					WHERE p.agent_id = %d
					ORDER BY p.created_date DESC",
					$realtor->ID
				)
			);

			$realtor_partners[] = array(
				'realtor'      => array(
					'id'           => $realtor->ID,
					'display_name' => $realtor->display_name,
					'email'        => $realtor->user_email,
				),
				'partnerships' => $partnerships,
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $realtor_partners,
			),
			200
		);
	}

	/**
	 * Get all loan officers for dropdowns.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_loan_officers( WP_REST_Request $request ) {
		$loan_officers = get_users( array( 'role' => 'loan_officer' ) );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $loan_officers,
			),
			200
		);
	}

	/**
	 * Send partnership invitation email.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function send_invitation( WP_REST_Request $request ) {
		$partnership_id = $request->get_param( 'partnership_id' );
		$message        = sanitize_textarea_field( $request->get_param( 'message' ) );

		$partnership = Partnership::find( $partnership_id );

		if ( ! $partnership ) {
			return new WP_Error( 'partnership_not_found', 'Partnership not found', array( 'status' => 404 ) );
		}

		// TODO: Send invitation email via notification system

		$partnership->invite_sent_date = current_time( 'mysql' );
		$partnership->updated_date     = current_time( 'mysql' );
		$partnership->save();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Invitation sent successfully',
			),
			200
		);
	}
}
