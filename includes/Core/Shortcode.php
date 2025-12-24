<?php
/**
 * Shortcode Handler
 *
 * Handles shortcode registration and rendering for the portal.
 *
 * @package LendingResourceHub\Core
 * @since 1.0.0
 */

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

/**
 * Class Shortcode
 *
 * Handles shortcode registration and rendering.
 *
 * @package LendingResourceHub\Core
 */
class Shortcode {

	use Base;

	/**
	 * Initialize shortcodes.
	 *
	 * @return void
	 */
	public function init() {
		// Generic component shortcode (for embedding any React component)
		add_shortcode( 'lrh_component', array( $this, 'render_component' ) );

		// New shortcodes
		add_shortcode( 'lrh_portal', array( $this, 'render_portal' ) );
		add_shortcode( 'lrh_portal_sidebar', array( $this, 'render_portal_sidebar' ) );
		add_shortcode( 'lrh_welcome_portal', array( $this, 'render_welcome_portal' ) );
		add_shortcode( 'lrh_partnerships_section', array( $this, 'render_partnerships_section' ) );
		add_shortcode( 'lrh_realtor_portal', array( $this, 'render_realtor_portal' ) );
		add_shortcode( 'frs_mortgage_calculator', array( $this, 'render_mortgage_calculator' ) );
		add_shortcode( 'frs_property_valuation', array( $this, 'render_property_valuation' ) );
		add_shortcode( 'frs_tools_landing', array( $this, 'render_tools_landing_page' ) );

		// Content-only shortcodes (without sidebar - for use in portal frames)
		add_shortcode( 'lrh_content_welcome', array( $this, 'render_content_welcome' ) );
		add_shortcode( 'lrh_content_profile', array( $this, 'render_content_profile' ) );
		add_shortcode( 'lrh_content_marketing', array( $this, 'render_content_marketing' ) );
		add_shortcode( 'lrh_content_calendar', array( $this, 'render_content_calendar' ) );
		add_shortcode( 'lrh_content_landing_pages', array( $this, 'render_content_landing_pages' ) );
		add_shortcode( 'lrh_content_email_campaigns', array( $this, 'render_content_email_campaigns' ) );
		add_shortcode( 'lrh_content_local_seo', array( $this, 'render_content_local_seo' ) );
		add_shortcode( 'lrh_content_brand_guide', array( $this, 'render_content_brand_guide' ) );
		add_shortcode( 'lrh_content_orders', array( $this, 'render_content_orders' ) );
		add_shortcode( 'lrh_content_lead_tracking', array( $this, 'render_content_lead_tracking' ) );
		add_shortcode( 'lrh_content_tools', array( $this, 'render_content_tools' ) );
		add_shortcode( 'lrh_content_settings', array( $this, 'render_content_settings' ) );
		add_shortcode( 'lrh_content_notifications', array( $this, 'render_content_notifications' ) );

		// Realtor portal content-only shortcodes (without sidebar)
		add_shortcode( 'lrh_content_realtor_overview', array( $this, 'render_content_realtor_overview' ) );
		add_shortcode( 'lrh_content_company_overview', array( $this, 'render_content_company_overview' ) );
		add_shortcode( 'lrh_content_marketing_tools', array( $this, 'render_content_marketing_tools' ) );
		add_shortcode( 'lrh_content_calculator_tools', array( $this, 'render_content_calculator_tools' ) );
		add_shortcode( 'lrh_content_resources', array( $this, 'render_content_resources' ) );
		add_shortcode( 'lrh_content_realtor_profile', array( $this, 'render_content_realtor_profile' ) );

		// Individual dashboard section cards (standalone components)
		add_shortcode( 'lrh_booking_calendar_card', array( $this, 'render_booking_calendar_card' ) );
		add_shortcode( 'lrh_landing_pages_card', array( $this, 'render_landing_pages_card' ) );
		add_shortcode( 'lrh_brand_guide_card', array( $this, 'render_brand_guide_card' ) );
		add_shortcode( 'lrh_print_social_media_card', array( $this, 'render_print_social_media_card' ) );
		add_shortcode( 'lrh_my_bookings_card', array( $this, 'render_my_bookings_card' ) );

		// Calendar bento widgets
		add_shortcode( 'lrh_calendar_setup_card', array( $this, 'render_calendar_setup_card' ) );

		// Subnavigation panels
		add_shortcode( 'lrh_marketing_subnav', array( $this, 'render_marketing_subnav' ) );

		// Individual page shortcodes (full pages from portal routes)
		add_shortcode( 'lrh_marketing_overview', array( $this, 'render_marketing_overview_page' ) );
		add_shortcode( 'lrh_my_profile', array( $this, 'render_my_profile_page' ) );
		add_shortcode( 'lrh_lead_tracking', array( $this, 'render_lead_tracking_page' ) );
		add_shortcode( 'lrh_fluent_booking_calendar', array( $this, 'render_fluent_booking_calendar_page' ) );
		add_shortcode( 'lrh_landing_pages', array( $this, 'render_landing_pages_page' ) );
		add_shortcode( 'lrh_email_campaigns', array( $this, 'render_email_campaigns_page' ) );
		add_shortcode( 'lrh_local_seo', array( $this, 'render_local_seo_page' ) );
		add_shortcode( 'lrh_brand_showcase', array( $this, 'render_brand_showcase_page' ) );
		add_shortcode( 'lrh_marketing_orders', array( $this, 'render_marketing_orders_page' ) );
		add_shortcode( 'lrh_mortgage_calculator_page', array( $this, 'render_mortgage_calculator_page' ) );
		add_shortcode( 'lrh_property_valuation', array( $this, 'render_property_valuation_page' ) );
		add_shortcode( 'lrh_settings', array( $this, 'render_settings_page' ) );

		// Lead Pages (Generation Station) shortcodes
		add_shortcode( 'lrh_lead_pages_card', array( $this, 'render_lead_pages_card' ) );
		add_shortcode( 'lrh_lead_page_submissions', array( $this, 'render_lead_page_submissions' ) );
		add_shortcode( 'lrh_generation_station', array( $this, 'render_generation_station_page' ) );

		// Legacy shortcode from old plugin (backward compatibility)
		add_shortcode( 'frs_partnership_portal', array( $this, 'render_legacy_portal' ) );

		// Legacy welcome shortcode alias
		add_shortcode( 'frs_lo_welcome', array( $this, 'render_content_welcome' ) );

		// Welcome bento individual widgets
		add_shortcode( 'lrh_welcome_header', array( $this, 'render_welcome_header_widget' ) );
		add_shortcode( 'lrh_clock_widget', array( $this, 'render_clock_widget' ) );
		add_shortcode( 'lrh_calendar_date_widget', array( $this, 'render_calendar_date_widget' ) );
		add_shortcode( 'lrh_daily_rates_widget', array( $this, 'render_daily_rates_widget' ) );
		add_shortcode( 'lrh_blog_posts_widget', array( $this, 'render_blog_posts_widget' ) );
		add_shortcode( 'lrh_app_launcher', array( $this, 'render_app_launcher_widget' ) );
	}

	/**
	 * Render a generic React component.
	 *
	 * Allows embedding any React component from your component library.
	 *
	 * Shortcode attributes:
	 * - name: Component name (required) - e.g., "MyProfile", "MarketingOverview"
	 * - props: JSON string of props to pass to component (optional)
	 * - wrapper_class: CSS classes for wrapper div (optional)
	 * - wrapper_style: Inline styles for wrapper div (optional)
	 *
	 * Example: [lrh_component name="MyProfile" props='{"userId":"123","autoEdit":true}']
	 * Example: [lrh_component name="LeadTracking" wrapper_class="p-4 bg-white"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_component( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'name'          => '',
				'props'         => '{}',
				'wrapper_class' => '',
				'wrapper_style' => '',
			),
			$atts,
			'lrh_component'
		);

		// Component name is required
		if ( empty( $atts['name'] ) ) {
			return '<!-- [lrh_component] Error: component name is required -->';
		}

		// Validate JSON props
		$props = json_decode( $atts['props'], true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return '<!-- [lrh_component] Error: invalid JSON in props attribute -->';
		}

		// Enqueue portal assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Generate unique ID for this component instance
		$component_id = 'lrh-component-' . sanitize_title( $atts['name'] ) . '-' . wp_rand( 1000, 9999 );

		// Build wrapper attributes
		$wrapper_attrs = array(
			'id'                 => $component_id,
			'data-lrh-component' => esc_attr( $atts['name'] ),
			'data-lrh-props'     => esc_attr( wp_json_encode( $props ) ),
		);

		if ( ! empty( $atts['wrapper_class'] ) ) {
			$wrapper_attrs['class'] = esc_attr( $atts['wrapper_class'] );
		}

		if ( ! empty( $atts['wrapper_style'] ) ) {
			$wrapper_attrs['style'] = esc_attr( $atts['wrapper_style'] );
		}

		// Build HTML
		$html = '<div';
		foreach ( $wrapper_attrs as $key => $value ) {
			$html .= sprintf( ' %s="%s"', $key, $value );
		}
		$html .= '></div>';

		return $html;
	}

	/**
	 * Render the main portal shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_portal( $atts ) {
		// Enqueue portal assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Return root element for React to mount with Interactivity API router region
		return '<div id="lrh-portal-root" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-portal"></div>';
	}

	/**
	 * Render the legacy portal shortcode (backward compatibility).
	 *
	 * Just an alias for render_portal() - does exactly the same thing.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_legacy_portal( $atts ) {
		return $this->render_portal( $atts );
	}

	/**
	 * Render the portal sidebar shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_portal_sidebar( $atts ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return '';
		}

		// Add body class for sidebar styling
		add_filter( 'body_class', array( $this, 'add_sidebar_body_class' ) );

		// Enqueue portal assets directly when shortcode is rendered
		// This ensures assets load even when shortcode is called via do_shortcode() in themes
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Return container with data-wp-interactive for Interactivity API router
		return '<div id="lrh-portal-sidebar-root" data-lrh-component="portal-sidebar" data-wp-interactive="lrh-portal"></div>';
	}

	/**
	 * Render the welcome portal shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_welcome_portal( $atts ) {
		// Enqueue welcome portal assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_welcome_portal_assets();

		// Return root element for React to mount
		return '<div id="lrh-welcome-portal-root"></div>';
	}

	/**
	 * Render the partnerships section shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_partnerships_section( $atts ) {
		// Enqueue partnerships section assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_partnerships_section_assets();

		// Return root element for React to mount
		return '<div id="lrh-partnerships-section-root"></div>';
	}


	/**
	 * Render the realtor portal shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_realtor_portal( $atts ) {
		// Enqueue realtor portal assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_realtor_portal_assets();

		// Return root element for React to mount with Interactivity API router region
		return '<div id="lrh-realtor-portal-root" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-realtor-portal"></div>';
	}

	/**
	 * Get primary portal role for user.
	 *
	 * @param WP_User $user The user object.
	 * @return string The primary portal role.
	 */
	private function get_primary_portal_role( $user ) {
		$portal_roles = array( 'loan_officer', 'realtor_partner', 'realtor', 'manager', 'frs_admin', 'administrator' );

		foreach ( $portal_roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return $role;
			}
		}

		return 'subscriber';
	}

	/**
	 * Get menu items for user based on role.
	 *
	 * @param WP_User $user The user object.
	 * @return array Menu items array.
	 */
	private function get_menu_items_for_user( $user ) {
		$role = $this->get_primary_portal_role( $user );

		// Base menu items for all users
		$menu_items = array(
			array(
				'id'    => 'home',
				'label' => 'Home',
				'icon'  => 'Home',
				'url'   => get_site_url(),
			),
		);

		// Add role-specific menu items
		if ( $role === 'loan_officer' ) {
			$menu_items[] = array(
				'id'       => 'dashboard',
				'label'    => 'Dashboard',
				'icon'     => 'LayoutDashboard',
				'url'      => get_site_url() . '/portal',
			);
		}

		return $menu_items;
	}

	/**
	 * Add body class for sidebar pages.
	 *
	 * @param array $classes Body classes.
	 * @return array Modified body classes.
	 */
	public function add_sidebar_body_class( $classes ) {
		$classes[] = 'has-lrh-portal-sidebar';
		return $classes;
	}

	/**
	 * Render the mortgage calculator shortcode.
	 *
	 * Shortcode attributes:
	 * - loan_officer_id: User ID of the loan officer (defaults to current user or URL param)
	 * - webhook_url: URL to send lead data via webhook
	 * - show_lead_form: Whether to show the lead capture form (default: true)
	 * - brand_color: Brand color hex code (default: #3b82f6)
	 * - logo_url: URL to logo image
	 *
	 * Example: [frs_mortgage_calculator loan_officer_id="123" webhook_url="https://example.com/webhook"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_mortgage_calculator( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'loan_officer_id' => '',
				'webhook_url'     => '',
				'show_lead_form'  => 'true',
				'brand_color'     => '',
				'logo_url'        => '',
			),
			$atts,
			'frs_mortgage_calculator'
		);

		// Enqueue widget assets directly when shortcode is rendered
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();

		// Build data attributes for the widget
		$data_attrs = array();

		if ( ! empty( $atts['loan_officer_id'] ) ) {
			$data_attrs[] = 'data-loan-officer-id="' . esc_attr( $atts['loan_officer_id'] ) . '"';
		}

		if ( ! empty( $atts['webhook_url'] ) ) {
			$data_attrs[] = 'data-webhook-url="' . esc_url( $atts['webhook_url'] ) . '"';
		}

		if ( ! empty( $atts['show_lead_form'] ) ) {
			$data_attrs[] = 'data-show-lead-form="' . esc_attr( $atts['show_lead_form'] ) . '"';
		}

		if ( ! empty( $atts['brand_color'] ) ) {
			$data_attrs[] = 'data-brand-color="' . esc_attr( $atts['brand_color'] ) . '"';
		}

		if ( ! empty( $atts['logo_url'] ) ) {
			$data_attrs[] = 'data-logo-url="' . esc_url( $atts['logo_url'] ) . '"';
		}

		// Return root element for React to mount
		return '<div id="mortgage-calculator" ' . implode( ' ', $data_attrs ) . '></div>';
	}

	/**
	 * Render the property valuation shortcode.
	 *
	 * Embeddable property valuation tool.
	 *
	 * Example: [frs_property_valuation user_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered shortcode HTML.
	 */
	public function render_property_valuation( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => '',
			),
			$atts,
			'frs_property_valuation'
		);

		// Enqueue widget assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();

		// Build data attributes
		$data_attrs = array();

		if ( ! empty( $atts['user_id'] ) ) {
			$data_attrs[] = 'data-loan-officer-id="' . esc_attr( $atts['user_id'] ) . '"';
		}

		// Return root element for React to mount
		return '<div id="property-valuation" ' . implode( ' ', $data_attrs ) . '></div>';
	}

	/**
	 * Content-only shortcode renderers
	 * These render just the page content without the portal sidebar
	 * For use in portal frames that provide their own sidebar
	 */

	public function render_content_welcome( $atts ) {
		// Assets are enqueued via wp_enqueue_scripts hook when shortcode is detected
		return '<div id="lrh-welcome-portal-root" data-content-only="true" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_content_profile( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-profile-root" data-lrh-content="profile" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_marketing( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-marketing-root" data-lrh-content="marketing" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_calendar( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-calendar-root" data-lrh-content="calendar" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_landing_pages( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-landing-pages-root" data-lrh-content="landing-pages" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_email_campaigns( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-email-campaigns-root" data-lrh-content="email-campaigns" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_local_seo( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-local-seo-root" data-lrh-content="local-seo" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_brand_guide( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-brand-guide-root" data-lrh-content="brand-guide" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_orders( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-orders-root" data-lrh-content="orders" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_lead_tracking( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-lead-tracking-root" data-lrh-content="lead-tracking" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_tools( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-tools-root" data-lrh-content="tools" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_settings( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-settings-root" data-lrh-content="settings" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	public function render_content_notifications( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-notifications-root" data-lrh-content="notifications" data-wp-interactive="lrh-portal" data-wp-router-region="lrh-content"></div>';
	}

	/**
	 * Realtor portal content-only shortcodes (without sidebar)
	 */

	public function render_content_realtor_overview( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-realtor-overview-root" data-lrh-component="RealtorOverview"></div>';
	}

	public function render_content_company_overview( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-company-overview-root" data-lrh-component="CompanyOverview"></div>';
	}

	public function render_content_marketing_tools( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-marketing-tools-root" data-lrh-component="MarketingTools"></div>';
	}

	public function render_content_calculator_tools( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-calculator-tools-root" data-lrh-component="CalculatorTools"></div>';
	}

	public function render_content_resources( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-resources-root" data-lrh-component="Resources"></div>';
	}

	public function render_content_realtor_profile( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-realtor-profile-root" data-lrh-component="Profile"></div>';
	}

	/**
	 * Individual dashboard section card shortcodes
	 */

	public function render_booking_calendar_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-booking-calendar-card-root" data-lrh-card="booking-calendar" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_landing_pages_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-landing-pages-card-root" data-lrh-card="landing-pages" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_brand_guide_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-brand-guide-card-root" data-lrh-card="brand-guide" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_print_social_media_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-print-social-media-card-root" data-lrh-card="print-social-media" data-wp-interactive="lrh-portal"></div>';
	}

	/**
	 * Individual page shortcodes (full pages from portal routes)
	 */

	public function render_marketing_overview_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-overview-root" data-lrh-page="marketing-overview" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_my_profile_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-my-profile-root" data-lrh-page="my-profile" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_lead_tracking_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-lead-tracking-root" data-lrh-page="lead-tracking" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_fluent_booking_calendar_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-fluent-booking-calendar-root" data-lrh-page="fluent-booking-calendar" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_landing_pages_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-landing-pages-page-root" data-lrh-page="landing-pages" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_email_campaigns_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-email-campaigns-root" data-lrh-page="email-campaigns" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_local_seo_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-local-seo-root" data-lrh-page="local-seo" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_brand_showcase_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-brand-showcase-root" data-lrh-page="brand-showcase" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_marketing_orders_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-orders-root" data-lrh-page="marketing-orders" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_mortgage_calculator_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-mortgage-calculator-page-root" data-lrh-page="mortgage-calculator" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_property_valuation_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-property-valuation-root" data-lrh-page="property-valuation" data-wp-interactive="lrh-portal"></div>';
	}

	public function render_settings_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-settings-root" data-lrh-page="settings" data-wp-interactive="lrh-portal"></div>';
	}

	/**
	 * Render marketing subnavigation panel
	 */
	public function render_marketing_subnav( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-subnav-root" data-lrh-subnav="marketing" data-wp-interactive="lrh-portal"></div>';
	}

	/**
	 * Render tools landing page with Mortgage Calculator & Property Valuation bentos.
	 *
	 * Public landing page for loan officers to share with leads.
	 * Shows loan officer profile, two tool cards, and lead capture.
	 *
	 * Usage: [frs_tools_landing user_id="123"]
	 * Or with URL param: page.com/tools/?loan_officer_id=123
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_tools_landing_page( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id'        => '',
				'webhook_url'    => '',
				'show_lead_form' => 'true',
			),
			$atts,
			'frs_tools_landing'
		);

		// Determine user ID: shortcode attr > URL param > current user
		$user_id = ! empty( $atts['user_id'] ) ? intval( $atts['user_id'] ) : 0;
		if ( ! $user_id && isset( $_GET['loan_officer_id'] ) ) {
			$user_id = intval( $_GET['loan_officer_id'] );
		}
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get loan officer data
		$user = get_user_by( 'id', $user_id );
		$lo_data = array(
			'id'    => $user_id,
			'name'  => '',
			'email' => '',
			'phone' => '',
			'nmls'  => '',
			'title' => '',
			'avatar' => '',
		);

		if ( $user ) {
			// Try REST API for full profile
			$response = wp_remote_get( rest_url( "frs-users/v1/profiles/user/{$user_id}" ), array( 'timeout' => 5 ) );
			if ( ! is_wp_error( $response ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['success'] ) && $body['success'] && isset( $body['data'] ) ) {
					$profile = $body['data'];
					$lo_data['name']   = trim( ( $profile['first_name'] ?? '' ) . ' ' . ( $profile['last_name'] ?? '' ) );
					$lo_data['email']  = $profile['email'] ?? $user->user_email;
					$lo_data['phone']  = $profile['mobile_number'] ?? $profile['phone_number'] ?? '';
					$lo_data['nmls']   = $profile['nmls'] ?? $profile['nmls_number'] ?? '';
					$lo_data['title']  = $profile['job_title'] ?? '';
					$lo_data['avatar'] = $profile['profile_photo'] ?? get_avatar_url( $user_id );
				}
			}

			// Fallback to user meta
			if ( empty( $lo_data['name'] ) ) {
				$lo_data['name'] = trim( get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true ) );
			}
			if ( empty( $lo_data['name'] ) ) {
				$lo_data['name'] = $user->display_name;
			}
			if ( empty( $lo_data['email'] ) ) {
				$lo_data['email'] = $user->user_email;
			}
			if ( empty( $lo_data['avatar'] ) ) {
				$lo_data['avatar'] = get_avatar_url( $user_id );
			}
		}

		// Enqueue widget assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();

		// Build data attributes
		$data_attrs = sprintf(
			'data-loan-officer-id="%d" data-loan-officer-name="%s" data-loan-officer-email="%s" data-loan-officer-phone="%s" data-loan-officer-nmls="%s" data-loan-officer-title="%s" data-loan-officer-avatar="%s" data-show-lead-form="%s"',
			$user_id,
			esc_attr( $lo_data['name'] ),
			esc_attr( $lo_data['email'] ),
			esc_attr( $lo_data['phone'] ),
			esc_attr( $lo_data['nmls'] ),
			esc_attr( $lo_data['title'] ),
			esc_url( $lo_data['avatar'] ),
			esc_attr( $atts['show_lead_form'] )
		);

		if ( ! empty( $atts['webhook_url'] ) ) {
			$data_attrs .= sprintf( ' data-webhook-url="%s"', esc_url( $atts['webhook_url'] ) );
		}

		return '<div id="frs-tools-landing-root" ' . $data_attrs . '></div>';
	}

	/**
	 * Lead Pages (Generation Station) Shortcodes
	 * ==========================================
	 */

	/**
	 * Render the Lead Pages Card widget
	 *
	 * Dashboard widget showing lead pages overview with stats.
	 *
	 * Shortcode attributes:
	 * - user_id: User ID (defaults to current user)
	 * - role: 'loan_officer' or 'realtor' (auto-detected from user)
	 * - compact: 'true' for compact card view (default: true)
	 *
	 * Example: [lrh_lead_pages_card]
	 * Example: [lrh_lead_pages_card user_id="123" compact="false"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_lead_pages_card( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => '',
				'role'    => '',
				'compact' => 'true',
			),
			$atts,
			'lrh_lead_pages_card'
		);

		// Get user ID
		$user_id = ! empty( $atts['user_id'] ) ? intval( $atts['user_id'] ) : get_current_user_id();
		if ( ! $user_id ) {
			return '<!-- [lrh_lead_pages_card] Error: User not logged in -->';
		}

		// Determine role
		$role = $atts['role'];
		if ( empty( $role ) ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user ) {
				if ( in_array( 'realtor_partner', $user->roles, true ) || in_array( 'realtor', $user->roles, true ) ) {
					$role = 'realtor';
				} else {
					$role = 'loan_officer';
				}
			}
		}

		// Enqueue portal assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Build props
		$props = array(
			'userId'   => (string) $user_id,
			'userRole' => $role,
			'compact'  => $atts['compact'] === 'true',
		);

		// Generate unique ID
		$component_id = 'lrh-lead-pages-card-' . wp_rand( 1000, 9999 );

		return sprintf(
			'<div id="%s" data-lrh-component="LeadPagesCard" data-lrh-props="%s"></div>',
			esc_attr( $component_id ),
			esc_attr( wp_json_encode( $props ) )
		);
	}

	/**
	 * Render the Lead Page Submissions list
	 *
	 * Full page component showing all submissions from lead pages.
	 *
	 * Shortcode attributes:
	 * - user_id: User ID (defaults to current user)
	 * - role: 'loan_officer' or 'realtor' (auto-detected from user)
	 *
	 * Example: [lrh_lead_page_submissions]
	 * Example: [lrh_lead_page_submissions user_id="123" role="realtor"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_lead_page_submissions( $atts ) {
		$atts = shortcode_atts(
			array(
				'user_id' => '',
				'role'    => '',
			),
			$atts,
			'lrh_lead_page_submissions'
		);

		// Get user ID
		$user_id = ! empty( $atts['user_id'] ) ? intval( $atts['user_id'] ) : get_current_user_id();
		if ( ! $user_id ) {
			return '<!-- [lrh_lead_page_submissions] Error: User not logged in -->';
		}

		// Determine role
		$role = $atts['role'];
		if ( empty( $role ) ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user ) {
				if ( in_array( 'realtor_partner', $user->roles, true ) || in_array( 'realtor', $user->roles, true ) ) {
					$role = 'realtor';
				} else {
					$role = 'loan_officer';
				}
			}
		}

		// Enqueue portal assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();

		// Build props
		$props = array(
			'userId'   => (string) $user_id,
			'userRole' => $role,
		);

		// Generate unique ID
		$component_id = 'lrh-lead-page-submissions-' . wp_rand( 1000, 9999 );

		return sprintf(
			'<div id="%s" data-lrh-component="LeadPageSubmissions" data-lrh-props="%s"></div>',
			esc_attr( $component_id ),
			esc_attr( wp_json_encode( $props ) )
		);
	}

	/**
	 * Render the Generation Station page
	 *
	 * Full page view for Generation Station (Lead Pages) management.
	 *
	 * Example: [lrh_generation_station]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_generation_station_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-generation-station-root" data-lrh-page="generation-station" data-wp-interactive="lrh-portal"></div>';
	}

	/**
	 * Render the FluentBooking My Bookings bento card
	 *
	 * Compact bento-style card showing upcoming bookings.
	 * Uses FluentBooking's [fluent_booking_lists] shortcode internally.
	 *
	 * Shortcode attributes:
	 * - title: Card title (default: "My Bookings")
	 * - per_page: Number of bookings to show (default: 3)
	 * - period: Booking period filter - all, upcoming, past (default: upcoming)
	 * - show_settings_link: Show link to calendar settings (default: true)
	 *
	 * Example: [lrh_my_bookings_card]
	 * Example: [lrh_my_bookings_card per_page="5" title="Upcoming Appointments"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_my_bookings_card( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'              => __( 'My Bookings', 'frs-lrg' ),
				'per_page'           => 3,
				'period'             => 'upcoming',
				'show_settings_link' => 'true',
			),
			$atts,
			'lrh_my_bookings_card'
		);

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return '';
		}

		// Check if FluentBooking is active
		if ( ! defined( 'FLUENT_BOOKING_VERSION' ) ) {
			return '<div class="lrh-bento-card"><p>' . __( 'FluentBooking plugin is required.', 'frs-lrg' ) . '</p></div>';
		}

		$title              = sanitize_text_field( $atts['title'] );
		$per_page           = intval( $atts['per_page'] );
		$period             = sanitize_text_field( $atts['period'] );
		$show_settings_link = $atts['show_settings_link'] === 'true';

		// Build the FluentBooking shortcode
		$booking_shortcode = sprintf(
			'[fluent_booking_lists title="" per_page="%d" period="%s" filter="hide" pagination="hide"]',
			$per_page,
			$period
		);

		// Get calendar settings URL
		$settings_url = admin_url( 'admin.php?page=fluent-booking#/calendars' );

		ob_start();
		?>
		<div class="lrh-bento-card lrh-my-bookings-card">
			<div class="lrh-bento-card-header">
				<div class="lrh-bento-card-title">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lrh-bento-icon"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
					<span><?php echo esc_html( $title ); ?></span>
				</div>
				<?php if ( $show_settings_link ) : ?>
				<a href="<?php echo esc_url( $settings_url ); ?>" class="lrh-bento-settings-link" title="<?php esc_attr_e( 'Calendar Settings', 'frs-lrg' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
				</a>
				<?php endif; ?>
			</div>
			<div class="lrh-bento-card-content lrh-bookings-list">
				<?php echo do_shortcode( $booking_shortcode ); ?>
			</div>
		</div>
		<style>
		.lrh-bento-card {
			background: white;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.06);
			overflow: hidden;
			height: 100%;
			display: flex;
			flex-direction: column;
		}
		.lrh-bento-card-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 12px 16px;
			border-bottom: 1px solid #f1f5f9;
			background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
		}
		.lrh-bento-card-title {
			display: flex;
			align-items: center;
			gap: 8px;
			font-weight: 600;
			font-size: 14px;
			color: #0B102C;
		}
		.lrh-bento-icon {
			color: #2563eb;
		}
		.lrh-bento-settings-link {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 28px;
			height: 28px;
			border-radius: 6px;
			color: #64748b;
			transition: all 0.2s ease;
		}
		.lrh-bento-settings-link:hover {
			background: #f1f5f9;
			color: #2563eb;
		}
		.lrh-bento-card-content {
			flex: 1;
			padding: 12px 16px;
			overflow-y: auto;
		}
		.lrh-bookings-list .fcal_booking_list_title {
			display: none;
		}
		.lrh-bookings-list .fcal_booking_lists_wrapper {
			padding: 0 !important;
		}
		.lrh-bookings-list .fcal_booking_list_item {
			padding: 10px 12px !important;
			margin-bottom: 8px;
			border-radius: 8px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
		}
		.lrh-bookings-list .fcal_booking_list_item:last-child {
			margin-bottom: 0;
		}
		.lrh-bookings-list .fcal_booking_list_item:hover {
			background: #f1f5f9;
			border-color: #cbd5e1;
		}
		.lrh-bookings-list .fcal_filters_wrapper,
		.lrh-bookings-list .fcal_pagination {
			display: none !important;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Calendar Setup Card (main widget with onboarding launcher)
	 *
	 * Shows overall calendar setup progress and launches onboarding tour.
	 * When setup is incomplete, shows checklist and "Start Setup" button.
	 * When complete, shows "All Set" status.
	 *
	 * Example: [lrh_calendar_setup_card]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_calendar_setup_card( $atts ) {
		if ( ! is_user_logged_in() || ! defined( 'FLUENT_BOOKING_VERSION' ) ) {
			return '';
		}

		$user_id = get_current_user_id();

		// Check all setup steps
		$steps = array(
			array(
				'id'        => 'calendar-sync',
				'label'     => __( 'Email Calendar Sync', 'frs-lrg' ),
				'completed' => $this->check_calendar_integration( $user_id ),
			),
			array(
				'id'        => 'video',
				'label'     => __( 'Video Conferencing', 'frs-lrg' ),
				'completed' => $this->check_video_integration( $user_id ),
			),
			array(
				'id'        => 'availability',
				'label'     => __( 'Availability Set', 'frs-lrg' ),
				'completed' => $this->check_availability_set( $user_id ),
			),
			array(
				'id'        => 'events',
				'label'     => __( 'Event Types Created', 'frs-lrg' ),
				'completed' => $this->check_event_types( $user_id ),
			),
		);

		$completed_count = count( array_filter( $steps, fn( $s ) => $s['completed'] ) );
		$total_count     = count( $steps );
		$is_complete     = $completed_count === $total_count;

		ob_start();
		?>
		<div class="lrh-calendar-setup-card" id="lrh-calendar-setup-card" data-user-id="<?php echo esc_attr( $user_id ); ?>">
			<div class="lrh-calendar-setup-card__header">
				<div class="lrh-calendar-setup-card__icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
				</div>
				<div class="lrh-calendar-setup-card__title-area">
					<h3 class="lrh-calendar-setup-card__title">
						<?php echo $is_complete ? __( 'Calendar Ready!', 'frs-lrg' ) : __( 'Complete Your Calendar Setup', 'frs-lrg' ); ?>
					</h3>
					<p class="lrh-calendar-setup-card__progress">
						<?php printf( __( '%d of %d steps completed', 'frs-lrg' ), $completed_count, $total_count ); ?>
					</p>
				</div>
			</div>

			<?php if ( ! $is_complete ) : ?>
			<div class="lrh-calendar-setup-card__checklist">
				<?php foreach ( $steps as $step ) : ?>
					<div class="lrh-calendar-setup-card__step <?php echo $step['completed'] ? 'lrh-calendar-setup-card__step--complete' : ''; ?>">
						<?php if ( $step['completed'] ) : ?>
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lrh-check-icon"><polyline points="20 6 9 17 4 12"/></svg>
						<?php else : ?>
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lrh-circle-icon"><circle cx="12" cy="12" r="10"/></svg>
						<?php endif; ?>
						<span><?php echo esc_html( $step['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="lrh-calendar-setup-card__cta" id="lrh-start-calendar-tour">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
				<?php _e( 'Start Setup Guide', 'frs-lrg' ); ?>
			</button>
			<?php else : ?>
			<div class="lrh-calendar-setup-card__complete">
				<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				<p><?php _e( 'Your calendar is fully configured. Clients can now book appointments with you!', 'frs-lrg' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/calendar/' ) ); ?>" class="lrh-calendar-setup-card__link">
					<?php _e( 'View My Calendar', 'frs-lrg' ); ?>
					<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</a>
			</div>
			<?php endif; ?>
		</div>

		<!-- Onboarding Tour Container -->
		<div id="lrh-calendar-onboarding-root" data-user-id="<?php echo esc_attr( $user_id ); ?>"></div>

		<style>
		.lrh-calendar-setup-card {
			background: linear-gradient(135deg, #eff6ff 0%, #faf5ff 100%);
			border: 1px solid #bfdbfe;
			border-radius: 16px;
			padding: 24px;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		.lrh-calendar-setup-card__header {
			display: flex;
			align-items: flex-start;
			gap: 16px;
			margin-bottom: 20px;
		}
		.lrh-calendar-setup-card__icon {
			flex-shrink: 0;
			width: 56px;
			height: 56px;
			background: linear-gradient(135deg, #2563eb 0%, #9333ea 100%);
			border-radius: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: white;
		}
		.lrh-calendar-setup-card__title-area {
			flex: 1;
		}
		.lrh-calendar-setup-card__title {
			margin: 0 0 4px;
			font-size: 18px;
			font-weight: 700;
			color: #0f172a;
		}
		.lrh-calendar-setup-card__progress {
			margin: 0;
			font-size: 13px;
			color: #64748b;
		}
		.lrh-calendar-setup-card__checklist {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 8px;
			margin-bottom: 20px;
		}
		.lrh-calendar-setup-card__step {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 13px;
			color: #374151;
		}
		.lrh-calendar-setup-card__step .lrh-circle-icon {
			color: #9ca3af;
		}
		.lrh-calendar-setup-card__step .lrh-check-icon {
			color: #16a34a;
		}
		.lrh-calendar-setup-card__step--complete span {
			color: #6b7280;
			text-decoration: line-through;
		}
		.lrh-calendar-setup-card__cta {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			width: 100%;
			padding: 14px 24px;
			background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
			color: white;
			font-size: 15px;
			font-weight: 600;
			border: none;
			border-radius: 10px;
			cursor: pointer;
			transition: all 0.2s;
		}
		.lrh-calendar-setup-card__cta:hover {
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
		}
		.lrh-calendar-setup-card__complete {
			text-align: center;
			padding: 20px 0 0;
		}
		.lrh-calendar-setup-card__complete svg {
			color: #16a34a;
			margin-bottom: 12px;
		}
		.lrh-calendar-setup-card__complete p {
			margin: 0 0 16px;
			font-size: 14px;
			color: #374151;
		}
		.lrh-calendar-setup-card__link {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-size: 14px;
			font-weight: 500;
			color: #2563eb;
			text-decoration: none;
		}
		.lrh-calendar-setup-card__link:hover {
			text-decoration: underline;
		}
		@media (max-width: 480px) {
			.lrh-calendar-setup-card__checklist {
				grid-template-columns: 1fr;
			}
		}
		</style>

		<script>
		(function() {
			const startBtn = document.getElementById('lrh-start-calendar-tour');
			if (startBtn) {
				startBtn.addEventListener('click', function() {
					// Dispatch event for React to pick up
					window.dispatchEvent(new CustomEvent('lrh-start-calendar-onboarding', {
						detail: { userId: this.closest('.lrh-calendar-setup-card').dataset.userId }
					}));
				});
			}
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Check if user has calendar integration (Google/Outlook)
	 */
	private function check_calendar_integration( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Meta' ) ) {
			return false;
		}

		$meta = \FluentBooking\App\Models\Meta::where( 'object_type', 'user_meta' )
			->where( 'object_id', $user_id )
			->where( 'key', 'google_calendar_token' )
			->first();

		if ( $meta ) {
			return true;
		}

		$meta = \FluentBooking\App\Models\Meta::where( 'object_type', 'user_meta' )
			->where( 'object_id', $user_id )
			->where( 'key', 'outlook_calendar_token' )
			->first();

		return ! empty( $meta );
	}

	/**
	 * Check if user has video conferencing integration (Zoom/Meet)
	 */
	private function check_video_integration( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Meta' ) ) {
			return false;
		}

		$meta = \FluentBooking\App\Models\Meta::where( 'object_type', 'user_meta' )
			->where( 'object_id', $user_id )
			->where( 'key', 'zoom_token' )
			->first();

		if ( $meta ) {
			return true;
		}

		$meta = \FluentBooking\App\Models\Meta::where( 'object_type', 'user_meta' )
			->where( 'object_id', $user_id )
			->where( 'key', 'google_meet_token' )
			->first();

		return ! empty( $meta );
	}

	/**
	 * Check if user has availability set
	 */
	private function check_availability_set( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\Calendar' ) ) {
			return false;
		}

		$calendar = \FluentBooking\App\Models\Calendar::where( 'user_id', $user_id )->first();
		if ( ! $calendar ) {
			return false;
		}

		// Check if calendar has availability settings
		$settings = $calendar->settings ?? array();
		return ! empty( $settings['weekly_schedules'] );
	}

	/**
	 * Check if user has event types created
	 */
	private function check_event_types( $user_id ) {
		if ( ! class_exists( '\FluentBooking\App\Models\CalendarSlot' ) ) {
			return false;
		}

		$events = \FluentBooking\App\Models\CalendarSlot::where( 'user_id', $user_id )
			->where( 'status', 'active' )
			->count();

		return $events > 0;
	}

	/**
	 * =========================================
	 * Welcome Bento Individual Widget Shortcodes
	 * =========================================
	 */

	/**
	 * Render Welcome Header widget
	 *
	 * Personalized welcome message with user's first name.
	 *
	 * Shortcode attributes:
	 * - greeting: Custom greeting text (default: "Welcome,")
	 * - subtitle: Subtitle text (default: "Your dashboard is ready")
	 *
	 * Example: [lrh_welcome_header]
	 * Example: [lrh_welcome_header greeting="Hello," subtitle="Let's get started!"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_welcome_header_widget( $atts ) {
		$atts = shortcode_atts(
			array(
				'greeting' => __( 'Welcome,', 'frs-lrg' ),
				'subtitle' => __( 'Your dashboard is ready', 'frs-lrg' ),
			),
			$atts,
			'lrh_welcome_header'
		);

		// Get current user first name
		$first_name = '';
		if ( is_user_logged_in() ) {
			$user       = wp_get_current_user();
			$first_name = get_user_meta( $user->ID, 'first_name', true );
			if ( empty( $first_name ) ) {
				$first_name = $user->display_name;
			}
		}

		ob_start();
		?>
		<div class="lrh-welcome-header">
			<div class="lrh-welcome-header__content">
				<h1 class="lrh-welcome-header__greeting">
					<?php echo esc_html( $atts['greeting'] ); ?><br>
					<?php echo esc_html( $first_name ); ?>
				</h1>
				<p class="lrh-welcome-header__subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
			</div>
			<div class="lrh-welcome-header__glow"></div>
		</div>
		<style>
		.lrh-welcome-header {
			position: relative;
			overflow: hidden;
			padding: 24px;
			border-radius: 16px;
			background: var(--gradient-brand-navy, linear-gradient(135deg, #0B102C 0%, #1a1f3a 50%, #263048 100%));
			box-shadow: 0 4px 16px rgba(38,48,66,0.4), 0 2px 6px rgba(0,0,0,0.2);
			ring: 1px solid rgba(255,255,255,0.1);
		}
		.lrh-welcome-header__content {
			position: relative;
			z-index: 10;
		}
		.lrh-welcome-header__greeting {
			margin: 0 0 8px;
			font-size: clamp(1.5rem, 3vw, 2rem);
			font-weight: 700;
			color: #ffffff;
			line-height: 1.2;
			font-family: Poppins, -apple-system, sans-serif;
		}
		.lrh-welcome-header__subtitle {
			margin: 0;
			font-size: 0.875rem;
			color: rgba(255,255,255,0.9);
			font-family: Poppins, -apple-system, sans-serif;
		}
		.lrh-welcome-header__glow {
			position: absolute;
			right: -40px;
			bottom: -40px;
			width: 120px;
			height: 120px;
			background: rgba(255,255,255,0.1);
			border-radius: 50%;
			filter: blur(48px);
		}
		@media (min-width: 768px) {
			.lrh-welcome-header__glow {
				width: 192px;
				height: 192px;
			}
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Clock widget
	 *
	 * Live clock with AM/PM display.
	 *
	 * Example: [lrh_clock_widget]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_clock_widget( $atts ) {
		$unique_id = 'lrh-clock-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div class="lrh-clock-widget" id="<?php echo esc_attr( $unique_id ); ?>">
			<div class="lrh-clock-widget__time">--:--</div>
			<div class="lrh-clock-widget__period">--</div>
		</div>
		<style>
		.lrh-clock-widget {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 16px 12px;
			border-radius: 12px;
			background: var(--gradient-hero, linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%));
			box-shadow: 0 2px 8px rgba(37,99,235,0.3), 0 1px 3px rgba(0,0,0,0.1);
			ring: 1px solid rgba(255,255,255,0.2);
		}
		.lrh-clock-widget__time {
			font-size: clamp(1.5rem, 3vw, 2rem);
			font-weight: 700;
			color: #ffffff;
			line-height: 1;
			font-family: Poppins, -apple-system, sans-serif;
		}
		.lrh-clock-widget__period {
			font-size: clamp(0.8rem, 1.5vw, 1rem);
			font-weight: 500;
			color: #ffffff;
			margin-top: 4px;
			font-family: Poppins, -apple-system, sans-serif;
		}
		</style>
		<script>
		(function() {
			const widget = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
			if (!widget) return;

			const timeEl = widget.querySelector('.lrh-clock-widget__time');
			const periodEl = widget.querySelector('.lrh-clock-widget__period');

			function updateClock() {
				const now = new Date();
				const timeStr = now.toLocaleTimeString('en-US', {
					hour: '2-digit',
					minute: '2-digit',
					hour12: true
				});
				const parts = timeStr.split(' ');
				timeEl.textContent = parts[0];
				periodEl.textContent = parts[1];
			}

			updateClock();
			setInterval(updateClock, 1000);
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Calendar Date widget (Tear-off calendar style)
	 *
	 * Shows current month, date, and day of week.
	 *
	 * Example: [lrh_calendar_date_widget]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_calendar_date_widget( $atts ) {
		$months = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
		$days   = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

		$current_month = $months[ (int) date( 'n' ) - 1 ];
		$current_date  = date( 'j' );
		$current_day   = $days[ (int) date( 'w' ) ];

		ob_start();
		?>
		<div class="lrh-calendar-date-widget">
			<div class="lrh-calendar-date-widget__month"><?php echo esc_html( $current_month ); ?></div>
			<div class="lrh-calendar-date-widget__body">
				<div class="lrh-calendar-date-widget__date"><?php echo esc_html( $current_date ); ?></div>
				<div class="lrh-calendar-date-widget__day"><?php echo esc_html( $current_day ); ?></div>
			</div>
		</div>
		<style>
		.lrh-calendar-date-widget {
			border-radius: 12px;
			overflow: hidden;
			background: #ffffff;
			box-shadow: 0 2px 8px rgba(0,0,0,0.12), 0 1px 3px rgba(0,0,0,0.08);
			ring: 1px solid rgba(0,0,0,0.05);
		}
		.lrh-calendar-date-widget__month {
			text-align: center;
			padding: 6px;
			background: linear-gradient(135deg, var(--brand-primary-blue, #2563eb) 0%, var(--brand-rich-teal, #14b8a6) 100%);
			color: #ffffff;
			font-size: clamp(0.7rem, 1.2vw, 0.85rem);
			font-weight: 600;
			font-family: Poppins, -apple-system, sans-serif;
			letter-spacing: 0.05em;
			text-transform: uppercase;
		}
		.lrh-calendar-date-widget__body {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 8px;
			background: #ffffff;
		}
		.lrh-calendar-date-widget__date {
			font-size: clamp(2rem, 4vw, 2.5rem);
			font-weight: 700;
			line-height: 1;
			color: var(--brand-dark-navy, #0B102C);
			font-family: Poppins, -apple-system, sans-serif;
		}
		.lrh-calendar-date-widget__day {
			font-size: clamp(0.7rem, 1.2vw, 0.85rem);
			font-weight: 500;
			color: var(--brand-slate, #64748b);
			margin-top: 4px;
			font-family: Poppins, -apple-system, sans-serif;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Daily Rates (Market Matters) widget
	 *
	 * Shows current 30-year and 15-year fixed mortgage rates.
	 * Fetches from API or uses cached data.
	 *
	 * Shortcode attributes:
	 * - title: Widget title (default: "Market Matters")
	 * - cache_hours: Cache duration in hours (default: 4)
	 *
	 * Example: [lrh_daily_rates_widget]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_daily_rates_widget( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'       => __( 'Market Matters', 'frs-lrg' ),
				'cache_hours' => 4,
			),
			$atts,
			'lrh_daily_rates_widget'
		);

		// Try to get cached rates
		$cache_key = 'lrh_mortgage_rates';
		$rates     = get_transient( $cache_key );

		if ( false === $rates ) {
			// Fetch from API
			$response = wp_remote_get(
				'https://api.api-ninjas.com/v1/mortgagerate',
				array(
					'headers' => array(
						'X-Api-Key' => 'TYgp30Q8LTuwp3KTbCku1Q==MFnAgH2amAue4QiZ',
					),
					'timeout' => 10,
				)
			);

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( ! empty( $body[0] ) ) {
					$rates = array(
						'frm_30' => $body[0]['data']['frm_30'] ?? '6.85',
						'frm_15' => $body[0]['data']['frm_15'] ?? '6.10',
						'week'   => $body[0]['data']['week'] ?? date( 'Y-m-d' ),
					);
					set_transient( $cache_key, $rates, absint( $atts['cache_hours'] ) * HOUR_IN_SECONDS );
				}
			}

			// Fallback if API fails
			if ( empty( $rates ) ) {
				$rates = array(
					'frm_30' => '6.85',
					'frm_15' => '6.10',
					'week'   => date( 'Y-m-d' ),
				);
			}
		}

		$date_display = date( 'M j', strtotime( $rates['week'] ) );

		ob_start();
		?>
		<div class="lrh-daily-rates-widget">
			<div class="lrh-daily-rates-widget__header">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
				<span><?php echo esc_html( $atts['title'] ); ?></span>
			</div>
			<div class="lrh-daily-rates-widget__rates">
				<div class="lrh-daily-rates-widget__rate">
					<div class="lrh-daily-rates-widget__rate-header">
						<span>30-Year Fixed</span>
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
					</div>
					<div class="lrh-daily-rates-widget__rate-value"><?php echo esc_html( number_format( (float) $rates['frm_30'], 2 ) ); ?>%</div>
					<div class="lrh-daily-rates-widget__rate-date"><?php echo esc_html( $date_display ); ?></div>
				</div>
				<div class="lrh-daily-rates-widget__rate">
					<div class="lrh-daily-rates-widget__rate-header">
						<span>15-Year Fixed</span>
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
					</div>
					<div class="lrh-daily-rates-widget__rate-value"><?php echo esc_html( number_format( (float) $rates['frm_15'], 2 ) ); ?>%</div>
					<div class="lrh-daily-rates-widget__rate-date"><?php echo esc_html( $date_display ); ?></div>
				</div>
			</div>
		</div>
		<style>
		.lrh-daily-rates-widget {
			border-radius: 12px;
			overflow: hidden;
			background: var(--gradient-hero, linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%));
			box-shadow: 0 4px 16px rgba(37,99,235,0.3), 0 2px 6px rgba(0,0,0,0.15);
			ring: 1px solid rgba(255,255,255,0.2);
			min-height: 240px;
		}
		.lrh-daily-rates-widget__header {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 12px 16px;
			border-bottom: 1px solid rgba(255,255,255,0.2);
			color: #ffffff;
			font-size: 14px;
			font-weight: 600;
		}
		.lrh-daily-rates-widget__rates {
			padding: 12px 16px;
			display: flex;
			flex-direction: column;
			gap: 12px;
		}
		.lrh-daily-rates-widget__rate {
			padding: 16px;
			background: rgba(255,255,255,0.1);
			border: 1px solid rgba(255,255,255,0.2);
			border-radius: 8px;
		}
		.lrh-daily-rates-widget__rate-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 4px;
			color: #ffffff;
			font-size: 14px;
			font-weight: 500;
		}
		.lrh-daily-rates-widget__rate-value {
			font-size: 1.875rem;
			font-weight: 700;
			color: #ffffff;
			line-height: 1.2;
		}
		.lrh-daily-rates-widget__rate-date {
			font-size: 12px;
			color: rgba(255,255,255,0.7);
			margin-top: 4px;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render Blog Posts widget
	 *
	 * Shows latest blog posts/announcements.
	 *
	 * Shortcode attributes:
	 * - title: Widget title (default: "Latest Updates")
	 * - count: Number of posts to show (default: 2)
	 * - category: Category slug to filter (optional)
	 *
	 * Example: [lrh_blog_posts_widget]
	 * Example: [lrh_blog_posts_widget count="3" category="announcements"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_blog_posts_widget( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'    => __( 'Latest Updates', 'frs-lrg' ),
				'count'    => 2,
				'category' => '',
			),
			$atts,
			'lrh_blog_posts_widget'
		);

		$query_args = array(
			'post_type'      => 'post',
			'posts_per_page' => absint( $atts['count'] ),
			'post_status'    => 'publish',
		);

		if ( ! empty( $atts['category'] ) ) {
			$query_args['category_name'] = sanitize_text_field( $atts['category'] );
		}

		$posts = get_posts( $query_args );

		ob_start();
		?>
		<div class="lrh-blog-posts-widget">
			<div class="lrh-blog-posts-widget__header">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
				<span><?php echo esc_html( $atts['title'] ); ?></span>
			</div>
			<div class="lrh-blog-posts-widget__content">
				<?php if ( ! empty( $posts ) ) : ?>
					<?php foreach ( $posts as $post ) : ?>
						<?php
						$author_id     = $post->post_author;
						$author_name   = get_the_author_meta( 'display_name', $author_id );
						$author_avatar = get_avatar_url( $author_id, array( 'size' => 96 ) );
						$post_date     = get_the_date( 'M Y', $post );
						$excerpt       = wp_trim_words( strip_tags( $post->post_excerpt ?: $post->post_content ), 20 );
						?>
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" target="_blank" class="lrh-blog-posts-widget__post">
							<h4 class="lrh-blog-posts-widget__post-title"><?php echo esc_html( $post->post_title ); ?></h4>
							<p class="lrh-blog-posts-widget__post-excerpt"><?php echo esc_html( $excerpt ); ?></p>
							<div class="lrh-blog-posts-widget__post-meta">
								<img src="<?php echo esc_url( $author_avatar ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" class="lrh-blog-posts-widget__post-avatar">
								<span class="lrh-blog-posts-widget__post-author"><?php echo esc_html( $author_name ); ?></span>
								<span class="lrh-blog-posts-widget__post-separator">•</span>
								<span class="lrh-blog-posts-widget__post-date"><?php echo esc_html( $post_date ); ?></span>
								<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lrh-blog-posts-widget__external-icon"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
							</div>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="lrh-blog-posts-widget__empty">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
						<p><?php _e( 'No updates available', 'frs-lrg' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<style>
		.lrh-blog-posts-widget {
			background: rgba(255,255,255,0.8);
			backdrop-filter: blur(8px);
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(0,0,0,0.06);
			border: 1px solid #e2e8f0;
		}
		.lrh-blog-posts-widget__header {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 12px 16px;
			border-bottom: 1px solid #f1f5f9;
			background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
			font-size: 14px;
			font-weight: 600;
			color: var(--brand-dark-navy, #0B102C);
		}
		.lrh-blog-posts-widget__header svg {
			color: var(--brand-primary-blue, #2563eb);
		}
		.lrh-blog-posts-widget__content {
			padding: 8px;
			max-height: 300px;
			overflow-y: auto;
		}
		.lrh-blog-posts-widget__post {
			display: block;
			padding: 12px;
			border-radius: 8px;
			text-decoration: none;
			transition: background 0.2s;
			margin-bottom: 8px;
		}
		.lrh-blog-posts-widget__post:last-child {
			margin-bottom: 0;
		}
		.lrh-blog-posts-widget__post:hover {
			background: #f8fafc;
		}
		.lrh-blog-posts-widget__post-title {
			margin: 0 0 4px;
			font-size: 13px;
			font-weight: 600;
			color: var(--brand-dark-navy, #0B102C);
			line-height: 1.4;
			display: -webkit-box;
			-webkit-line-clamp: 1;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
		.lrh-blog-posts-widget__post-excerpt {
			margin: 0 0 8px;
			font-size: 12px;
			color: var(--brand-slate, #64748b);
			line-height: 1.5;
			display: -webkit-box;
			-webkit-line-clamp: 1;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
		.lrh-blog-posts-widget__post-meta {
			display: flex;
			align-items: center;
			gap: 6px;
			font-size: 11px;
			color: var(--brand-slate, #64748b);
		}
		.lrh-blog-posts-widget__post-avatar {
			width: 16px;
			height: 16px;
			border-radius: 50%;
			border: 1px solid #e2e8f0;
		}
		.lrh-blog-posts-widget__post-author {
			font-weight: 500;
			color: var(--brand-dark-navy, #0B102C);
		}
		.lrh-blog-posts-widget__external-icon {
			margin-left: auto;
			color: var(--brand-slate, #64748b);
		}
		.lrh-blog-posts-widget__empty {
			text-align: center;
			padding: 32px 16px;
			color: var(--brand-slate, #64748b);
		}
		.lrh-blog-posts-widget__empty svg {
			margin-bottom: 8px;
			opacity: 0.5;
		}
		.lrh-blog-posts-widget__empty p {
			margin: 0;
			font-size: 13px;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render App Launcher widget
	 *
	 * Grid of tool shortcuts and external app links.
	 *
	 * Shortcode attributes:
	 * - show_tools: Show internal tools (default: true)
	 * - show_apps: Show external apps (default: true)
	 *
	 * Example: [lrh_app_launcher]
	 * Example: [lrh_app_launcher show_tools="true" show_apps="false"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string The rendered HTML.
	 */
	public function render_app_launcher_widget( $atts ) {
		$atts = shortcode_atts(
			array(
				'show_tools' => 'true',
				'show_apps'  => 'true',
			),
			$atts,
			'lrh_app_launcher'
		);

		$show_tools = $atts['show_tools'] === 'true';
		$show_apps  = $atts['show_apps'] === 'true';

		// Internal tools
		$tools = array(
			array(
				'id'    => 'mortgage-calculator',
				'title' => __( 'Mortgage Calculator', 'frs-lrg' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="18"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="12" y1="10" x2="12" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="12" y1="18" x2="12" y2="18.01"/></svg>',
				'color' => '#2563eb',
				'url'   => home_url( '/tools/mortgage-calculator/' ),
			),
			array(
				'id'    => 'property-valuation',
				'title' => __( 'Property Valuation', 'frs-lrg' ),
				'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
				'color' => '#0B102C',
				'url'   => home_url( '/tools/property-valuation/' ),
			),
		);

		// External apps
		$apps = array(
			array(
				'id'    => 'outlook',
				'title' => __( 'Outlook', 'frs-lrg' ),
				'image' => plugins_url( 'icons8-outlook.svg', FRS_LRG_PLUGIN_FILE ),
				'url'   => 'https://outlook.office.com/',
			),
			array(
				'id'    => 'arive',
				'title' => __( 'Arive', 'frs-lrg' ),
				'image' => plugins_url( 'assets/images/Arive-Highlight-Logo - 01.webp', FRS_LRG_PLUGIN_FILE ),
				'url'   => 'https://app.arive.com/login',
			),
			array(
				'id'    => 'fub',
				'title' => __( 'Follow Up Boss', 'frs-lrg' ),
				'image' => plugins_url( 'assets/images/FUB LOG.webp', FRS_LRG_PLUGIN_FILE ),
				'url'   => 'https://app.followupboss.com/login',
			),
		);

		ob_start();
		?>
		<div class="lrh-app-launcher">
			<?php if ( $show_tools ) : ?>
				<?php foreach ( $tools as $tool ) : ?>
					<a href="<?php echo esc_url( $tool['url'] ); ?>" class="lrh-app-launcher__item">
						<div class="lrh-app-launcher__icon" style="color: <?php echo esc_attr( $tool['color'] ); ?>;">
							<?php echo $tool['icon']; ?>
						</div>
						<span class="lrh-app-launcher__title"><?php echo esc_html( $tool['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( $show_apps ) : ?>
				<?php foreach ( $apps as $app ) : ?>
					<a href="<?php echo esc_url( $app['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="lrh-app-launcher__item">
						<div class="lrh-app-launcher__image">
							<img src="<?php echo esc_url( $app['image'] ); ?>" alt="<?php echo esc_attr( $app['title'] ); ?>">
						</div>
						<span class="lrh-app-launcher__title"><?php echo esc_html( $app['title'] ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<style>
		.lrh-app-launcher {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
			gap: 8px;
		}
		.lrh-app-launcher__item {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			padding: 16px 8px;
			background: #ffffff;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(0,0,0,0.06);
			border: 1px solid #e2e8f0;
			text-decoration: none;
			transition: all 0.2s;
		}
		.lrh-app-launcher__item:hover {
			background: #f8fafc;
			border-color: #cbd5e1;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
		}
		.lrh-app-launcher__icon {
			width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 8px;
		}
		.lrh-app-launcher__image {
			width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 8px;
		}
		.lrh-app-launcher__image img {
			max-width: 100%;
			max-height: 100%;
			object-fit: contain;
		}
		.lrh-app-launcher__title {
			font-size: 11px;
			font-weight: 500;
			color: var(--brand-dark-navy, #0B102C);
			text-align: center;
			line-height: 1.3;
		}
		</style>
		<?php
		return ob_get_clean();
	}

}
