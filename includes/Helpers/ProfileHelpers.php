<?php
/**
 * Profile Helper Functions
 *
 * Utility functions for working with frs-wp-users Profile data.
 *
 * @package LendingResourceHub\Helpers
 * @since 1.0.0
 */

namespace LendingResourceHub\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProfileHelpers
 *
 * Static helper methods for Profile operations.
 *
 * @package LendingResourceHub\Helpers
 */
class ProfileHelpers {

	/**
	 * Generate Arrive link from NMLS number.
	 *
	 * @param string|int $nmls NMLS number.
	 * @return string Arrive registration URL.
	 */
	public static function generate_arrive_link( $nmls ) {
		if ( empty( $nmls ) ) {
			return '';
		}

		return 'https://21stcenturylending.my1003app.com/' . sanitize_text_field( $nmls ) . '/register';
	}

	/**
	 * Get arrive link for a user.
	 *
	 * Checks Profile first, falls back to generating from NMLS if missing.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return string Arrive link or empty string.
	 */
	public static function get_user_arrive_link( $user_id ) {
		if ( ! class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			return '';
		}

		$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();

		if ( ! $profile ) {
			return '';
		}

		// Return existing arrive link
		if ( ! empty( $profile->arrive ) ) {
			return $profile->arrive;
		}

		// Generate from NMLS if missing
		if ( ! empty( $profile->nmls ) ) {
			return self::generate_arrive_link( $profile->nmls );
		}

		return '';
	}

	/**
	 * Auto-generate and save arrive links for all profiles with NMLS.
	 *
	 * @return array Stats array with 'generated' and 'skipped' counts.
	 */
	public static function generate_all_arrive_links() {
		if ( ! class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			return array(
				'generated' => 0,
				'skipped'   => 0,
				'error'     => 'Profile model not found',
			);
		}

		$profiles = \FRSUsers\Models\Profile::whereNotNull( 'nmls' )
			->where( 'nmls', '!=', '' )
			->get();

		$generated = 0;
		$skipped   = 0;

		foreach ( $profiles as $profile ) {
			if ( empty( $profile->arrive ) ) {
				$arrive_url      = self::generate_arrive_link( $profile->nmls );
				$profile->arrive = $arrive_url;
				$profile->save();
				++$generated;
			} else {
				++$skipped;
			}
		}

		return array(
			'generated' => $generated,
			'skipped'   => $skipped,
		);
	}

	/**
	 * Get Profile data for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return \FRSUsers\Models\Profile|null Profile object or null.
	 */
	public static function get_profile( $user_id ) {
		if ( ! class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			return null;
		}

		return \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
	}
}
