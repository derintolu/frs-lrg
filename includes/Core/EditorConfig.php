<?php
/**
 * Editor Configuration
 *
 * Configures the WordPress block editor for seamless iframe embedding.
 *
 * @package LendingResourceHub\Core
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class EditorConfig
 *
 * Handles editor UI customization for landing page editing in iframe.
 */
class EditorConfig {

	use Base;

	/**
	 * Initialize the editor configuration
	 */
	public function init() {
		// Hide admin bar for landing page editors
		add_action( 'admin_init', array( $this, 'hide_admin_bar_for_landing_pages' ) );

		// Customize editor for iframe mode
		add_filter( 'block_editor_settings_all', array( $this, 'configure_editor_settings' ), 10, 2 );

		// Enqueue custom CSS to hide UI elements
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_styles' ) );

		// Remove unnecessary meta boxes
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 999 );
	}

	/**
	 * Hide admin bar when editing landing pages
	 */
	public function hide_admin_bar_for_landing_pages() {
		global $pagenow;

		if ( 'post.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) {
			$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

			if ( $post_id ) {
				$post_type = get_post_type( $post_id );

				// Hide admin bar for landing page post types
				if ( in_array( $post_type, array( 'frs_biolink', 'frs_mortgage_lp', 'frs_prequal_lp', 'frs_openhouse_lp' ), true ) ) {
					show_admin_bar( false );
				}
			}
		}
	}

	/**
	 * Configure block editor settings for cleaner UI
	 *
	 * @param array $settings Editor settings.
	 * @param object $context Editor context.
	 * @return array Modified settings.
	 */
	public function configure_editor_settings( $settings, $context ) {
		// Only modify for landing page post types
		if ( isset( $context->post ) ) {
			$post_type = get_post_type( $context->post );

			if ( in_array( $post_type, array( 'frs_biolink', 'frs_mortgage_lp', 'frs_prequal_lp', 'frs_openhouse_lp' ), true ) ) {
				// Disable code editor
				$settings['codeEditingEnabled'] = false;

				// Disable template editing
				$settings['supportsTemplateMode'] = false;

				// Focus on content editing
				$settings['focusMode'] = false;

				// Keep distraction-free mode available
				$settings['hasFixedToolbar'] = false;
			}
		}

		return $settings;
	}

	/**
	 * Enqueue custom CSS to hide UI elements in editor
	 */
	public function enqueue_editor_styles() {
		global $pagenow;

		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;

		if ( ! $post_id && 'post-new.php' !== $pagenow ) {
			return;
		}

		if ( $post_id ) {
			$post_type = get_post_type( $post_id );
		} else {
			$post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'post';
		}

		// Only apply to landing page post types
		if ( ! in_array( $post_type, array( 'frs_biolink', 'frs_mortgage_lp', 'frs_prequal_lp', 'frs_openhouse_lp' ), true ) ) {
			return;
		}

		// Add custom CSS to hide unnecessary UI elements
		$custom_css = "
			/* Hide WordPress admin bar */
			#wpadminbar {
				display: none !important;
			}

			/* Hide admin menu */
			#adminmenumain,
			#adminmenuback,
			#adminmenuwrap {
				display: none !important;
			}

			/* Clean body */
			body {
				padding: 0 !important;
				margin: 0 !important;
			}

			html.wp-toolbar {
				padding-top: 0 !important;
			}

			/* Remove all extra margins and padding - iframe handles positioning */
			.interface-interface-skeleton,
			.interface-interface-skeleton__body,
			.edit-post-layout,
			.edit-site-layout {
				margin: 0 !important;
				padding: 0 !important;
				left: 0 !important;
				top: 0 !important;
			}

			.edit-post-layout__content,
			.edit-site-layout__content {
				padding: 0 !important;
			}

			.interface-interface-skeleton__content {
				padding: 0 !important;
			}

			/* Clean editor header */
			.edit-post-header {
				background: white !important;
				border-bottom: 1px solid #e5e7eb !important;
				box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
			}
		";

		wp_add_inline_style( 'wp-edit-blocks', $custom_css );
	}

	/**
	 * Remove unnecessary meta boxes from landing page editor
	 */
	public function remove_meta_boxes() {
		$post_types = array( 'frs_biolink', 'frs_mortgage_lp', 'frs_prequal_lp', 'frs_openhouse_lp' );

		foreach ( $post_types as $post_type ) {
			// Remove default post meta boxes that aren't needed
			remove_meta_box( 'slugdiv', $post_type, 'normal' );
			remove_meta_box( 'trackbacksdiv', $post_type, 'normal' );
			remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
			remove_meta_box( 'commentsdiv', $post_type, 'normal' );
			remove_meta_box( 'authordiv', $post_type, 'normal' );
			remove_meta_box( 'revisionsdiv', $post_type, 'normal' );
		}
	}
}
