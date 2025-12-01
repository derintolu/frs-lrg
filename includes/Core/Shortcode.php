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
		// New shortcodes
		add_shortcode( 'lrh_portal', array( $this, 'render_portal' ) );
		add_shortcode( 'lrh_portal_sidebar', array( $this, 'render_portal_sidebar' ) );
		add_shortcode( 'lrh_welcome_portal', array( $this, 'render_welcome_portal' ) );
		add_shortcode( 'lrh_partnerships_section', array( $this, 'render_partnerships_section' ) );
		add_shortcode( 'lrh_realtor_portal', array( $this, 'render_realtor_portal' ) );
		add_shortcode( 'frs_mortgage_calculator', array( $this, 'render_mortgage_calculator' ) );

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

		// Individual dashboard section cards (standalone components)
		add_shortcode( 'lrh_booking_calendar_card', array( $this, 'render_booking_calendar_card' ) );
		add_shortcode( 'lrh_landing_pages_card', array( $this, 'render_landing_pages_card' ) );
		add_shortcode( 'lrh_brand_guide_card', array( $this, 'render_brand_guide_card' ) );
		add_shortcode( 'lrh_print_social_media_card', array( $this, 'render_print_social_media_card' ) );

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

		// Legacy shortcode from old plugin (backward compatibility)
		add_shortcode( 'frs_partnership_portal', array( $this, 'render_legacy_portal' ) );
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

		// Return root element for React to mount
		return '<div id="lrh-portal-root"></div>';
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

		// Return ONLY the container div
		// Frontend.php handles ALL asset loading and configuration
		// when it detects this shortcode via should_load_portal()
		return '<div id="lrh-portal-sidebar-root" data-lrh-component="portal-sidebar"></div>';
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

		// Return root element for React to mount
		return '<div id="lrh-realtor-portal-root"></div>';
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
	 * Content-only shortcode renderers
	 * These render just the page content without the portal sidebar
	 * For use in portal frames that provide their own sidebar
	 */

	public function render_content_welcome( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_welcome_portal_assets();
		return '<div id="lrh-welcome-portal-root" data-content-only="true"></div>';
	}

	public function render_content_profile( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-profile-root" data-lrh-content="profile"></div>';
	}

	public function render_content_marketing( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-marketing-root" data-lrh-content="marketing"></div>';
	}

	public function render_content_calendar( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-calendar-root" data-lrh-content="calendar"></div>';
	}

	public function render_content_landing_pages( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-landing-pages-root" data-lrh-content="landing-pages"></div>';
	}

	public function render_content_email_campaigns( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-email-campaigns-root" data-lrh-content="email-campaigns"></div>';
	}

	public function render_content_local_seo( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-local-seo-root" data-lrh-content="local-seo"></div>';
	}

	public function render_content_brand_guide( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-brand-guide-root" data-lrh-content="brand-guide"></div>';
	}

	public function render_content_orders( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-orders-root" data-lrh-content="orders"></div>';
	}

	public function render_content_lead_tracking( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-lead-tracking-root" data-lrh-content="lead-tracking"></div>';
	}

	public function render_content_tools( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-tools-root" data-lrh-content="tools"></div>';
	}

	public function render_content_settings( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-settings-root" data-lrh-content="settings"></div>';
	}

	public function render_content_notifications( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-content-notifications-root" data-lrh-content="notifications"></div>';
	}

	/**
	 * Individual dashboard section card shortcodes
	 */

	public function render_booking_calendar_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-booking-calendar-card-root" data-lrh-card="booking-calendar"></div>';
	}

	public function render_landing_pages_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-landing-pages-card-root" data-lrh-card="landing-pages"></div>';
	}

	public function render_brand_guide_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-brand-guide-card-root" data-lrh-card="brand-guide"></div>';
	}

	public function render_print_social_media_card( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_dashboard_cards_assets();
		return '<div id="lrh-print-social-media-card-root" data-lrh-card="print-social-media"></div>';
	}

	/**
	 * Individual page shortcodes (full pages from portal routes)
	 */

	public function render_marketing_overview_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-overview-root" data-lrh-page="marketing-overview"></div>';
	}

	public function render_my_profile_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-my-profile-root" data-lrh-page="my-profile"></div>';
	}

	public function render_lead_tracking_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-lead-tracking-root" data-lrh-page="lead-tracking"></div>';
	}

	public function render_fluent_booking_calendar_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-fluent-booking-calendar-root" data-lrh-page="fluent-booking-calendar"></div>';
	}

	public function render_landing_pages_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-landing-pages-page-root" data-lrh-page="landing-pages"></div>';
	}

	public function render_email_campaigns_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-email-campaigns-root" data-lrh-page="email-campaigns"></div>';
	}

	public function render_local_seo_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-local-seo-root" data-lrh-page="local-seo"></div>';
	}

	public function render_brand_showcase_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-brand-showcase-root" data-lrh-page="brand-showcase"></div>';
	}

	public function render_marketing_orders_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-orders-root" data-lrh-page="marketing-orders"></div>';
	}

	public function render_mortgage_calculator_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-mortgage-calculator-page-root" data-lrh-page="mortgage-calculator"></div>';
	}

	public function render_property_valuation_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-property-valuation-root" data-lrh-page="property-valuation"></div>';
	}

	public function render_settings_page( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-settings-root" data-lrh-page="settings"></div>';
	}

	/**
	 * Render marketing subnavigation panel
	 */
	public function render_marketing_subnav( $atts ) {
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
		return '<div id="lrh-marketing-subnav-root" data-lrh-subnav="marketing"></div>';
	}

}
