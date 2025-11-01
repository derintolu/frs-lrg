<?php
/**
 * Admin Dashboard Template
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Lending Resource Hub Dashboard', 'lending-resource-hub' ); ?></h1>

	<!-- Quick Stats -->
	<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
		<div style="background: #fff; padding: 20px; border-left: 4px solid #2271b1; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h3 style="margin: 0 0 10px 0; font-size: 32px; color: #2271b1;"><?php echo esc_html( $active_partnerships ); ?></h3>
			<p style="margin: 0; color: #646970;"><?php esc_html_e( 'Active Partnerships', 'lending-resource-hub' ); ?></p>
		</div>
		<div style="background: #fff; padding: 20px; border-left: 4px solid #f0b849; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h3 style="margin: 0 0 10px 0; font-size: 32px; color: #f0b849;"><?php echo esc_html( $pending_partnerships ); ?></h3>
			<p style="margin: 0; color: #646970;"><?php esc_html_e( 'Pending Invitations', 'lending-resource-hub' ); ?></p>
		</div>
		<div style="background: #fff; padding: 20px; border-left: 4px solid #00a32a; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h3 style="margin: 0 0 10px 0; font-size: 32px; color: #00a32a;"><?php echo esc_html( $total_leads ); ?></h3>
			<p style="margin: 0; color: #646970;"><?php esc_html_e( 'Total Leads', 'lending-resource-hub' ); ?></p>
		</div>
		<div style="background: #fff; padding: 20px; border-left: 4px solid #72aee6; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
			<h3 style="margin: 0 0 10px 0; font-size: 32px; color: #72aee6;"><?php echo esc_html( $recent_leads ); ?></h3>
			<p style="margin: 0; color: #646970;"><?php esc_html_e( 'Leads This Week', 'lending-resource-hub' ); ?></p>
		</div>
	</div>

	<!-- Quick Actions -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Quick Actions', 'lending-resource-hub' ); ?></h2>
		<div style="display: flex; gap: 10px; flex-wrap: wrap;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-partnerships' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Manage Partnerships', 'lending-resource-hub' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-integrations' ) ); ?>" class="button">
				<?php esc_html_e( 'Setup Integrations', 'lending-resource-hub' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-bulk-invites' ) ); ?>" class="button">
				<?php esc_html_e( 'Bulk Invites', 'lending-resource-hub' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-settings' ) ); ?>" class="button">
				<?php esc_html_e( 'Plugin Settings', 'lending-resource-hub' ); ?>
			</a>
		</div>
	</div>

	<!-- User Overview -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'User Overview', 'lending-resource-hub' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Role', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Count', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'lending-resource-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Loan Officers', 'lending-resource-hub' ); ?></td>
					<td><?php echo esc_html( $loan_officers_count ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'users.php?role=loan_officer' ) ); ?>" class="button button-small">
							<?php esc_html_e( 'View Users', 'lending-resource-hub' ); ?>
						</a>
					</td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Realtor Partners', 'lending-resource-hub' ); ?></td>
					<td><?php echo esc_html( $agents_count ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'users.php?role=realtor_partner' ) ); ?>" class="button button-small">
							<?php esc_html_e( 'View Users', 'lending-resource-hub' ); ?>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- Shortcode Helper -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Frontend Portal Shortcode', 'lending-resource-hub' ); ?></h2>
		<p><?php esc_html_e( 'Use this shortcode to display the portal on any page:', 'lending-resource-hub' ); ?></p>
		<input type="text" value="[lrh_portal]" readonly style="width: 200px; padding: 8px; font-family: monospace;" onclick="this.select();">
		<p class="description">
			<?php esc_html_e( 'Click the shortcode above to select and copy it. Paste it into any page where you want the portal to appear.', 'lending-resource-hub' ); ?>
		</p>
	</div>

	<!-- Setup Checklist -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Setup Checklist', 'lending-resource-hub' ); ?></h2>
		<ul style="list-style: none; padding-left: 0;">
			<li style="padding: 8px 0;">
				<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
				<?php esc_html_e( 'Plugin installed and activated', 'lending-resource-hub' ); ?>
			</li>
			<li style="padding: 8px 0;">
				<span class="dashicons dashicons-marker" style="color: #f0b849;"></span>
				<?php esc_html_e( 'Configure integrations', 'lending-resource-hub' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-integrations' ) ); ?>" class="button button-small" style="margin-left: 10px;">
					<?php esc_html_e( 'Configure', 'lending-resource-hub' ); ?>
				</a>
			</li>
			<li style="padding: 8px 0;">
				<span class="dashicons dashicons-marker" style="color: #f0b849;"></span>
				<?php esc_html_e( 'Create a portal page with [lrh_portal] shortcode', 'lending-resource-hub' ); ?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="button button-small" style="margin-left: 10px;">
					<?php esc_html_e( 'Create Page', 'lending-resource-hub' ); ?>
				</a>
			</li>
			<li style="padding: 8px 0;">
				<span class="dashicons dashicons-marker" style="color: #f0b849;"></span>
				<?php esc_html_e( 'Send partnership invitations', 'lending-resource-hub' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=lrh-partnerships' ) ); ?>" class="button button-small" style="margin-left: 10px;">
					<?php esc_html_e( 'Manage', 'lending-resource-hub' ); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
