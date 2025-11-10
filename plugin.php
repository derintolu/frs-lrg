<?php
use LendingResourceHub\Core\Api;
use LendingResourceHub\Core\Shortcode;
use LendingResourceHub\Core\PostTypes;
use LendingResourceHub\Core\Redirects;
use LendingResourceHub\Core\MortgageLandingGenerator;
use LendingResourceHub\Core\UserPageRewrites;
use LendingResourceHub\Admin\Menu;
use LendingResourceHub\Core\Template;
use LendingResourceHub\Assets\Frontend;
use LendingResourceHub\Helpers\ProfileHelpers;
// use LendingResourceHub\Assets\Admin; // Not needed - admin uses PHP templates, not React
use LendingResourceHub\Integrations\FluentBooking;
use LendingResourceHub\Integrations\FluentForms;
use LendingResourceHub\Controllers\Biolinks\Blocks;
use LendingResourceHub\Traits\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class LendingResourceHub
 *
 * The main class for the Coldmailar plugin, responsible for initialization and setup.
 *
 * @since 1.0.0
 */
final class LendingResourceHub {

	use Base;

	/**
	 * Class constructor to set up constants for the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		define( 'LRH_VERSION', '1.0.0' );
		define( 'LRH_PLUGIN_FILE', __FILE__ );
		define( 'LRH_DIR', plugin_dir_path( __FILE__ ) );
		define( 'LRH_URL', plugin_dir_url( __FILE__ ) );
		define( 'LRH_ASSETS_URL', LRH_URL . '/assets' );
		define( 'LRH_ROUTE_PREFIX', 'lrh/v1' );
	}

	/**
	 * Main execution point where the plugin will fire up.
	 *
	 * Initializes necessary components for both admin and frontend.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		if ( is_admin() ) {
			Menu::get_instance()->init();
			// Note: Admin interface uses PHP templates (not React)
			// React is only used for frontend shortcodes
			// Admin::get_instance()->bootstrap(); // Removed - not needed for PHP admin
		}

		// Initialize core functionalities.
		Frontend::get_instance()->bootstrap();
		API::get_instance()->init();
		Template::get_instance()->init();
		Shortcode::get_instance()->init();
		PostTypes::get_instance()->init();
		UserPageRewrites::get_instance()->init();
		Redirects::get_instance()->init();
		Blocks::get_instance()->init();

		// Initialize mortgage landing page generation
		MortgageLandingGenerator::get_instance()->init();

		// Initialize integrations
		FluentBooking::get_instance()->init();

		// Initialize FluentForms if plugin is active
		if ( FluentForms::is_active() ) {
			FluentForms::get_instance()->init();
		}

		add_action( 'init', array( $this, 'i18n' ) );
	}


	/**
	 * Internationalization setup for language translations.
	 *
	 * Loads the plugin text domain for localization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'lending-resource-hub', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
