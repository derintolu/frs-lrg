<?php
/**
 * Debug script to check what's happening in the customizer
 *
 * Add ?debug_customizer=1 to the customizer URL to see diagnostic info
 */

// Only run if debug parameter is set
if ( !isset( $_GET['debug_customizer'] ) ) {
	return;
}

// Hook into template_redirect with high priority to see what's running
add_action( 'template_redirect', function() {
	if ( !is_customize_preview() ) {
		error_log( 'DEBUG: Not in customizer preview' );
		return;
	}

	error_log( 'DEBUG: In customizer preview' );
	error_log( 'DEBUG: is_404: ' . ( is_404() ? 'YES' : 'NO' ) );
	error_log( 'DEBUG: is_page: ' . ( is_page() ? 'YES' : 'NO' ) );
	error_log( 'DEBUG: is_singular: ' . ( is_singular() ? 'YES' : 'NO' ) );
	error_log( 'DEBUG: current URL: ' . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : 'N/A' ) );

	global $wp_query;
	error_log( 'DEBUG: Query vars: ' . print_r( $wp_query->query_vars, true ) );

}, 1 );

// Hook into template_include to see what template is being loaded
add_filter( 'template_include', function( $template ) {
	if ( !isset( $_GET['debug_customizer'] ) ) {
		return $template;
	}

	error_log( 'DEBUG: Template being loaded: ' . $template );
	return $template;
}, 999 );
