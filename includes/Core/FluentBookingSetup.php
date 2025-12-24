<?php
/**
 * FluentBooking Auto-Setup
 *
 * Automatically creates FluentBooking calendars for users with specific roles.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class FluentBookingSetup
 *
 * Handles automatic calendar creation for loan officers, authors, editors, and admins.
 *
 * @package LendingResourceHub\Core
 */
class FluentBookingSetup {

	use Base;

	/**
	 * Roles that should have automatic calendar setup.
	 *
	 * @var array
	 */
	private $calendar_roles = array(
		'loan_officer',
		'author',
		'editor',
		'administrator',
	);

	/**
	 * Initialize the class.
	 *
	 * @return void
	 */
	public function init() {
		// Only run if FluentBooking is active
		if ( ! defined( 'FLUENT_BOOKING_VERSION' ) ) {
			return;
		}

		// Auto-create calendar on user login
		add_action( 'wp_login', array( $this, 'maybe_create_calendar_on_login' ), 10, 2 );

		// Auto-create calendar when user role is changed
		add_action( 'set_user_role', array( $this, 'maybe_create_calendar_on_role_change' ), 10, 3 );

		// Auto-create calendar for new users
		add_action( 'user_register', array( $this, 'maybe_create_calendar_for_new_user' ), 20 );

		// Add admin action to bulk create calendars
		add_action( 'admin_init', array( $this, 'handle_bulk_calendar_creation' ) );

		// Add WP-CLI command
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'lrh fluent-booking setup-calendars', array( $this, 'cli_setup_calendars' ) );
		}
	}

	/**
	 * Check if user should have a calendar based on role.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private function user_should_have_calendar( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		foreach ( $this->calendar_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if user already has a calendar.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private function user_has_calendar( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Calendar' ) ) {
			return false;
		}

		$calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
		return ! empty( $calendar );
	}

	/**
	 * Create a calendar for a user.
	 *
	 * @param int $user_id User ID.
	 * @return bool|int Calendar ID on success, false on failure.
	 */
	public function create_calendar_for_user( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Calendar' ) ) {
			return false;
		}

		// Don't create if user already has one
		if ( $this->user_has_calendar( $user_id ) ) {
			return false;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		// Get user display name
		$display_name = trim( $user->first_name . ' ' . $user->last_name );
		if ( empty( $display_name ) ) {
			$display_name = $user->display_name;
		}

		// Get timezone
		$timezone = wp_timezone_string();

		// Create calendar data
		$calendar_data = array(
			'title'           => $display_name,
			'description'     => sprintf( __( 'Schedule a meeting with %s', 'frs-lrg' ), $display_name ),
			'user_id'         => $user_id,
			'type'            => 'simple',
			'status'          => 'active',
			'author_timezone' => $timezone,
			'created_by'      => 'auto_setup',
		);

		try {
			$calendar = \FluentBooking\App\Models\Calendar::create( $calendar_data );

			if ( $calendar && $calendar->id ) {
				// Grant manage_own_calendar permission
				$this->grant_calendar_permission( $user_id );

				// Create a default event slot
				$this->create_default_event( $calendar, $user );

				do_action( 'lrh/fluent_booking/calendar_created', $calendar, $user );

				return $calendar->id;
			}
		} catch ( \Exception $e ) {
			error_log( 'FluentBooking calendar creation failed for user ' . $user_id . ': ' . $e->getMessage() );
		}

		return false;
	}

	/**
	 * Create a default 30-minute meeting event for the calendar.
	 *
	 * @param object $calendar Calendar model.
	 * @param object $user WP_User object.
	 * @return bool|int Event ID on success, false on failure.
	 */
	private function create_default_event( $calendar, $user ) {
		if ( ! class_exists( '\FluentBooking\App\Models\CalendarSlot' ) ) {
			return false;
		}

		$display_name = trim( $user->first_name . ' ' . $user->last_name );
		if ( empty( $display_name ) ) {
			$display_name = $user->display_name;
		}

		$event_data = array(
			'calendar_id'       => $calendar->id,
			'user_id'           => $user->ID,
			'title'             => __( '30 Minute Meeting', 'frs-lrg' ),
			'slug'              => '30-minute-meeting',
			'hash'              => wp_generate_password( 12, false ),
			'duration'          => 30,
			'description'       => sprintf( __( 'Schedule a 30-minute consultation with %s.', 'frs-lrg' ), $display_name ),
			'status'            => 'active',
			'type'              => 'free',
			'event_type'        => 'one-to-one',
			'color_schema'      => '#2563eb',
			'location_settings' => array(
				array(
					'type'        => 'online_meeting',
					'title'       => __( 'Online Meeting', 'frs-lrg' ),
					'description' => __( 'A meeting link will be provided upon booking.', 'frs-lrg' ),
				),
			),
			'settings'          => array(
				'schedule_type'     => 'weekly_schedules',
				'weekly_schedules'  => $this->get_default_weekly_schedule(),
				'buffer_time_after' => 15,
				'range_type'        => 'range_days',
				'range_days'        => 60,
				'slot_interval'     => 30,
			),
		);

		try {
			$event = \FluentBooking\App\Models\CalendarSlot::create( $event_data );
			return $event ? $event->id : false;
		} catch ( \Exception $e ) {
			error_log( 'FluentBooking event creation failed: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get default weekly schedule (Mon-Fri 9am-5pm).
	 *
	 * @return array
	 */
	private function get_default_weekly_schedule() {
		$schedule = array();
		$days     = array( 'mon', 'tue', 'wed', 'thu', 'fri' );

		foreach ( $days as $day ) {
			$schedule[ $day ] = array(
				'enabled' => true,
				'slots'   => array(
					array(
						'start' => '09:00',
						'end'   => '17:00',
					),
				),
			);
		}

		// Weekend off
		$schedule['sat'] = array( 'enabled' => false, 'slots' => array() );
		$schedule['sun'] = array( 'enabled' => false, 'slots' => array() );

		return $schedule;
	}

	/**
	 * Grant manage_own_calendar permission to user.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	private function grant_calendar_permission( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Meta' ) ) {
			return;
		}

		// Check if permission already exists
		$existing = \FluentBooking\App\Models\Meta::where( 'object_type', 'user_meta' )
			->where( 'object_id', $user_id )
			->where( 'key', '_access_permissions' )
			->first();

		if ( $existing ) {
			return;
		}

		\FluentBooking\App\Models\Meta::create(
			array(
				'object_type' => 'user_meta',
				'object_id'   => $user_id,
				'key'         => '_access_permissions',
				'value'       => array( 'manage_own_calendar' ),
			)
		);
	}

	/**
	 * Maybe create calendar on user login.
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user User object.
	 * @return void
	 */
	public function maybe_create_calendar_on_login( $user_login, $user ) {
		if ( ! $this->user_should_have_calendar( $user->ID ) ) {
			return;
		}

		if ( $this->user_has_calendar( $user->ID ) ) {
			return;
		}

		$this->create_calendar_for_user( $user->ID );
	}

	/**
	 * Maybe create calendar when user role is changed.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role New role.
	 * @param array  $old_roles Old roles.
	 * @return void
	 */
	public function maybe_create_calendar_on_role_change( $user_id, $role, $old_roles ) {
		if ( ! in_array( $role, $this->calendar_roles, true ) ) {
			return;
		}

		if ( $this->user_has_calendar( $user_id ) ) {
			return;
		}

		$this->create_calendar_for_user( $user_id );
	}

	/**
	 * Maybe create calendar for new user.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function maybe_create_calendar_for_new_user( $user_id ) {
		if ( ! $this->user_should_have_calendar( $user_id ) ) {
			return;
		}

		$this->create_calendar_for_user( $user_id );
	}

	/**
	 * Handle bulk calendar creation from admin.
	 *
	 * @return void
	 */
	public function handle_bulk_calendar_creation() {
		if ( ! isset( $_GET['lrh_setup_calendars'] ) || $_GET['lrh_setup_calendars'] !== '1' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'lrh_setup_calendars' ) ) {
			return;
		}

		$result = $this->setup_all_calendars();

		add_action(
			'admin_notices',
			function () use ( $result ) {
				?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						__( 'FluentBooking calendars created for %d users. %d users already had calendars.', 'frs-lrg' ),
						$result['created'],
						$result['skipped']
					);
					?>
				</p>
			</div>
				<?php
			}
		);
	}

	/**
	 * Setup calendars for all eligible users.
	 *
	 * @return array Results with 'created' and 'skipped' counts.
	 */
	public function setup_all_calendars() {
		$created = 0;
		$skipped = 0;

		// Get all users with the target roles
		$users = get_users(
			array(
				'role__in' => $this->calendar_roles,
				'fields'   => 'ID',
			)
		);

		foreach ( $users as $user_id ) {
			if ( $this->user_has_calendar( $user_id ) ) {
				$skipped++;
				continue;
			}

			$result = $this->create_calendar_for_user( $user_id );
			if ( $result ) {
				$created++;
			}
		}

		return array(
			'created' => $created,
			'skipped' => $skipped,
			'total'   => count( $users ),
		);
	}

	/**
	 * WP-CLI command to setup calendars for all eligible users.
	 *
	 * @param array $args Positional args.
	 * @param array $assoc_args Named args.
	 * @return void
	 */
	public function cli_setup_calendars( $args, $assoc_args ) {
		if ( ! defined( 'FLUENT_BOOKING_VERSION' ) ) {
			\WP_CLI::error( 'FluentBooking is not active.' );
			return;
		}

		\WP_CLI::log( 'Setting up FluentBooking calendars for users with roles: ' . implode( ', ', $this->calendar_roles ) );

		$result = $this->setup_all_calendars();

		\WP_CLI::success(
			sprintf(
				'Created %d calendars. Skipped %d users (already had calendars). Total eligible users: %d',
				$result['created'],
				$result['skipped'],
				$result['total']
			)
		);
	}
}
