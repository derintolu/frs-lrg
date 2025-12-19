<?php
/**
 * Discussion Feed Shortcode
 *
 * Displays a feed from SureDash discussion pages.
 *
 * Usage:
 * [frs_discussion_feed] - Show all discussions
 * [frs_discussion_feed forum_id="123"] - Show discussions from specific forum
 * [frs_discussion_feed limit="5"] - Limit number of posts
 * [frs_discussion_feed show_comments="true"] - Show comment previews
 * [frs_discussion_feed layout="cards"] - Use card layout (default: list)
 *
 * @package FRS_LRG
 */

namespace LendingResourceHub\Shortcodes;

class DiscussionFeed {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_discussion_feed', [ __CLASS__, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_routes' ] );
	}

	/**
	 * Register REST API routes for fetching discussions
	 */
	public static function register_rest_routes() {
		register_rest_route(
			'lrh/v1',
			'/discussions',
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'get_discussions' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'forum_id' => [
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					],
					'limit'    => [
						'type'              => 'integer',
						'default'           => 10,
						'sanitize_callback' => 'absint',
					],
					'page'     => [
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint',
					],
					'include_comments' => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
			]
		);
	}

	/**
	 * REST API callback to get discussions
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public static function get_discussions( $request ) {
		$forum_id         = $request->get_param( 'forum_id' );
		$limit            = $request->get_param( 'limit' ) ?: 10;
		$page             = $request->get_param( 'page' ) ?: 1;
		$include_comments = $request->get_param( 'include_comments' );

		$posts = self::fetch_discussion_posts( $forum_id, $limit, $page, $include_comments );

		return rest_ensure_response( [
			'success' => true,
			'data'    => $posts,
			'meta'    => [
				'page'     => $page,
				'per_page' => $limit,
				'total'    => self::get_total_posts( $forum_id ),
			],
		] );
	}

	/**
	 * Get total number of discussion posts
	 *
	 * @param int|null $forum_id Optional forum ID to filter by.
	 * @return int
	 */
	private static function get_total_posts( $forum_id = null ) {
		$args = [
			'post_type'      => 'community-post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		if ( $forum_id ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'community-forum',
					'field'    => 'term_id',
					'terms'    => $forum_id,
				],
			];
		}

		$query = new \WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Fetch discussion posts from SureDash
	 *
	 * @param int|null $forum_id         Optional forum ID to filter by.
	 * @param int      $limit            Number of posts to fetch.
	 * @param int      $page             Page number for pagination.
	 * @param bool     $include_comments Whether to include comment previews.
	 * @return array
	 */
	private static function fetch_discussion_posts( $forum_id = null, $limit = 10, $page = 1, $include_comments = false ) {
		// Check if SureDash is active
		if ( ! post_type_exists( 'community-post' ) ) {
			return [];
		}

		$args = [
			'post_type'      => 'community-post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'paged'          => $page,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
		];

		// Filter by forum if specified
		if ( $forum_id ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'community-forum',
					'field'    => 'term_id',
					'terms'    => $forum_id,
				],
			];
		}

		$query = new \WP_Query( $args );
		$posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$author  = get_userdata( get_post_field( 'post_author', $post_id ) );

				$post_data = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'content'       => wp_trim_words( get_the_content(), 50, '...' ),
					'fullContent'   => get_the_content(),
					'excerpt'       => get_the_excerpt(),
					'date'          => get_the_date( 'c' ),
					'dateFormatted' => get_the_date( 'M j, Y' ),
					'timeAgo'       => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago',
					'author'        => [
						'id'     => $author ? $author->ID : 0,
						'name'   => $author ? $author->display_name : 'Unknown',
						'avatar' => $author ? get_avatar_url( $author->ID, [ 'size' => 96 ] ) : '',
					],
					'commentCount'  => (int) get_comments_number(),
					'permalink'     => get_permalink(),
					'coverImage'    => get_post_meta( $post_id, 'custom_post_cover_image', true ) ?: '',
					'forum'         => self::get_post_forum( $post_id ),
				];

				// Include comment previews if requested
				if ( $include_comments && $post_data['commentCount'] > 0 ) {
					$post_data['comments'] = self::get_comment_previews( $post_id, 3 );
				}

				$posts[] = $post_data;
			}
			wp_reset_postdata();
		}

		return $posts;
	}

	/**
	 * Get forum/category info for a post
	 *
	 * @param int $post_id Post ID.
	 * @return array|null
	 */
	private static function get_post_forum( $post_id ) {
		$terms = get_the_terms( $post_id, 'community-forum' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$term = $terms[0];
			return [
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			];
		}
		return null;
	}

	/**
	 * Get comment previews for a post
	 *
	 * @param int $post_id Post ID.
	 * @param int $limit   Number of comments to fetch.
	 * @return array
	 */
	private static function get_comment_previews( $post_id, $limit = 3 ) {
		$comments = get_comments( [
			'post_id' => $post_id,
			'status'  => 'approve',
			'number'  => $limit,
			'orderby' => 'comment_date',
			'order'   => 'DESC',
		] );

		$result = [];
		foreach ( $comments as $comment ) {
			$result[] = [
				'id'       => $comment->comment_ID,
				'content'  => wp_trim_words( $comment->comment_content, 20, '...' ),
				'author'   => [
					'id'     => (int) $comment->user_id,
					'name'   => $comment->comment_author,
					'avatar' => get_avatar_url( $comment->user_id ?: $comment->comment_author_email, [ 'size' => 48 ] ),
				],
				'date'     => get_comment_date( 'c', $comment ),
				'timeAgo'  => human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) . ' ago',
			];
		}

		return $result;
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_discussion_feed' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Get available forums for dropdown
	 *
	 * @return array
	 */
	private static function get_available_forums() {
		$terms = get_terms( [
			'taxonomy'   => 'community-forum',
			'hide_empty' => false,
		] );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		$forums = [];
		foreach ( $terms as $term ) {
			$forums[] = [
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			];
		}

		return $forums;
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
				'forum_id'      => '',
				'limit'         => 10,
				'show_comments' => 'false',
				'layout'        => 'list', // list, cards, compact
				'show_header'   => 'true',
				'title'         => 'Discussion Feed',
				'show_forum'    => 'true',
				'show_author'   => 'true',
				'show_date'     => 'true',
				'show_excerpt'  => 'true',
				'link_to_post'  => 'true',
			],
			$atts,
			'frs_discussion_feed'
		);

		// Validate layout
		$valid_layouts = [ 'list', 'cards', 'compact' ];
		$layout        = in_array( $atts['layout'], $valid_layouts, true ) ? $atts['layout'] : 'list';

		// Build data attributes
		$data_attrs = [
			'data-forum-id'      => esc_attr( $atts['forum_id'] ),
			'data-limit'         => esc_attr( $atts['limit'] ),
			'data-show-comments' => $atts['show_comments'] === 'true' ? 'true' : 'false',
			'data-layout'        => esc_attr( $layout ),
			'data-show-header'   => $atts['show_header'] === 'true' ? 'true' : 'false',
			'data-title'         => esc_attr( $atts['title'] ),
			'data-show-forum'    => $atts['show_forum'] === 'true' ? 'true' : 'false',
			'data-show-author'   => $atts['show_author'] === 'true' ? 'true' : 'false',
			'data-show-date'     => $atts['show_date'] === 'true' ? 'true' : 'false',
			'data-show-excerpt'  => $atts['show_excerpt'] === 'true' ? 'true' : 'false',
			'data-link-to-post'  => $atts['link_to_post'] === 'true' ? 'true' : 'false',
			'data-rest-url'      => esc_url( rest_url( 'lrh/v1/discussions' ) ),
			'data-nonce'         => wp_create_nonce( 'wp_rest' ),
		];

		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', $key, $value );
		}

		return '<div id="frs-discussion-feed" class="frs-discussion-feed-widget"' . $attr_string . '></div>';
	}
}
