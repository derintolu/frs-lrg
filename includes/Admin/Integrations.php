<?php
/**
 * Admin Integrations Page
 *
 * @package LendingResourceHub\Admin
 * @since 1.0.0
 */

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;

/**
 * Class Integrations
 *
 * Handles the integrations settings page.
 *
 * @package LendingResourceHub\Admin
 */
class Integrations {

	use Base;

	/**
	 * Render the integrations page.
	 *
	 * @return void
	 */
	public function render() {
		// Get saved settings from WordPress options
		$arrive_enabled = get_option( 'lrh_arrive_enabled', false );
		$arrive_api_key = get_option( 'lrh_arrive_api_key', '' );
		$fub_enabled    = get_option( 'lrh_fub_enabled', false );
		$fub_api_key    = get_option( 'lrh_fub_api_key', '' );

		// Handle form submission
		if ( isset( $_POST['lrh_integrations_nonce'] ) && wp_verify_nonce( $_POST['lrh_integrations_nonce'], 'lrh_save_integrations' ) ) {
			// Arrive settings
			update_option( 'lrh_arrive_enabled', isset( $_POST['arrive_enabled'] ) );
			update_option( 'lrh_arrive_api_key', sanitize_text_field( $_POST['arrive_api_key'] ?? '' ) );

			// FUB settings
			update_option( 'lrh_fub_enabled', isset( $_POST['fub_enabled'] ) );
			update_option( 'lrh_fub_api_key', sanitize_text_field( $_POST['fub_api_key'] ?? '' ) );

			// Reload values
			$arrive_enabled = isset( $_POST['arrive_enabled'] );
			$arrive_api_key = sanitize_text_field( $_POST['arrive_api_key'] ?? '' );
			$fub_enabled    = isset( $_POST['fub_enabled'] );
			$fub_api_key    = sanitize_text_field( $_POST['fub_api_key'] ?? '' );

			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Integration settings saved successfully.', 'lending-resource-hub' ) . '</p></div>';
		}

		// Load template
		include LRH_DIR . 'views/admin/integrations.php';
	}
}
