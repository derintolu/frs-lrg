<?php

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class Navigation
 *
 * Manages WordPress menus for the portal sidebars.
 *
 * @package LendingResourceHub\Core
 */
class Navigation {

	use Base;

	/**
	 * Initialize navigation menus.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'after_setup_theme', array( $this, 'register_menus' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Register navigation menu locations.
	 *
	 * @return void
	 */
	public function register_menus() {
		register_nav_menus(
			array(
				'lrh_lo_portal_menu'      => __( 'LO Portal Sidebar Menu', 'lending-resource-hub' ),
				'lrh_realtor_portal_menu' => __( 'Realtor Portal Sidebar Menu', 'lending-resource-hub' ),
			)
		);
	}

	/**
	 * Register REST API routes for fetching menu data.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		register_rest_route(
			'lrh/v1',
			'/menu/(?P<location>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_menu_items' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'location' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array( $param, array( 'lrh_lo_portal_menu', 'lrh_realtor_portal_menu' ), true );
						},
					),
				),
			)
		);
	}

	/**
	 * Check permissions for menu API.
	 *
	 * @return bool
	 */
	public function check_permissions() {
		return is_user_logged_in();
	}

	/**
	 * Get menu items for a location.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_menu_items( $request ) {
		$location = $request->get_param( 'location' );

		// Get theme locations
		$locations = get_nav_menu_locations();

		if ( ! isset( $locations[ $location ] ) ) {
			return new \WP_Error(
				'no_menu',
				__( 'No menu assigned to this location', 'lending-resource-hub' ),
				array( 'status' => 404 )
			);
		}

		$menu_id = $locations[ $location ];
		$items   = wp_get_nav_menu_items( $menu_id );

		if ( ! $items ) {
			return rest_ensure_response( array() );
		}

		// Transform menu items to our format
		$formatted_items = $this->format_menu_items( $items );

		return rest_ensure_response( $formatted_items );
	}

	/**
	 * Format menu items for React consumption.
	 *
	 * @param array $items WordPress menu items.
	 * @return array Formatted menu items.
	 */
	private function format_menu_items( $items ) {
		$menu_tree = array();
		$items_by_id = array();

		// First pass: organize items by ID
		foreach ( $items as $item ) {
			$items_by_id[ $item->ID ] = array(
				'id'       => (string) $item->ID,
				'label'    => $item->title,
				'url'      => $this->get_relative_url( $item->url ),
				'icon'     => get_post_meta( $item->ID, '_menu_item_icon', true ) ?: 'circle',
				'target'   => $item->target ?: '_self',
				'classes'  => implode( ' ', $item->classes ),
				'parent'   => (int) $item->menu_item_parent,
				'children' => array(),
			);
		}

		// Second pass: build tree structure
		foreach ( $items_by_id as $id => $item ) {
			if ( $item['parent'] === 0 ) {
				// Top level item
				$menu_tree[] = $item;
			} else {
				// Child item - add to parent's children array
				if ( isset( $items_by_id[ $item['parent'] ] ) ) {
					$items_by_id[ $item['parent'] ]['children'][] = $item;
				}
			}
		}

		// Third pass: update parent items in tree with their children
		foreach ( $menu_tree as $key => $item ) {
			if ( isset( $items_by_id[ $item['id'] ]['children'] ) && ! empty( $items_by_id[ $item['id'] ]['children'] ) ) {
				$menu_tree[ $key ]['children'] = $items_by_id[ $item['id'] ]['children'];
			}
		}

		return $menu_tree;
	}

	/**
	 * Convert absolute URL to relative for React Router.
	 *
	 * @param string $url Full URL.
	 * @return string Relative URL.
	 */
	private function get_relative_url( $url ) {
		$site_url = home_url();
		if ( strpos( $url, $site_url ) === 0 ) {
			$url = substr( $url, strlen( $site_url ) );
		}
		return $url ?: '/';
	}
}
