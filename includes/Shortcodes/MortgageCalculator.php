<?php
/**
 * Mortgage Calculator Shortcode
 *
 * @package FRS_LRG
 */

namespace FRS_LRG\Shortcodes;

class MortgageCalculator {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_mortgage_calculator', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		// Only enqueue if shortcode is present on the page
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_mortgage_calculator' ) ) {
			wp_enqueue_style(
				'frs-mortgage-calculator',
				plugins_url( 'assets/widget/dist/frs-mortgage-calculator.css', dirname( dirname( __FILE__ ) ) ),
				[],
				filemtime( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'assets/widget/dist/frs-mortgage-calculator.css' )
			);

			wp_enqueue_script(
				'frs-mortgage-calculator',
				plugins_url( 'assets/widget/dist/frs-mortgage-calculator.js', dirname( dirname( __FILE__ ) ) ),
				[],
				filemtime( plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'assets/widget/dist/frs-mortgage-calculator.js' ),
				true
			);
		}
	}

	/**
	 * Render the shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			[
				'type' => 'conventional', // Default to conventional calculator
			],
			$atts,
			'frs_mortgage_calculator'
		);

		return '<div id="mortgage-calculator" class="frs-mortgage-calculator-widget"></div>';
	}
}
