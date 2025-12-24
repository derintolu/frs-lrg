<?php
/**
 * Contact Widget Shortcode
 *
 * Displays a contact card for any user (loan officers, realtor partners, etc.)
 *
 * Usage:
 * [frs_contact_widget user_id="123"] - Specific user by ID
 * [frs_contact_widget user_id="123" variant="inline"] - Inline layout
 * [frs_contact_widget user_id="123" variant="minimal"] - Minimal layout
 * [frs_contact_widget user_id="123" variant="card"] - Full card layout (default)
 *
 * Variants:
 * - card: Full card with avatar, name, title, NMLS, call/email buttons
 * - inline: Horizontal layout with avatar, info, and action buttons
 * - minimal: Compact row with avatar, name, and single phone button
 *
 * @package FRS_LRG
 */

namespace LendingResourceHub\Shortcodes;

class ContactWidget {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_contact_widget', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_contact_widget' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Get user data for widget
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array|false User data or false if not found.
	 */
	private static function get_user_data( $user_id ) {
		if ( ! $user_id ) {
			return false;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		// Try to get profile data from frs-wp-users API first
		$profile_data = [];
		$response = wp_remote_get( rest_url( "frs-users/v1/profiles/user/{$user_id}" ), [
			'timeout' => 5,
		] );

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $body['success'] ) && $body['success'] && isset( $body['data'] ) ) {
				$profile_data = $body['data'];
			}
		}

		// Build user data from profile or fallback to user meta
		$first_name = $profile_data['first_name'] ?? get_user_meta( $user_id, 'first_name', true );
		$last_name = $profile_data['last_name'] ?? get_user_meta( $user_id, 'last_name', true );
		$full_name = trim( $first_name . ' ' . $last_name );

		if ( empty( $full_name ) ) {
			$full_name = $user->display_name;
		}

		// Determine title based on role
		$title = $profile_data['job_title'] ?? get_user_meta( $user_id, 'job_title', true );
		if ( empty( $title ) ) {
			// Infer from role
			$roles = $user->roles;
			if ( in_array( 'loan_officer', $roles, true ) ) {
				$title = 'Loan Officer';
			} elseif ( in_array( 'realtor_partner', $roles, true ) ) {
				$title = 'Real Estate Agent';
			} elseif ( in_array( 'manager', $roles, true ) ) {
				$title = 'Manager';
			} elseif ( in_array( 'frs_admin', $roles, true ) ) {
				$title = 'Administrator';
			} else {
				$title = 'Team Member';
			}
		}

		// Get phone - try multiple sources
		$phone = $profile_data['mobile_number']
			?? $profile_data['phone_number']
			?? get_user_meta( $user_id, 'phone', true )
			?? get_user_meta( $user_id, 'billing_phone', true )
			?? '';

		// Get NMLS (mainly for loan officers)
		$nmls = $profile_data['nmls']
			?? $profile_data['nmls_number']
			?? get_user_meta( $user_id, 'nmls', true )
			?? '';

		// Get avatar
		$avatar = $profile_data['profile_photo'] ?? '';
		if ( empty( $avatar ) ) {
			$avatar = get_avatar_url( $user_id, [ 'size' => 200 ] );
		}

		return [
			'name'   => $full_name,
			'title'  => $title,
			'email'  => $profile_data['email'] ?? $user->user_email,
			'phone'  => $phone,
			'nmls'   => $nmls,
			'avatar' => $avatar,
		];
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
				'user_id' => 0,
				'variant' => 'card', // card, inline, minimal
			],
			$atts,
			'frs_contact_widget'
		);

		$user_id = intval( $atts['user_id'] );
		if ( ! $user_id ) {
			return '<!-- frs_contact_widget: No user_id specified -->';
		}

		$user_data = self::get_user_data( $user_id );
		if ( ! $user_data ) {
			return '<!-- frs_contact_widget: User not found -->';
		}

		// Validate variant
		$valid_variants = [ 'card', 'inline', 'minimal' ];
		$variant = in_array( $atts['variant'], $valid_variants, true ) ? $atts['variant'] : 'card';

		// Build data attributes
		$data_attrs = [
			'data-name'    => esc_attr( $user_data['name'] ),
			'data-title'   => esc_attr( $user_data['title'] ),
			'data-email'   => esc_attr( $user_data['email'] ),
			'data-phone'   => esc_attr( $user_data['phone'] ),
			'data-nmls'    => esc_attr( $user_data['nmls'] ),
			'data-avatar'  => esc_url( $user_data['avatar'] ),
			'data-variant' => esc_attr( $variant ),
		];

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			if ( ! empty( $value ) ) {
				$attr_string .= sprintf( ' %s="%s"', $key, $value );
			}
		}

		// Using class selector allows multiple widgets on same page
		return '<div class="frs-lo-contact frs-contact-widget"' . $attr_string . '></div>';
	}
}
