<?php
/**
 * Open House Blocks Registration and Page Generation
 *
 * Handles open house co-branded page generation for loan officer + realtor partnerships.
 *
 * @package LendingResourceHub\Controllers\OpenHouse
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\OpenHouse;

use LendingResourceHub\Traits\Base;
use FRSUsers\Models\Profile;

/**
 * Class Blocks
 *
 * Manages co-branded open house pages with loan officer, realtor partner, and property information.
 *
 * @package LendingResourceHub\Controllers\OpenHouse
 */
class Blocks {

	use Base;

	/**
	 * Initialize blocks and hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );

		// Page view tracking
		add_action( 'template_redirect', array( $this, 'track_openhouse_view' ) );
	}

	/**
	 * Register open house blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		$blocks_dir = LRH_DIR . 'assets/blocks/';

		// Register open house blocks
		register_block_type( $blocks_dir . 'openhouse-carousel' );
	}

	/**
	 * Add custom block category.
	 *
	 * @param array $categories Existing block categories.
	 * @return array Modified categories.
	 */
	public function add_block_category( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'frs-openhouse',
					'title' => __( 'Open House Blocks', 'lending-resource-hub' ),
				),
			)
		);
	}

	/**
	 * Track page view for open house page.
	 *
	 * @return void
	 */
	public function track_openhouse_view() {
		if ( ! is_singular( 'frs_openhouse' ) ) {
			return;
		}

		$post_id = get_the_ID();

		// Skip if no post ID or if user is logged in
		if ( ! $post_id || is_user_logged_in() ) {
			return;
		}

		// Increment page views
		$views = (int) get_post_meta( $post_id, '_frs_page_views', true );
		update_post_meta( $post_id, '_frs_page_views', $views + 1 );
	}

	/**
	 * Generate open house page for partnership.
	 *
	 * Creates a co-branded page with loan officer, realtor partner, and property information.
	 *
	 * @param int    $loan_officer_id Loan officer user ID.
	 * @param int    $realtor_id Realtor partner user ID.
	 * @param string $property_address Property address for the open house.
	 * @param array  $property_data Optional additional property data (images, price, etc.).
	 * @return array|false Array with page data or false on failure.
	 */
	public static function generate_openhouse_page( $loan_officer_id, $realtor_id, $property_address, $property_data = array() ) {
		// Validate property address
		if ( empty( $property_address ) ) {
			return false;
		}

		// Get both profiles from Eloquent
		$lo_profile      = Profile::where( 'user_id', $loan_officer_id )->first();
		$realtor_profile = Profile::where( 'user_id', $realtor_id )->first();

		if ( ! $lo_profile || ! $realtor_profile ) {
			return false;
		}

		// Generate unique slug from property address
		$slug = self::generate_unique_openhouse_slug( $property_address );

		// Create page content with open house carousel
		$page_content = '<!-- wp:lrh/openhouse-carousel {"address":"' . esc_attr( $property_address ) . '"} /-->';

		// Add any additional blocks if needed
		$page_content .= "\n\n" . '<!-- wp:paragraph -->'
			. "\n" . '<p>Join us for an exclusive open house event!</p>'
			. "\n" . '<!-- /wp:paragraph -->';

		// Build page title from property address
		$page_title = sprintf(
			'Open House - %s',
			$property_address
		);

		// Create the page
		$page_data = array(
			'post_title'   => $page_title,
			'post_name'    => $slug,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'frs_openhouse',
			'post_author'  => $loan_officer_id,
			'meta_input'   => array(
				'_frs_loan_officer_id'  => $loan_officer_id,
				'_frs_partner_user_id'  => $realtor_id,
				'_frs_property_address' => $property_address,
				'_frs_page_views'       => 0,
				'_frs_page_conversions' => 0,
			),
		);

		// Add optional property data to meta
		if ( ! empty( $property_data['price'] ) ) {
			$page_data['meta_input']['_frs_property_price'] = $property_data['price'];
		}

		if ( ! empty( $property_data['open_house_date'] ) ) {
			$page_data['meta_input']['_frs_open_house_date'] = $property_data['open_house_date'];
		}

		if ( ! empty( $property_data['partnership_id'] ) ) {
			$page_data['meta_input']['_frs_partnership_id'] = $property_data['partnership_id'];
		}

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return false;
		}

		// Set featured image from property images if provided
		if ( ! empty( $property_data['featured_image_id'] ) ) {
			set_post_thumbnail( $page_id, $property_data['featured_image_id'] );
		} elseif ( $realtor_profile->headshot_id ) {
			// Fall back to realtor's headshot
			set_post_thumbnail( $page_id, $realtor_profile->headshot_id );
		}

		return array(
			'id'               => $page_id,
			'url'              => get_permalink( $page_id ),
			'edit_url'         => get_edit_post_link( $page_id, 'raw' ),
			'loan_officer_id'  => $loan_officer_id,
			'realtor_id'       => $realtor_id,
			'property_address' => $property_address,
		);
	}

	/**
	 * Generate unique slug for open house page.
	 *
	 * @param string $property_address Property address.
	 * @return string Unique slug.
	 */
	private static function generate_unique_openhouse_slug( $property_address ) {
		// Sanitize property address for slug
		$slug = sanitize_title( $property_address . '-open-house' );

		// Check if slug exists
		$existing = get_page_by_path( $slug, OBJECT, 'frs_openhouse' );

		if ( ! $existing ) {
			return $slug;
		}

		// Add random number to make unique
		return $slug . '-' . wp_rand( 100, 999 );
	}
}
