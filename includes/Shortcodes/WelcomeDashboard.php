<?php
/**
 * Welcome Dashboard Shortcode
 *
 * Displays a bento-style dashboard widget with:
 * - Welcome header with user's name
 * - Live clock
 * - Calendar
 * - Market rates (optional)
 *
 * Usage:
 * [frs_welcome_dashboard] - Uses current logged-in user
 * [frs_welcome_dashboard user_id="123"] - Uses specific user ID
 * [frs_welcome_dashboard show_market_rates="false"] - Hide market rates
 *
 * @package FRS_LRG
 */

namespace LendingResourceHub\Shortcodes;

class WelcomeDashboard {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_welcome_dashboard', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_welcome_dashboard' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Get user display name
	 *
	 * @param int $user_id WordPress user ID.
	 * @return string User display name.
	 */
	private static function get_user_name( $user_id ) {
		if ( ! $user_id ) {
			return 'Friend';
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return 'Friend';
		}

		// Try first name first
		$first_name = get_user_meta( $user_id, 'first_name', true );
		if ( ! empty( $first_name ) ) {
			$last_name = get_user_meta( $user_id, 'last_name', true );
			return trim( $first_name . ' ' . $last_name );
		}

		// Fall back to display name
		return $user->display_name ?: 'Friend';
	}

	/**
	 * Render the shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			[
				'user_id'           => 0,
				'show_market_rates' => 'true',
			],
			$atts,
			'frs_welcome_dashboard'
		);

		// Determine user ID
		$user_id = intval( $atts['user_id'] );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get user name
		$user_name = self::get_user_name( $user_id );

		// Build data attributes
		$data_attrs = [
			'data-user-name'        => esc_attr( $user_name ),
			'data-show-market-rates' => $atts['show_market_rates'] === 'true' ? 'true' : 'false',
		];

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', $key, $value );
		}

		return '<div id="frs-welcome-dashboard" class="frs-welcome-dashboard-widget"' . $attr_string . '></div>';
	}
}
