<?php
/**
 * Manual Data Migration Script
 *
 * Run this file once to migrate data from the old frs-partnership-portal plugin
 * to the new lending-resource-hub plugin.
 *
 * Usage: Navigate to this file in your browser or run via WP-CLI:
 * wp eval-file migrate-data.php
 *
 * @package LendingResourceHub
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have permission to run this script.' );
}

// Load the migration class
require_once __DIR__ . '/database/Migrations/MigrateOldData.php';

use LendingResourceHub\Database\Migrations\MigrateOldData;

echo '<h1>LRH Data Migration</h1>';
echo '<p>Starting data migration from old FRS Partnership Portal plugin...</p>';

// Clear the migration flag to allow re-running
delete_option( 'lrh_data_migration_completed' );

// Run the migration
try {
	MigrateOldData::up();
	echo '<p style="color: green;"><strong>✓ Migration completed successfully!</strong></p>';

	// Show migration results
	global $wpdb;
	$partnerships_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}partnerships" );
	$leads_count        = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lead_submissions" );
	$assignments_count  = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}page_assignments" );

	echo '<h2>Migration Results:</h2>';
	echo '<ul>';
	echo "<li>Partnerships: <strong>{$partnerships_count}</strong></li>";
	echo "<li>Lead Submissions: <strong>{$leads_count}</strong></li>";
	echo "<li>Page Assignments: <strong>{$assignments_count}</strong></li>";
	echo '</ul>';

	echo '<p><a href="' . admin_url( 'admin.php?page=lending-resource-hub' ) . '">Go to Plugin Dashboard</a></p>';

} catch ( Exception $e ) {
	echo '<p style="color: red;"><strong>✗ Migration failed:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
}

echo '<hr>';
echo '<p><em>You can delete this file after successful migration.</em></p>';
