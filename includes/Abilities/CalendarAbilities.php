<?php
/**
 * Calendar Abilities
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

namespace LendingResourceHub\Abilities;

use WP_Error;

/**
 * Class CalendarAbilities
 *
 * Registers abilities for calendar and booking management via FluentBooking integration.
 */
class CalendarAbilities {

	/**
	 * Register all calendar abilities
	 *
	 * @return void
	 */
	public static function register(): void {
		self::register_check_availability();
		self::register_get_bookings();
	}

	/**
	 * Register check-availability ability
	 *
	 * @return void
	 */
	private static function register_check_availability(): void {
		wp_register_ability(
			'lrh/check-availability',
			array(
				'label'       => __( 'Check Availability', 'lending-resource-hub' ),
				'description' => __( 'Checks calendar availability for a user or team member for scheduling appointments.', 'lending-resource-hub' ),
				'category'    => 'calendar-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'User ID to check availability for. Defaults to current user.', 'lending-resource-hub' ),
						),
						'date' => array(
							'type'        => 'string',
							'description' => __( 'Date to check in YYYY-MM-DD format.', 'lending-resource-hub' ),
							'format'      => 'date',
						),
					),
					'required'             => array( 'date' ),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id'        => array( 'type' => 'integer' ),
						'date'           => array( 'type' => 'string' ),
						'available_slots' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'start_time' => array( 'type' => 'string' ),
									'end_time'   => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_check_availability' ),
				'permission_callback' => function() {
					return current_user_can( 'read' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
						'instructions' => 'Requires FluentBooking plugin to be active.',
					),
				),
			)
		);
	}

	/**
	 * Execute check-availability ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Availability data or error.
	 */
	public static function execute_check_availability( array $input ) {
		// Check if FluentBooking is active
		if ( ! function_exists( 'FluentBooking' ) ) {
			return new WP_Error(
				'fluentbooking_not_active',
				__( 'FluentBooking plugin is not active.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : get_current_user_id();
		$date = sanitize_text_field( $input['date'] );

		// Validate date format
		$date_obj = \DateTime::createFromFormat( 'Y-m-d', $date );
		if ( ! $date_obj || $date_obj->format( 'Y-m-d' ) !== $date ) {
			return new WP_Error(
				'invalid_date_format',
				__( 'Invalid date format. Use YYYY-MM-DD.', 'lending-resource-hub' ),
				array( 'status' => 400 )
			);
		}

		// Check if user exists
		if ( ! get_user_by( 'id', $user_id ) ) {
			return new WP_Error(
				'user_not_found',
				__( 'User not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Mock availability data (integrate with FluentBooking API when available)
		$available_slots = array(
			array(
				'start_time' => '09:00:00',
				'end_time'   => '10:00:00',
			),
			array(
				'start_time' => '10:00:00',
				'end_time'   => '11:00:00',
			),
			array(
				'start_time' => '14:00:00',
				'end_time'   => '15:00:00',
			),
			array(
				'start_time' => '15:00:00',
				'end_time'   => '16:00:00',
			),
		);

		return array(
			'user_id'         => $user_id,
			'date'            => $date,
			'available_slots' => $available_slots,
		);
	}

	/**
	 * Register get-bookings ability
	 *
	 * @return void
	 */
	private static function register_get_bookings(): void {
		wp_register_ability(
			'lrh/get-bookings',
			array(
				'label'       => __( 'Get Bookings', 'lending-resource-hub' ),
				'description' => __( 'Retrieves calendar bookings for a user within a specified date range.', 'lending-resource-hub' ),
				'category'    => 'calendar-management',
				'input_schema' => array(
					'type'       => 'object',
					'properties' => array(
						'user_id' => array(
							'type'        => 'integer',
							'description' => __( 'User ID to get bookings for. Defaults to current user.', 'lending-resource-hub' ),
						),
						'start_date' => array(
							'type'        => 'string',
							'description' => __( 'Start date in YYYY-MM-DD format.', 'lending-resource-hub' ),
							'format'      => 'date',
						),
						'end_date' => array(
							'type'        => 'string',
							'description' => __( 'End date in YYYY-MM-DD format.', 'lending-resource-hub' ),
							'format'      => 'date',
						),
						'limit' => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of results to return.', 'lending-resource-hub' ),
							'default'     => 20,
							'minimum'     => 1,
							'maximum'     => 100,
						),
					),
					'additionalProperties' => false,
				),
				'output_schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array( 'type' => 'integer' ),
							'user_id'      => array( 'type' => 'integer' ),
							'title'        => array( 'type' => 'string' ),
							'description'  => array( 'type' => 'string' ),
							'start_time'   => array( 'type' => 'string' ),
							'end_time'     => array( 'type' => 'string' ),
							'status'       => array( 'type' => 'string' ),
							'attendee_name' => array( 'type' => 'string' ),
							'attendee_email' => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback' => array( self::class, 'execute_get_bookings' ),
				'permission_callback' => function() {
					return current_user_can( 'edit_posts' );
				},
				'meta' => array(
					'show_in_rest' => true,
					'annotations'  => array(
						'readonly'   => true,
						'idempotent' => true,
						'instructions' => 'Requires FluentBooking plugin to be active.',
					),
				),
			)
		);
	}

	/**
	 * Execute get-bookings ability
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Bookings data or error.
	 */
	public static function execute_get_bookings( array $input ) {
		// Check if FluentBooking is active
		if ( ! function_exists( 'FluentBooking' ) ) {
			return new WP_Error(
				'fluentbooking_not_active',
				__( 'FluentBooking plugin is not active.', 'lending-resource-hub' ),
				array( 'status' => 500 )
			);
		}

		$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : get_current_user_id();
		$limit = isset( $input['limit'] ) ? absint( $input['limit'] ) : 20;

		// Check if user exists
		if ( ! get_user_by( 'id', $user_id ) ) {
			return new WP_Error(
				'user_not_found',
				__( 'User not found.', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		// Validate dates if provided
		if ( isset( $input['start_date'] ) ) {
			$start_date = sanitize_text_field( $input['start_date'] );
			$start_date_obj = \DateTime::createFromFormat( 'Y-m-d', $start_date );
			if ( ! $start_date_obj || $start_date_obj->format( 'Y-m-d' ) !== $start_date ) {
				return new WP_Error(
					'invalid_date_format',
					__( 'Invalid start_date format. Use YYYY-MM-DD.', 'lending-resource-hub' ),
					array( 'status' => 400 )
				);
			}
		}

		if ( isset( $input['end_date'] ) ) {
			$end_date = sanitize_text_field( $input['end_date'] );
			$end_date_obj = \DateTime::createFromFormat( 'Y-m-d', $end_date );
			if ( ! $end_date_obj || $end_date_obj->format( 'Y-m-d' ) !== $end_date ) {
				return new WP_Error(
					'invalid_date_format',
					__( 'Invalid end_date format. Use YYYY-MM-DD.', 'lending-resource-hub' ),
					array( 'status' => 400 )
				);
			}
		}

		// Mock bookings data (integrate with FluentBooking API when available)
		$bookings = array(
			array(
				'id'             => 1,
				'user_id'        => $user_id,
				'title'          => 'Mortgage Consultation',
				'description'    => 'Initial consultation with client about mortgage options',
				'start_time'     => '2025-01-15 10:00:00',
				'end_time'       => '2025-01-15 11:00:00',
				'status'         => 'confirmed',
				'attendee_name'  => 'John Doe',
				'attendee_email' => 'john@example.com',
			),
			array(
				'id'             => 2,
				'user_id'        => $user_id,
				'title'          => 'Property Viewing',
				'description'    => 'Show property to potential buyer',
				'start_time'     => '2025-01-16 14:00:00',
				'end_time'       => '2025-01-16 15:00:00',
				'status'         => 'confirmed',
				'attendee_name'  => 'Jane Smith',
				'attendee_email' => 'jane@example.com',
			),
		);

		return array_slice( $bookings, 0, $limit );
	}
}
