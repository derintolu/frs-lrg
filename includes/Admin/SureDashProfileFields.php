<?php
/**
 * SureDash Profile Fields Integration
 *
 * Adds realtor partner fields to SureDash user profile editor.
 *
 * @package LendingResourceHub\Admin
 * @since 1.0.0
 */

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class SureDashProfileFields
 *
 * Integrates custom profile fields into SureDash profile editor.
 *
 * @package LendingResourceHub\Admin
 */
class SureDashProfileFields {

	use Base;

	/**
	 * Initialize SureDash profile fields integration.
	 *
	 * @return void
	 */
	public function init() {
		// Add custom profile fields to SureDash
		add_filter( 'suredash_user_profile_fields', array( $this, 'add_realtor_fields' ), 10, 1 );
	}

	/**
	 * Add realtor partner fields to SureDash profile editor.
	 *
	 * @param array $fields Existing profile fields.
	 * @return array Modified profile fields.
	 */
	public function add_realtor_fields( $fields ) {
		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		// Only add for realtor_partner role or administrators
		if ( ! $user ) {
			return $fields;
		}

		$allowed_roles = array( 'realtor_partner', 'administrator' );
		$user_roles    = (array) $user->roles;
		$has_access    = ! empty( array_intersect( $allowed_roles, $user_roles ) );

		if ( ! $has_access ) {
			return $fields;
		}

		// Get current values from user meta
		$license_number  = get_user_meta( $user_id, 'license_number', true );
		$office_address  = get_user_meta( $user_id, 'office_address', true );
		$company_name    = get_user_meta( $user_id, 'company_name', true );
		$specialties     = get_user_meta( $user_id, 'realtor_specialties', true );
		$credentials     = get_user_meta( $user_id, 'realtor_credentials', true );

		// Add realtor-specific fields
		$realtor_fields = array(
			'license_number'      => array(
				'label'       => __( 'License #', 'lending-resource-hub' ),
				'placeholder' => __( 'Enter your real estate license number', 'lending-resource-hub' ),
				'value'       => ! empty( $license_number ) ? $license_number : '',
				'type'        => 'input',
				'external'    => true,
			),
			'office_address'      => array(
				'label'       => __( 'Office Address', 'lending-resource-hub' ),
				'placeholder' => __( 'Enter your office address', 'lending-resource-hub' ),
				'value'       => ! empty( $office_address ) ? $office_address : '',
				'type'        => 'textarea',
				'external'    => true,
			),
			'company_name'        => array(
				'label'       => __( 'Company/Brokerage', 'lending-resource-hub' ),
				'placeholder' => __( 'Enter your company or brokerage name', 'lending-resource-hub' ),
				'value'       => ! empty( $company_name ) ? $company_name : '',
				'type'        => 'input',
				'external'    => true,
			),
			'realtor_specialties' => array(
				'label'       => __( 'Specialties', 'lending-resource-hub' ),
				'placeholder' => __( 'e.g., Luxury Homes, First-Time Buyers', 'lending-resource-hub' ),
				'value'       => ! empty( $specialties ) ? $specialties : '',
				'type'        => 'textarea',
				'external'    => true,
			),
			'realtor_credentials' => array(
				'label'       => __( 'Credentials', 'lending-resource-hub' ),
				'placeholder' => __( 'e.g., CRS, GRI, ABR', 'lending-resource-hub' ),
				'value'       => ! empty( $credentials ) ? $credentials : '',
				'type'        => 'input',
				'external'    => true,
			),
		);

		// Merge with existing fields
		return array_merge( $fields, $realtor_fields );
	}
}
