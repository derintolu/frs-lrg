<?php

declare(strict_types=1);

namespace LendingResourceHub\Assets;

use LendingResourceHub\Core\Template;
use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

/**
 * Class Frontend
 *
 * Handles frontend functionalities for the LendingResourceHub.
 *
 * @package LendingResourceHub\Assets
 */
class Frontend {

	use Base;

	/**
	 * Script handle for LendingResourceHub.
	 */
	const HANDLE = 'wordpress-plugin-boilerplate-frontend';

	/**
	 * JS Object name for LendingResourceHub.
	 */
	const OBJ_NAME = 'wordpressPluginBoilerplateFrontend';

	/**
	 * Development script path for LendingResourceHub.
	 */
	const DEV_SCRIPT = 'src/frontend/main.jsx';

	/**
	 * List of allowed screens for script enqueue.
	 *
	 * @var array
	 */
	private $allowed_screens = array(
		'toplevel_page_wordpress-plugin-boilerplate',
	);

	/**
	 * Frontend bootstrapper.
	 *
	 * @return void
	 */
	public function bootstrap() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue script based on the current screen.
	 *
	 * @param string $screen The current screen.
	 */
	public function enqueue_script( $screen ) {
		global $post;

		$current_screen     = $screen;
		$template_file_name = Template::FRONTEND_TEMPLATE;

		// Check for portal shortcodes - more robust checking
		$has_portal_shortcode = false;
		if ( ! is_admin() ) {
			// Check global post content
			if ( $post && is_object( $post ) && isset( $post->post_content ) ) {
				if ( has_shortcode( $post->post_content, 'lrh_portal' ) ||
					has_shortcode( $post->post_content, 'frs_partnership_portal' ) ||
					has_shortcode( $post->post_content, 'lrh_portal_sidebar' ) ) {
					$has_portal_shortcode = true;
				}
			}

			// Also check if we're on a page that typically uses the portal
			// This is a fallback for when shortcodes are in widgets or custom fields
			if ( ! $has_portal_shortcode && is_page() ) {
				global $wpdb;
				$post_id = get_the_ID();
				if ( $post_id ) {
					// Check post content and meta for shortcodes
					$content = get_post_field( 'post_content', $post_id );
					if ( $content && (
						strpos( $content, '[lrh_portal' ) !== false ||
						strpos( $content, '[frs_partnership_portal' ) !== false
					) ) {
						$has_portal_shortcode = true;
					}
				}
			}

			// Force load on specific pages (add page slugs as needed)
			$portal_pages = array( 'portal', 'loan-officer-portal', 'my-portal', 'partnership-portal', 'dashboard' );
			if ( is_page( $portal_pages ) ) {
				$has_portal_shortcode = true;
			}
		}

		// Debug logging (remove in production)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'LRH Portal Detection - Page: %s, Has Shortcode: %s, Post ID: %s',
				is_page() ? get_the_title() : 'not a page',
				$has_portal_shortcode ? 'YES' : 'NO',
				get_the_ID() ?: 'none'
			) );
		}

		// Ensure we always check the content one more time
		if ( ! $has_portal_shortcode && is_singular() ) {
			$content = get_the_content();
			if ( $content && (
				strpos( $content, '[lrh_portal' ) !== false ||
				strpos( $content, '[frs_partnership_portal' ) !== false
			) ) {
				$has_portal_shortcode = true;
			}
		}

		if ( ! is_admin() ) {
			$template_slug = get_page_template_slug();
			if ( $template_slug ) {

				if ( $template_slug === $template_file_name ) {
					array_push( $this->allowed_screens, $template_file_name );
					$current_screen = $template_file_name;
				}
			}
		}

		// Allow filtering portal detection
		$has_portal_shortcode = apply_filters( 'lrh_force_load_portal_assets', $has_portal_shortcode );

		// Enqueue portal assets if shortcode is present
		if ( $has_portal_shortcode ) {
			$this->enqueue_portal_assets();
		}

		if ( in_array( $current_screen, $this->allowed_screens, true ) ) {
			Assets\enqueue_asset(
				LRH_DIR . '/assets/frontend/dist',
				self::DEV_SCRIPT,
				$this->get_config()
			);
			wp_localize_script( self::HANDLE, self::OBJ_NAME, $this->get_data() );
		}
	}

	/**
	 * Enqueue portal assets.
	 *
	 * @return void
	 */
	public function enqueue_portal_assets_public() {
		$this->enqueue_portal_assets();
	}

	/**
	 * Enqueue portal assets (internal).
	 *
	 * @return void
	 */
	private function enqueue_portal_assets() {
		// Ensure React dependencies are enqueued first
		wp_enqueue_script( 'react' );
		wp_enqueue_script( 'react-dom' );

		Assets\enqueue_asset(
			LRH_DIR . '/assets/frontend/dist',
			'src/frontend/portal/main.tsx',
			array(
				'dependencies' => array( 'react', 'react-dom' ),
				'handle'       => 'lrh-portal',
				'in-footer'    => true,
			)
		);

		// Add portal config inline script
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;

		$user_role = 'loan_officer';
		if ( $user_id > 0 ) {
			if ( in_array( 'realtor_partner', $current_user->roles ) || in_array( 'realtor', $current_user->roles ) ) {
				$user_role = 'realtor';
			} elseif ( in_array( 'manager', $current_user->roles ) ) {
				$user_role = 'manager';
			} elseif ( in_array( 'frs_admin', $current_user->roles ) || in_array( 'administrator', $current_user->roles ) ) {
				$user_role = 'admin';
			}
		}

		wp_add_inline_script(
			'lrh-portal',
			sprintf(
				'window.lrhPortalConfig = {
					userId: %d,
					userName: "%s",
					userEmail: "%s",
					userAvatar: "%s",
					userRole: "%s",
					restNonce: "%s",
					apiUrl: "%s",
					gradientUrl: "%s"
				};',
				$user_id,
				esc_js( $current_user->display_name ),
				esc_js( $current_user->user_email ),
				esc_url( get_avatar_url( $user_id ) ),
				esc_js( $user_role ),
				wp_create_nonce( 'wp_rest' ),
				rest_url( LRH_ROUTE_PREFIX . '/' ),
				esc_url( LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4' )
			),
			'before'
		);
	}

	/**
	 * Get the script configuration.
	 *
	 * @return array The script configuration.
	 */
	public function get_config() {
		return array(
			'dependencies' => array( 'react', 'react-dom' ),
			'handle'       => self::HANDLE,
			'in-footer'    => true,
		);
	}

	/**
	 * Get data for script localization.
	 *
	 * @return array The localized script data.
	 */
	public function get_data() {

		return array(
			'developer' => 'prappo',
			'isAdmin'   => is_admin(),
			'apiUrl'    => rest_url(),
			'userInfo'  => $this->get_user_data(),
		);
	}

	/**
	 * Get user data for script localization.
	 *
	 * @return array The user data.
	 */
	private function get_user_data() {
		$username   = '';
		$avatar_url = '';

		if ( is_user_logged_in() ) {
			// Get current user's data .
			$current_user = wp_get_current_user();

			// Get username.
			$username = $current_user->user_login; // or use user_nicename, display_name, etc.

			// Get avatar URL.
			$avatar_url = get_avatar_url( $current_user->ID );
		}

		return array(
			'username' => $username,
			'avatar'   => $avatar_url,
		);
	}
}
