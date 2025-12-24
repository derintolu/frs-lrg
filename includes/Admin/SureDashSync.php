<?php
/**
 * SureDash Profile Photo Sync
 *
 * Syncs profile photos from frs-wp-users to SureDash user meta.
 *
 * @package LendingResourceHub\Admin
 * @since 1.0.0
 */

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class SureDashSync
 *
 * Handles syncing profile photos between frs-wp-users and SureDash.
 *
 * @package LendingResourceHub\Admin
 */
class SureDashSync {

	use Base;

	/**
	 * Initialize SureDash sync functionality.
	 *
	 * @return void
	 */
	public function init() {
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register AJAX handlers
		add_action( 'wp_ajax_lrh_sync_suredash_photos', array( $this, 'ajax_sync_photos' ) );
	}

	/**
	 * Add admin menu page.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Sync SureDash Photos', 'lending-resource-hub' ),
			__( 'Sync SureDash Photos', 'lending-resource-hub' ),
			'manage_options',
			'lrh-suredash-sync',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sync Profile Photos to SureDash', 'lending-resource-hub' ); ?></h1>

			<div class="card" style="max-width: 800px;">
				<h2><?php esc_html_e( 'Sync User Profile Photos', 'lending-resource-hub' ); ?></h2>
				<p><?php esc_html_e( 'This tool syncs profile photos from the FRS Users plugin to SureDash user meta. It will update the SureDash profile photo for all users who have a headshot in their FRS profile.', 'lending-resource-hub' ); ?></p>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="sync-roles"><?php esc_html_e( 'User Roles to Sync', 'lending-resource-hub' ); ?></label>
						</th>
						<td>
							<select id="sync-roles" name="sync_roles[]" multiple style="width: 300px; height: 150px;">
								<option value="loan_officer" selected><?php esc_html_e( 'Loan Officers', 'lending-resource-hub' ); ?></option>
								<option value="realtor_partner" selected><?php esc_html_e( 'Realtor Partners', 'lending-resource-hub' ); ?></option>
								<option value="staff"><?php esc_html_e( 'Staff', 'lending-resource-hub' ); ?></option>
								<option value="leadership"><?php esc_html_e( 'Leadership', 'lending-resource-hub' ); ?></option>
								<option value="administrator"><?php esc_html_e( 'Administrators', 'lending-resource-hub' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Hold Ctrl (Cmd on Mac) to select multiple roles.', 'lending-resource-hub' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label>
								<input type="checkbox" id="overwrite-existing" name="overwrite_existing" value="1" />
								<?php esc_html_e( 'Overwrite Existing Photos', 'lending-resource-hub' ); ?>
							</label>
						</th>
						<td>
							<p class="description"><?php esc_html_e( 'If checked, will overwrite existing SureDash profile photos. If unchecked, only syncs users without a SureDash photo.', 'lending-resource-hub' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="button" id="sync-photos-btn" class="button button-primary button-large">
						<?php esc_html_e( 'Sync Profile Photos', 'lending-resource-hub' ); ?>
					</button>
				</p>

				<div id="sync-results" style="display: none; margin-top: 20px;">
					<h3><?php esc_html_e( 'Sync Results', 'lending-resource-hub' ); ?></h3>
					<div id="sync-progress" style="background: #f0f0f1; padding: 15px; border-radius: 4px;">
						<p id="sync-message"></p>
					</div>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('#sync-photos-btn').on('click', function() {
				const btn = $(this);
				const selectedRoles = $('#sync-roles').val();
				const overwrite = $('#overwrite-existing').is(':checked');

				if (!selectedRoles || selectedRoles.length === 0) {
					alert('<?php esc_html_e( 'Please select at least one user role to sync.', 'lending-resource-hub' ); ?>');
					return;
				}

				btn.prop('disabled', true).text('<?php esc_html_e( 'Syncing...', 'lending-resource-hub' ); ?>');
				$('#sync-results').show();
				$('#sync-message').html('<span class="spinner is-active" style="float: none; margin: 0 10px 0 0;"></span><?php esc_html_e( 'Syncing profile photos...', 'lending-resource-hub' ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'lrh_sync_suredash_photos',
						nonce: '<?php echo esc_js( wp_create_nonce( 'lrh_sync_suredash_photos' ) ); ?>',
						roles: selectedRoles,
						overwrite: overwrite ? 1 : 0
					},
					success: function(response) {
						if (response.success) {
							$('#sync-message').html(
								'<p style="color: #46b450; font-weight: 600;">✓ ' + response.data.message + '</p>' +
								'<ul style="margin: 10px 0;">' +
								'<li><strong><?php esc_html_e( 'Total Users Checked:', 'lending-resource-hub' ); ?></strong> ' + response.data.stats.total_checked + '</li>' +
								'<li><strong><?php esc_html_e( 'Photos Synced:', 'lending-resource-hub' ); ?></strong> ' + response.data.stats.synced + '</li>' +
								'<li><strong><?php esc_html_e( 'Skipped (No Photo):', 'lending-resource-hub' ); ?></strong> ' + response.data.stats.skipped_no_photo + '</li>' +
								'<li><strong><?php esc_html_e( 'Skipped (Already Has Photo):', 'lending-resource-hub' ); ?></strong> ' + response.data.stats.skipped_existing + '</li>' +
								'</ul>'
							);
						} else {
							$('#sync-message').html('<p style="color: #dc3232;">✗ ' + response.data + '</p>');
						}
						btn.prop('disabled', false).text('<?php esc_html_e( 'Sync Profile Photos', 'lending-resource-hub' ); ?>');
					},
					error: function(xhr, status, error) {
						$('#sync-message').html('<p style="color: #dc3232;">✗ <?php esc_html_e( 'Error:', 'lending-resource-hub' ); ?> ' + error + '</p>');
						btn.prop('disabled', false).text('<?php esc_html_e( 'Sync Profile Photos', 'lending-resource-hub' ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for syncing photos.
	 *
	 * @return void
	 */
	public function ajax_sync_photos() {
		// Check nonce
		check_ajax_referer( 'lrh_sync_suredash_photos', 'nonce' );

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'lending-resource-hub' ) );
		}

		// Get parameters
		$roles     = isset( $_POST['roles'] ) ? (array) $_POST['roles'] : array();
		$overwrite = isset( $_POST['overwrite'] ) && $_POST['overwrite'] === '1';

		if ( empty( $roles ) ) {
			wp_send_json_error( __( 'No roles selected.', 'lending-resource-hub' ) );
		}

		// Sanitize roles
		$roles = array_map( 'sanitize_text_field', $roles );

		// Perform sync
		$result = $this->sync_profile_photos( $roles, $overwrite );

		if ( $result['success'] ) {
			wp_send_json_success(
				array(
					'message' => __( 'Profile photos synced successfully!', 'lending-resource-hub' ),
					'stats'   => $result['stats'],
				)
			);
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * Sync profile photos from frs-wp-users to SureDash.
	 *
	 * @param array $roles User roles to sync.
	 * @param bool  $overwrite Whether to overwrite existing SureDash photos.
	 * @return array Result with success status and stats.
	 */
	private function sync_profile_photos( $roles, $overwrite = false ) {
		$stats = array(
			'total_checked'      => 0,
			'synced'             => 0,
			'skipped_no_photo'   => 0,
			'skipped_existing'   => 0,
			'skipped_no_profile' => 0,
		);

		// Check if frs-wp-users Profile model exists
		if ( ! class_exists( 'FRSUsers\Models\Profile' ) ) {
			return array(
				'success' => false,
				'message' => __( 'FRS Users plugin is not active or Profile model not found.', 'lending-resource-hub' ),
			);
		}

		// Get users with specified roles
		$users = get_users(
			array(
				'role__in' => $roles,
				'fields'   => 'ID',
			)
		);

		if ( empty( $users ) ) {
			return array(
				'success' => false,
				'message' => __( 'No users found with the selected roles.', 'lending-resource-hub' ),
			);
		}

		foreach ( $users as $user_id ) {
			$stats['total_checked']++;

			// Get FRS profile
			$profile = \FRSUsers\Models\Profile::where( 'user_id', $user_id )->first();

			if ( ! $profile || ! $profile->headshot_id ) {
				$stats['skipped_no_photo']++;
				continue;
			}

			// Check if user already has a SureDash photo
			$existing_photo = get_user_meta( $user_id, 'user_profile_photo', true );

			if ( ! $overwrite && ! empty( $existing_photo ) ) {
				$stats['skipped_existing']++;
				continue;
			}

			// Get image URL from attachment ID
			$image_url = wp_get_attachment_url( $profile->headshot_id );

			if ( ! $image_url ) {
				$stats['skipped_no_photo']++;
				continue;
			}

			// Update SureDash profile photo
			update_user_meta( $user_id, 'user_profile_photo', $image_url );
			$stats['synced']++;
		}

		return array(
			'success' => true,
			'stats'   => $stats,
		);
	}
}
