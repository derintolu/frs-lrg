<?php
/**
 * Partner Portal Blocks Registration and Rendering
 *
 * Handles partner portal page generation, block rendering, and customization.
 * Partner portals are branded landing pages for partner real estate companies.
 * Each partner company (e.g. "Keller Williams Downtown") gets one portal shared by
 * all their realtors and assigned loan officers.
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\PartnerPortals;

use LendingResourceHub\Traits\Base;
use FRSUsers\Models\Profile;

/**
 * Class Blocks
 *
 * Manages partner company portal pages with custom branding and DataKit integration.
 *
 * Structure:
 * - 1 Partner Portal = 1 Real Estate Company (e.g., "Keller Williams Downtown")
 * - Multiple loan officers can be assigned to one partner company
 * - All realtors from that company share the same portal
 * - Tied to a BuddyPress group containing all realtors + loan officers
 *
 * @package LendingResourceHub\Controllers\PartnerPortals
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
		add_action( 'save_post_frs_partner_portal', array( $this, 'update_portal_featured_image' ), 10, 2 );

		// Page view tracking
		add_action( 'template_redirect', array( $this, 'track_portal_view' ) );

		// Add Carbon Fields for branding customizations
		add_action( 'carbon_fields_register_fields', array( $this, 'register_carbon_fields' ) );
	}

	/**
	 * Register partner portal blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		$blocks_dir = LRH_DIR . 'assets/blocks/';

		// Reuse biolink component blocks for partner portals
		// (header, button, social, form components can be reused)

		// Register partner portal page wrapper block with dynamic rendering
		register_block_type(
			$blocks_dir . 'partner-portal-page',
			array(
				'render_callback' => array( $this, 'render_partner_portal_page_block' ),
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
					'slug'  => 'lrh-partner-portal',
					'title' => __( 'Partner Portal Blocks', 'lending-resource-hub' ),
				),
			)
		);
	}

	/**
	 * Register Carbon Fields for partner portal branding.
	 *
	 * Allows customization of:
	 * - Primary brand color
	 * - Secondary brand color
	 * - Custom logo
	 * - Header background
	 * - Button styles
	 *
	 * @return void
	 */
	public function register_carbon_fields() {
		if ( ! class_exists( '\Carbon_Fields\Container' ) ) {
			return;
		}

		\Carbon_Fields\Container::make( 'post_meta', __( 'Partner Portal Branding', 'lending-resource-hub' ) )
			->where( 'post_type', '=', 'frs_partner_portal' )
			->add_tab(
				__( 'Branding', 'lending-resource-hub' ),
				array(
					\Carbon_Fields\Field::make( 'color', 'pp_primary_color', __( 'Primary Brand Color', 'lending-resource-hub' ) )
						->set_default_value( '#2563eb' )
						->set_help_text( __( 'Main color used for buttons and accents', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'color', 'pp_secondary_color', __( 'Secondary Brand Color', 'lending-resource-hub' ) )
						->set_default_value( '#2dd4da' )
						->set_help_text( __( 'Secondary color used for gradients and highlights', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'image', 'pp_custom_logo', __( 'Custom Logo', 'lending-resource-hub' ) )
						->set_help_text( __( 'Upload a custom logo for this partner portal', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'image', 'pp_header_background', __( 'Header Background Image', 'lending-resource-hub' ) )
						->set_help_text( __( 'Optional background image for the header section', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'select', 'pp_button_style', __( 'Button Style', 'lending-resource-hub' ) )
						->set_options(
							array(
								'rounded'  => __( 'Rounded', 'lending-resource-hub' ),
								'square'   => __( 'Square', 'lending-resource-hub' ),
								'gradient' => __( 'Gradient', 'lending-resource-hub' ),
							)
						)
						->set_default_value( 'rounded' ),
				)
			)
			->add_tab(
				__( 'Landing Pages', 'lending-resource-hub' ),
				array(
					\Carbon_Fields\Field::make( 'complex', 'pp_landing_pages', __( 'Associated Landing Pages', 'lending-resource-hub' ) )
						->set_help_text( __( 'Select and preview landing pages for this partner portal', 'lending-resource-hub' ) )
						->add_fields(
							array(
								\Carbon_Fields\Field::make( 'select', 'page_type', __( 'Page Type', 'lending-resource-hub' ) )
									->set_options(
										array(
											'biolink'      => __( 'Biolink', 'lending-resource-hub' ),
											'prequal'      => __( 'Pre-qualification', 'lending-resource-hub' ),
											'open_house'   => __( 'Open House', 'lending-resource-hub' ),
											'mortgage_lp'  => __( 'Mortgage Landing Page', 'lending-resource-hub' ),
										)
									),
								\Carbon_Fields\Field::make( 'association', 'page_id', __( 'Select Page', 'lending-resource-hub' ) )
									->set_types(
										array(
											array(
												'type'      => 'post',
												'post_type' => array( 'frs_biolink', 'frs_prequal', 'frs_openhouse', 'frs_mortgage_lp' ),
											),
										)
									),
							)
						),
				)
			)
			->add_tab(
				__( 'Partner Company & Access', 'lending-resource-hub' ),
				array(
					\Carbon_Fields\Field::make( 'text', 'pp_company_name', __( 'Partner Company Name', 'lending-resource-hub' ) )
						->set_help_text( __( 'Name of the partner real estate company (e.g., "Keller Williams Downtown")', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'text', 'pp_buddypress_group_id', __( 'BuddyPress Group ID', 'lending-resource-hub' ) )
						->set_attribute( 'type', 'number' )
						->set_help_text( __( 'Partner company sub-group ID in BuddyPress. All group members (realtors + loan officers) have access.', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'html', 'pp_group_info' )
						->set_html( '<p><em>All BuddyPress group members (realtors and loan officers from this partner company) will automatically have access to this portal.</em></p>' ),
					\Carbon_Fields\Field::make( 'association', 'pp_loan_officers', __( 'Assigned Loan Officers', 'lending-resource-hub' ) )
						->set_types(
							array(
								array(
									'type' => 'user',
									'role' => array( 'loan_officer', 'administrator' ),
								),
							)
						)
						->set_help_text( __( 'Loan officers assigned to this partner company (can be multiple)', 'lending-resource-hub' ) )
						->set_max( -1 ),
					\Carbon_Fields\Field::make( 'separator', 'pp_separator_1', __( 'Manual Overrides', 'lending-resource-hub' ) ),
					\Carbon_Fields\Field::make( 'association', 'pp_additional_realtors', __( 'Additional Realtors', 'lending-resource-hub' ) )
						->set_types(
							array(
								array(
									'type' => 'user',
									'role' => array( 'realtor', 'subscriber' ),
								),
							)
						)
						->set_help_text( __( 'Manually add realtors not in the BuddyPress group (edge cases only)', 'lending-resource-hub' ) )
						->set_max( -1 ),
				)
			);
	}

	/**
	 * Render complete partner company portal page block.
	 *
	 * Pulls loan officer profile data and applies partner company's custom branding.
	 * Displays the first assigned loan officer's information on the portal.
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_partner_portal_page_block( $attributes ) {
		global $post;

		// Get first assigned loan officer (their profile will be displayed on the portal)
		$loan_officer_ids = carbon_get_post_meta( $post->ID, 'pp_loan_officers' );
		$loan_officer_id  = ! empty( $loan_officer_ids ) ? $loan_officer_ids[0]['id'] : $post->post_author;

		if ( ! $loan_officer_id ) {
			return '<p>' . __( 'No loan officers assigned to this partner company portal.', 'lending-resource-hub' ) . '</p>';
		}

		// Get profile data from Eloquent Profile model
		$profile = Profile::where( 'user_id', $loan_officer_id )->first();

		if ( ! $profile ) {
			return '<p>' . __( 'Loan officer profile not found.', 'lending-resource-hub' ) . '</p>';
		}

		// Get branding customizations
		$branding = array(
			'primary_color'       => carbon_get_post_meta( $post->ID, 'pp_primary_color' ) ?: '#2563eb',
			'secondary_color'     => carbon_get_post_meta( $post->ID, 'pp_secondary_color' ) ?: '#2dd4da',
			'custom_logo'         => carbon_get_post_meta( $post->ID, 'pp_custom_logo' ),
			'header_background'   => carbon_get_post_meta( $post->ID, 'pp_header_background' ),
			'button_style'        => carbon_get_post_meta( $post->ID, 'pp_button_style' ) ?: 'rounded',
		);

		// Build user data array from Profile model
		$nmls_number = $profile->nmls_number ?? '';
		$title       = $profile->job_title ?: __( 'Licensed Loan Officer', 'lending-resource-hub' );

		// Add NMLS number to title if available
		if ( ! empty( $nmls_number ) ) {
			$title .= ' | NMLS# ' . $nmls_number;
		}

		$user_data = array(
			'name'    => trim( $profile->first_name . ' ' . $profile->last_name ),
			'email'   => $profile->email,
			'title'   => $title,
			'company' => '21st Century Lending',
			'phone'   => $profile->phone_number ?: $profile->mobile_number,
			'avatar'  => $this->get_profile_headshot_url( $profile ),
			'arrive'  => $profile->arrive,
		);

		// Render partner portal page with custom branding
		return $this->render_portal_content( $user_data, $branding );
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
	 * Render partner company portal content with custom branding.
	 *
	 * Applies the partner company's colors, logo, and styling to the portal page.
	 *
	 * @param array $user_data Loan officer data to display.
	 * @param array $branding Partner company's branding customizations.
	 * @return string Rendered HTML.
	 */
	private function render_portal_content( $user_data, $branding ) {
		// Get logo URL (partner company's custom logo or 21st Century default)
		$logo_url = ! empty( $branding['custom_logo'] )
			? wp_get_attachment_image_url( $branding['custom_logo'], 'full' )
			: LRH_URL . 'assets/images/21C-Wordmark-White.svg';

		// Get video or background image
		$has_custom_bg = ! empty( $branding['header_background'] );
		$video_url     = LRH_URL . 'assets/images/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-1.mp4';
		$bg_gradient   = "linear-gradient(135deg, {$branding['primary_color']} 0%, {$branding['secondary_color']} 100%)";

		// Button border radius based on style
		$button_radius = array(
			'rounded'  => '25px',
			'square'   => '6px',
			'gradient' => '12px',
		);
		$border_radius = $button_radius[ $branding['button_style'] ] ?? '25px';

		ob_start();
		?>
		<!-- Header Section with Custom Branding -->
		<div class="frs-partner-portal-header" style="position: relative; padding: 40px 0; text-align: center; color: white; overflow: hidden; min-height: 400px; background: <?php echo esc_attr( $bg_gradient ); ?>; font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
			<?php if ( ! $has_custom_bg ) : ?>
				<video style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1;" autoplay muted loop playsinline>
					<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
				</video>
			<?php else : ?>
				<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('<?php echo esc_url( wp_get_attachment_image_url( $branding['header_background'], 'full' ) ); ?>') center/cover; z-index: 1;"></div>
			<?php endif; ?>

			<div style="position: relative; z-index: 3; max-width: 570px; margin: 0 auto; padding: 10px 20px 0 20px; box-sizing: border-box;">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $user_data['company'] ); ?>" style="max-width: 200px; height: auto; margin: 0 auto 15px auto; margin-top: 0; display: block; filter: brightness(1) contrast(1); transform: translateY(-10px);">

				<?php if ( $user_data['avatar'] ) : ?>
					<img src="<?php echo esc_url( $user_data['avatar'] ); ?>" alt="<?php echo esc_attr( $user_data['name'] ); ?>" style="width: clamp(120px, 14vw, 150px); height: clamp(120px, 14vw, 150px); border-radius: 50%; margin: 0 auto 5px auto; display: block; border: 4px solid rgba(255,255,255,0.3); box-shadow: 0 4px 20px rgba(0,0,0,0.3); object-fit: cover; margin-top: 0; transform: translateY(-5px);">
				<?php endif; ?>

				<h1 style="margin: 0 0 5px 0; font-size: 2.2rem; font-weight: bold; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html( $user_data['name'] ); ?></h1>
				<p style="margin: 0 0 5px 0; font-size: 1.2rem; opacity: 0.95; text-shadow: 0 1px 2px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html( $user_data['title'] ); ?></p>
				<p style="margin: 0; font-size: 1.1rem; opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5); font-family: 'Mona Sans Extended', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transform: translateY(-5px);"><?php echo esc_html( $user_data['company'] ); ?></p>
			</div>
		</div>

		<!-- Action Buttons Section with Custom Branding -->
		<div class="frs-partner-portal-buttons" style="padding: 20px; background: white; max-width: 400px; margin: 0 auto;">
			<?php
			$phone_url = ! empty( $user_data['phone'] ) ? 'tel:' . preg_replace( '/[^0-9+]/', '', $user_data['phone'] ) : '';
			if ( $phone_url ) :
			?>
			<a href="<?php echo esc_url( $phone_url ); ?>" class="frs-partner-portal-button" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: <?php echo $branding['button_style'] === 'gradient' ? esc_attr( $bg_gradient ) : '#ffffff'; ?>; color: <?php echo $branding['button_style'] === 'gradient' ? '#ffffff' : '#000000'; ?>; border: <?php echo $branding['button_style'] === 'gradient' ? 'none' : '1px solid #ddd'; ?>; border-radius: <?php echo esc_attr( $border_radius ); ?>; text-decoration: none; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
				<span><?php _e( 'Call Me Now', 'lending-resource-hub' ); ?></span>
			</a>
			<?php endif; ?>

			<button onclick="showForm('scheduling')" class="frs-partner-portal-button" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: <?php echo $branding['button_style'] === 'gradient' ? esc_attr( $bg_gradient ) : '#ffffff'; ?>; color: <?php echo $branding['button_style'] === 'gradient' ? '#ffffff' : '#000000'; ?>; border: <?php echo $branding['button_style'] === 'gradient' ? 'none' : '1px solid #ddd'; ?>; border-radius: <?php echo esc_attr( $border_radius ); ?>; font-weight: 500; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
				<span><?php _e( 'Schedule Appointment', 'lending-resource-hub' ); ?></span>
			</button>

			<?php if ( $user_data['arrive'] ) : ?>
			<a href="<?php echo esc_url( $user_data['arrive'] ); ?>" target="_blank" class="frs-partner-portal-button" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 20px; margin-bottom: 15px; background: <?php echo esc_attr( $bg_gradient ); ?>; color: #ffffff; border: none; border-radius: <?php echo esc_attr( $border_radius ); ?>; text-decoration: none; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
				<span><?php _e( 'Get Pre-Approved', 'lending-resource-hub' ); ?></span>
			</a>
			<?php endif; ?>
		</div>

		<!-- Hidden Form: Schedule Appointment -->
		<div id="scheduling" data-form-id="7" style="display: none; padding: 20px; background: white;">
			<div style="width: 100%; max-width: 600px; margin: 0 auto;">
				<h2 style="color: #333; margin-bottom: 30px; text-align: center; font-size: 28px;"><?php _e( 'Schedule Appointment', 'lending-resource-hub' ); ?></h2>
				<?php
				if ( function_exists( 'wpFluentForm' ) ) {
					echo do_shortcode( '[fluentform type="conversational" id="7"]' );
				}
				?>
			</div>
		</div>

		<script>
		function showForm(formType) {
			const form = document.getElementById(formType);
			if (form) {
				form.style.display = 'block';
				form.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Update partner company portal featured image when page is saved.
	 *
	 * Uses the headshot of the first assigned loan officer.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function update_portal_featured_image( $post_id, $post ) {
		// Get first assigned loan officer's ID (use their headshot for featured image)
		$loan_officer_ids = carbon_get_post_meta( $post_id, 'pp_loan_officers' );
		$loan_officer_id  = ! empty( $loan_officer_ids ) ? $loan_officer_ids[0]['id'] : $post->post_author;

		if ( ! $loan_officer_id ) {
			return;
		}

		// Get profile
		$profile = Profile::where( 'user_id', $loan_officer_id )->first();

		if ( $profile && $profile->headshot_id ) {
			set_post_thumbnail( $post_id, $profile->headshot_id );
		}
	}

	/**
	 * Track partner company portal page view.
	 *
	 * Increments view counter each time the portal is visited.
	 *
	 * @return void
	 */
	public function track_portal_view() {
		if ( ! is_singular( 'frs_partner_portal' ) ) {
			return;
		}

		global $post;

		// Increment view count for analytics
		$views = (int) carbon_get_post_meta( $post->ID, '_pp_page_views' );
		carbon_set_post_meta( $post->ID, '_pp_page_views', $views + 1 );
	}

	/**
	 * Check if current user has access to this partner company portal.
	 *
	 * Access is granted to:
	 * - Administrators
	 * - Assigned loan officers for this partner company
	 * - BuddyPress group members (all realtors + LOs in the partner company)
	 * - Manually added realtors (edge cases)
	 *
	 * @param int $post_id Partner portal post ID.
	 * @param int $user_id User ID to check (defaults to current user).
	 * @return bool True if user has access.
	 */
	public static function user_has_portal_access( $post_id, $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Admin always has access
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check if user is one of the assigned loan officers
		$loan_officer_ids = carbon_get_post_meta( $post_id, 'pp_loan_officers' );
		if ( ! empty( $loan_officer_ids ) && in_array( $user_id, wp_list_pluck( $loan_officer_ids, 'id' ) ) ) {
			return true;
		}

		// Check BuddyPress group membership (all realtors + loan officers in the partner company group)
		$group_id = carbon_get_post_meta( $post_id, 'pp_buddypress_group_id' );
		if ( $group_id && function_exists( 'groups_is_user_member' ) ) {
			if ( groups_is_user_member( $user_id, $group_id ) ) {
				return true;
			}
		}

		// Check additional realtors (manual overrides)
		$additional_realtors = carbon_get_post_meta( $post_id, 'pp_additional_realtors' );
		if ( ! empty( $additional_realtors ) && in_array( $user_id, wp_list_pluck( $additional_realtors, 'id' ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Auto-generate partner portal page for a partner company.
	 *
	 * @param int          $buddypress_group_id BuddyPress group ID for the partner company.
	 * @param array|int    $loan_officer_ids Array of loan officer WordPress user IDs, or single ID.
	 * @param string       $portal_name Custom portal name (optional).
	 * @param string       $company_name Partner company name (optional).
	 * @return array|false Page data or false on failure.
	 */
	public static function generate_partner_portal( $buddypress_group_id, $loan_officer_ids, $portal_name = '', $company_name = '' ) {
		// Ensure loan_officer_ids is an array
		if ( ! is_array( $loan_officer_ids ) ) {
			$loan_officer_ids = array( $loan_officer_ids );
		}

		// Get first loan officer's profile for featured image
		$primary_lo_id = $loan_officer_ids[0];
		$profile = Profile::where( 'user_id', $primary_lo_id )->first();

		if ( ! $profile ) {
			return false;
		}

		// Generate portal name from BuddyPress group
		if ( empty( $portal_name ) ) {
			if ( function_exists( 'groups_get_group' ) ) {
				$group = groups_get_group( $buddypress_group_id );
				$portal_name = sprintf(
					__( '%s - Partner Portal', 'lending-resource-hub' ),
					$group->name
				);
				if ( empty( $company_name ) ) {
					$company_name = $group->name;
				}
			} else {
				$portal_name = __( 'Partner Portal', 'lending-resource-hub' );
				if ( empty( $company_name ) ) {
					$company_name = __( 'Partner Company', 'lending-resource-hub' );
				}
			}
		}

		// Generate unique slug
		$slug = sanitize_title( $portal_name );

		// Create page with partner portal block
		$page_content = '<!-- wp:lrh/partner-portal-page /-->';

		// Create the page
		$page_data = array(
			'post_title'   => $portal_name,
			'post_name'    => $slug,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_type'    => 'frs_partner_portal',
			'post_author'  => $primary_lo_id,
		);

		$page_id = wp_insert_post( $page_data );

		if ( is_wp_error( $page_id ) ) {
			return false;
		}

		// Set Carbon Fields meta
		$lo_associations = array();
		foreach ( $loan_officer_ids as $lo_id ) {
			$lo_associations[] = array( 'id' => $lo_id, 'type' => 'user' );
		}
		carbon_set_post_meta( $page_id, 'pp_loan_officers', $lo_associations );
		carbon_set_post_meta( $page_id, 'pp_buddypress_group_id', $buddypress_group_id );
		carbon_set_post_meta( $page_id, 'pp_company_name', $company_name );
		carbon_set_post_meta( $page_id, '_pp_page_views', 0 );

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
}
