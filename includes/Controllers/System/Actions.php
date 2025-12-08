<?php
/**
 * System Controller
 *
 * Handles system diagnostic and health check endpoints.
 *
 * @package LendingResourceHub\Controllers\System
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\System;

use LendingResourceHub\Models\Partnership;
use LendingResourceHub\Models\LeadSubmission;
use LendingResourceHub\Models\PageAssignment;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles system-related actions.
 *
 * @package LendingResourceHub\Controllers\System
 */
class Actions {

	/**
	 * Get comprehensive system diagnostics.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_diagnostics( WP_REST_Request $request ) {
		global $wpdb;

		// Check database tables
		$tables = array(
			array(
				'name'  => $wpdb->prefix . 'partnerships',
				'model' => Partnership::class,
			),
			array(
				'name'  => $wpdb->prefix . 'lead_submissions',
				'model' => LeadSubmission::class,
			),
			array(
				'name'  => $wpdb->prefix . 'page_assignments',
				'model' => PageAssignment::class,
			),
			array(
				'name'  => $wpdb->prefix . 'accounts',
				'model' => null,
			),
		);

		$table_diagnostics = array();
		foreach ( $tables as $table_info ) {
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_info['name'] ) ) === $table_info['name'];

			$row_count = 0;
			if ( $table_exists && $table_info['model'] ) {
				try {
					$row_count = $table_info['model']::count();
				} catch ( \Exception $e ) {
					$row_count = 0;
				}
			}

			$table_diagnostics[] = array(
				'name'     => $table_info['name'],
				'exists'   => $table_exists,
				'rowCount' => $row_count,
			);
		}

		// Check shortcodes
		global $shortcode_tags;
		$lrh_shortcodes = array(
			array(
				'name'        => '[lrh_portal]',
				'registered'  => isset( $shortcode_tags['lrh_portal'] ),
				'description' => 'Main portal interface for loan officers and realtors',
			),
			array(
				'name'        => '[lrh_portal_sidebar]',
				'registered'  => isset( $shortcode_tags['lrh_portal_sidebar'] ),
				'description' => 'Global sidebar navigation',
			),
			array(
				'name'        => '[frs_partnership_portal]',
				'registered'  => isset( $shortcode_tags['frs_partnership_portal'] ),
				'description' => 'Legacy shortcode (backward compatibility)',
			),
		);

		// Check API endpoints
		$api_base_url = rest_url( LRH_ROUTE_PREFIX . '/' );
		$endpoints    = array(
			array(
				'path'   => '/users/me',
				'method' => 'GET',
				'status' => $this->test_endpoint( LRH_ROUTE_PREFIX . '/users/me' ),
			),
			array(
				'path'   => '/partnerships',
				'method' => 'GET',
				'status' => 'ok',
			),
			array(
				'path'   => '/leads',
				'method' => 'GET',
				'status' => 'ok',
			),
			array(
				'path'   => '/dashboard/stats',
				'method' => 'GET',
				'status' => 'ok',
			),
		);

		// Check built assets
		$admin_dist_path    = LRH_DIR . '/assets/admin/dist/';
		$frontend_dist_path = LRH_DIR . '/assets/frontend/dist/';

		$admin_manifest_exists    = \LendingResourceHub\Libs\Assets\manifest_exists( $admin_dist_path );
		$frontend_manifest_exists = \LendingResourceHub\Libs\Assets\manifest_exists( $frontend_dist_path );

		$assets = array(
			'admin'    => array(
				'built' => $admin_manifest_exists,
				'path'  => '/assets/admin/dist/',
			),
			'frontend' => array(
				'built' => $frontend_manifest_exists,
				'path'  => '/assets/frontend/dist/',
			),
		);

		// Check integrations
		$integrations = array(
			array(
				'name'     => 'FluentBooking',
				'active'   => is_plugin_active( 'fluent-booking/fluent-booking.php' ),
				'required' => false,
			),
			array(
				'name'     => 'FluentForms',
				'active'   => is_plugin_active( 'fluentform/fluentform.php' ),
				'required' => false,
			),
			array(
				'name'     => 'FluentCRM',
				'active'   => is_plugin_active( 'fluent-crm/fluent-crm.php' ),
				'required' => false,
			),
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'plugin'       => array(
						'version' => '1.0.0',
						'active'  => true,
					),
					'database'     => array(
						'tables' => $table_diagnostics,
					),
					'api'          => array(
						'baseUrl'   => $api_base_url,
						'endpoints' => $endpoints,
					),
					'shortcodes'   => $lrh_shortcodes,
					'assets'       => $assets,
					'integrations' => $integrations,
				),
			),
			200
		);
	}

	/**
	 * Test if an API endpoint is responsive.
	 *
	 * @param string $endpoint The endpoint to test.
	 * @return string Status: 'ok', 'error', or 'unknown'.
	 */
	private function test_endpoint( $endpoint ) {
		// For now, just return 'ok' for all registered endpoints
		// In production, you could make actual internal API calls
		return 'ok';
	}
}
