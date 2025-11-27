<?php
/**
 * Partner Company Importer
 *
 * Imports partner company data from CSV and creates BuddyPress groups.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class PartnerCompanyImporter
 *
 * Handles importing partner companies from CSV files.
 *
 * @package LendingResourceHub\Core
 */
class PartnerCompanyImporter {

	use Base;

	/**
	 * Import partner company from CSV file.
	 *
	 * Creates:
	 * - BuddyPress parent group for the company
	 * - WordPress users for all realtors
	 * - Adds all realtors as group members
	 * - Sets group branding metadata
	 *
	 * @param string $csv_path Absolute path to CSV file.
	 * @param string $company_name Name of the partner company.
	 * @param array  $branding Optional branding settings.
	 * @return array Results with success/error messages.
	 */
	public function import_from_csv( $csv_path, $company_name, $branding = array() ) {
		if ( ! file_exists( $csv_path ) ) {
			return array(
				'success' => false,
				'message' => 'CSV file not found',
			);
		}

		// Check if BuddyPress is active
		if ( ! function_exists( 'groups_create_group' ) ) {
			return array(
				'success' => false,
				'message' => 'BuddyPress is not active',
			);
		}

		$results = array(
			'group_id'        => 0,
			'users_created'   => 0,
			'users_existing'  => 0,
			'members_added'   => 0,
			'errors'          => array(),
		);

		// Parse CSV
		$realtors = $this->parse_csv( $csv_path );

		if ( empty( $realtors ) ) {
			return array(
				'success' => false,
				'message' => 'No valid realtor data found in CSV',
			);
		}

		// Create BuddyPress group for the company
		$group_id = $this->create_bp_group( $company_name, $branding );

		if ( ! $group_id ) {
			return array(
				'success' => false,
				'message' => 'Failed to create BuddyPress group',
			);
		}

		$results['group_id'] = $group_id;

		// Import realtors
		foreach ( $realtors as $realtor ) {
			$result = $this->import_realtor( $realtor, $group_id );

			if ( $result['created'] ) {
				$results['users_created']++;
			} elseif ( $result['existing'] ) {
				$results['users_existing']++;
			}

			if ( $result['member_added'] ) {
				$results['members_added']++;
			}

			if ( ! empty( $result['error'] ) ) {
				$results['errors'][] = $result['error'];
			}
		}

		$results['success'] = true;
		$results['message'] = sprintf(
			'Successfully imported %d realtors (%d new, %d existing) into group #%d',
			$results['members_added'],
			$results['users_created'],
			$results['users_existing'],
			$group_id
		);

		return $results;
	}

	/**
	 * Parse CSV file into array of realtor data.
	 *
	 * @param string $csv_path Path to CSV file.
	 * @return array Array of realtor data.
	 */
	private function parse_csv( $csv_path ) {
		$realtors = array();
		$handle   = fopen( $csv_path, 'r' );

		if ( ! $handle ) {
			return $realtors;
		}

		// Skip header row
		fgetcsv( $handle );

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			// Expected columns: Office, LAST NAME, FIRST NAME, Production YTD, Phone #, Email
			if ( count( $row ) < 6 ) {
				continue;
			}

			$email = trim( $row[5] );

			// Skip if no email
			if ( empty( $email ) || ! is_email( $email ) ) {
				continue;
			}

			$realtors[] = array(
				'office'      => trim( $row[0] ),
				'last_name'   => trim( $row[1] ),
				'first_name'  => trim( $row[2] ),
				'production'  => trim( $row[3] ),
				'phone'       => $this->clean_phone( trim( $row[4] ) ),
				'email'       => $email,
			);
		}

		fclose( $handle );

		return $realtors;
	}

	/**
	 * Clean phone number to standard format.
	 *
	 * @param string $phone Raw phone number.
	 * @return string Cleaned phone number.
	 */
	private function clean_phone( $phone ) {
		// Remove all non-numeric characters except +
		$phone = preg_replace( '/[^0-9+]/', '', $phone );

		// Format as (XXX) XXX-XXXX if 10 digits
		if ( strlen( $phone ) === 10 ) {
			return sprintf( '(%s) %s-%s', substr( $phone, 0, 3 ), substr( $phone, 3, 3 ), substr( $phone, 6 ) );
		}

		return $phone;
	}

	/**
	 * Create BuddyPress group for partner company.
	 *
	 * @param string $company_name Company name.
	 * @param array  $branding Branding settings.
	 * @return int Group ID or 0 on failure.
	 */
	private function create_bp_group( $company_name, $branding = array() ) {
		// Check if group already exists
		$existing_group = groups_get_id( sanitize_title( $company_name ) );
		if ( $existing_group ) {
			return $existing_group;
		}

		// Get current user or default to admin (user ID 1)
		$creator_id = get_current_user_id();
		if ( ! $creator_id ) {
			$creator_id = 1;
		}

		$group_id = groups_create_group(
			array(
				'creator_id'   => $creator_id,
				'name'         => $company_name,
				'description'  => sprintf( 'Partner real estate company: %s', $company_name ),
				'slug'         => sanitize_title( $company_name ),
				'status'       => 'private', // Private group - members only
				'enable_forum' => true,
			)
		);

		if ( ! $group_id ) {
			return 0;
		}

		// Set group type
		bp_groups_set_group_type( $group_id, 'partner-org' );

		// Set default branding
		$default_branding = array(
			'primary_color'   => '#2563eb',
			'secondary_color' => '#2dd4da',
			'button_style'    => 'rounded',
		);

		$branding = wp_parse_args( $branding, $default_branding );

		// Store branding in group meta
		groups_update_groupmeta( $group_id, 'pp_primary_color', $branding['primary_color'] );
		groups_update_groupmeta( $group_id, 'pp_secondary_color', $branding['secondary_color'] );
		groups_update_groupmeta( $group_id, 'pp_button_style', $branding['button_style'] );

		// Initialize analytics
		groups_update_groupmeta( $group_id, '_pp_page_views', 0 );
		groups_update_groupmeta( $group_id, '_pp_conversions', 0 );

		return $group_id;
	}

	/**
	 * Import single realtor and add to group.
	 *
	 * @param array $realtor Realtor data.
	 * @param int   $group_id BP group ID.
	 * @return array Result with status flags.
	 */
	private function import_realtor( $realtor, $group_id ) {
		$result = array(
			'created'      => false,
			'existing'     => false,
			'member_added' => false,
			'error'        => '',
		);

		// Check if user already exists
		$user = get_user_by( 'email', $realtor['email'] );

		if ( ! $user ) {
			// Create new WordPress user
			$username = sanitize_user( strtolower( $realtor['first_name'] . '.' . $realtor['last_name'] ) );

			// Ensure unique username
			$base_username = $username;
			$counter       = 1;
			while ( username_exists( $username ) ) {
				$username = $base_username . $counter;
				$counter++;
			}

			$user_id = wp_create_user(
				$username,
				wp_generate_password( 16, true, true ),
				$realtor['email']
			);

			if ( is_wp_error( $user_id ) ) {
				$result['error'] = sprintf( 'Failed to create user for %s: %s', $realtor['email'], $user_id->get_error_message() );
				return $result;
			}

			// Update user meta
			wp_update_user(
				array(
					'ID'         => $user_id,
					'first_name' => $realtor['first_name'],
					'last_name'  => $realtor['last_name'],
					'role'       => 'subscriber', // Realtors are subscribers by default
				)
			);

			// Store additional data in user meta
			update_user_meta( $user_id, 'phone', $realtor['phone'] );
			update_user_meta( $user_id, 'office_location', $realtor['office'] );
			update_user_meta( $user_id, 'production_ytd', $realtor['production'] );

			$result['created'] = true;
			$user              = get_user_by( 'id', $user_id );
		} else {
			$result['existing'] = true;
		}

		// Add user to BuddyPress group
		if ( $user ) {
			$member_added = groups_join_group( $group_id, $user->ID );

			if ( $member_added ) {
				$result['member_added'] = true;

				// Set member type meta
				bp_set_member_type( $user->ID, 'realtor' );
			} else {
				$result['error'] = sprintf( 'Failed to add %s to group', $realtor['email'] );
			}
		}

		return $result;
	}

	/**
	 * Get group statistics.
	 *
	 * @param int $group_id BP group ID.
	 * @return array Statistics.
	 */
	public function get_group_stats( $group_id ) {
		return array(
			'member_count' => groups_get_total_member_count( $group_id ),
			'page_views'   => (int) groups_get_groupmeta( $group_id, '_pp_page_views' ),
			'conversions'  => (int) groups_get_groupmeta( $group_id, '_pp_conversions' ),
		);
	}
}
