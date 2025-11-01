<?php
/**
 * Data migration from old FRS Partnership Portal plugin.
 *
 * @package LendingResourceHub
 * @subpackage Database
 * @since 1.0.0
 */

namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;

/**
 * Class MigrateOldData
 *
 * Migrates data from old frs-partnership-portal tables to new lending-resource-hub tables.
 *
 * @package LendingResourceHub\Database\Migrations
 */
class MigrateOldData implements Migration {

	/**
	 * Run the data migration.
	 */
	public static function up() {
		global $wpdb;

		$old_partnerships_table      = $wpdb->prefix . 'frs_partnerships';
		$old_lead_submissions_table  = $wpdb->prefix . 'frs_lead_submissions';
		$old_page_assignments_table  = $wpdb->prefix . 'frs_page_assignments';

		$new_partnerships_table      = $wpdb->prefix . 'partnerships';
		$new_lead_submissions_table  = $wpdb->prefix . 'lead_submissions';
		$new_page_assignments_table  = $wpdb->prefix . 'page_assignments';

		// Check if migration has already been run
		$migration_flag = get_option( 'lrh_data_migration_completed' );
		if ( $migration_flag ) {
			return;
		}

		// Migrate partnerships
		if ( self::table_exists( $old_partnerships_table ) ) {
			$partnerships = $wpdb->get_results( "SELECT * FROM {$old_partnerships_table}" );

			if ( ! empty( $partnerships ) ) {
				foreach ( $partnerships as $partnership ) {
					// Check if already migrated
					$existing = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT id FROM {$new_partnerships_table} WHERE loan_officer_id = %d AND partner_email = %s",
							$partnership->loan_officer_id,
							$partnership->partner_email
						)
					);

					if ( ! $existing ) {
						$wpdb->insert(
							$new_partnerships_table,
							array(
								'loan_officer_id'  => $partnership->loan_officer_id,
								'agent_id'         => $partnership->agent_id,
								'partner_post_id'  => $partnership->partner_post_id,
								'partner_email'    => $partnership->partner_email,
								'partner_name'     => $partnership->partner_name,
								'status'           => $partnership->status,
								'invite_token'     => $partnership->invite_token,
								'invite_sent_date' => $partnership->invite_sent_date,
								'accepted_date'    => $partnership->accepted_date,
								'custom_data'      => $partnership->custom_data,
								'created_date'     => $partnership->created_date,
								'updated_date'     => $partnership->updated_date,
							),
							array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
						);
					}
				}
			}
		}

		// Migrate lead submissions
		if ( self::table_exists( $old_lead_submissions_table ) ) {
			$leads = $wpdb->get_results( "SELECT * FROM {$old_lead_submissions_table}" );

			if ( ! empty( $leads ) ) {
				foreach ( $leads as $lead ) {
					// Check if already migrated
					$existing = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT id FROM {$new_lead_submissions_table} WHERE email = %s AND created_date = %s",
							$lead->email,
							$lead->created_date
						)
					);

					if ( ! $existing ) {
						$wpdb->insert(
							$new_lead_submissions_table,
							array(
								'partnership_id'   => $lead->partnership_id,
								'loan_officer_id'  => $lead->loan_officer_id,
								'agent_id'         => $lead->agent_id,
								'lead_source'      => $lead->lead_source,
								'first_name'       => $lead->first_name,
								'last_name'        => $lead->last_name,
								'email'            => $lead->email,
								'phone'            => $lead->phone,
								'loan_amount'      => $lead->loan_amount,
								'property_value'   => $lead->property_value,
								'property_address' => $lead->property_address,
								'message'          => null, // New field, no data in old table
								'lead_data'        => $lead->lead_data,
								'form_data'        => $lead->form_data,
								'notes'            => null, // New field, no data in old table
								'status'           => $lead->status,
								'created_date'     => $lead->created_date,
								'updated_date'     => $lead->updated_date,
							),
							array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
						);
					}
				}
			}
		}

		// Migrate page assignments
		if ( self::table_exists( $old_page_assignments_table ) ) {
			$assignments = $wpdb->get_results( "SELECT * FROM {$old_page_assignments_table}" );

			if ( ! empty( $assignments ) ) {
				foreach ( $assignments as $assignment ) {
					// Check if already migrated
					$existing = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT id FROM {$new_page_assignments_table} WHERE user_id = %d AND assigned_page_id = %d",
							$assignment->user_id,
							$assignment->assigned_page_id
						)
					);

					if ( ! $existing ) {
						$wpdb->insert(
							$new_page_assignments_table,
							array(
								'user_id'          => $assignment->user_id,
								'template_page_id' => $assignment->template_page_id,
								'assigned_page_id' => $assignment->assigned_page_id,
								'page_type'        => $assignment->page_type,
								'slug_pattern'     => $assignment->slug_pattern,
								'created_date'     => $assignment->created_date,
							),
							array( '%d', '%d', '%d', '%s', '%s', '%s' )
						);
					}
				}
			}
		}

		// Mark migration as complete
		update_option( 'lrh_data_migration_completed', true );

		// Log the migration
		error_log( 'LRH: Data migration from old plugin completed' );
	}

	/**
	 * Reverse the migrations (not applicable for data migration).
	 */
	public static function down() {
		// Data migration is not reversible
		// Only the schema migration can be reversed
	}

	/**
	 * Check if a table exists.
	 *
	 * @param string $table_name The table name to check.
	 * @return bool True if table exists, false otherwise.
	 */
	private static function table_exists( $table_name ) {
		global $wpdb;
		$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		return $table === $table_name;
	}
}
