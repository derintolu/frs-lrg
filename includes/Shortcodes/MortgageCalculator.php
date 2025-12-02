<?php
/**
 * Mortgage Calculator Shortcode
 *
 * Supports per-user customization via shortcode attributes or URL parameters.
 *
 * Usage:
 * [frs_mortgage_calculator] - Uses current logged-in user
 * [frs_mortgage_calculator user_id="123"] - Uses specific user ID
 * [frs_mortgage_calculator show_lead_form="true" webhook_url="https://..."]
 *
 * External Embed (JavaScript):
 * <div id="mortgage-calculator"
 *      data-loan-officer-id="123"
 *      data-webhook-url="https://your-webhook.com"
 *      data-show-lead-form="true">
 * </div>
 * <script src="https://yoursite.com/wp-content/plugins/frs-lrg/assets/widget/dist/assets/widget-*.js"></script>
 *
 * @package FRS_LRG
 */

namespace FRS_LRG\Shortcodes;

class MortgageCalculator {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'frs_mortgage_calculator', [ __CLASS__, 'render' ] );
		add_shortcode( 'frs_mortgage_calculator_embed_code', [ __CLASS__, 'render_embed_code' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue widget assets
	 */
	public static function enqueue_assets() {
		// Only enqueue if shortcode is present on the page
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'frs_mortgage_calculator' ) ) {
			\LendingResourceHub\Assets\Frontend::get_instance()->enqueue_widget_assets();
		}
	}

	/**
	 * Get user data for widget configuration
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array User data for widget.
	 */
	private static function get_user_data( $user_id ) {
		if ( ! $user_id ) {
			return [];
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return [];
		}

		// Try to get profile data from frs-wp-users API
		$profile_data = [];
		$response = wp_remote_get( rest_url( "frs-users/v1/profiles/user/{$user_id}" ), [
			'timeout' => 5,
		] );

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $body['success'] ) && $body['success'] && isset( $body['data'] ) ) {
				$profile_data = $body['data'];
			}
		}

		// Build data from profile or fallback to user meta
		$first_name = $profile_data['first_name'] ?? get_user_meta( $user_id, 'first_name', true );
		$last_name = $profile_data['last_name'] ?? get_user_meta( $user_id, 'last_name', true );

		return [
			'loan_officer_id'    => $user_id,
			'loan_officer_name'  => trim( $first_name . ' ' . $last_name ),
			'loan_officer_email' => $profile_data['email'] ?? $user->user_email,
			'loan_officer_phone' => $profile_data['mobile_number'] ?? $profile_data['phone_number'] ?? get_user_meta( $user_id, 'phone', true ),
			'nmls'               => $profile_data['nmls'] ?? $profile_data['nmls_number'] ?? get_user_meta( $user_id, 'nmls', true ),
			'job_title'          => $profile_data['job_title'] ?? get_user_meta( $user_id, 'job_title', true ),
			'avatar'             => $profile_data['profile_photo'] ?? get_avatar_url( $user_id ),
		];
	}

	/**
	 * Render the shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			[
				'user_id'        => 0,
				'show_lead_form' => 'true',
				'webhook_url'    => '',
				'brand_color'    => '#3b82f6',
				'logo_url'       => '',
				'email_enabled'  => 'true',
				'disclaimer'     => '',
				'gradient_start' => '#2563eb',
				'gradient_end'   => '#2dd4da',
			],
			$atts,
			'frs_mortgage_calculator'
		);

		// Determine user ID: shortcode attr > URL param > current user
		$user_id = intval( $atts['user_id'] );
		if ( ! $user_id && isset( $_GET['loan_officer_id'] ) ) {
			$user_id = intval( $_GET['loan_officer_id'] );
		}
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Get user data
		$user_data = self::get_user_data( $user_id );

		// Build data attributes
		$data_attrs = [];

		if ( $user_id ) {
			$data_attrs['data-loan-officer-id'] = esc_attr( $user_id );
		}
		if ( ! empty( $user_data['loan_officer_name'] ) ) {
			$data_attrs['data-loan-officer-name'] = esc_attr( trim( $user_data['loan_officer_name'] ) );
		}
		if ( ! empty( $user_data['loan_officer_email'] ) ) {
			$data_attrs['data-loan-officer-email'] = esc_attr( $user_data['loan_officer_email'] );
		}
		if ( ! empty( $user_data['loan_officer_phone'] ) ) {
			$data_attrs['data-loan-officer-phone'] = esc_attr( $user_data['loan_officer_phone'] );
		}
		if ( ! empty( $atts['webhook_url'] ) ) {
			$data_attrs['data-webhook-url'] = esc_url( $atts['webhook_url'] );
		}
		$data_attrs['data-show-lead-form'] = $atts['show_lead_form'] === 'true' ? 'true' : 'false';
		$data_attrs['data-brand-color'] = esc_attr( $atts['brand_color'] );
		$data_attrs['data-email-enabled'] = $atts['email_enabled'] === 'true' ? 'true' : 'false';

		if ( ! empty( $atts['logo_url'] ) ) {
			$data_attrs['data-logo-url'] = esc_url( $atts['logo_url'] );
		}
		if ( ! empty( $atts['disclaimer'] ) ) {
			$data_attrs['data-disclaimer'] = esc_attr( $atts['disclaimer'] );
		}
		$data_attrs['data-gradient-start'] = esc_attr( $atts['gradient_start'] );
		$data_attrs['data-gradient-end'] = esc_attr( $atts['gradient_end'] );

		// Build attribute string
		$attr_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$attr_string .= sprintf( ' %s="%s"', $key, $value );
		}

		return '<div id="mortgage-calculator" class="frs-mortgage-calculator-widget"' . $attr_string . '></div>';
	}

	/**
	 * Render embed code display for users to copy
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML with embed code.
	 */
	public static function render_embed_code( $atts ) {
		$atts = shortcode_atts(
			[
				'user_id' => get_current_user_id(),
			],
			$atts,
			'frs_mortgage_calculator_embed_code'
		);

		$user_id = intval( $atts['user_id'] );
		$user_data = self::get_user_data( $user_id );

		// Get the widget JS URL from manifest
		$manifest_path = LRH_DIR . '/assets/widget/dist/manifest.json';
		$js_file = 'assets/widget.js';
		$css_file = 'assets/widget.css';

		if ( file_exists( $manifest_path ) ) {
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
			if ( isset( $manifest['src/widget/widget.tsx']['file'] ) ) {
				$js_file = $manifest['src/widget/widget.tsx']['file'];
			}
			if ( isset( $manifest['src/widget/widget.css']['file'] ) ) {
				$css_file = $manifest['src/widget/widget.css']['file'];
			}
		}

		$base_url = LRH_URL . 'assets/widget/dist/';
		$js_url = $base_url . $js_file;
		$css_url = $base_url . $css_file;

		// Build embed code
		$embed_code = sprintf(
			'<!-- Mortgage Calculator Widget -->
<link rel="stylesheet" href="%s">
<div id="mortgage-calculator"
     data-loan-officer-id="%d"
     data-loan-officer-name="%s"
     data-loan-officer-email="%s"
     data-loan-officer-phone="%s"
     data-show-lead-form="true"
     data-brand-color="#3b82f6">
</div>
<script src="%s"></script>',
			esc_url( $css_url ),
			$user_id,
			esc_attr( trim( $user_data['loan_officer_name'] ?? '' ) ),
			esc_attr( $user_data['loan_officer_email'] ?? '' ),
			esc_attr( $user_data['loan_officer_phone'] ?? '' ),
			esc_url( $js_url )
		);

		ob_start();
		?>
		<div class="frs-embed-code-container p-6 bg-gray-50 rounded-lg">
			<h3 class="text-lg font-semibold mb-4">Embed Code for Your Website</h3>
			<p class="text-sm text-gray-600 mb-4">Copy and paste this code into any HTML page to display your personalized mortgage calculator:</p>
			<div class="relative">
				<pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto text-sm"><code id="embed-code-text"><?php echo esc_html( $embed_code ); ?></code></pre>
				<button
					onclick="navigator.clipboard.writeText(document.getElementById('embed-code-text').textContent); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Code', 2000);"
					class="absolute top-2 right-2 px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors"
				>
					Copy Code
				</button>
			</div>
			<div class="mt-4 p-4 bg-blue-50 rounded-lg">
				<h4 class="font-medium text-blue-900 mb-2">Configuration Options:</h4>
				<ul class="text-sm text-blue-800 space-y-1">
					<li><code>data-show-lead-form</code> - "true" or "false" to show/hide the lead capture form</li>
					<li><code>data-webhook-url</code> - URL to receive lead submissions via POST</li>
					<li><code>data-brand-color</code> - Hex color for buttons (e.g., "#3b82f6")</li>
					<li><code>data-logo-url</code> - URL to your logo image</li>
					<li><code>data-disclaimer</code> - Custom disclaimer text</li>
				</ul>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
