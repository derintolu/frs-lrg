<?php
/**
 * Shortcode Handler
 *
 * Handles shortcode registration and rendering for the portal.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

/**
 * Class Shortcode
 *
 * Handles shortcode registration and rendering.
 *
 * @package LendingResourceHub\Core
 */
class Shortcode {

	use Base;

	/**
	 * Initialize shortcodes.
	 *
	 * @return void
	 */
	public function init() {
		// New shortcodes
		add_shortcode( 'lrh_portal', array( $this, 'render_portal' ) );
		add_shortcode( 'lrh_portal_sidebar', array( $this, 'render_portal_sidebar' ) );

		// Legacy shortcode from old plugin (backward compatibility)
		add_shortcode( 'frs_partnership_portal', array( $this, 'render_legacy_portal' ) );
	}

	/**
	 * Render the main portal shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_portal( $atts ) {
		// Enqueue portal assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Return root element for React to mount
		return '<div id="lrh-portal-root"></div>';
	}

	/**
	 * Render the legacy portal shortcode (backward compatibility).
	 *
	 * Uses the old root element ID that the legacy React app expects.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_legacy_portal( $atts ) {
		// Enqueue portal assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Return legacy root element ID for backward compatibility
		return '<div id="frs-partnership-portal-root"></div>';
	}

	/**
	 * Render the portal sidebar shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_portal_sidebar( $atts ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return '';
		}

		// Add body class for sidebar styling
		add_filter( 'body_class', array( $this, 'add_sidebar_body_class' ) );

		// Return ONLY the container div
		// Frontend.php handles ALL asset loading and configuration
		// when it detects this shortcode via should_load_portal()
		return '<div id="lrh-portal-sidebar-root" data-lrh-component="portal-sidebar"></div>';
	}


	/**
	 * Get primary portal role for user.
	 *
	 * @param WP_User $user The user object.
	 * @return string The primary portal role.
	 */
	private function get_primary_portal_role( $user ) {
		$portal_roles = array( 'loan_officer', 'realtor_partner', 'realtor', 'manager', 'frs_admin', 'administrator' );

		foreach ( $portal_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return $role;
			}
		}

		return 'subscriber';
	}

	/**
	 * Get menu items for user based on role.
	 *
	 * @param WP_User $user The user object.
	 * @return array Menu items array.
	 */
	private function get_menu_items_for_user( $user ) {
		$role = $this->get_primary_portal_role( $user );

		// Base menu items for all users
		$menu_items = array(
			array(
				'id'    => 'home',
				'label' => 'Home',
				'icon'  => 'Home',
				'url'   => get_site_url(),
			),
		);

		// Add role-specific menu items
		if ( $role === 'loan_officer' ) {
			$menu_items[] = array(
				'id'       => 'dashboard',
				'label'    => 'Dashboard',
				'icon'     => 'LayoutDashboard',
				'url'      => get_site_url() . '/portal',
			);
		}

		return $menu_items;
	}

	/**
	 * Add body class for sidebar pages.
	 *
	 * @param array $classes Body classes.
	 * @return array Modified body classes.
	 */
	public function add_sidebar_body_class( $classes ) {
		$classes[] = 'has-lrh-portal-sidebar';
		return $classes;
	}
}
