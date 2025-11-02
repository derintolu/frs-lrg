<?php
/**
 * Biolink Blocks Registration and Rendering
 *
 * Handles biolink page generation, block rendering, and Fluent Forms integration.
 *
 * @package LendingResourceHub\Controllers\Biolinks
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Biolinks;

use LendingResourceHub\Traits\Base;
use FRSUsers\Models\Profile;

/**
 * Class Blocks
 *
 * Manages biolink pages with single dynamic block that pulls from Profile model.
 *
 * @package LendingResourceHub\Controllers\Biolinks
 */
class Blocks {

	use Base;

	/**
	 * Initialize blocks and hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );

		// Metafield management
		add_action( 'save_post_frs_biolink', array( $this, 'update_biolink_featured_image' ), 10, 2 );
		add_action( 'profile_update', array( $this, 'update_user_biolink_featured_images' ) );

		// Fluent Forms integration
		add_action( 'wp_footer', array( $this, 'add_biolink_scripts' ) );
		add_action( 'frs_lead_captured', array( $this, 'trigger_js_lead_captured' ), 10, 3 );

		// Page view tracking
		add_action( 'template_redirect', array( $this, 'track_biolink_view' ) );
	}

	/**
	 * Register biolink blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		// Register single dynamic biolink page block
		register_block_type(
			LRH_DIR . 'blocks/biolink-page/block.json',
			array(
				'render_callback' => array( $this, 'render_biolink_page_block' ),
			)
		);
	}

	/**
	 * Add custom block category.
	 *
	 * @param array $categories Existing block categories.
	 * @return array Modified categories.
	 */
	public function add_block_category( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'lrh-biolink',
					'title' => __( 'Biolink Blocks', 'lending-resource-hub' ),
				),
			)
		);
	}

	/**
	 * Render complete biolink page block.
	 *
	 * Pulls data from Profile model and renders dynamic content.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_biolink_page_block( $attributes ) {
		global $post;

		// Get user ID from block attributes, meta field, or post author
		$user_id = $this->get_biolink_user_id( $attributes, $post );

		if ( ! $user_id ) {
			return '<p>' . __( 'No user ID found', 'lending-resource-hub' ) . '</p>';
		}

		// Get profile data from Eloquent Profile model
		$profile = Profile::where( 'user_id', $user_id )->first();

		if ( ! $profile ) {
			return '<p>' . __( 'Profile not found', 'lending-resource-hub' ) . '</p>';
		}

		// Build user data array from Profile model
		$user_data = array(
			'name'    => trim( $profile->first_name . ' ' . $profile->last_name ),
			'email'   => $profile->email,
			'title'   => $profile->job_title ?: __( 'Loan Officer', 'lending-resource-hub' ),
			'company' => '21st Century Lending',
			'phone'   => $profile->phone_number ?: $profile->mobile_number,
			'avatar'  => $this->get_profile_headshot_url( $profile ),
			'arrive'  => $profile->arrive,
		);

		// Render all biolink sections
		$output  = $this->render_header_block( $user_data );
		$output .= $this->render_social_block( $user_data );
		$output .= $this->render_call_button( $user_data );
		$output .= $this->render_schedule_button();
		$output .= $this->render_schedule_form(); // Hidden form - Form ID: 7
		$output .= $this->render_preapproval_button( $user_data );
		$output .= $this->render_ratequote_button();
		$output .= $this->render_ratequote_form(); // Hidden form - Form ID: 6
		$output .= $this->render_thankyou_block( $user_data );

		return $output;
	}

	/**
	 * Get biolink user ID from various sources.
	 *
	 * @param array        $attributes Block attributes.
	 * @param \WP_Post|null $post Current post.
	 * @return int|null User ID.
	 */
	private function get_biolink_user_id( $attributes, $post ) {
		// Priority 1: Block attribute
		if ( ! empty( $attributes['user_id'] ) ) {
			return intval( $attributes['user_id'] );
		}

		// Priority 2: Post meta
		if ( $post ) {
			$meta_user_id = get_post_meta( $post->ID, 'frs_biolink_user', true );
			if ( $meta_user_id ) {
				return intval( $meta_user_id );
			}

			$meta_user_id = get_post_meta( $post->ID, '_frs_loan_officer_id', true );
			if ( $meta_user_id ) {
				return intval( $meta_user_id );
			}
		}

		// Priority 3: Post author
		if ( $post ) {
			return intval( $post->post_author );
		}

		return null;
	}

	/**
	 * Get profile headshot URL.
	 *
	 * @param Profile $profile Profile model instance.
	 * @return string Headshot URL.
	 */
	private function get_profile_headshot_url( $profile ) {
		if ( $profile->headshot_id ) {
			$url = wp_get_attachment_image_url( $profile->headshot_id, 'medium' );
			if ( $url ) {
				return $url;
			}
		}

		// Fallback to WordPress avatar
		if ( $profile->user_id ) {
			return get_avatar_url( $profile->user_id );
		}

		return '';
	}

	/**
	 * Render header block with video background.
	 *
	 * @param array $user_data User data array.
	 * @return string Rendered HTML.
	 */
	private function render_header_block( $user_data ) {
		$video_url  = LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';
		$logo_url   = LRH_URL . 'assets/images/21C-Wordmark-White.svg';
		$bg_color   = 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #2dd4da 100%)';

		ob_start();
		?>
		<div class="lrh-biolink-header" style="position: relative; padding: 40px 0; text-align: center; color: white; overflow: hidden; min-height: 400px; background: <?php echo esc_attr( $bg_color ); ?>; font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<!-- Video Background -->
			<video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" autoplay muted loop playsinline>
				<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
			</video>

			<!-- Content -->
			<div style="position: relative; z-index: 3; max-width: 570px; margin: 0 auto; padding: 10px 20px 0 20px; box-sizing: border-box;">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="21st Century Lending" style="max-width: 200px; height: auto; margin: 0 auto 15px auto; display: block; transform: translateY(-10px);">

				<?php if ( $user_data['avatar'] ) : ?>
					<img src="<?php echo esc_url( $user_data['avatar'] ); ?>" alt="<?php echo esc_attr( $user_data['name'] ); ?>" style="width: clamp(120px, 14vw, 150px); height: clamp(120px, 14vw, 150px); border-radius: 50%; margin: 0 auto 5px auto; display: block; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 20px rgba(0,0,0,0.3); object-fit: cover; transform: translateY(-5px);">
				<?php endif; ?>

				<h1 style="margin: 0 0 5px 0; font-size: 2.2rem; font-weight: bold; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5); transform: translateY(-5px);"><?php echo esc_html( $user_data['name'] ); ?></h1>
				<p style="margin: 0 0 5px 0; font-size: 1.2rem; opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.5); transform: translateY(-5px);"><?php echo esc_html( $user_data['title'] ); ?></p>
				<p style="margin: 0; font-size: 1.1rem; opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5); transform: translateY(-5px);"><?php echo esc_html( $user_data['company'] ); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render social media block.
	 *
	 * @param array $user_data User data array.
	 * @return string Rendered HTML.
	 */
	private function render_social_block( $user_data ) {
		// TODO: Add social media fields to Profile model
		$links = array(
			array(
				'platform' => 'email',
				'url'      => 'mailto:' . $user_data['email'],
			),
		);

		if ( empty( $links ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="lrh-biolink-social" style="text-align: center; background: white; padding: 15px;">
			<div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap;">
				<?php foreach ( $links as $link ) : ?>
					<?php if ( ! empty( $link['url'] ) && $link['url'] !== '#' ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: white; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-decoration: none; transition: transform 0.2s;">
							<?php echo $this->get_social_icon( $link['platform'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render call me button.
	 *
	 * @param array $user_data User data array.
	 * @return string Rendered HTML.
	 */
	private function render_call_button( $user_data ) {
		$phone_url = ! empty( $user_data['phone'] ) ? 'tel:' . preg_replace( '/[^0-9+]/', '', $user_data['phone'] ) : '';

		return $this->render_button(
			__( 'Call Me Now', 'lending-resource-hub' ),
			$phone_url,
			'phone'
		);
	}

	/**
	 * Render schedule appointment button.
	 *
	 * @return string Rendered HTML.
	 */
	private function render_schedule_button() {
		return $this->render_button(
			__( 'Schedule Appointment', 'lending-resource-hub' ),
			'#schedule-appointment',
			'calendar'
		);
	}

	/**
	 * Render pre-approval button.
	 *
	 * @param array $user_data User data array.
	 * @return string Rendered HTML.
	 */
	private function render_preapproval_button( $user_data ) {
		$arrive_url = $user_data['arrive'] ?: '#';

		return $this->render_button(
			__( 'Get Pre-Approved', 'lending-resource-hub' ),
			$arrive_url,
			'check-circle'
		);
	}

	/**
	 * Render rate quote button.
	 *
	 * @return string Rendered HTML.
	 */
	private function render_ratequote_button() {
		return $this->render_button(
			__( 'Free Rate Quote', 'lending-resource-hub' ),
			'#rate-quote',
			'calculator'
		);
	}

	/**
	 * Render button element.
	 *
	 * @param string $text Button text.
	 * @param string $url Button URL.
	 * @param string $icon Icon name.
	 * @return string Rendered HTML.
	 */
	private function render_button( $text, $url, $icon ) {
		ob_start();
		?>
		<div class="lrh-biolink-button" style="background: white; padding: 0 15px; box-sizing: border-box;">
			<a href="<?php echo esc_url( $url ); ?>" style="display: flex; align-items: center; justify-content: center; gap: 12px; background: linear-gradient(145deg, #f8f9fa, #e9ecef); color: #212529; text-decoration: none; padding: 18px 32px; margin-bottom: 8px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04); font-size: 17.6px; font-weight: 500; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.8);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.04)'">
				<?php echo $this->get_action_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $text ); ?></span>
			</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render hidden schedule appointment form.
	 *
	 * METAFIELD: Form ID 7 (conversational)
	 *
	 * @return string Rendered HTML.
	 */
	private function render_schedule_form() {
		return $this->render_hidden_form(
			'7',
			'scheduling',
			__( 'Schedule Appointment', 'lending-resource-hub' ),
			'conversational'
		);
	}

	/**
	 * Render hidden rate quote form.
	 *
	 * METAFIELD: Form ID 6 (conversational)
	 *
	 * @return string Rendered HTML.
	 */
	private function render_ratequote_form() {
		return $this->render_hidden_form(
			'6',
			'rate-quote',
			__( 'Free Rate Quote', 'lending-resource-hub' ),
			'conversational'
		);
	}

	/**
	 * Render hidden form block.
	 *
	 * @param string $form_id Fluent Forms form ID.
	 * @param string $form_html_id HTML ID for the form container.
	 * @param string $form_title Form title.
	 * @param string $form_type Form type (conversational or default).
	 * @return string Rendered HTML.
	 */
	private function render_hidden_form( $form_id, $form_html_id, $form_title, $form_type = 'conversational' ) {
		ob_start();
		?>
		<div id="<?php echo esc_attr( $form_html_id ); ?>" data-form-id="<?php echo esc_attr( $form_id ); ?>">
			<button class="lrh-form-back" style="position: absolute; top: 60px; left: 20px; background: none; border: none; font-size: 16px; cursor: pointer; z-index: 10;">← <?php esc_html_e( 'Back to Profile', 'lending-resource-hub' ); ?></button>
			<div style="width: 100%; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
				<div style="width: 100%; max-width: 600px;">
					<h2 style="color: #333; margin-bottom: 30px; text-align: center; font-size: 28px;"><?php echo esc_html( $form_title ); ?></h2>
					<div style="width: 100%;">
						<?php
						if ( function_exists( 'wpFluentForm' ) ) {
							if ( $form_type === 'conversational' ) {
								echo do_shortcode( '[fluentform type="conversational" id="' . esc_attr( $form_id ) . '"]' );
							} else {
								echo do_shortcode( '[fluentform id="' . esc_attr( $form_id ) . '"]' );
							}
						} else {
							echo '<p style="text-align: center; padding: 40px; color: #666;">' . esc_html__( 'Form is currently unavailable. Please contact us directly.', 'lending-resource-hub' ) . '</p>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render thank you block.
	 *
	 * @param array $user_data User data array.
	 * @return string Rendered HTML.
	 */
	private function render_thankyou_block( $user_data ) {
		$first_name = explode( ' ', $user_data['name'] )[0];

		ob_start();
		?>
		<div class="lrh-thank-you" style="display: none;">
			<div class="lrh-thank-you-content">
				<h2><?php esc_html_e( 'Thank You!', 'lending-resource-hub' ); ?></h2>
				<p><?php echo esc_html( sprintf( __( 'Thanks for reaching out! %s will personally review your information and get back to you soon.', 'lending-resource-hub' ), $first_name ) ); ?></p>
				<div class="lrh-thank-you-buttons">
					<button type="button" class="lrh-thank-you-close"><?php esc_html_e( 'Return to Profile', 'lending-resource-hub' ); ?></button>
					<button type="button" class="lrh-thank-you-close-tab" onclick="window.close()"><?php esc_html_e( 'Close Tab', 'lending-resource-hub' ); ?></button>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get social media icon SVG.
	 *
	 * @param string $platform Platform name.
	 * @return string SVG markup.
	 */
	private function get_social_icon( $platform ) {
		$icons = array(
			'email'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" fill="none"/><polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/></svg>',
			'facebook'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
			'instagram' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
			'linkedin'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
			'twitter'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		);

		return $icons[ $platform ] ?? $icons['email'];
	}

	/**
	 * Get action icon SVG.
	 *
	 * @param string $icon Icon name.
	 * @return string SVG markup.
	 */
	private function get_action_icon( $icon ) {
		$icons = array(
			'phone'        => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
			'calendar'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>',
			'check-circle' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
			'calculator'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8-4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zM9 9H7V7h2v2zm4 0h-2V7h2v2zm4 0h-2V7h2v2z"/></svg>',
		);

		return $icons[ $icon ] ?? $icons['phone'];
	}

	/**
	 * Auto-generate biolink page for a user.
	 *
	 * METAFIELD PLACEMENT: Sets frs_biolink_user, _frs_loan_officer_id, frs_biolink_page
	 *
	 * @param int $user_id WordPress user ID.
	 * @return array|false Page data or false on failure.
	 */
	public static function generate_biolink_page( $user_id ) {
		// Get profile from Eloquent
		$profile = Profile::where( 'user_id', $user_id )->first();

		if ( ! $profile ) {
			return false;
		}

		// Generate unique slug
		$slug = self::generate_unique_biolink_slug( $profile->first_name, $user_id );

		// Create page with single dynamic block
		$page_content = '<!-- wp:lrh/biolink-page {"user_id":' . intval( $user_id ) . '} /-->';

		// Create the page
		$page_data = array(
			'post_title'   => trim( $profile->first_name . ' ' . $profile->last_name ),
			'post_name'    => $slug,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'frs_biolink',
			'post_author'  => $user_id,
			'meta_input'   => array(
				'frs_biolink_page'      => '1',
				'frs_biolink_user'      => $user_id,
				'_frs_loan_officer_id'  => $user_id,
				'_frs_page_views'       => 0,
				'_frs_page_conversions' => 0,
			),
		);

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return false;
		}

		// Set featured image if headshot exists
		if ( $profile->headshot_id ) {
			set_post_thumbnail( $page_id, $profile->headshot_id );
		}

		return array(
			'id'       => $page_id,
			'url'      => get_permalink( $page_id ),
			'edit_url' => get_edit_post_link( $page_id, 'raw' ),
		);
	}

	/**
	 * Generate unique biolink slug.
	 *
	 * @param string $first_name First name.
	 * @param int    $user_id User ID.
	 * @return string Unique slug.
	 */
	private static function generate_unique_biolink_slug( $first_name, $user_id ) {
		$slug = sanitize_title( $first_name );

		// Check if slug exists
		$existing = get_page_by_path( $slug, OBJECT, 'frs_biolink' );

		if ( ! $existing || $existing->post_author == $user_id ) {
			return $slug;
		}

		// Add user ID to make unique
		return $slug . $user_id;
	}

	/**
	 * Update biolink featured image when page is saved.
	 *
	 * METAFIELD: Syncs featured image with profile headshot
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function update_biolink_featured_image( $post_id, $post ) {
		// Get user ID from metafields
		$user_id = get_post_meta( $post_id, 'frs_biolink_user', true );
		if ( ! $user_id ) {
			$user_id = get_post_meta( $post_id, '_frs_loan_officer_id', true );
		}
		if ( ! $user_id ) {
			$user_id = $post->post_author;
		}

		if ( ! $user_id ) {
			return;
		}

		// Get profile
		$profile = Profile::where( 'user_id', $user_id )->first();

		if ( $profile && $profile->headshot_id ) {
			set_post_thumbnail( $post_id, $profile->headshot_id );
		}
	}

	/**
	 * Update featured images for all biolink pages when user profile changes.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function update_user_biolink_featured_images( $user_id ) {
		// Get all biolink pages for this user
		$pages = get_posts(
			array(
				'post_type'      => 'frs_biolink',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => 'frs_biolink_user',
						'value'   => $user_id,
						'compare' => '=',
					),
					array(
						'key'     => '_frs_loan_officer_id',
						'value'   => $user_id,
						'compare' => '=',
					),
				),
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
			)
		);

		if ( empty( $pages ) ) {
			return;
		}

		// Get profile
		$profile = Profile::where( 'user_id', $user_id )->first();

		if ( ! $profile || ! $profile->headshot_id ) {
			return;
		}

		// Update all pages
		foreach ( $pages as $page ) {
			set_post_thumbnail( $page->ID, $profile->headshot_id );
		}
	}

	/**
	 * Track biolink page view.
	 *
	 * METAFIELD: Increments _frs_page_views
	 *
	 * @return void
	 */
	public function track_biolink_view() {
		if ( ! is_singular( 'frs_biolink' ) ) {
			return;
		}

		global $post;

		// Increment view count
		$views = (int) get_post_meta( $post->ID, '_frs_page_views', true );
		update_post_meta( $post->ID, '_frs_page_views', $views + 1 );
	}

	/**
	 * Add biolink frontend scripts and styles.
	 *
	 * FLUENT FORMS INTEGRATION: Event listeners and thank you overlay
	 *
	 * @return void
	 */
	public function add_biolink_scripts() {
		if ( get_post_type() !== 'frs_biolink' ) {
			return;
		}

		// Enqueue built assets
		wp_enqueue_script(
			'lrh-biolink-frontend',
			LRH_URL . 'assets/frontend/biolink-frontend.js',
			array( 'jquery' ),
			LRH_VERSION,
			true
		);

		wp_enqueue_style(
			'lrh-biolink-frontend',
			LRH_URL . 'assets/frontend/biolink-frontend.css',
			array(),
			LRH_VERSION
		);

		// Pass nonce for AJAX
		wp_localize_script(
			'lrh-biolink-frontend',
			'lrhBiolinkData',
			array(
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'videoUrl' => LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4',
			)
		);
	}

	/**
	 * Bridge PHP action to JavaScript event.
	 *
	 * FLUENT FORMS INTEGRATION: Triggers JS event when lead is captured
	 *
	 * @param int   $lead_id Lead ID.
	 * @param array $lead_data Lead data.
	 * @param array $context Context.
	 * @return void
	 */
	public function trigger_js_lead_captured( $lead_id, $lead_data, $context ) {
		if ( ! is_singular( 'frs_biolink' ) ) {
			return;
		}

		// Increment conversion count
		global $post;
		$conversions = (int) get_post_meta( $post->ID, '_frs_page_conversions', true );
		update_post_meta( $post->ID, '_frs_page_conversions', $conversions + 1 );

		// Trigger JavaScript event
		wp_add_inline_script(
			'jquery',
			"
			jQuery(document).ready(function() {
				setTimeout(function() {
					var event = new CustomEvent('lrh_lead_captured', {
						detail: {
							lead_id: " . intval( $lead_id ) . ",
							lead_data: " . wp_json_encode( $lead_data ) . ",
							context: " . wp_json_encode( $context ) . "
						}
					});
					document.dispatchEvent(event);
				}, 100);
			});
		"
		);
	}
}
