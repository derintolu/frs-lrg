<?php
/**
 * System Diagnostic Template
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'System Diagnostics', 'lending-resource-hub' ); ?></h1>
	<p><?php esc_html_e( 'Validate plugin installation and component status', 'lending-resource-hub' ); ?></p>

	<!-- Health Score -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'System Health', 'lending-resource-hub' ); ?></h2>
		<div style="display: flex; align-items: center; gap: 20px;">
			<div style="position: relative; width: 120px; height: 120px;">
				<svg width="120" height="120" viewBox="0 0 120 120">
					<circle cx="60" cy="60" r="50" fill="none" stroke="#e5e7eb" stroke-width="10"></circle>
					<circle
						cx="60"
						cy="60"
						r="50"
						fill="none"
						stroke="<?php echo $health_score >= 80 ? '#00a32a' : ( $health_score >= 50 ? '#f0b849' : '#d63638' ); ?>"
						stroke-width="10"
						stroke-dasharray="<?php echo ( $health_score / 100 ) * 314; ?> 314"
						stroke-linecap="round"
						transform="rotate(-90 60 60)"
					></circle>
				</svg>
				<div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
					<strong style="font-size: 28px;"><?php echo esc_html( $health_score ); ?>%</strong>
				</div>
			</div>
			<div>
				<h3 style="margin: 0 0 10px 0;">
					<?php
					if ( $health_score >= 80 ) {
						esc_html_e( 'Excellent', 'lending-resource-hub' );
					} elseif ( $health_score >= 50 ) {
						esc_html_e( 'Fair', 'lending-resource-hub' );
					} else {
						esc_html_e( 'Needs Attention', 'lending-resource-hub' );
					}
					?>
				</h3>
				<p style="margin: 0; color: #646970;">
					<?php esc_html_e( $health_score >= 80 ? 'All core components are functioning normally' : 'Some components need attention', 'lending-resource-hub' ); ?>
				</p>
			</div>
		</div>
	</div>

	<!-- Shortcodes -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Available Shortcodes', 'lending-resource-hub' ); ?></h2>
		<p><?php esc_html_e( 'Copy these shortcodes to use the portal on frontend pages', 'lending-resource-hub' ); ?></p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Description', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Status', 'lending-resource-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $lrh_shortcodes as $shortcode ) : ?>
					<tr>
						<td>
							<input type="text" value="<?php echo esc_attr( $shortcode['name'] ); ?>" readonly style="padding: 4px 8px; font-family: monospace; width: 250px;" onclick="this.select();">
						</td>
						<td><?php echo esc_html( $shortcode['description'] ); ?></td>
						<td>
							<?php if ( $shortcode['registered'] ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
								<span style="color: #00a32a;"><?php esc_html_e( 'Registered', 'lending-resource-hub' ); ?></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
								<span style="color: #d63638;"><?php esc_html_e( 'Not Registered', 'lending-resource-hub' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- Database Tables -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Database Tables', 'lending-resource-hub' ); ?></h2>
		<p><?php esc_html_e( 'Custom tables created by the plugin', 'lending-resource-hub' ); ?></p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Table Name', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Status', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Rows', 'lending-resource-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $table_diagnostics as $table ) : ?>
					<tr>
						<td><code><?php echo esc_html( $table['name'] ); ?></code></td>
						<td>
							<?php if ( $table['exists'] ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
								<span style="color: #00a32a;"><?php esc_html_e( 'Exists', 'lending-resource-hub' ); ?></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
								<span style="color: #d63638;"><?php esc_html_e( 'Missing', 'lending-resource-hub' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo $table['exists'] ? esc_html( $table['rowCount'] ) . ' rows' : 'N/A'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- Frontend Assets -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Frontend Assets', 'lending-resource-hub' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Frontend Portal App', 'lending-resource-hub' ); ?></strong></td>
					<td>
						<?php if ( $frontend_manifest_exists ) : ?>
							<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
							<span style="color: #00a32a;"><?php esc_html_e( 'Built', 'lending-resource-hub' ); ?></span>
						<?php else : ?>
							<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
							<span style="color: #d63638;"><?php esc_html_e( 'Not Built', 'lending-resource-hub' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php if ( ! $frontend_manifest_exists ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php esc_html_e( 'Frontend assets are not built. Run:', 'lending-resource-hub' ); ?>
					<code>npm run build</code>
				</p>
			</div>
		<?php endif; ?>
	</div>

	<!-- Plugin Integrations -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'Plugin Integrations', 'lending-resource-hub' ); ?></h2>
		<p><?php esc_html_e( 'Third-party plugin dependencies', 'lending-resource-hub' ); ?></p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Plugin', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Status', 'lending-resource-hub' ); ?></th>
					<th><?php esc_html_e( 'Required', 'lending-resource-hub' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $integrations as $integration ) : ?>
					<tr>
						<td><?php echo esc_html( $integration['name'] ); ?></td>
						<td>
							<?php if ( $integration['active'] ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
								<span style="color: #00a32a;"><?php esc_html_e( 'Active', 'lending-resource-hub' ); ?></span>
							<?php else : ?>
								<span class="dashicons dashicons-marker" style="color: #f0b849;"></span>
								<span style="color: #f0b849;"><?php esc_html_e( 'Not Active', 'lending-resource-hub' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo $integration['required'] ? esc_html__( 'Yes', 'lending-resource-hub' ) : esc_html__( 'Optional', 'lending-resource-hub' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- REST API Info -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
		<h2><?php esc_html_e( 'REST API', 'lending-resource-hub' ); ?></h2>
		<p><?php esc_html_e( 'Base URL:', 'lending-resource-hub' ); ?> <code><?php echo esc_html( rest_url( LRH_ROUTE_PREFIX . '/' ) ); ?></code></p>
		<p><?php esc_html_e( '44 API endpoints registered', 'lending-resource-hub' ); ?></p>
		<a href="<?php echo esc_url( rest_url( LRH_ROUTE_PREFIX . '/' ) ); ?>" class="button" target="_blank">
			<?php esc_html_e( 'View API Endpoints', 'lending-resource-hub' ); ?>
		</a>
	</div>
</div>
