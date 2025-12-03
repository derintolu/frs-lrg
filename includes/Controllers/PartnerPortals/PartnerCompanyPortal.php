<?php
/**
 * Partner Company Portal
 *
 * Interface for loan officers to manage partner companies.
 * Note: BuddyPress integration removed - now uses custom post types.
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\PartnerPortals;

use LendingResourceHub\Traits\Base;

/**
 * Class PartnerCompanyPortal
 *
 * Creates a hybrid React/BP interface for partner company management.
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
 */
class PartnerCompanyPortal {

	use Base;

	/**
	 * Initialize partner company portal.
	 *
	 * @return void
	 */
	public function init() {
		// Register shortcode for partner company portal
		add_shortcode( 'partner_company_portal', array( $this, 'render_portal_shortcode' ) );

		// Register shortcode for group list
		add_shortcode( 'partner_company_list', array( $this, 'render_list_shortcode' ) );

		// Auto-inject portal on frs_re_portal pages
		add_filter( 'the_content', array( $this, 'auto_inject_portal' ), 10 );

		// Build hierarchical permalinks for frs_re_portal
		add_filter( 'post_type_link', array( $this, 'build_hierarchical_permalink' ), 10, 2 );

		// Register Carbon Fields for organization branding
		add_action( 'carbon_fields_register_fields', array( $this, 'register_organization_branding' ) );
	}

	/**
	 * Build hierarchical permalink for frs_re_portal posts.
	 *
	 * Generates URLs like /re/organization-name/page-name/
	 *
	 * @param string   $permalink The post's permalink.
	 * @param \WP_Post $post      The post object.
	 * @return string Modified permalink.
	 */
	public function build_hierarchical_permalink( $permalink, $post ) {
		// Only apply to frs_re_portal post type
		if ( $post->post_type !== 'frs_re_portal' ) {
			return $permalink;
		}

		// If no parent, return default permalink
		if ( ! $post->post_parent ) {
			return $permalink;
		}

		// Build hierarchical path
		$slug_path = array( $post->post_name );
		$parent_id = $post->post_parent;

		// Walk up the parent tree
		while ( $parent_id ) {
			$parent = get_post( $parent_id );
			if ( ! $parent ) {
				break;
			}
			array_unshift( $slug_path, $parent->post_name );
			$parent_id = $parent->post_parent;
		}

		// Build the full URL
		$hierarchical_path = implode( '/', $slug_path );
		return home_url( "/re/{$hierarchical_path}/" );
	}

	/**
	 * Auto-inject portal on frs_re_portal pages.
	 *
	 * @param string $content Post content.
	 * @return string Modified content with portal injected.
	 */
	public function auto_inject_portal( $content ) {
		// Only run on singular frs_re_portal pages in the main query
		if ( ! is_singular( 'frs_re_portal' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Get the portal root element
		$portal_html = $this->render_portal_root();

		// Inject portal before content
		return $portal_html . $content;
	}

	/**
	 * Render partner company portal root element.
	 *
	 * Enqueues realtor portal React app and returns mount point.
	 *
	 * @return string Rendered HTML.
	 */
	private function render_portal_root() {
		// Enqueue realtor portal assets
		\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_realtor_portal_assets();

		// Get current post info for company context
		global $post;
		$company_slug = $post->post_name ?? '';
		$company_id = $post->ID ?? '';
		$company_name = $post->post_title ?? '';

		// Get organization branding (look for parent with branding, or use current)
		$branding_post_id = $post->post_parent ? $post->post_parent : $post->ID;
		$branding = array(
			'primaryColor'     => carbon_get_post_meta( $branding_post_id, 're_primary_color' ) ?: '#2563eb',
			'secondaryColor'   => carbon_get_post_meta( $branding_post_id, 're_secondary_color' ) ?: '#2dd4da',
			'customLogo'       => wp_get_attachment_image_url( carbon_get_post_meta( $branding_post_id, 're_custom_logo' ), 'full' ) ?: '',
			'headerBackground' => wp_get_attachment_url( carbon_get_post_meta( $branding_post_id, 're_header_background' ) ) ?: '',
			'companyName'      => carbon_get_post_meta( $branding_post_id, 're_company_name' ) ?: $company_name,
		);

		// Return React mount point
		ob_start();
		?>
		<div
			id="lrh-realtor-portal-root"
			data-wp-interactive="lrh-portal"
			data-wp-router-region="lrh-realtor-portal"
			data-company-slug="<?php echo esc_attr( $company_slug ); ?>"
			data-company-id="<?php echo esc_attr( $company_id ); ?>"
			data-company-name="<?php echo esc_attr( $branding['companyName'] ); ?>"
			data-branding="<?php echo esc_attr( wp_json_encode( $branding ) ); ?>"
		></div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render partner company portal shortcode.
	 *
	 * Shows React-based partner company portal.
	 *
	 * ## Usage:
	 * [partner_company_portal]
	 * [partner_company_portal post_id="123"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_portal_shortcode( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'post_id' => get_the_ID(),
			),
			$atts,
			'partner_company_portal'
		);

		// If post_id specified, set up post data
		if ( $atts['post_id'] && $atts['post_id'] != get_the_ID() ) {
			$old_post = $GLOBALS['post'];
			$GLOBALS['post'] = get_post( $atts['post_id'] );
			setup_postdata( $GLOBALS['post'] );
			$html = $this->render_portal_root();
			$GLOBALS['post'] = $old_post;
			setup_postdata( $old_post );
			return $html;
		}

		return $this->render_portal_root();
	}

	/**
	 * Register Carbon Fields for organization branding.
	 *
	 * Allows each RE organization to customize:
	 * - Primary and secondary brand colors
	 * - Custom logo
	 * - Header background
	 * - Button styles
	 *
	 * @return void
	 */
	public function register_organization_branding() {
		if ( ! class_exists( '\Carbon_Fields\Container' ) ) {
			return;
		}

		\Carbon_Fields\Container::make( 'post_meta', __( 'Organization Branding', 'lending-resource-hub' ) )
			->where( 'post_type', '=', 'frs_re_portal' )
			->add_fields(
				array(
					\Carbon_Fields\Field::make( 'color', 're_primary_color', __( 'Primary Brand Color', 'lending-resource-hub' ) )
						->set_default_value( '#2563eb' )
						->set_help_text( __( 'Main color used for sidebar, buttons and accents', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'color', 're_secondary_color', __( 'Secondary Brand Color', 'lending-resource-hub' ) )
						->set_default_value( '#2dd4da' )
						->set_help_text( __( 'Secondary color used for gradients and highlights', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'image', 're_custom_logo', __( 'Custom Logo', 'lending-resource-hub' ) )
						->set_help_text( __( 'Upload a custom logo for this organization', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'file', 're_header_background', __( 'Header Background Image/Video', 'lending-resource-hub' ) )
						->set_type( 'video, image' )
						->set_help_text( __( 'Upload a background image or video (.mp4) for the sidebar header', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'text', 're_company_name', __( 'Company Display Name', 'lending-resource-hub' ) )
						->set_help_text( __( 'Full company name (e.g., "Century 21 Downtown")', 'lending-resource-hub' ) ),
				)
			);
	}

	/**
	 * Render partner company list shortcode.
	 *
	 * Shows list of all partner companies user is a member of.
	 *
	 * ## Usage:
	 * [partner_company_list]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_list_shortcode( $atts ) {
		// Check dependencies
		if ( ! function_exists( 'groups_get_user_groups' ) ) {
			return '<p>' . __( 'BuddyPress Groups is not active.', 'lending-resource-hub' ) . '</p>';
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '<p>' . __( 'You must be logged in to view partner companies.', 'lending-resource-hub' ) . '</p>';
		}

		// Return React mount point
		ob_start();
		?>
		<div
			id="frs-partner-company-list-<?php echo esc_attr( $user_id ); ?>"
			class="frs-partner-company-list-root"
			data-user-id="<?php echo esc_attr( $user_id ); ?>"
		>
			<p><?php esc_html_e( 'Loading partner companies...', 'lending-resource-hub' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Check if user can manage a partner company.
	 *
	 * User can manage if they are an admin or mod of the group.
	 *
	 * @param int $group_id Group ID.
	 * @param int $user_id User ID (defaults to current user).
	 * @return bool True if user can manage.
	 */
	public static function can_user_manage( $group_id, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		// Admins and mods can manage
		return groups_is_user_admin( $user_id, $group_id ) || groups_is_user_mod( $user_id, $group_id );
	}
}
