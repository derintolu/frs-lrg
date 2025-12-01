<?php
use LendingResourceHub\Core\Api;
use LendingResourceHub\Core\Shortcode;
use LendingResourceHub\Core\PostTypes;
use LendingResourceHub\Core\Redirects;
use LendingResourceHub\Core\MortgageLandingGenerator;
use LendingResourceHub\Core\UserPageRewrites;
use LendingResourceHub\Core\Blocks as CoreBlocks;
use LendingResourceHub\Core\DataKit;
use LendingResourceHub\Core\PartnerCompanyImporter;
use LendingResourceHub\CLI\PartnerCompanyCommands;
use LendingResourceHub\Admin\Menu;
use LendingResourceHub\Core\Template;
use LendingResourceHub\Assets\Frontend;
use LendingResourceHub\Helpers\ProfileHelpers;
// use LendingResourceHub\Assets\Admin; // Not needed - admin uses PHP templates, not React
use LendingResourceHub\Integrations\FluentBooking;
use LendingResourceHub\Integrations\FluentForms;
use LendingResourceHub\Integrations\FluentCRMSync;
use LendingResourceHub\Controllers\Biolinks\Blocks as BiolinkBlocks;
use LendingResourceHub\Controllers\PartnerPortals\Blocks as PartnerPortalBlocks;
use LendingResourceHub\Controllers\PartnerPortals\Api as PartnerPortalApi;
use LendingResourceHub\Controllers\PartnerPortals\PartnerCompanyPortal;
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
		CoreBlocks::get_instance()->init();
		BiolinkBlocks::get_instance()->init();
		PartnerPortalBlocks::get_instance()->init();
		PartnerPortalApi::get_instance()->init();
		PartnerCompanyPortal::get_instance()->init();

		// Initialize DataKit integration if SDK is available
		if ( class_exists( 'DataKit\DataViews\DataView\DataView' ) ) {
			DataKit::get_instance()->init();
		}

		// Initialize mortgage landing page generation
		MortgageLandingGenerator::get_instance()->init();

		// Initialize integrations
		FluentBooking::get_instance()->init();

		// Initialize FluentForms if plugin is active
		if ( FluentForms::is_active() ) {
			FluentForms::get_instance()->init();
		}

		// Initialize FluentCRM partnership sync if plugin is active
		if ( function_exists('FluentCrmApi') ) {
			FluentCRMSync::get_instance()->init();
		}

		// Check dependencies and show admin notices
		add_action( 'admin_notices', array( $this, 'check_dependencies' ) );

		add_action( 'init', array( $this, 'i18n' ) );
		add_action( 'init', array( $this, 'register_user_meta_fields' ) );

		// Register WP-CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'lrh partner-company', PartnerCompanyCommands::class );
		}
	}

	/**
	 * Register custom user meta fields for REST API access.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_user_meta_fields() {
		register_meta(
			'user',
			'profile_completion_reached_100',
			array(
				'type'         => 'string',
				'description'  => 'Whether user has reached 100% profile completion',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => '0',
			)
		);
	}

	/**
	 * Check plugin dependencies and show admin notices
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_dependencies() {
		$missing = array();

		// Check for FRS User Profiles plugin
		if ( !class_exists('FRSUsers') ) {
			$missing[] = 'FRS User Profiles';
		}

		// Check for FluentCRM
		if ( !function_exists('FluentCrmApi') ) {
			$missing[] = 'FluentCRM (optional - required for partnership sync)';
		}

		// Show notice if dependencies are missing
		if ( !empty($missing) ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong>FRS Lending Resource Hub</strong> requires the following plugins to function properly:
				</p>
				<ul style="list-style: disc; margin-left: 20px;">
					<?php foreach ($missing as $plugin): ?>
						<li><?php echo esc_html($plugin); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
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
