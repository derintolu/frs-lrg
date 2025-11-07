<?php
/**
 * Custom Post Types Registration
 *
 * Registers all custom post types for the Lending Resource Hub.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class PostTypes
 *
 * Handles registration of custom post types.
 *
 * @package LendingResourceHub\Core
 */
class PostTypes {

	use Base;

	/**
	 * Initialize post types.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		// Biolink landing pages
		register_post_type(
			'frs_biolink',
			array(
				'labels'       => array(
					'name'          => __( 'Biolink Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'Biolink Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'Biolink Pages', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Biolink Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Biolink Page', 'lending-resource-hub' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
				'has_archive'  => false,
				'rewrite'      => array(
					'slug'       => '/',
					'with_front' => false,
				),
				'menu_icon'    => 'dashicons-id',
			)
		);

		// Prequalification landing pages
		register_post_type(
			'frs_prequal',
			array(
				'labels'        => array(
					'name'          => __( 'Prequal Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'Prequal Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'Prequal Pages', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Prequal Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Prequal Page', 'lending-resource-hub' ),
				),
				'public'        => true,
				'show_ui'       => true,
				'show_in_menu'  => 'lending-resource-hub',
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
				'has_archive'   => false,
				'rewrite'       => array( 'slug' => 'prequal' ),
				'menu_icon'     => 'dashicons-groups',
				'template'      => array(
					array( 'frs/prequal-heading' ),
					array( 'frs/prequal-subheading' ),
				),
				'template_lock' => 'all',
			)
		);

		// Open House landing pages
		register_post_type(
			'frs_openhouse',
			array(
				'labels'       => array(
					'name'          => __( 'Open House Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'Open House Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'Open House Pages', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Open House Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Open House Page', 'lending-resource-hub' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'open-house' ),
				'menu_icon'    => 'dashicons-admin-multisite',
			)
		);

		// Mortgage Landing Pages
		register_post_type(
			'frs_mortgage_lp',
			array(
				'labels'        => array(
					'name'          => __( 'Mortgage Landing Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'Mortgage Landing Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'Mortgage Pages', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Mortgage Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Mortgage Page', 'lending-resource-hub' ),
				),
				'public'        => true,
				'show_ui'       => true,
				'show_in_menu'  => 'lending-resource-hub',
				'show_in_rest'  => true,
				'supports'      => array( 'title', 'editor', 'custom-fields' ),
				'has_archive'   => false,
				'rewrite'       => array( 'slug' => 'apply' ),
				'menu_icon'     => 'dashicons-money-alt',
				'template'      => array(),
				'template_lock' => 'all',
			)
		);

		// Announcements
		register_post_type(
			'frs_announcement',
			array(
				'labels'       => array(
					'name'          => __( 'Announcements', 'lending-resource-hub' ),
					'singular_name' => __( 'Announcement', 'lending-resource-hub' ),
					'menu_name'     => __( 'Announcements', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Announcement', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Announcement', 'lending-resource-hub' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail' ),
				'has_archive'  => false,
				'menu_icon'    => 'dashicons-megaphone',
			)
		);

		// Custom Links
		register_post_type(
			'frs_custom_link',
			array(
				'labels'       => array(
					'name'          => __( 'Custom Links', 'lending-resource-hub' ),
					'singular_name' => __( 'Custom Link', 'lending-resource-hub' ),
					'menu_name'     => __( 'Custom Links', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Custom Link', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Custom Link', 'lending-resource-hub' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'custom-fields' ),
				'has_archive'  => false,
				'menu_icon'    => 'dashicons-admin-links',
			)
		);
	}

	/**
	 * Register taxonomies.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		// Add taxonomies if needed
	}
}
