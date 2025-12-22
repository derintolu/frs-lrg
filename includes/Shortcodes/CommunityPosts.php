<?php
/**
 * Community Posts Widget Shortcode
 *
 * Displays community posts from "The Pulse" in a React widget.
 *
 * Usage:
 * [frs_community_posts] - Display community posts with default settings
 * [frs_community_posts limit="10"] - Show 10 posts
 * [frs_community_posts title="Latest News"] - Custom title
 * [frs_community_posts layout="grid"] - Grid layout (list, grid, compact)
 * [frs_community_posts show_header="false"] - Hide header
 *
 * @package LendingResourceHub\Shortcodes
 */

namespace LendingResourceHub\Shortcodes;

class CommunityPosts {

	/**
	 * Initialize the shortcode.
	 */
	public static function init() {
		add_shortcode( 'frs_community_posts', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets.
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_community_posts' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render( $atts ): string {
		$atts = shortcode_atts(
			[
				'limit'       => 5,
				'title'       => 'The Pulse',
				'show_header' => 'true',
				'layout'      => 'list', // list, grid, compact
				'class'       => '',
			],
			$atts,
			'frs_community_posts'
		);

		// Enqueue widget assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();

		$container_class = 'frs-community-posts-widget';
		if ( ! empty( $atts['class'] ) ) {
			$container_class .= ' ' . esc_attr( $atts['class'] );
		}

		$data_attrs = sprintf(
			'data-limit="%d" data-title="%s" data-show-header="%s" data-layout="%s" data-rest-url="%s"',
			absint( $atts['limit'] ),
			esc_attr( $atts['title'] ),
			esc_attr( $atts['show_header'] ),
			esc_attr( $atts['layout'] ),
			esc_url( rest_url( 'wp/v2' ) )
		);

		return sprintf(
			'<div id="frs-community-posts" class="%s" %s></div>',
			esc_attr( $container_class ),
			$data_attrs
		);
	}
}
