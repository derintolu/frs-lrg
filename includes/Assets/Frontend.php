<?php

declare(strict_types=1);

namespace LendingResourceHub\Assets;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

/**
 * Class Frontend
 *
 * Handles frontend asset loading for the LendingResourceHub.
 *
 * CRITICAL: This follows the WordPress Plugin Boilerplate pattern:
 * - Single handle for all frontend assets
 * - Single entry point (src/frontend/main.jsx)
 * - Uses wp_localize_script for reliable config injection
 * - Automatic dev/prod detection via @kucrut/vite-for-wp
 *
 * @package LendingResourceHub\Assets
 */
class Frontend {

	use Base;

	/**
	 * Script handle for frontend assets.
	 *
	 * IMPORTANT: This is the ONLY handle used for all frontend scripts.
	 * Do not create multiple handles for the same entry point.
	 */
	const HANDLE = 'lrh-frontend';

	/**
	 * JS Object name for portal configuration.
	 *
	 * Creates: window.lrhPortalConfig
	 */
	const OBJ_NAME = 'lrhPortalConfig';

	/**
	 * Development script path (Vite entry point).
	 *
	 * This matches the 'input' in vite.frontend.config.js
	 */
	const DEV_SCRIPT = 'src/frontend/main.jsx';

	/**
	 * Frontend bootstrapper.
	 *
	 * @return void
	 */
	public function bootstrap() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 *
	 * Loads assets only when portal shortcodes are present or on specific pages.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// Skip if in admin
		if ( is_admin() ) {
			return;
		}

		// Check if we should load portal assets
		if ( ! $this->should_load_portal() ) {
			return;
		}

		// Enqueue React dependencies first (from WordPress core)
		wp_enqueue_script( 'react' );
		wp_enqueue_script( 'react-dom' );

		// Enqueue the main frontend bundle
		// This uses @kucrut/vite-for-wp which automatically detects:
		// - Development: Loads from vite-dev-server.json (localhost:5173)
		// - Production: Loads from manifest.json (hashed assets)
		Assets\enqueue_asset(
			LRH_DIR . '/assets/frontend/dist',
			self::DEV_SCRIPT,
			array(
				'handle'       => self::HANDLE,
				'dependencies' => array( 'react', 'react-dom' ),
				'in-footer'    => true,
			)
		);

		// Add configuration data using wp_localize_script
		// This is MORE RELIABLE than wp_add_inline_script for ES6 modules
		wp_localize_script( self::HANDLE, self::OBJ_NAME, $this->get_config_data() );

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf(
				'[LRH] Portal assets enqueued on page: %s (ID: %s)',
				is_page() ? get_the_title() : get_post_type(),
				get_the_ID() ?: 'none'
			) );
		}
	}

	/**
	 * Public method to enqueue portal assets (for direct calls).
	 *
	 * Used by shortcode handlers that need to force asset loading.
	 *
	 * @return void
	 */
	public function enqueue_portal_assets_public() {
		$this->enqueue_scripts();
	}

	/**
	 * Determine if portal assets should be loaded.
	 *
	 * Checks for:
	 * - Portal shortcodes in post content
	 * - Specific page slugs
	 * - Filter override
	 *
	 * @return bool True if portal assets should load.
	 */
	private function should_load_portal() {
		global $post;

		// Check for portal shortcodes
		if ( $post && is_object( $post ) && isset( $post->post_content ) ) {
			if ( has_shortcode( $post->post_content, 'lrh_portal' ) ||
				has_shortcode( $post->post_content, 'frs_partnership_portal' ) ||
				has_shortcode( $post->post_content, 'lrh_portal_sidebar' ) ) {
				return true;
			}
		}

		// Check specific page slugs where portal should load
		$portal_pages = array(
			'portal',
			'loan-officer-portal',
			'my-portal',
			'partnership-portal',
			'dashboard',
		);

		if ( is_page( $portal_pages ) ) {
			return true;
		}

		// Allow filtering
		return apply_filters( 'lrh_should_load_portal', false );
	}

	/**
	 * Get configuration data for JavaScript.
	 *
	 * This creates window.lrhPortalConfig with:
	 * - Current user data
	 * - REST API configuration
	 * - Portal settings
	 *
	 * Structure matches old frs-partnership-portal for compatibility.
	 *
	 * @return array Configuration data.
	 */
	private function get_config_data() {
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		$user_role    = $this->get_user_role( $current_user );

		$gradient_url = LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';

		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[LRH] Config data - LRH_URL: ' . LRH_URL );
			error_log( '[LRH] Config data - gradientUrl: ' . $gradient_url );
		}

		return array(
			// Primary config (used directly by React components)
			'userId'       => $user_id,
			'userName'     => $current_user->display_name,
			'userEmail'    => $current_user->user_email,
			'userAvatar'   => get_avatar_url( $user_id ),
			'userRole'     => $user_role,
			'restNonce'    => wp_create_nonce( 'wp_rest' ),
			'apiUrl'       => rest_url( LRH_ROUTE_PREFIX . '/' ),
			'gradientUrl'  => $gradient_url,
			'siteUrl'      => home_url(),
			'portalUrl'    => home_url( '/portal' ),

			// Compatibility structure for DataService fallback
			// Matches old frs-partnership-portal window.frsPortalData.currentUser
			'currentUser'  => array(
				'id'     => $user_id,
				'name'   => $current_user->display_name,
				'email'  => $current_user->user_email,
				'avatar' => get_avatar_url( $user_id ),
				'roles'  => $current_user->roles,
			),

			// Menu items for sidebar
			'menuItems'    => apply_filters( 'lrh_portal_menu_items', $this->get_menu_items_for_user( $current_user ) ),

			// Additional metadata
			'nonce'        => wp_create_nonce( 'wp_rest' ), // Alias for restNonce
			'siteName'     => get_bloginfo( 'name' ),
			'siteLogo'     => $this->get_site_logo(),
			'logoutUrl'    => wp_logout_url( home_url() ),
		);
	}

	/**
	 * Get primary user role for portal.
	 *
	 * Maps WordPress roles to simplified portal roles.
	 *
	 * @param \WP_User $user WordPress user object.
	 * @return string Portal role (loan_officer|realtor|manager|admin).
	 */
	private function get_user_role( $user ) {
		if ( ! $user || ! $user->roles ) {
			return 'loan_officer';
		}

		if ( in_array( 'realtor_partner', $user->roles, true ) ||
			 in_array( 'realtor', $user->roles, true ) ) {
			return 'realtor';
		}

		if ( in_array( 'manager', $user->roles, true ) ) {
			return 'manager';
		}

		if ( in_array( 'frs_admin', $user->roles, true ) ||
			 in_array( 'administrator', $user->roles, true ) ) {
			return 'admin';
		}

		return 'loan_officer';
	}

	/**
	 * Get site logo URL.
	 *
	 * @return string Logo URL or empty string.
	 */
	private function get_site_logo() {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			return wp_get_attachment_image_url( $custom_logo_id, 'full' ) ?: '';
		}
		return '';
	}

	/**
	 * Get menu items for user based on role.
	 *
	 * @param \WP_User $user The user object.
	 * @return array Menu items array.
	 */
	private function get_menu_items_for_user( $user ) {
		$role = $this->get_user_role( $user );

		// Base menu items for all users
		$menu_items = array(
			array(
				'id'    => 'home',
				'label' => 'Home',
				'icon'  => 'Home',
				'url'   => home_url(),
			),
		);

		// Add role-specific menu items
		if ( $role === 'loan_officer' || $role === 'admin' ) {
			$menu_items[] = array(
				'id'    => 'dashboard',
				'label' => 'Dashboard',
				'icon'  => 'LayoutDashboard',
				'url'   => home_url( '/portal' ),
			);
		}

		if ( $role === 'realtor' ) {
			$menu_items[] = array(
				'id'    => 'partnerships',
				'label' => 'My Loan Officers',
				'icon'  => 'Users',
				'url'   => home_url( '/portal' ),
			);
		}

		return $menu_items;
	}
}
