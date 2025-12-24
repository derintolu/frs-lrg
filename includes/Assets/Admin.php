<?php

declare(strict_types=1);

namespace LendingResourceHub\Assets;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

/**
 * Class Admin
 *
 * Handles admin functionalities for the LendingResourceHub.
 *
 * @package LendingResourceHub\Admin
 */
class Admin {

	use Base;

	/**
	 * Script handle for LendingResourceHub.
	 */
	const HANDLE = 'lrh-admin';

	/**
	 * JS Object name for LendingResourceHub.
	 */
	const OBJ_NAME = 'lrhAdmin';

	/**
	 * Development script path for LendingResourceHub.
	 */
	const DEV_SCRIPT = 'src/admin/main.jsx';

	/**
	 * List of allowed screens for script enqueue.
	 *
	 * @var array
	 */
	private $allowed_screens = array(
		'toplevel_page_lending-resource-hub',
	);

	/**
	 * Frontend bootstrapper.
	 *
	 * @return void
	 */
	public function bootstrap() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}

	/**
	 * Enqueue script based on the current screen.
	 *
	 * @param string $screen The current screen.
	 */
	public function enqueue_script( $screen ) {
		if ( in_array( $screen, $this->allowed_screens, true ) ) {
			Assets\enqueue_asset(
				LRH_DIR . '/assets/admin/dist',
				self::DEV_SCRIPT,
				$this->get_config()
			);
			wp_localize_script( self::HANDLE, self::OBJ_NAME, $this->get_data() );
		}
	}

	/**
	 * Get the script configuration.
	 *
	 * @return array The script configuration.
	 */
	public function get_config() {
		return array(
			'dependencies' => array( 'react', 'react-dom' ),
			'handle'       => self::HANDLE,
			'in-footer'    => true,
		);
	}

	/**
	 * Get data for script localization.
	 *
	 * @return array The localized script data.
	 */
	public function get_data() {
		$current_user = wp_get_current_user();

		return array(
			'developer' => 'prappo',
			'isAdmin'   => is_admin(),
			'apiUrl'    => rest_url( LRH_ROUTE_PREFIX . '/' ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'userId'    => $current_user->ID,
			'userName'  => $current_user->display_name,
			'userEmail' => $current_user->user_email,
			'userInfo'  => $this->get_user_data(),
		);
	}

	/**
	 * Get user data for script localization.
	 *
	 * @return array The user data.
	 */
	private function get_user_data() {
		$username   = '';
		$avatar_url = '';

		if ( is_user_logged_in() ) {
			// Get current user's data .
			$current_user = wp_get_current_user();

			// Get username.
			$username = $current_user->user_login; // or use user_nicename, display_name, etc.

			// Get avatar URL.
			$avatar_url = get_avatar_url( $current_user->ID );
		}

		return array(
			'username' => $username,
			'avatar'   => $avatar_url,
		);
	}
}
