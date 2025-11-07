<?php
/**
 * Create Mortgage Calculator Landing Pages
 *
 * This script creates mortgage calculator landing pages for test users
 *
 * Usage: php create-mortgage-calculator-pages.php
 * Or: wp eval-file create-mortgage-calculator-pages.php
 */

// Load WordPress
if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
	if ( file_exists( $wp_load ) ) {
		require_once $wp_load;
	} else {
		die( "Error: Cannot find wp-load.php. Please run this script from the correct location.\n" );
	}
}

/**
 * Create test users if they don't exist
 */
function create_test_users() {
	$test_users = [
		[
			'username'     => 'john_smith_lo',
			'email'        => 'john.smith@21cmortgage.com',
			'display_name' => 'John Smith',
			'first_name'   => 'John',
			'last_name'    => 'Smith',
			'nmls'         => '123456',
			'phone'        => '(555) 123-4567',
		],
		[
			'username'     => 'sarah_johnson_lo',
			'email'        => 'sarah.johnson@21cmortgage.com',
			'display_name' => 'Sarah Johnson',
			'first_name'   => 'Sarah',
			'last_name'    => 'Johnson',
			'nmls'         => '234567',
			'phone'        => '(555) 234-5678',
		],
		[
			'username'     => 'mike_davis_lo',
			'email'        => 'mike.davis@21cmortgage.com',
			'display_name' => 'Mike Davis',
			'first_name'   => 'Mike',
			'last_name'    => 'Davis',
			'nmls'         => '345678',
			'phone'        => '(555) 345-6789',
		],
	];

	$created_users = [];

	foreach ( $test_users as $user_data ) {
		// Check if user exists
		$user = get_user_by( 'login', $user_data['username'] );

		if ( ! $user ) {
			// Create user
			$user_id = wp_create_user(
				$user_data['username'],
				wp_generate_password( 12, true, true ),
				$user_data['email']
			);

			if ( is_wp_error( $user_id ) ) {
				echo "Error creating user {$user_data['username']}: {$user_id->get_error_message()}\n";
				continue;
			}

			// Update user meta
			wp_update_user([
				'ID'           => $user_id,
				'display_name' => $user_data['display_name'],
				'first_name'   => $user_data['first_name'],
				'last_name'    => $user_data['last_name'],
			]);

			// Add custom meta
			update_user_meta( $user_id, 'nmls_number', $user_data['nmls'] );
			update_user_meta( $user_id, 'phone_number', $user_data['phone'] );

			echo "✓ Created user: {$user_data['display_name']} (ID: {$user_id})\n";
		} else {
			$user_id = $user->ID;
			echo "→ User already exists: {$user_data['display_name']} (ID: {$user_id})\n";
		}

		$created_users[] = [
			'id'           => $user_id,
			'username'     => $user_data['username'],
			'display_name' => $user_data['display_name'],
			'email'        => $user_data['email'],
			'nmls'         => $user_data['nmls'],
			'phone'        => $user_data['phone'],
		];
	}

	return $created_users;
}

/**
 * Create mortgage calculator page for a user
 */
function create_mortgage_calculator_page( $user_data ) {
	$slug = sanitize_title( $user_data['username'] . '-mortgage-calculator' );

	// Check if page already exists
	$existing_page = get_page_by_path( $slug );

	if ( $existing_page ) {
		echo "→ Page already exists: {$existing_page->post_title} (ID: {$existing_page->ID})\n";
		echo "   URL: " . get_permalink( $existing_page->ID ) . "\n";
		return $existing_page->ID;
	}

	// Get user avatar
	$avatar_url = get_avatar_url( $user_data['id'], [ 'size' => 96 ] );

	// Create page content with shortcode
	$content = sprintf(
		'[frs_mortgage_calculator loan_officer_id="%d" loan_officer_name="%s" loan_officer_email="%s" loan_officer_phone="%s" loan_officer_nmls="%s" loan_officer_avatar="%s" primary_color="#2563eb" secondary_color="#2dd4da" default_calculator="payment"]',
		$user_data['id'],
		$user_data['display_name'],
		$user_data['email'],
		$user_data['phone'],
		$user_data['nmls'],
		$avatar_url
	);

	// Create the page
	$page_data = [
		'post_title'   => sprintf( '%s - Mortgage Calculator', $user_data['display_name'] ),
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => $user_data['id'],
		'meta_input'   => [
			'_loan_officer_id' => $user_data['id'],
			'_page_type'       => 'mortgage_calculator',
		],
	];

	$page_id = wp_insert_post( $page_data, true );

	if ( is_wp_error( $page_id ) ) {
		echo "✗ Error creating page for {$user_data['display_name']}: {$page_id->get_error_message()}\n";
		return false;
	}

	echo "✓ Created page: {$user_data['display_name']} - Mortgage Calculator (ID: {$page_id})\n";
	echo "   URL: " . get_permalink( $page_id ) . "\n";

	return $page_id;
}

/**
 * Main execution
 */
function main() {
	echo "\n";
	echo "====================================\n";
	echo "Mortgage Calculator Page Generator\n";
	echo "====================================\n\n";

	echo "Step 1: Creating/Verifying Test Users\n";
	echo "--------------------------------------\n";
	$users = create_test_users();

	echo "\n";
	echo "Step 2: Creating Mortgage Calculator Pages\n";
	echo "-------------------------------------------\n";

	$created_pages = [];
	foreach ( $users as $user_data ) {
		$page_id = create_mortgage_calculator_page( $user_data );
		if ( $page_id ) {
			$created_pages[] = [
				'user'    => $user_data['display_name'],
				'page_id' => $page_id,
				'url'     => get_permalink( $page_id ),
			];
		}
	}

	echo "\n";
	echo "====================================\n";
	echo "Summary\n";
	echo "====================================\n";
	echo "Total users: " . count( $users ) . "\n";
	echo "Total pages created: " . count( $created_pages ) . "\n\n";

	if ( ! empty( $created_pages ) ) {
		echo "Pages:\n";
		foreach ( $created_pages as $page ) {
			echo "  • {$page['user']}\n";
			echo "    {$page['url']}\n\n";
		}
	}

	echo "====================================\n\n";
}

// Run the script
main();
