<?php
/**
 * WP-CLI Commands for Partner Company Management
 *
 * @package LendingResourceHub\CLI
 * @since 1.0.0
 */

namespace LendingResourceHub\CLI;

use LendingResourceHub\Core\PartnerCompanyImporter;
use WP_CLI;

/**
 * Manage partner companies via WP-CLI.
 *
 * @package LendingResourceHub\CLI
 */
class PartnerCompanyCommands {

	/**
	 * Import partner company from CSV file.
	 *
	 * Creates a BuddyPress group and imports all realtors from CSV.
	 *
	 * ## OPTIONS
	 *
	 * <csv_path>
	 * : Absolute path to the CSV file.
	 *
	 * <company_name>
	 * : Name of the partner company.
	 *
	 * [--primary-color=<color>]
	 * : Primary brand color (hex). Default: #2563eb
	 *
	 * [--secondary-color=<color>]
	 * : Secondary brand color (hex). Default: #2dd4da
	 *
	 * [--button-style=<style>]
	 * : Button style (rounded|square|gradient). Default: rounded
	 *
	 * ## EXAMPLES
	 *
	 *     wp lrh partner-company import "/path/to/roster.csv" "Century 21 Professionals"
	 *     wp lrh partner-company import "/path/to/roster.csv" "Century 21 Professionals" --primary-color="#ff0000"
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function import( $args, $assoc_args ) {
		list( $csv_path, $company_name ) = $args;

		// Validate CSV file
		if ( ! file_exists( $csv_path ) ) {
			WP_CLI::error( sprintf( 'CSV file not found: %s', $csv_path ) );
			return;
		}

		WP_CLI::log( sprintf( 'Importing partner company: %s', $company_name ) );
		WP_CLI::log( sprintf( 'CSV file: %s', $csv_path ) );

		// Parse branding options
		$branding = array(
			'primary_color'   => isset( $assoc_args['primary-color'] ) ? $assoc_args['primary-color'] : '#2563eb',
			'secondary_color' => isset( $assoc_args['secondary-color'] ) ? $assoc_args['secondary-color'] : '#2dd4da',
			'button_style'    => isset( $assoc_args['button-style'] ) ? $assoc_args['button-style'] : 'rounded',
		);

		// Run import
		$importer = PartnerCompanyImporter::get_instance();
		$results  = $importer->import_from_csv( $csv_path, $company_name, $branding );

		if ( ! $results['success'] ) {
			WP_CLI::error( $results['message'] );
			return;
		}

		// Display results
		WP_CLI::success( $results['message'] );

		WP_CLI::log( '' );
		WP_CLI::log( 'Import Summary:' );
		WP_CLI::log( sprintf( '  Group ID: %d', $results['group_id'] ) );
		WP_CLI::log( sprintf( '  New users created: %d', $results['users_created'] ) );
		WP_CLI::log( sprintf( '  Existing users: %d', $results['users_existing'] ) );
		WP_CLI::log( sprintf( '  Total members added: %d', $results['members_added'] ) );

		if ( ! empty( $results['errors'] ) ) {
			WP_CLI::log( '' );
			WP_CLI::warning( sprintf( '%d errors occurred:', count( $results['errors'] ) ) );
			foreach ( $results['errors'] as $error ) {
				WP_CLI::log( '  - ' . $error );
			}
		}

		// Display group stats
		$stats = $importer->get_group_stats( $results['group_id'] );
		WP_CLI::log( '' );
		WP_CLI::log( 'Group Statistics:' );
		WP_CLI::log( sprintf( '  Total members: %d', $stats['member_count'] ) );
		WP_CLI::log( sprintf( '  Page views: %d', $stats['page_views'] ) );
		WP_CLI::log( sprintf( '  Conversions: %d', $stats['conversions'] ) );

		// Display next steps
		WP_CLI::log( '' );
		WP_CLI::log( 'Next Steps:' );
		WP_CLI::log( sprintf( '  1. View group: %s', bp_get_group_permalink( groups_get_group( $results['group_id'] ) ) ) );
		WP_CLI::log( '  2. Assign loan officers to this group' );
		WP_CLI::log( '  3. Configure group branding in the admin' );
	}

	/**
	 * List all partner company groups.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lrh partner-company list
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function list( $args, $assoc_args ) {
		if ( ! function_exists( 'groups_get_groups' ) ) {
			WP_CLI::error( 'BuddyPress is not active' );
			return;
		}

		// Get all groups with partner-org type
		$groups = groups_get_groups(
			array(
				'type'     => 'alphabetical',
				'per_page' => 999,
			)
		);

		if ( empty( $groups['groups'] ) ) {
			WP_CLI::log( 'No partner company groups found.' );
			return;
		}

		$importer = PartnerCompanyImporter::get_instance();
		$rows     = array();

		foreach ( $groups['groups'] as $group ) {
			// Check if this is a partner-org group
			$group_type = bp_groups_get_group_type( $group->id );
			if ( $group_type !== 'partner-org' ) {
				continue;
			}

			$stats = $importer->get_group_stats( $group->id );

			$rows[] = array(
				'ID'           => $group->id,
				'Name'         => $group->name,
				'Slug'         => $group->slug,
				'Members'      => $stats['member_count'],
				'Page Views'   => $stats['page_views'],
				'Conversions'  => $stats['conversions'],
			);
		}

		if ( empty( $rows ) ) {
			WP_CLI::log( 'No partner company groups found.' );
			return;
		}

		WP_CLI\Utils\format_items( 'table', $rows, array( 'ID', 'Name', 'Slug', 'Members', 'Page Views', 'Conversions' ) );
	}

	/**
	 * Delete a partner company group.
	 *
	 * ## OPTIONS
	 *
	 * <group_id>
	 * : The group ID to delete.
	 *
	 * [--yes]
	 * : Skip confirmation prompt.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lrh partner-company delete 123
	 *     wp lrh partner-company delete 123 --yes
	 *
	 * @param array $args Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function delete( $args, $assoc_args ) {
		list( $group_id ) = $args;

		if ( ! function_exists( 'groups_delete_group' ) ) {
			WP_CLI::error( 'BuddyPress is not active' );
			return;
		}

		$group = groups_get_group( $group_id );
		if ( ! $group || ! $group->id ) {
			WP_CLI::error( sprintf( 'Group #%d not found', $group_id ) );
			return;
		}

		// Confirm deletion
		if ( ! isset( $assoc_args['yes'] ) ) {
			WP_CLI::confirm( sprintf( 'Are you sure you want to delete "%s" (Group #%d)?', $group->name, $group_id ) );
		}

		$deleted = groups_delete_group( $group_id );

		if ( $deleted ) {
			WP_CLI::success( sprintf( 'Deleted group #%d: %s', $group_id, $group->name ) );
		} else {
			WP_CLI::error( sprintf( 'Failed to delete group #%d', $group_id ) );
		}
	}
}
