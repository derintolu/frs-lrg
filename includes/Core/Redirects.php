<?php
/**
 * Redirects Handler
 *
 * Handles URL redirects for logged-in users.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;

/**
 * Class Redirects
 *
 * Manages automatic redirects for the plugin.
 *
 * @package LendingResourceHub\Core
 */
class Redirects {

	use Base;

	/**
	 * Initialize redirects.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'template_redirect', array( $this, 'redirect_logged_in_users_from_homepage' ) );
	}

	/**
	 * Redirect logged-in users from homepage to their dashboard.
	 *
	 * Homepage is the login page, so no logged-in user needs to see it.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function redirect_logged_in_users_from_homepage() {
		// Only redirect if user is logged in
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Only redirect from the front page (homepage)
		if ( ! is_front_page() || is_admin() ) {
			return;
		}

		// Redirect all logged-in users to /welcome
		$dashboard_url = home_url( '/welcome' );

		// Perform the redirect
		wp_safe_redirect( $dashboard_url );
		exit;
	}
}
