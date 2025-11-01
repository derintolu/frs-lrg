<?php
/**
 * Users Controller
 *
 * Handles user-related API endpoints.
 *
 * @package LendingResourceHub\Controllers\Users
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Users;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles user-related actions.
 *
 * @package LendingResourceHub\Controllers\Users
 */
class Actions {

	/**
	 * Get current user data.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function get_current_user( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'User not logged in', array( 'status' => 401 ) );
		}

		$user = get_userdata( $user_id );

		$role = 'loan_officer';
		if ( in_array( 'realtor_partner', $user->roles ) || in_array( 'realtor', $user->roles ) ) {
			$role = 'realtor';
		} elseif ( in_array( 'manager', $user->roles ) ) {
			$role = 'manager';
		} elseif ( in_array( 'frs_admin', $user->roles ) ) {
			$role = 'admin';
		}

		$response = array(
			'id'        => $user->ID,
			'name'      => $user->display_name,
			'email'     => $user->user_email,
			'role'      => $role,
			'status'    => 'active',
			'avatar'    => get_avatar_url( $user->ID ),
			'createdAt' => $user->user_registered,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get user by ID.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function get_user_by_id( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );
		$user    = get_userdata( $user_id );

		if ( ! $user ) {
			return new WP_Error( 'user_not_found', 'User not found', array( 'status' => 404 ) );
		}

		$role = 'loan_officer';
		if ( in_array( 'realtor_partner', $user->roles ) || in_array( 'realtor', $user->roles ) ) {
			$role = 'realtor';
		} elseif ( in_array( 'manager', $user->roles ) ) {
			$role = 'manager';
		} elseif ( in_array( 'frs_admin', $user->roles ) ) {
			$role = 'admin';
		}

		$response = array(
			'id'        => $user->ID,
			'name'      => $user->display_name,
			'email'     => $user->user_email,
			'role'      => $role,
			'status'    => 'active',
			'avatar'    => get_avatar_url( $user->ID ),
			'createdAt' => $user->user_registered,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get Person CPT profile for current user.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function get_person_profile( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'User not logged in', array( 'status' => 401 ) );
		}

		// Get Person CPT data via ACF integration
		if ( ! class_exists( 'FRS_ACF_Fields' ) ) {
			return new WP_Error( 'acf_not_available', 'ACF integration not available', array( 'status' => 500 ) );
		}

		$person_data = \FRS_ACF_Fields::get_loan_officer_person( $user_id );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $person_data,
			),
			200
		);
	}

	/**
	 * Update user profile.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function update_profile( WP_REST_Request $request ) {
		$user_id = $request->get_param( 'id' );

		// Only allow users to update their own profile
		if ( $user_id != get_current_user_id() && ! current_user_can( 'edit_users' ) ) {
			return new WP_Error( 'forbidden', 'Not authorized', array( 'status' => 403 ) );
		}

		$updates = array( 'ID' => $user_id );

		if ( $request->has_param( 'name' ) ) {
			$updates['display_name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}

		if ( $request->has_param( 'email' ) ) {
			$updates['user_email'] = sanitize_email( $request->get_param( 'email' ) );
		}

		$result = wp_update_user( $updates );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Profile updated successfully',
			),
			200
		);
	}

	/**
	 * Get user's profile data (for /profile endpoint).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function get_profile( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'User not logged in', array( 'status' => 401 ) );
		}

		$user = get_userdata( $user_id );

		// Get Person CPT data if available
		$person_data = array();
		if ( class_exists( 'FRS_ACF_Fields' ) ) {
			$person_data = \FRS_ACF_Fields::get_loan_officer_person( $user_id );
		}

		$response = array(
			'id'          => $user->ID,
			'name'        => $user->display_name,
			'email'       => $user->user_email,
			'avatar'      => get_avatar_url( $user->ID ),
			'person_data' => $person_data,
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Update user's profile data (for /profile endpoint).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function update_profile_post( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return new WP_Error( 'not_logged_in', 'User not logged in', array( 'status' => 401 ) );
		}

		$updates = array( 'ID' => $user_id );

		if ( $request->has_param( 'name' ) ) {
			$updates['display_name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}

		if ( $request->has_param( 'email' ) ) {
			$updates['user_email'] = sanitize_email( $request->get_param( 'email' ) );
		}

		$result = wp_update_user( $updates );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// TODO: Update Person CPT data via ACF if needed

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Profile updated successfully',
			),
			200
		);
	}
}
