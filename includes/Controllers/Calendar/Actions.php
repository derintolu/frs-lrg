<?php
/**
 * Calendar Controller
 *
 * Handles FluentBooking calendar integration endpoints.
 *
 * @package LendingResourceHub\Controllers\Calendar
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Calendar;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ReflectionClass;

/**
 * Class Actions
 *
 * Handles calendar-related actions.
 *
 * @package LendingResourceHub\Controllers\Calendar
 */
class Actions {

	/**
	 * Setup FluentBooking calendar for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function setup_calendar( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		// Check if FluentBooking is active
		if ( ! class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			return new WP_Error( 'plugin_not_active', 'FluentBooking plugin is not active', array( 'status' => 500 ) );
		}

		// Check if calendar already exists
		$existing_calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )
			->where( 'type', 'simple' )
			->first();

		if ( $existing_calendar ) {
			// Mark setup as complete
			update_user_meta( $user_id, 'frs_calendar_setup_complete', true );

			return new WP_REST_Response(
				array(
					'success'     => true,
					'message'     => 'Calendar already exists',
					'calendar_id' => $existing_calendar->id,
				),
				200
			);
		}

		// Create calendar using FluentBooking API
		// Note: This assumes there's a method to create calendars
		// If not available, this will need to be implemented differently
		do_action( 'lrh_create_calendar_for_user', $user_id );

		// Verify calendar was created
		$calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )
			->where( 'type', 'simple' )
			->first();

		if ( $calendar ) {
			return new WP_REST_Response(
				array(
					'success'     => true,
					'message'     => 'Calendar created successfully',
					'calendar_id' => $calendar->id,
				),
				200
			);
		}

		return new WP_Error( 'creation_failed', 'Failed to create calendar', array( 'status' => 500 ) );
	}

	/**
	 * Get calendar setup status for current user.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_setup_status( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		$setup_complete = get_user_meta( $user_id, 'frs_calendar_setup_complete', true );
		$has_calendar   = false;

		// Check if FluentBooking calendar exists
		if ( class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			$calendar     = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )
				->where( 'type', 'simple' )
				->first();
			$has_calendar = ! empty( $calendar );
		}

		return new WP_REST_Response(
			array(
				'setup_complete' => (bool) $setup_complete,
				'has_calendar'   => $has_calendar,
				'needs_setup'    => ! $setup_complete && ! $has_calendar,
			),
			200
		);
	}

	/**
	 * Mark calendar setup as complete.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function complete_setup( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'frs_calendar_setup_complete', true );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Calendar setup marked as complete',
			),
			200
		);
	}

	/**
	 * Get all loan officers with calendar status (admin only).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_calendar_users( WP_REST_Request $request ) {
		// Get all loan officers
		$loan_officers = get_users(
			array(
				'role'    => 'loan_officer',
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);

		$users = array();

		foreach ( $loan_officers as $user ) {
			$has_calendar   = false;
			$setup_complete = (bool) get_user_meta( $user->ID, 'frs_calendar_setup_complete', true );

			// Check if user has FluentBooking calendar
			if ( class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
				$calendar     = \FluentBooking\App\Models\Calendar::where( 'user_id', $user->ID )
					->where( 'type', 'simple' )
					->first();
				$has_calendar = ! empty( $calendar );
			}

			$users[] = array(
				'id'             => $user->ID,
				'name'           => $user->display_name,
				'email'          => $user->user_email,
				'has_calendar'   => $has_calendar,
				'setup_complete' => $setup_complete,
			);
		}

		return new WP_REST_Response(
			array(
				'users' => $users,
			),
			200
		);
	}

	/**
	 * Reset calendar setup for selected users (admin only).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function reset_calendar( WP_REST_Request $request ) {
		$user_ids = $request->get_param( 'user_ids' );

		if ( empty( $user_ids ) || ! is_array( $user_ids ) ) {
			return new WP_Error( 'invalid_request', 'user_ids must be an array', array( 'status' => 400 ) );
		}

		$reset_count = 0;

		foreach ( $user_ids as $user_id ) {
			$user_id = absint( $user_id );

			// Verify user exists and is a loan officer
			$user = get_user_by( 'ID', $user_id );
			if ( ! $user || ! in_array( 'loan_officer', $user->roles, true ) ) {
				continue;
			}

			// Delete the setup completion flag
			delete_user_meta( $user_id, 'frs_calendar_setup_complete' );
			$reset_count++;
		}

		return new WP_REST_Response(
			array(
				'success'     => true,
				'message'     => "Reset calendar setup for $reset_count user(s)",
				'reset_count' => $reset_count,
			),
			200
		);
	}
}
