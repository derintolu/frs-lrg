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
		$blocks_dir = LRH_DIR . 'blocks/';

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
		include LRH_DIR . 'blocks/loan-officer-directory/render.php';
		return ob_get_clean();
	}
}
