<?php
/**
 * Ability Categories Registration
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

/**
 * Class Categories
 *
 * Registers all ability categories for the Lending Resource Hub plugin.
 */
class Categories {

	/**
	 * Register all categories
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_partnership_management();
		self::register_lead_management();
		self::register_portal_management();
		self::register_property_data();
		self::register_calendar_management();
	}

	/**
	 * Register partnership-management category
	 *
	 * @return void
	 */
	private static function register_partnership_management(): void {
		wp_register_ability_category(
			'partnership-management',
			array(
				'label'       => __( 'Partnership Management', 'lending-resource-hub' ),
				'description' => __( 'Abilities for managing loan officer and realtor partnerships, including creating, reading, updating, and deleting partnership relationships.', 'lending-resource-hub' ),
			)
		);
	}

	/**
	 * Register lead-management category
	 *
	 * @return void
	 */
	private static function register_lead_management(): void {
		wp_register_ability_category(
			'lead-management',
			array(
				'label'       => __( 'Lead Management', 'lending-resource-hub' ),
				'description' => __( 'Abilities for managing lead submissions, tracking lead status, and processing incoming leads from partner portals.', 'lending-resource-hub' ),
			)
		);
	}

	/**
	 * Register portal-management category
	 *
	 * @return void
	 */
	private static function register_portal_management(): void {
		wp_register_ability_category(
			'portal-management',
			array(
				'label'       => __( 'Portal Management', 'lending-resource-hub' ),
				'description' => __( 'Abilities for managing custom portal pages, user page assignments, and portal tool configurations for loan officers and realtors.', 'lending-resource-hub' ),
			)
		);
	}

	/**
	 * Register property-data category
	 *
	 * @return void
	 */
	private static function register_property_data(): void {
		wp_register_ability_category(
			'property-data',
			array(
				'label'       => __( 'Property Data', 'lending-resource-hub' ),
				'description' => __( 'Abilities for accessing real estate property data, valuations, and market information via Rentcast API integration.', 'lending-resource-hub' ),
			)
		);
	}

	/**
	 * Register calendar-management category
	 *
	 * @return void
	 */
	private static function register_calendar_management(): void {
		wp_register_ability_category(
			'calendar-management',
			array(
				'label'       => __( 'Calendar Management', 'lending-resource-hub' ),
				'description' => __( 'Abilities for checking availability, managing bookings, and integrating with FluentBooking calendar system.', 'lending-resource-hub' ),
			)
		);
	}
}
