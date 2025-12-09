<?php
/**
 * Team Widget Shortcode
 *
 * Displays a team of loan officers/members with "Your 21st Century Lending Team!" header.
 * Each member links to their SureDash profile.
 *
 * Usage:
 * [frs_team_widget user_ids="3,12"] - Show specific users by ID (Holley=3, Jacquelyn=12)
 * [frs_team_widget user_ids="3,12" title="Your Lending Team"]
 * [frs_team_widget user_ids="3,12" layout="column"]
 *
 * Layouts: row (default), column, grid
 *
 * @package FRS_LRG
 */

namespace LendingResourceHub\Shortcodes;

class TeamWidget {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_team_widget', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_team_widget' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Get user data for a team member
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

		// Get phone
		$phone = $profile_data['mobile_number']
			?? $profile_data['phone_number']
			?? get_user_meta( $user_id, 'phone', true )
			?? get_user_meta( $user_id, 'billing_phone', true )
			?? '';

		// Get NMLS
		$nmls = $profile_data['nmls']
			?? $profile_data['nmls_number']
			?? get_user_meta( $user_id, 'nmls', true )
			?? '';

		// Get avatar
		$avatar = $profile_data['profile_photo'] ?? '';
		if ( empty( $avatar ) ) {
			$avatar = get_avatar_url( $user_id, [ 'size' => 200 ] );
		}

		// Build SureDash profile URL
		// Format: /portal/member/{user_id} or /space/members/{user_id}
		$profile_url = self::get_suredash_profile_url( $user_id );

		return [
			'id'         => (string) $user_id,
			'name'       => $full_name,
			'title'      => $title,
			'email'      => $profile_data['email'] ?? $user->user_email,
			'phone'      => $phone,
			'nmls'       => $nmls,
			'avatar'     => $avatar,
			'profileUrl' => $profile_url,
		];
	}

	/**
	 * Get SureDash profile URL for a user
	 *
	 * @param int $user_id WordPress user ID.
	 * @return string Profile URL.
	 */
	private static function get_suredash_profile_url( $user_id ) {
		// Try to get the SureDash member profile URL
		// This looks for the portal space and member URL pattern

		// Check if SureDash is active and get portal URL
		$portal_page_id = get_option( 'suredash_portal_page_id', 0 );
		if ( $portal_page_id ) {
			$portal_url = get_permalink( $portal_page_id );
			if ( $portal_url ) {
				return trailingslashit( $portal_url ) . 'members/' . $user_id;
			}
		}

		// Fallback: Try common SureDash URL patterns
		// Check for portal page in common locations
		$portal_page = get_page_by_path( 'portal' );
		if ( $portal_page ) {
			return home_url( '/portal/members/' . $user_id );
		}

		$dashboard_page = get_page_by_path( 'dashboard' );
		if ( $dashboard_page ) {
			return home_url( '/dashboard/members/' . $user_id );
		}

		// Default fallback - author archive
		return get_author_posts_url( $user_id );
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
				'user_ids'   => '',
				'title'      => 'Your 21st Century Lending Team!',
				'show_title' => 'true',
				'layout'     => 'row', // row, column, grid
				'size'       => 'default', // default, large
			],
			$atts,
			'frs_team_widget'
		);

		// Parse user IDs
		$user_ids = array_filter( array_map( 'intval', explode( ',', $atts['user_ids'] ) ) );

		if ( empty( $user_ids ) ) {
			return '<!-- frs_team_widget: No user_ids specified -->';
		}

		// Get data for each user
		$members = [];
		foreach ( $user_ids as $user_id ) {
			$user_data = self::get_user_data( $user_id );
			if ( $user_data ) {
				$members[] = $user_data;
			}
		}

		if ( empty( $members ) ) {
			return '<!-- frs_team_widget: No valid users found -->';
		}

		// Validate layout
		$valid_layouts = [ 'row', 'column', 'grid' ];
		$layout = in_array( $atts['layout'], $valid_layouts, true ) ? $atts['layout'] : 'row';

		// Validate size
		$valid_sizes = [ 'default', 'large' ];
		$size = in_array( $atts['size'], $valid_sizes, true ) ? $atts['size'] : 'default';

		// Build data attributes
		$data_attrs = [
			'data-title'      => esc_attr( $atts['title'] ),
			'data-show-title' => $atts['show_title'] === 'true' ? 'true' : 'false',
			'data-layout'     => esc_attr( $layout ),
			'data-size'       => esc_attr( $size ),
			'data-members'    => esc_attr( wp_json_encode( $members ) ),
		];

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', $key, $value );
		}

		return '<div id="frs-team-widget" class="frs-team-widget"' . $attr_string . '></div>';
	}
}
