<?php
/**
 * Gutenberg Blocks Registration
 *
 * @package LendingResourceHub\Core
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class Blocks
 *
 * Handles registration and rendering of Gutenberg blocks.
 *
 * @package LendingResourceHub\Core
 */
class Blocks {

	use Base;

	/**
	 * Initialize blocks
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_categories' ) );
	}

	/**
	 * Register all blocks
	 *
	 * @return void
	 */
	public function register_blocks() {
		$blocks_dir = LRH_DIR . 'assets/blocks/';

		// Register bento grid block
		register_block_type(
			$blocks_dir . 'bento/block.json',
			array(
				'render_callback' => array( $this, 'render_bento' ),
			)
		);

		// Register loan officer directory block
		register_block_type(
			$blocks_dir . 'loan-officer-directory/block.json',
			array(
				'render_callback' => array( $this, 'render_loan_officer_directory' ),
			)
		);

		// Register mortgage calculator block
		register_block_type(
			$blocks_dir . 'mortgage-calculator/block.json'
		);

		// Register loan officer block
		register_block_type(
			$blocks_dir . 'loan-officer/block.json'
		);
	}

	/**
	 * Register custom block categories
	 *
	 * @param array $categories Existing block categories.
	 * @return array Modified categories.
	 */
	public function register_block_categories( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'frs-blocks',
					'title' => __( 'FRS Blocks', 'lending-resource-hub' ),
				),
			)
		);
	}

	/**
	 * Render bento grid block
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 * @return string Rendered HTML.
	 */
	public function render_bento( $attributes, $content, $block ) {
		// Get current user data
		$current_user = wp_get_current_user();
		$user_id      = get_current_user_id();

		// Set up config for view script
		// WordPress automatically enqueues viewScript from block.json as lrh-bento-view-script
		$config = array(
			'userId'     => $user_id,
			'userName'   => $current_user->display_name ?? '',
			'userEmail'  => $current_user->user_email ?? '',
			'userAvatar' => get_avatar_url( $user_id ) ?? '',
			'apiUrl'     => rest_url(),
			'restNonce'  => wp_create_nonce( 'wp_rest' ),
		);

		// Add inline script to set config before the view script runs
		wp_add_inline_script(
			'lrh-bento-view-script',
			'window.frsPortalConfig = ' . wp_json_encode( $config ) . ';',
			'before'
		);

		// Return the saved block content (view.js will populate it)
		return $content;
	}

	/**
	 * Render loan officer directory block
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @param WP_Block $block    Block instance.
	 * @return string Rendered HTML.
	 */
	public function render_loan_officer_directory( $attributes, $content, $block ) {
		// Include render.php and let it output directly, then capture with ob
		ob_start();
		include LRH_DIR . 'assets/blocks/loan-officer-directory/render.php';
		return ob_get_clean();
	}
}
