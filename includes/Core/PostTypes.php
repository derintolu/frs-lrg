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
		add_filter( 'user_has_cap', array( $this, 'allow_author_frontend_editing' ), 10, 4 );
	}

	/**
	 * Register custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		// RE portal pages
		register_post_type(
			'frs_re_portal',
			array(
				'labels'       => array(
					'name'          => __( 'RE Portal Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'RE Portal Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'RE Portal', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Portal Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Portal Page', 'lending-resource-hub' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail', 'page-attributes' ),
				'has_archive'  => false,
				'hierarchical' => true,
				'rewrite'      => array(
					'slug'         => 're',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'menu_icon'    => 'dashicons-admin-home',
			)
		);

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
					'slug'       => 'l',
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
				'hierarchical'  => true,
				'supports'      => array( 'title', 'editor', 'custom-fields', 'page-attributes' ),
				'has_archive'   => false,
				'rewrite'       => array(
					'slug'       => 'apply',
					'with_front' => false,
					'hierarchical' => true,
				),
				'menu_icon'     => 'dashicons-money-alt',
				'template'      => array(),
				'template_lock' => false, // Allow editing - bound blocks stay locked to profile
				'capability_type' => 'post',
				'map_meta_cap'    => true,
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

		// Partner Company Portals
		register_post_type(
			'frs_partner_portal',
			array(
				'labels'       => array(
					'name'          => __( 'Partner Company Portals', 'lending-resource-hub' ),
					'singular_name' => __( 'Partner Company Portal', 'lending-resource-hub' ),
					'menu_name'     => __( 'Partner Companies', 'lending-resource-hub' ),
					'add_new'       => __( 'Add Partner Company Portal', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit Partner Company Portal', 'lending-resource-hub' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => 'lending-resource-hub',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'custom-fields', 'thumbnail', 'author' ),
				'has_archive'  => false,
				'rewrite'      => array(
					'slug'       => 'partner',
					'with_front' => false,
				),
				'menu_icon'    => 'dashicons-groups',
				'capability_type' => 'post',
				'map_meta_cap' => true,
			)
		);

		// LO Portal Pages
		register_post_type(
			'lo_portal_page',
			array(
				'labels'       => array(
					'name'          => __( 'LO Portal Pages', 'lending-resource-hub' ),
					'singular_name' => __( 'LO Portal Page', 'lending-resource-hub' ),
					'menu_name'     => __( 'LO Portal', 'lending-resource-hub' ),
					'add_new'       => __( 'Add LO Page', 'lending-resource-hub' ),
					'edit_item'     => __( 'Edit LO Page', 'lending-resource-hub' ),
				),
				'public'          => true,
				'show_ui'         => true,
				'show_in_menu'    => 'lending-resource-hub',
				'show_in_rest'    => true,
				'supports'        => array( 'title', 'editor', 'custom-fields', 'page-attributes' ),
				'hierarchical'    => true,
				'has_archive'     => false,
				'rewrite'         => array(
					'slug'       => 'lo',
					'with_front' => false,
				),
				'menu_icon'       => 'dashicons-businessman',
				'capability_type' => 'page',
				'map_meta_cap'    => true,
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

	/**
	 * Allow authors to edit their own mortgage landing pages from frontend.
	 *
	 * @param array   $allcaps All capabilities.
	 * @param array   $caps    Required capabilities.
	 * @param array   $args    Arguments.
	 * @param WP_User $user    User object.
	 * @return array Modified capabilities.
	 */
	public function allow_author_frontend_editing( $allcaps, $caps, $args, $user ) {
		// Only for mortgage landing pages
		if ( ! isset( $args[0] ) ) {
			return $allcaps;
		}

		$capability = $args[0];

		// Check if this is an edit capability
		if ( ! in_array( $capability, array( 'edit_post', 'delete_post', 'edit_posts' ), true ) ) {
			return $allcaps;
		}

		// If there's a post ID, check ownership
		if ( isset( $args[2] ) && $args[2] ) {
			$post = get_post( $args[2] );

			// Only for mortgage landing pages
			if ( $post && 'frs_mortgage_lp' === $post->post_type ) {
				// Authors can edit their own posts
				if ( (int) $post->post_author === (int) $user->ID ) {
					$allcaps['edit_post'] = true;
					$allcaps['delete_post'] = true;
				}
			}
		}

		// Grant edit_posts capability to loan officers for creating/viewing
		if ( 'edit_posts' === $capability && in_array( 'loan_officer', $user->roles, true ) ) {
			$allcaps['edit_posts'] = true;
		}

		return $allcaps;
	}
}
