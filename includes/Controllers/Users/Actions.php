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
	 * Get current user data with profile integration.
	 *
	 * Integrates with frs-wp-users plugin to provide complete profile data.
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

		// Determine user role
		$role = 'loan_officer';
		if ( in_array( 'realtor_partner', $user->roles ) || in_array( 'realtor', $user->roles ) ) {
			$role = 'realtor';
		} elseif ( in_array( 'manager', $user->roles ) ) {
			$role = 'manager';
		} elseif ( in_array( 'frs_admin', $user->roles ) || in_array( 'administrator', $user->roles ) ) {
			$role = 'admin';
		}

		// Try to get profile from frs-wp-users plugin
		$profile = null;
		if ( class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
		}

		// Base response with WordPress user data
		$response = array(
			'id'        => (string) $user->ID,
			'name'      => $user->display_name,
			'email'     => $user->user_email,
			'role'      => $role,
			'status'    => 'active',
			'avatar'    => get_avatar_url( $user->ID ),
			'createdAt' => $user->user_registered,
		);

		// Merge profile data if available
		if ( $profile ) {
			$response = array_merge(
				$response,
				array(
					'profile_id'       => $profile->id,
					'user_id'          => $profile->user_id,
					'phone'            => $profile->phone_number,
					'mobile_number'    => $profile->mobile_number,
					'company'          => $profile->office,
					'office'           => $profile->office,
					'headshot_id'      => $profile->headshot_id,
					'headshot_url'     => $profile->headshot_id ? wp_get_attachment_url( $profile->headshot_id ) : null,
					'location'         => $profile->city_state,
					'city_state'       => $profile->city_state,
					'region'           => $profile->region,
					'title'            => $profile->job_title,
					'job_title'        => $profile->job_title,
					'biography'        => $profile->biography,
					'nmls'             => $profile->nmls ?: $profile->nmls_number,
					'nmls_number'      => $profile->nmls_number,
					'license_number'   => $profile->license_number,
					'dre_license'      => $profile->dre_license,
					'brand'            => $profile->brand,
					'select_person_type' => $profile->select_person_type,
					// Social media
					'facebook_url'     => $profile->facebook_url,
					'instagram_url'    => $profile->instagram_url,
					'linkedin_url'     => $profile->linkedin_url,
					'twitter_url'      => $profile->twitter_url,
					'youtube_url'      => $profile->youtube_url,
					'tiktok_url'       => $profile->tiktok_url,
					'linkedin'         => $profile->linkedin_url,
					// Professional arrays (decode JSON)
					'specialties'      => is_string( $profile->specialties ) ? json_decode( $profile->specialties, true ) : $profile->specialties,
					'specialties_lo'   => is_string( $profile->specialties_lo ) ? json_decode( $profile->specialties_lo, true ) : $profile->specialties_lo,
					'languages'        => is_string( $profile->languages ) ? json_decode( $profile->languages, true ) : $profile->languages,
					'awards'           => is_string( $profile->awards ) ? json_decode( $profile->awards, true ) : $profile->awards,
					'nar_designations' => is_string( $profile->nar_designations ) ? json_decode( $profile->nar_designations, true ) : $profile->nar_designations,
					'namb_certifications' => is_string( $profile->namb_certifications ) ? json_decode( $profile->namb_certifications, true ) : $profile->namb_certifications,
					// Tools & Platforms
					'arrive'           => $profile->arrive,
					'canva_folder_link' => $profile->canva_folder_link,
					'niche_bio_content' => $profile->niche_bio_content,
					'personal_branding_images' => is_string( $profile->personal_branding_images ) ? json_decode( $profile->personal_branding_images, true ) : $profile->personal_branding_images,
					// Additional
					'frs_agent_id'     => $profile->frs_agent_id,
					'is_guest'         => empty( $profile->user_id ),
				)
			);
		}

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Get user by ID with profile integration.
	 *
	 * Integrates with frs-wp-users plugin to provide complete profile data.
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

		// Determine user role
		$role = 'loan_officer';
		if ( in_array( 'realtor_partner', $user->roles ) || in_array( 'realtor', $user->roles ) ) {
			$role = 'realtor';
		} elseif ( in_array( 'manager', $user->roles ) ) {
			$role = 'manager';
		} elseif ( in_array( 'frs_admin', $user->roles ) || in_array( 'administrator', $user->roles ) ) {
			$role = 'admin';
		}

		// Try to get profile from frs-wp-users plugin
		$profile = null;
		if ( class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
		}

		// Base response with WordPress user data
		$response = array(
			'id'        => (string) $user->ID,
			'name'      => $user->display_name,
			'email'     => $user->user_email,
			'role'      => $role,
			'status'    => 'active',
			'avatar'    => get_avatar_url( $user->ID ),
			'createdAt' => $user->user_registered,
		);

		// Merge profile data if available
		if ( $profile ) {
			$response = array_merge(
				$response,
				array(
					'profile_id'       => $profile->id,
					'user_id'          => $profile->user_id,
					'phone'            => $profile->phone_number,
					'mobile_number'    => $profile->mobile_number,
					'company'          => $profile->office,
					'office'           => $profile->office,
					'headshot_id'      => $profile->headshot_id,
					'headshot_url'     => $profile->headshot_id ? wp_get_attachment_url( $profile->headshot_id ) : null,
					'location'         => $profile->city_state,
					'city_state'       => $profile->city_state,
					'region'           => $profile->region,
					'title'            => $profile->job_title,
					'job_title'        => $profile->job_title,
					'biography'        => $profile->biography,
					'nmls'             => $profile->nmls ?: $profile->nmls_number,
					'nmls_number'      => $profile->nmls_number,
					'license_number'   => $profile->license_number,
					'dre_license'      => $profile->dre_license,
					'brand'            => $profile->brand,
					'select_person_type' => $profile->select_person_type,
					// Social media
					'facebook_url'     => $profile->facebook_url,
					'instagram_url'    => $profile->instagram_url,
					'linkedin_url'     => $profile->linkedin_url,
					'twitter_url'      => $profile->twitter_url,
					'youtube_url'      => $profile->youtube_url,
					'tiktok_url'       => $profile->tiktok_url,
					'linkedin'         => $profile->linkedin_url,
					// Professional arrays (decode JSON)
					'specialties'      => is_string( $profile->specialties ) ? json_decode( $profile->specialties, true ) : $profile->specialties,
					'specialties_lo'   => is_string( $profile->specialties_lo ) ? json_decode( $profile->specialties_lo, true ) : $profile->specialties_lo,
					'languages'        => is_string( $profile->languages ) ? json_decode( $profile->languages, true ) : $profile->languages,
					'awards'           => is_string( $profile->awards ) ? json_decode( $profile->awards, true ) : $profile->awards,
					'nar_designations' => is_string( $profile->nar_designations ) ? json_decode( $profile->nar_designations, true ) : $profile->nar_designations,
					'namb_certifications' => is_string( $profile->namb_certifications ) ? json_decode( $profile->namb_certifications, true ) : $profile->namb_certifications,
					// Tools & Platforms
					'arrive'           => $profile->arrive,
					'canva_folder_link' => $profile->canva_folder_link,
					'niche_bio_content' => $profile->niche_bio_content,
					'personal_branding_images' => is_string( $profile->personal_branding_images ) ? json_decode( $profile->personal_branding_images, true ) : $profile->personal_branding_images,
					// Additional
					'frs_agent_id'     => $profile->frs_agent_id,
					'is_guest'         => empty( $profile->user_id ),
				)
			);
		}

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
