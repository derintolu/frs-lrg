<?php
/**
 * Admin Integrations Template
 *
 * @package LendingResourceHub
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Integrations', 'lending-resource-hub' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Connect your CRM and lead management systems to automate your workflow.', 'lending-resource-hub' ); ?></p>

	<form method="post" action="">
		<?php wp_nonce_field( 'lrh_save_integrations', 'lrh_integrations_nonce' ); ?>

		<!-- Integration Cards Grid -->
		<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; margin: 30px 0;">

			<!-- ARRIVE Integration -->
			<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<div style="padding: 20px; border-bottom: 1px solid #f0f0f1;">
					<div style="display: flex; align-items: center; gap: 15px;">
						<div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20 5L35 15V25L20 35L5 25V15L20 5Z" fill="white" fill-opacity="0.9"/>
								<path d="M20 12L28 17V27L20 32L12 27V17L20 12Z" fill="white"/>
							</svg>
						</div>
						<div style="flex: 1;">
							<h2 style="margin: 0 0 5px 0; font-size: 18px;"><?php esc_html_e( 'ARRIVE', 'lending-resource-hub' ); ?></h2>
							<p style="margin: 0; color: #646970; font-size: 13px;"><?php esc_html_e( 'Loan Officer Marketing Platform', 'lending-resource-hub' ); ?></p>
						</div>
						<label class="switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
							<input type="checkbox" name="arrive_enabled" value="1" <?php checked( $arrive_enabled ); ?> style="opacity: 0; width: 0; height: 0;">
							<span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span>
						</label>
					</div>
				</div>
				<div style="padding: 20px;">
					<p style="margin: 0 0 15px 0; color: #50575e; font-size: 13px; line-height: 1.6;">
						<?php esc_html_e( 'Connect ARRIVE to automatically sync your loan officer profiles and marketing materials. ARRIVE provides comprehensive CRM and marketing automation for mortgage professionals.', 'lending-resource-hub' ); ?>
					</p>
					<div style="margin-bottom: 15px;">
						<label for="arrive_api_key" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">
							<?php esc_html_e( 'API Key', 'lending-resource-hub' ); ?>
						</label>
						<input
							type="password"
							id="arrive_api_key"
							name="arrive_api_key"
							value="<?php echo esc_attr( $arrive_api_key ); ?>"
							class="regular-text"
							placeholder="<?php esc_attr_e( 'Enter your ARRIVE API key', 'lending-resource-hub' ); ?>"
							style="width: 100%;"
						>
						<p class="description" style="margin: 5px 0 0 0;">
							<?php
							printf(
								/* translators: %s: ARRIVE dashboard URL */
								esc_html__( 'Get your API key from your %s.', 'lending-resource-hub' ),
								'<a href="https://arrive.com/settings/api" target="_blank">' . esc_html__( 'ARRIVE dashboard', 'lending-resource-hub' ) . '</a>'
							);
							?>
						</p>
					</div>
					<?php if ( $arrive_enabled && ! empty( $arrive_api_key ) ) : ?>
						<div style="padding: 10px; background: #d7f3e3; border-left: 4px solid #00a32a; margin-top: 10px;">
							<span class="dashicons dashicons-yes-alt" style="color: #00a32a; margin-right: 5px;"></span>
							<span style="color: #00a32a; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Connected', 'lending-resource-hub' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Follow Up Boss Integration -->
			<div style="background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<div style="padding: 20px; border-bottom: 1px solid #f0f0f1;">
					<div style="display: flex; align-items: center; gap: 15px;">
						<div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
							<svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="20" cy="14" r="6" fill="white" fill-opacity="0.9"/>
								<path d="M10 35C10 27.268 15.268 22 20 22C24.732 22 30 27.268 30 35" stroke="white" stroke-width="3" fill="none"/>
								<path d="M25 10L30 15M30 15L25 20M30 15H15" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
							</svg>
						</div>
						<div style="flex: 1;">
							<h2 style="margin: 0 0 5px 0; font-size: 18px;"><?php esc_html_e( 'Follow Up Boss', 'lending-resource-hub' ); ?></h2>
							<p style="margin: 0; color: #646970; font-size: 13px;"><?php esc_html_e( 'Real Estate CRM', 'lending-resource-hub' ); ?></p>
						</div>
						<label class="switch" style="position: relative; display: inline-block; width: 50px; height: 24px;">
							<input type="checkbox" name="fub_enabled" value="1" <?php checked( $fub_enabled ); ?> style="opacity: 0; width: 0; height: 0;">
							<span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span>
						</label>
					</div>
				</div>
				<div style="padding: 20px;">
					<p style="margin: 0 0 15px 0; color: #50575e; font-size: 13px; line-height: 1.6;">
						<?php esc_html_e( 'Integrate Follow Up Boss to sync leads and contacts automatically. Follow Up Boss is the leading CRM for real estate agents and teams, now integrated with your loan officer partnerships.', 'lending-resource-hub' ); ?>
					</p>
					<div style="margin-bottom: 15px;">
						<label for="fub_api_key" style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px;">
							<?php esc_html_e( 'API Key', 'lending-resource-hub' ); ?>
						</label>
						<input
							type="password"
							id="fub_api_key"
							name="fub_api_key"
							value="<?php echo esc_attr( $fub_api_key ); ?>"
							class="regular-text"
							placeholder="<?php esc_attr_e( 'Enter your Follow Up Boss API key', 'lending-resource-hub' ); ?>"
							style="width: 100%;"
						>
						<p class="description" style="margin: 5px 0 0 0;">
							<?php
							printf(
								/* translators: %s: Follow Up Boss settings URL */
								esc_html__( 'Find your API key in %s.', 'lending-resource-hub' ),
								'<a href="https://app.followupboss.com/settings/integrations" target="_blank">' . esc_html__( 'Follow Up Boss settings', 'lending-resource-hub' ) . '</a>'
							);
							?>
						</p>
					</div>
					<?php if ( $fub_enabled && ! empty( $fub_api_key ) ) : ?>
						<div style="padding: 10px; background: #d7f3e3; border-left: 4px solid #00a32a; margin-top: 10px;">
							<span class="dashicons dashicons-yes-alt" style="color: #00a32a; margin-right: 5px;"></span>
							<span style="color: #00a32a; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Connected', 'lending-resource-hub' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>

		<!-- Save Button -->
		<p class="submit">
			<button type="submit" class="button button-primary button-large">
				<span class="dashicons dashicons-saved" style="margin-top: 4px;"></span>
				<?php esc_html_e( 'Save Integration Settings', 'lending-resource-hub' ); ?>
			</button>
		</p>
	</form>

	<!-- Additional Information -->
	<div style="background: #fff; padding: 20px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-left: 4px solid #72aee6;">
		<h3 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Need Help?', 'lending-resource-hub' ); ?></h3>
		<p style="margin: 0;">
			<?php esc_html_e( 'For setup assistance or to request additional integrations, please contact support or refer to our documentation.', 'lending-resource-hub' ); ?>
		</p>
	</div>
</div>

<style>
/* Toggle Switch Styling */
.switch input:checked + span {
	background-color: #2271b1;
}

.switch input:checked + span:before {
	transform: translateX(26px);
}

.switch span:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .4s;
	border-radius: 50%;
}
</style>
