<?php
/**
 * Settings Controller
 *
 * Handles user settings API endpoints.
 *
 * @package LendingResourceHub\Controllers\Settings
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Settings;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles settings-related actions.
 *
 * @package LendingResourceHub\Controllers\Settings
 */
class Actions {

	/**
	 * Get plugin settings.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function get_settings( WP_REST_Request $request ) {
		// Check if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'unauthorized', 'You must be an administrator to access this resource', array( 'status' => 401 ) );
		}

		// Get plugin settings from WordPress options
		$settings = array(
			'notify_loan_officer'       => (bool) get_option( 'lrh_notify_loan_officer', true ),
			'notify_agent'              => (bool) get_option( 'lrh_notify_agent', true ),
			'notify_admin'              => (bool) get_option( 'lrh_notify_admin', false ),
			'admin_notification_email'  => get_option( 'lrh_admin_notification_email', '' ),
			'invitation_expiry'         => (int) get_option( 'lrh_invitation_expiry', 30 ),
			'max_partnerships'          => (int) get_option( 'lrh_max_partnerships', 0 ),
			'debug_mode'                => (bool) get_option( 'lrh_debug_mode', false ),
			'cleanup_on_deactivate'     => (bool) get_option( 'lrh_cleanup_on_deactivate', false ),
		);

		return new WP_REST_Response( array( 'success' => true, 'data' => $settings ), 200 );
	}

	/**
	 * Update plugin settings.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function update_settings( WP_REST_Request $request ) {
		// Check if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'unauthorized', 'You must be an administrator to access this resource', array( 'status' => 401 ) );
		}

		$params = $request->get_json_params();

		if ( ! $params ) {
			$params = $request->get_params();
		}

		// Update notification settings
		if ( isset( $params['notify_loan_officer'] ) ) {
			update_option( 'lrh_notify_loan_officer', (bool) $params['notify_loan_officer'] );
		}
		if ( isset( $params['notify_agent'] ) ) {
			update_option( 'lrh_notify_agent', (bool) $params['notify_agent'] );
		}
		if ( isset( $params['notify_admin'] ) ) {
			update_option( 'lrh_notify_admin', (bool) $params['notify_admin'] );
		}
		if ( isset( $params['admin_notification_email'] ) ) {
			$email = sanitize_email( $params['admin_notification_email'] );
			if ( empty( $email ) || is_email( $email ) ) {
				update_option( 'lrh_admin_notification_email', $email );
			} else {
				return new WP_Error( 'invalid_email', 'Invalid email address', array( 'status' => 400 ) );
			}
		}

		// Update advanced settings
		if ( isset( $params['invitation_expiry'] ) ) {
			$expiry = (int) $params['invitation_expiry'];
			if ( $expiry >= 1 && $expiry <= 365 ) {
				update_option( 'lrh_invitation_expiry', $expiry );
			}
		}
		if ( isset( $params['max_partnerships'] ) ) {
			$max = (int) $params['max_partnerships'];
			if ( $max >= 0 ) {
				update_option( 'lrh_max_partnerships', $max );
			}
		}
		if ( isset( $params['debug_mode'] ) ) {
			update_option( 'lrh_debug_mode', (bool) $params['debug_mode'] );
		}
		if ( isset( $params['cleanup_on_deactivate'] ) ) {
			update_option( 'lrh_cleanup_on_deactivate', (bool) $params['cleanup_on_deactivate'] );
		}

		return new WP_REST_Response( array( 'success' => true, 'message' => 'Settings updated successfully' ), 200 );
	}

	/**
	 * Get system information.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response or error.
	 */
	public function get_system_info( WP_REST_Request $request ) {
		// Check if user can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'unauthorized', 'You must be an administrator to access this resource', array( 'status' => 401 ) );
		}

		global $wpdb;

		// Get plugin version
		$plugin_data = get_file_data(
			dirname( dirname( dirname( __DIR__ ) ) ) . '/plugin.php',
			array( 'Version' => 'Version' )
		);

		// Get counts
		$total_partnerships   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}partnerships" );
		$total_leads          = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lead_submissions" );
		$total_loan_officers  = count( get_users( array( 'role' => 'loan_officer' ) ) );
		$total_realtors       = count( get_users( array( 'role' => 'realtor_partner' ) ) );

		$system_info = array(
			'plugin_version'       => $plugin_data['Version'] ?? '1.0.0',
			'wp_version'           => get_bloginfo( 'version' ),
			'php_version'          => phpversion(),
			'db_version'           => $wpdb->db_version(),
			'total_partnerships'   => (int) $total_partnerships,
			'total_leads'          => (int) $total_leads,
			'total_loan_officers'  => $total_loan_officers,
			'total_realtors'       => $total_realtors,
		);

		return new WP_REST_Response( array( 'success' => true, 'data' => $system_info ), 200 );
	}
}
