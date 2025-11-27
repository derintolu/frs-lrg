 <?php
/**
 * Plugin Name: Lending Resource Hub
 * Description: Learning management and partnership platform for 21st Century Lending
 * Author: 21st Century Lending
 * Author URI: https://hub21loan.com
 * License: GPLv2
 * Version: 1.0.0
 * Text Domain: lending-resource-hub
 * Domain Path: /languages
 *
 * @package Lending Resource Hub
 */

use LendingResourceHub\Core\Install;

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Load DataKit SDK if available and not already loaded
if ( ! class_exists( 'DataKit\DataViews\DataView\DataView' ) ) {
	if ( file_exists( plugin_dir_path( __FILE__ ) . 'libs/datakit/vendor/autoload.php' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'libs/datakit/vendor/autoload.php';
	}
}

require_once plugin_dir_path( __FILE__ ) . 'plugin.php';

// Debug script for customizer issues (only runs with ?debug_customizer=1)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	require_once plugin_dir_path( __FILE__ ) . 'debug-customizer.php';
}

/**
 * Initializes the LendingResourceHub plugin when plugins are loaded.
 *
 * @since 1.0.0
 * @return void
 */
function lending_resource_hub_init() {
	LendingResourceHub::get_instance()->init();
}

// Hook for plugin initialization.
add_action( 'plugins_loaded', 'lending_resource_hub_init' );

// Hook for plugin activation.
register_activation_hook( __FILE__, array( Install::get_instance(), 'init' ) );
