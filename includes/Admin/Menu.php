<?php

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;

/**
 * Class Menu
 *
 * Represents the admin menu management for the LendingResourceHub plugin.
 *
 * @package LendingResourceHub\Admin
 */
class Menu {

	use Base;

	/**
	 * Parent slug for the menu.
	 *
	 * @var string
	 */
	private $parent_slug = 'lending-resource-hub';

	/**
	 * Initializes the admin menu.
	 *
	 * @return void
	 */
	public function init() {
		// Hook the function to the admin menu.
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	/**
	 * Adds a menu to the WordPress admin dashboard.
	 *
	 * @return void
	 */
	public function menu() {

		add_menu_page(
			__( 'Lending Resource Hub', 'lending-resource-hub' ),
			__( 'LRH Portal', 'lending-resource-hub' ),
			'manage_options',
			$this->parent_slug,
			array( $this, 'admin_page' ),
			'dashicons-groups',
			3
		);

		$plugin_url = admin_url( '/admin.php?page=' . $this->parent_slug );

		$current_page = get_admin_page_parent();

		if ( $current_page === $this->parent_slug ) {
			$plugin_url = '';
		}

		$submenu_pages = array(
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Dashboard', 'lending-resource-hub' ),
				'menu_title'  => __( 'Dashboard', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $this->parent_slug,
				'function'    => array( $this, 'admin_page' ),
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Partnerships', 'lending-resource-hub' ),
				'menu_title'  => __( 'Partnerships', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $plugin_url . '/#/partnerships',
				'function'    => null,
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Bulk Invites', 'lending-resource-hub' ),
				'menu_title'  => __( 'Bulk Invites', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $plugin_url . '/#/bulk-invites',
				'function'    => null,
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Leads', 'lending-resource-hub' ),
				'menu_title'  => __( 'Leads', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $plugin_url . '/#/leads',
				'function'    => null,
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Integrations', 'lending-resource-hub' ),
				'menu_title'  => __( 'Integrations', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $plugin_url . '/#/integrations',
				'function'    => null,
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Settings', 'lending-resource-hub' ),
				'menu_title'  => __( 'Settings', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $plugin_url . '/#/settings',
				'function'    => null,
			),
		);

		$plugin_submenu_pages = apply_filters( 'lrh_submenu_pages', $submenu_pages );

		foreach ( $plugin_submenu_pages as $submenu ) {

			add_submenu_page(
				$submenu['parent_slug'],
				$submenu['page_title'],
				$submenu['menu_title'],
				$submenu['capability'],
				$submenu['menu_slug'],
				$submenu['function']
			);
		}
	}

	/**
	 * Callback function for the main admin menu page.
	 *
	 * @return void
	 */
	public function admin_page() {
		?>
		<div id="lrh-admin-root" class="lrh-admin-app"></div>
		<?php
	}
}
