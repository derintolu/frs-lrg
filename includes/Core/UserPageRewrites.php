<?php
/**
 * User Page Rewrites
 *
 * Handles custom rewrite rules for user-specific landing pages.
 * Structure: /{username}/{page-type}
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class UserPageRewrites
 *
 * Manages custom URL structure for user landing pages.
 *
 * @package LendingResourceHub\Core
 */
class UserPageRewrites {

	use Base;

	/**
	 * Page type mappings.
	 *
	 * Maps URL slugs to post types and meta queries.
	 */
	private $page_types = array(
		'links'      => array(
			'post_type' => 'frs_biolink',
			'meta_key'  => 'frs_biolink_user',
		),
		'rate-quote' => array(
			'post_type' => 'frs_mortgage_lp',
			'meta_key'  => '_frs_loan_officer_id',
			'meta_value_suffix' => '-rate-quote',
		),
		'loan-app'   => array(
			'post_type' => 'frs_mortgage_lp',
			'meta_key'  => '_frs_loan_officer_id',
			'meta_value_suffix' => '-loan-app',
		),
		'prequal'    => array(
			'post_type' => 'frs_prequal',
			'meta_key'  => '_frs_loan_officer_id',
		),
		'open-house' => array(
			'post_type' => 'frs_openhouse',
			'meta_key'  => '_frs_loan_officer_id',
		),
	);

	/**
	 * Initialize rewrites.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ), 10 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'handle_user_page_request' ), 5 );
	}

	/**
	 * Add custom rewrite rules.
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {
		// Add rewrite rule for /{username}/{page-type}
		// This captures URLs like /blake/links, /blake/rate-quote, etc.
		add_rewrite_rule(
			'^([^/]+)/(links|rate-quote|loan-app|prequal|open-house)/?$',
			'index.php?user_page=1&username=$matches[1]&page_type=$matches[2]',
			'top'
		);
	}

	/**
	 * Add custom query vars.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'user_page';
		$vars[] = 'username';
		$vars[] = 'page_type';
		return $vars;
	}

	/**
	 * Handle user page requests.
	 *
	 * Intercepts requests matching our custom URL pattern and loads the appropriate post.
	 *
	 * @return void
	 */
	public function handle_user_page_request() {
		global $wp_query;

		// Check if this is a user page request
		if ( ! get_query_var( 'user_page' ) ) {
			return;
		}

		$username  = get_query_var( 'username' );
		$page_type = get_query_var( 'page_type' );

		// Validate page type
		if ( ! isset( $this->page_types[ $page_type ] ) ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Get user by username
		$user = get_user_by( 'slug', $username );
		if ( ! $user ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Get page type config
		$config = $this->page_types[ $page_type ];

		// Query for the post
		// Try post_author first (for new pages), then fall back to meta (for legacy pages)
		$args = array(
			'post_type'      => $config['post_type'],
			'posts_per_page' => 1,
			'author'         => $user->ID,
			'post_status'    => 'publish',
		);

		// Add additional meta query for mortgage page types (rate-quote vs loan-app)
		if ( isset( $config['meta_value_suffix'] ) ) {
			// For mortgage pages, also check the page template type
			$args['name'] = $username . $config['meta_value_suffix'];
		}

		$posts = get_posts( $args );

		// If no posts found by author, try meta query for legacy posts
		if ( empty( $posts ) ) {
			$args = array(
				'post_type'      => $config['post_type'],
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => $config['meta_key'],
						'value' => $user->ID,
					),
				),
				'post_status'    => 'publish',
			);

			if ( isset( $config['meta_value_suffix'] ) ) {
				$args['name'] = $username . $config['meta_value_suffix'];
			}

			$posts = get_posts( $args );
		}

		if ( empty( $posts ) ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Set up the query to display this post
		$post = $posts[0];

		// Reset query
		$wp_query->init();
		$wp_query->query_vars['p'] = $post->ID;
		$wp_query->query_vars['post_type'] = $config['post_type'];
		$wp_query->is_single = true;
		$wp_query->is_singular = true;
		$wp_query->is_404 = false;
		$wp_query->post = $post;
		$wp_query->posts = array( $post );
		$wp_query->post_count = 1;
		$wp_query->queried_object = $post;
		$wp_query->queried_object_id = $post->ID;

		// Set up global $post
		setup_postdata( $post );
	}
}
