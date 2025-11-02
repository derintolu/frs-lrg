<?php
/**
 * FluentBooking Integration
 *
 * Handles FluentBooking calendar auto-creation for loan officers.
 *
 * @package LendingResourceHub\Integrations
 * @since 1.0.0
 */

namespace LendingResourceHub\Integrations;

use LendingResourceHub\Traits\Base;

/**
 * Class FluentBooking
 *
 * Auto-creates FluentBooking calendars for loan officers on registration or role change.
 *
 * @package LendingResourceHub\Integrations
 */
class FluentBooking {

	use Base;

	/**
	 * Initialize FluentBooking integration.
	 *
	 * @return void
	 */
	public function init() {
		// Create calendar on user registration
		add_action( 'user_register', array( $this, 'create_calendar_for_user' ) );

		// Create calendar when user role changes to loan_officer
		add_action( 'set_user_role', array( $this, 'create_calendar_on_role_change' ), 10, 3 );

		// Fix FluentBooking frontend CSS conflicts with Blocksy theme - load EARLY to prevent flash
		add_action( 'wp_head', array( $this, 'fix_blocksy_css_conflicts' ), 1 ); // Priority 1 (very early)
		add_action( 'fluent_booking/front_head', array( $this, 'fix_blocksy_css_conflicts' ), 1 );
	}

	/**
	 * Fix FluentBooking frontend CSS conflicts with Blocksy theme.
	 *
	 * Hides Blocksy drawer canvas that interferes with FluentBooking frontend dashboard.
	 *
	 * @return void
	 */
	public function fix_blocksy_css_conflicts() {
		?>
		<style>
			/* Fix Blocksy theme drawer canvas conflict with FluentBooking dashboard */
			html body.fluentboards_page_fluent_booking.sticky-menu div.ct-drawer-canvas div.ct-drawer-inner,
			html body.fluentboards_page_fluent_booking.sticky-menu div.ct-drawer-canvas div.ct-drawer-inner * {
				display: none !important;
				visibility: hidden !important;
			}

			/* Remove padding/margins from FluentBooking dashboard containers */
			body.fluent-booking-page,
			body.fluent-booking-page .fframe_wrapper,
			body.fluent-booking-page .fframe_app,
			body.fluent-booking-page .fframe_content,
			body.fluent-booking-page .container,
			body.fluent-booking-page .wrap,
			body.fluent-booking-page > div,
			#fluent-booking-app,
			#fluent-booking-app > div {
				padding: 0 !important;
				margin: 0 !important;
				max-width: 100% !important;
			}
		</style>
		<?php
	}

	/**
	 * Create FluentBooking calendar for loan officers on user registration.
	 *
	 * @param int $user_id The ID of the newly registered user.
	 * @return void
	 */
	public function create_calendar_for_user( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		// Only create calendar for loan officers
		if ( ! in_array( 'loan_officer', $user->roles ) ) {
			return;
		}

		$this->create_loan_officer_calendar( $user_id );
	}

	/**
	 * Create FluentBooking calendar when user role changes to loan officer.
	 *
	 * @param int    $user_id   The ID of the user.
	 * @param string $role      The new role.
	 * @param array  $old_roles The old roles.
	 * @return void
	 */
	public function create_calendar_on_role_change( $user_id, $role, $old_roles ) {
		// Only create calendar when changing TO loan_officer role
		if ( $role !== 'loan_officer' ) {
			return;
		}

		// Don't create if they already had loan_officer role
		if ( in_array( 'loan_officer', $old_roles ) ) {
			return;
		}

		$this->create_loan_officer_calendar( $user_id );
	}

	/**
	 * Create a FluentBooking calendar for a loan officer.
	 *
	 * @param int $user_id The ID of the loan officer.
	 * @return void
	 */
	private function create_loan_officer_calendar( $user_id ) {
		// Check if FluentBooking is active
		if ( ! class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			error_log( 'FluentBooking: FluentBooking plugin is not active' );
			return;
		}

		// Check if calendar already exists for this user
		$existing_calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
		if ( $existing_calendar ) {
			return; // Calendar already exists
		}

		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return;
		}

		// Get user's full name from Person CPT or WP user
		$person_name = $user->display_name;

		// Try to get name from Person CPT via ACF
		if ( class_exists( 'FRS_ACF_Fields' ) ) {
			$person_data = \FRS_ACF_Fields::get_loan_officer_person( $user_id );
			if ( ! empty( $person_data['name'] ) ) {
				$person_name = $person_data['name'];
			}
		}

		// Create calendar
		try {
			$calendar = \FluentBooking\App\Models\Calendar::create(
				array(
					'user_id'     => $user_id,
					'title'       => $person_name . ' - Calendar',
					'slug'        => sanitize_title( $person_name . '-' . $user_id ),
					'description' => 'Booking calendar for ' . $person_name,
					'status'      => 'active',
					'author_id'   => $user_id,
					'type'        => 'simple',
					'settings'    => array(
						'event_color'         => '#2563eb', // Brand blue
						'max_book_per_slot'   => 1,
						'buffer_time_before'  => 0,
						'buffer_time_after'   => 0,
					),
				)
			);

			// Grant FluentBooking permissions to the user
			if ( function_exists( 'fluent_booking_permission_add_to_calendar' ) ) {
				fluent_booking_permission_add_to_calendar( $user_id, $calendar->id, 'can_manage' );
			}

			// Log success
			error_log( 'FluentBooking: Created calendar for loan officer ' . $user_id . ' (Calendar ID: ' . $calendar->id . ')' );

		} catch ( \Exception $e ) {
			error_log( 'FluentBooking: Failed to create calendar for user ' . $user_id . ': ' . $e->getMessage() );
		}
	}

	/**
	 * Check if a loan officer has a FluentBooking calendar.
	 *
	 * @param int $user_id The ID of the loan officer.
	 * @return bool True if calendar exists, false otherwise.
	 */
	public static function has_calendar( $user_id ) {
		if ( ! class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			return false;
		}

		$calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
		return ! empty( $calendar );
	}

	/**
	 * Get a loan officer's FluentBooking calendar.
	 *
	 * @param int $user_id The ID of the loan officer.
	 * @return object|null Calendar object or null if not found.
	 */
	public static function get_calendar( $user_id ) {
		if ( ! class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			return null;
		}

		return \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
	}

	/**
	 * Reset (delete and recreate) a loan officer's calendar.
	 *
	 * @param int $user_id The ID of the loan officer.
	 * @return bool True on success, false on failure.
	 */
	public static function reset_calendar( $user_id ) {
		if ( ! class_exists( 'FluentBooking\App\Models\Calendar' ) ) {
			return false;
		}

		// Delete existing calendar
		$calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
		if ( $calendar ) {
			$calendar->delete();
			error_log( 'FluentBooking: Deleted calendar for user ' . $user_id );
		}

		// Recreate calendar
		$instance = self::get_instance();
		$instance->create_loan_officer_calendar( $user_id );

		return true;
	}
}
