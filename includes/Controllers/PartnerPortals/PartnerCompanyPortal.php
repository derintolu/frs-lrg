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
	}

	/**
	 * Render partner company portal shortcode.
	 *
	 * Shows hybrid interface for managing a specific partner company group.
	 *
	 * ## Usage:
	 * [partner_company_portal group_id="1"]
	 * [partner_company_portal slug="century-21-professionals-in-michigan"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML.
	 */
	public function render_portal_shortcode( $atts ) {
		// Check dependencies
		if ( ! function_exists( 'groups_get_group' ) ) {
			return '<p>' . __( 'BuddyPress Groups is not active.', 'lending-resource-hub' ) . '</p>';
		}

		// Parse attributes
		$atts = shortcode_atts(
			array(
				'group_id' => 0,
				'slug'     => '',
			),
			$atts,
			'partner_company_portal'
		);

		$group_id = absint( $atts['group_id'] );
		$slug     = sanitize_text_field( $atts['slug'] );

		// Get group
		if ( $slug ) {
			$group_id = groups_get_id( $slug );
		}

		if ( ! $group_id ) {
			return '<p>' . __( 'Partner company not found.', 'lending-resource-hub' ) . '</p>';
		}

		$group = groups_get_group( $group_id );
		if ( ! $group || ! $group->id ) {
			return '<p>' . __( 'Partner company not found.', 'lending-resource-hub' ) . '</p>';
		}

		// Check if this is a partner-org group
		$group_type = bp_groups_get_group_type( $group_id );
		if ( $group_type !== 'partner-org' ) {
			return '<p>' . __( 'This is not a partner company group.', 'lending-resource-hub' ) . '</p>';
		}

		// Check if user has access (must be a member)
		$user_id = get_current_user_id();
		if ( ! $user_id || ! groups_is_user_member( $user_id, $group_id ) ) {
			return '<p>' . __( 'You do not have access to this partner company.', 'lending-resource-hub' ) . '</p>';
		}

		// Get user's role in the group
		$is_admin = groups_is_user_admin( $user_id, $group_id );
		$is_mod   = groups_is_user_mod( $user_id, $group_id );
		$role     = $is_admin ? 'admin' : ( $is_mod ? 'mod' : 'member' );

		// Get group branding
		$branding = array(
			'primary_color'   => groups_get_groupmeta( $group_id, 'pp_primary_color' ) ?: '#2563eb',
			'secondary_color' => groups_get_groupmeta( $group_id, 'pp_secondary_color' ) ?: '#2dd4da',
			'button_style'    => groups_get_groupmeta( $group_id, 'pp_button_style' ) ?: 'rounded',
		);

		// Enqueue assets (React app will be loaded separately)
		wp_enqueue_script( 'frs-lrg-portal' );
		wp_enqueue_style( 'frs-lrg-portal' );

		// Return React mount point - data will be fetched via REST API
		ob_start();
		?>
		<div
			id="frs-partner-company-portal-<?php echo esc_attr( $group_id ); ?>"
			class="frs-partner-company-portal-root"
			data-group-id="<?php echo esc_attr( $group_id ); ?>"
			data-group-slug="<?php echo esc_attr( $group->slug ); ?>"
			data-group-name="<?php echo esc_attr( $group->name ); ?>"
			data-user-id="<?php echo esc_attr( $user_id ); ?>"
			data-user-role="<?php echo esc_attr( $role ); ?>"
			data-branding="<?php echo esc_attr( wp_json_encode( $branding ) ); ?>"
		>
			<p><?php esc_html_e( 'Loading partner company portal...', 'lending-resource-hub' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
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
