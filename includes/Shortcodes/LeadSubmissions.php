<?php
/**
 * Lead Submissions Widget Shortcode
 *
 * Displays recent lead submissions for Loan Officers and Realtors.
 * Automatically detects user role and shows appropriate leads.
 *
 * Usage:
 * [frs_lead_submissions] - Auto-detect current user's role and show their leads
 * [frs_lead_submissions user_id="123"] - Show leads for specific user
 * [frs_lead_submissions limit="10"] - Limit number of leads shown
 * [frs_lead_submissions show_header="false"] - Hide the header
 * [frs_lead_submissions title="My Leads"] - Custom title
 *
 * @package LendingResourceHub\Shortcodes
 */

namespace LendingResourceHub\Shortcodes;

class LeadSubmissions {

	/**
	 * Initialize the shortcode.
	 */
	public static function init() {
		add_shortcode( 'frs_lead_submissions', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets.
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_lead_submissions' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Determine user role (loan_officer or realtor).
	 *
	 * @param int $user_id The user ID.
	 * @return string 'loan_officer' or 'realtor'
	 */
	private static function get_user_role( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return 'loan_officer';
		}

		// Check WordPress roles
		$roles = $user->roles;
		if ( in_array( 'realtor', $roles, true ) || in_array( 'realtor_partner', $roles, true ) ) {
			return 'realtor';
		}

		// Check FRS Users profile if available
		if ( class_exists( 'FRSUsers\Models\Profile' ) ) {
			$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();
			if ( $profile && $profile->select_person_type === 'realtor_partner' ) {
				return 'realtor';
			}
		}

		// Check user meta as fallback
		$person_type = get_user_meta( $user_id, 'select_person_type', true );
		if ( $person_type === 'realtor_partner' ) {
			return 'realtor';
		}

		return 'loan_officer';
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			[
				'user_id'     => '',
				'limit'       => 5,
				'show_header' => 'true',
				'title'       => 'Recent Leads',
			],
			$atts,
			'frs_lead_submissions'
		);

		// Get user ID - use provided or current user
		$user_id = ! empty( $atts['user_id'] ) ? absint( $atts['user_id'] ) : get_current_user_id();

		if ( ! $user_id ) {
			return '<div class="frs-lead-submissions-widget"><p>Please log in to view leads.</p></div>';
		}

		// Determine user role
		$user_role = self::get_user_role( $user_id );

		// Build data attributes
		$data_attrs = [
			'data-user-id'     => esc_attr( $user_id ),
			'data-user-role'   => esc_attr( $user_role ),
			'data-limit'       => esc_attr( $atts['limit'] ),
			'data-show-header' => $atts['show_header'] === 'true' ? 'true' : 'false',
			'data-title'       => esc_attr( $atts['title'] ),
			'data-rest-url'    => esc_url( rest_url( 'lrh/v1' ) ),
			'data-nonce'       => wp_create_nonce( 'wp_rest' ),
		];

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', $key, $value );
		}

		return '<div id="frs-lead-submissions" class="frs-lead-submissions-widget"' . $attr_string . '></div>';
	}
}
