<?php
/**
 * Admin System Diagnostic Page
 *
 * Traditional PHP admin page (not React).
 *
 * @package LendingResourceHub\Admin
 * @since 1.0.0
 */

namespace LendingResourceHub\Admin;

use LendingResourceHub\Models\Partnership;
use LendingResourceHub\Models\LeadSubmission;
use LendingResourceHub\Models\PageAssignment;
use LendingResourceHub\Traits\Base;

/**
 * Class SystemDiagnostic
 *
 * Handles the system diagnostic page.
 *
 * @package LendingResourceHub\Admin
 */
class SystemDiagnostic {

	use Base;

	/**
	 * Render the system diagnostic page.
	 *
	 * @return void
	 */
	public function render() {
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

		// Check built assets
		$admin_manifest_exists    = \LendingResourceHub\Libs\Assets\manifest_exists( LRH_DIR . '/assets/admin/dist' );
		$frontend_manifest_exists = \LendingResourceHub\Libs\Assets\manifest_exists( LRH_DIR . '/assets/frontend/dist' );

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

		// Calculate health score
		$total   = 0;
		$healthy = 0;

		foreach ( $table_diagnostics as $table ) {
			$total++;
			if ( $table['exists'] ) {
				$healthy++;
			}
		}

		foreach ( $lrh_shortcodes as $shortcode ) {
			$total++;
			if ( $shortcode['registered'] ) {
				$healthy++;
			}
		}

		$total++;
		if ( $frontend_manifest_exists ) {
			$healthy++;
		}

		$health_score = $total > 0 ? round( ( $healthy / $total ) * 100 ) : 0;

		// Load template
		include LRH_DIR . 'views/admin/system-diagnostic.php';
	}
}
