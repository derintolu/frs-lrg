<?php
/**
 * Prequal Blocks Registration and Page Generation
 *
 * Handles pre-qualification co-branded page generation for loan officer + realtor partnerships.
 *
 * @package LendingResourceHub\Controllers\Prequal
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Prequal;

use LendingResourceHub\Traits\Base;
use FRSUsers\Models\Profile;

/**
 * Class Blocks
 *
 * Manages co-branded pre-qualification pages with loan officer and realtor partner.
 *
 * @package LendingResourceHub\Controllers\Prequal
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
		add_action( 'template_redirect', array( $this, 'track_prequal_view' ) );
	}

	/**
	 * Register prequal blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		$blocks_dir = LRH_DIR . 'assets/blocks/';

		// Register prequal blocks
		register_block_type( $blocks_dir . 'prequal-heading' );
		register_block_type( $blocks_dir . 'prequal-subheading' );
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
					'slug'  => 'frs-prequal',
					'title' => __( 'Prequal Blocks', 'lending-resource-hub' ),
				),
			)
		);
	}

	/**
	 * Track page view for prequal page.
	 *
	 * @return void
	 */
	public function track_prequal_view() {
		if ( ! is_singular( 'frs_prequal' ) ) {
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
	 * Generate pre-qualification page for partnership.
	 *
	 * Creates a co-branded page with both loan officer and realtor partner information.
	 *
	 * @param int      $loan_officer_id Loan officer user ID.
	 * @param int      $realtor_id Realtor partner user ID.
	 * @param int|null $partnership_id Optional partnership ID to link.
	 * @return array|false Array with page data or false on failure.
	 */
	public static function generate_prequal_page( $loan_officer_id, $realtor_id, $partnership_id = null ) {
		// Try to get profiles from frs-wp-users Eloquent model
		$lo_profile = null;
		$realtor_profile = null;
		if ( class_exists( 'FRSUsers\Models\Profile' ) ) {
			$lo_profile      = Profile::where( 'user_id', $loan_officer_id )->first();
			$realtor_profile = Profile::where( 'user_id', $realtor_id )->first();
		}

		// Get WordPress users
		$lo_user = get_user_by( 'id', $loan_officer_id );
		$realtor_user = get_user_by( 'id', $realtor_id );

		if ( ! $lo_user || ! $realtor_user ) {
			return false;
		}

		// Get names from profile or WordPress user meta
		$lo_first_name = $lo_profile ? $lo_profile->first_name : ( get_user_meta( $loan_officer_id, 'first_name', true ) ?: $lo_user->user_login );
		$lo_last_name = $lo_profile ? $lo_profile->last_name : ( get_user_meta( $loan_officer_id, 'last_name', true ) ?: '' );
		$lo_headshot = $lo_profile->headshot_id ?? null;

		$realtor_first_name = $realtor_profile ? $realtor_profile->first_name : ( get_user_meta( $realtor_id, 'first_name', true ) ?: $realtor_user->user_login );
		$realtor_last_name = $realtor_profile ? $realtor_profile->last_name : ( get_user_meta( $realtor_id, 'last_name', true ) ?: '' );

		// Generate unique slug combining both names
		$slug = sanitize_title( $lo_first_name . '-' . $realtor_first_name . '-prequal-' . wp_generate_password( 4, false ) );

		// Create page content with template blocks
		// Template is already defined in post type registration: prequal-heading + prequal-subheading
		$page_content = '<!-- wp:lrh/prequal-heading /-->'
			. "\n\n" . '<!-- wp:lrh/prequal-subheading /-->';

		// Build page title from both names
		$page_title = sprintf(
			'%s & %s - Pre-Qualification',
			$lo_first_name,
			$realtor_first_name
		);

		// Create the page
		$page_data = array(
			'post_title'   => $page_title,
			'post_name'    => $slug,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'frs_prequal',
			'post_author'  => $loan_officer_id,
			'meta_input'   => array(
				'_frs_loan_officer_id'  => $loan_officer_id,
				'_frs_partner_user_id'  => $realtor_id,
				'_frs_page_views'       => 0,
				'_frs_page_conversions' => 0,
			),
		);

		// Add partnership ID if provided
		if ( $partnership_id ) {
			$page_data['meta_input']['_frs_partnership_id'] = $partnership_id;
		}

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return false;
		}

		// Set featured image from loan officer's headshot
		if ( $lo_headshot ) {
			set_post_thumbnail( $page_id, $lo_headshot );
		}

		// Set default meta values for heading blocks
		update_post_meta( $page_id, '_frs_prequal_heading_line1', 'One Team. One Goal.' );
		update_post_meta( $page_id, '_frs_prequal_heading_line2', 'From Approval to Close.' );

		return array(
			'id'              => $page_id,
			'url'             => get_permalink( $page_id ),
			'edit_url'        => get_edit_post_link( $page_id, 'raw' ),
			'loan_officer_id' => $loan_officer_id,
			'realtor_id'      => $realtor_id,
		);
	}

	/**
	 * Generate unique slug for prequal page.
	 *
	 * @param Profile $lo_profile Loan officer profile.
	 * @param Profile $realtor_profile Realtor profile.
	 * @return string Unique slug.
	 */
	private static function generate_unique_prequal_slug( $lo_profile, $realtor_profile ) {
		// Combine both first names
		$slug = sanitize_title( $lo_profile->first_name . '-' . $realtor_profile->first_name . '-prequal' );

		// Check if slug exists
		$existing = get_page_by_path( $slug, OBJECT, 'frs_prequal' );

		if ( ! $existing ) {
			return $slug;
		}

		// Add random number to make unique
		return $slug . '-' . wp_rand( 100, 999 );
	}
}
