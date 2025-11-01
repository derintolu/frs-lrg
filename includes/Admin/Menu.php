<?php

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Admin\Dashboard;
use LendingResourceHub\Admin\SystemDiagnostic;

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
			array( $this, 'dashboard_page' ),
			'dashicons-groups',
			3
		);

		$submenu_pages = array(
			// Main
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Dashboard', 'lending-resource-hub' ),
				'menu_title'  => __( 'Dashboard', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $this->parent_slug,
				'function'    => array( $this, 'dashboard_page' ),
			),

			// Management
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Partnerships', 'lending-resource-hub' ),
				'menu_title'  => __( 'Partnerships', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-partnerships',
				'function'    => array( $this, 'partnerships_page' ),
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Bulk Invites', 'lending-resource-hub' ),
				'menu_title'  => __( 'Bulk Invites', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-bulk-invites',
				'function'    => array( $this, 'bulk_invites_page' ),
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Leads', 'lending-resource-hub' ),
				'menu_title'  => __( 'Leads', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-leads',
				'function'    => array( $this, 'leads_page' ),
			),

			// System
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'System Diagnostic', 'lending-resource-hub' ),
				'menu_title'  => __( 'System Diagnostic', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-system-diagnostic',
				'function'    => array( $this, 'system_diagnostic_page' ),
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Integrations', 'lending-resource-hub' ),
				'menu_title'  => __( 'Integrations', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-integrations',
				'function'    => array( $this, 'integrations_page' ),
			),
			array(
				'parent_slug' => $this->parent_slug,
				'page_title'  => __( 'Settings', 'lending-resource-hub' ),
				'menu_title'  => __( 'Settings', 'lending-resource-hub' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'lrh-settings',
				'function'    => array( $this, 'settings_page' ),
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
	 * Dashboard page callback.
	 *
	 * @return void
	 */
	public function dashboard_page() {
		Dashboard::get_instance()->render();
	}

	/**
	 * Partnerships page callback.
	 *
	 * @return void
	 */
	public function partnerships_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Partnerships', 'lending-resource-hub' ); ?></h1>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Partnerships management page - coming soon', 'lending-resource-hub' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Bulk Invites page callback.
	 *
	 * @return void
	 */
	public function bulk_invites_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bulk Invites', 'lending-resource-hub' ); ?></h1>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Bulk invites page - coming soon', 'lending-resource-hub' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Leads page callback.
	 *
	 * @return void
	 */
	public function leads_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Leads', 'lending-resource-hub' ); ?></h1>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Leads management page - coming soon', 'lending-resource-hub' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * System Diagnostic page callback.
	 *
	 * @return void
	 */
	public function system_diagnostic_page() {
		SystemDiagnostic::get_instance()->render();
	}

	/**
	 * Integrations page callback.
	 *
	 * @return void
	 */
	public function integrations_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Integrations', 'lending-resource-hub' ); ?></h1>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Integrations page - coming soon', 'lending-resource-hub' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Settings page callback.
	 *
	 * @return void
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Settings', 'lending-resource-hub' ); ?></h1>
			<div class="notice notice-info inline">
				<p><?php esc_html_e( 'Settings page - coming soon', 'lending-resource-hub' ); ?></p>
			</div>
		</div>
		<?php
	}
}
