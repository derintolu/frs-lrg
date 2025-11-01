<?php
/**
 * Partnerships Controller
 *
 * Handles partnership-related API endpoints.
 *
 * @package LendingResourceHub\Controllers\Partnerships
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Partnerships;

use LendingResourceHub\Models\Partnership;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class Actions
 *
 * Handles partnership-related actions.
 *
 * @package LendingResourceHub\Controllers\Partnerships
 */
class Actions {

	/**
	 * Get all partnerships for current user.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnerships( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$status  = $request->get_param( 'status' );

		$query = Partnership::where( 'loan_officer_id', $user_id )
			->orWhere( 'agent_id', $user_id );

		if ( $status ) {
			$query->where( 'status', $status );
		}

		$partnerships = $query->orderBy( 'created_date', 'desc' )->get();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnerships,
			),
			200
		);
	}

	/**
	 * Create a new partnership invitation.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function create_partnership( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' );
		$partner_email   = sanitize_email( $request->get_param( 'email' ) );
		$partner_name    = sanitize_text_field( $request->get_param( 'name' ) );
		$message         = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( ! $partner_email ) {
			return new WP_Error( 'invalid_email', 'Valid email is required', array( 'status' => 400 ) );
		}

		// Check if partnership already exists
		$existing = Partnership::where( 'loan_officer_id', $loan_officer_id )
			->where( 'partner_email', $partner_email )
			->first();

		if ( $existing ) {
			return new WP_Error( 'partnership_exists', 'Partnership already exists', array( 'status' => 409 ) );
		}

		// Create partnership
		$partnership = Partnership::create(
			array(
				'loan_officer_id'  => $loan_officer_id,
				'partner_email'    => $partner_email,
				'partner_name'     => $partner_name,
				'status'           => 'pending',
				'invite_token'     => wp_generate_password( 32, false ),
				'invite_sent_date' => current_time( 'mysql' ),
				'created_date'     => current_time( 'mysql' ),
				'updated_date'     => current_time( 'mysql' ),
			)
		);

		// Send invitation email
		$email_sent = $this->send_invitation_email( $partnership, $message );

		if ( ! $email_sent ) {
			// Partnership created but email failed - log warning but don't fail the request
			error_log( '[LRH] Partnership created but invitation email failed to send (Partnership ID: ' . $partnership->id . ')' );
			return new WP_REST_Response(
				array(
					'success'      => true,
					'data'         => $partnership,
					'message'      => 'Partnership created but email notification failed. You can resend the invitation from the partnerships page.',
					'email_failed' => true,
				),
				201
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
				'message' => 'Partnership invitation sent successfully',
			),
			201
		);
	}

	/**
	 * Assign partnership directly (no invitation).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function assign_partnership( WP_REST_Request $request ) {
		$loan_officer_id = $request->get_param( 'loan_officer_id' );
		$realtor_id      = $request->get_param( 'realtor_id' );

		if ( ! $loan_officer_id || ! $realtor_id ) {
			return new WP_Error( 'missing_params', 'Loan officer ID and realtor ID are required', array( 'status' => 400 ) );
		}

		// Get realtor user data
		$realtor = get_userdata( $realtor_id );
		if ( ! $realtor ) {
			return new WP_Error( 'invalid_realtor', 'Realtor not found', array( 'status' => 404 ) );
		}

		// Check if partnership already exists
		$existing = Partnership::where( 'loan_officer_id', $loan_officer_id )
			->where( 'agent_id', $realtor_id )
			->first();

		if ( $existing ) {
			return new WP_Error( 'partnership_exists', 'Partnership already exists', array( 'status' => 409 ) );
		}

		// Create active partnership
		$partnership = Partnership::create(
			array(
				'loan_officer_id' => $loan_officer_id,
				'agent_id'        => $realtor_id,
				'partner_email'   => $realtor->user_email,
				'partner_name'    => $realtor->display_name,
				'status'          => 'active',
				'accepted_date'   => current_time( 'mysql' ),
				'created_date'    => current_time( 'mysql' ),
				'updated_date'    => current_time( 'mysql' ),
			)
		);

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
				'message' => 'Partnership assigned successfully',
			),
			201
		);
	}

	/**
	 * Get partnerships for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnerships_for_lo( WP_REST_Request $request ) {
		$lo_id = $request->get_param( 'id' );

		$partnerships = Partnership::where( 'loan_officer_id', $lo_id )
			->orderBy( 'created_date', 'desc' )
			->get();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnerships,
			),
			200
		);
	}

	/**
	 * Get partnership for realtor.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partnership_for_realtor( WP_REST_Request $request ) {
		$realtor_id = $request->get_param( 'id' );

		$partnership = Partnership::where( 'agent_id', $realtor_id )
			->first();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $partnership,
			),
			200
		);
	}

	/**
	 * Get partners (realtor users) for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_partners_for_lo( WP_REST_Request $request ) {
		$lo_id = $request->get_param( 'id' );

		$partnerships = Partnership::where( 'loan_officer_id', $lo_id )
			->where( 'status', 'active' )
			->get();

		// Extract partner data
		$partners = array();
		foreach ( $partnerships as $partnership ) {
			if ( $partnership->agent_id ) {
				$agent = get_userdata( $partnership->agent_id );
				if ( $agent ) {
					$partners[] = array(
						'id'            => $agent->ID,
						'name'          => $agent->display_name,
						'email'         => $agent->user_email,
						'role'          => 'realtor',
						'status'        => 'active',
						'createdAt'     => $partnership->created_date,
						'partnershipId' => $partnership->id,
					);
				}
			}
		}

		return new WP_REST_Response( $partners, 200 );
	}

	/**
	 * Get all realtor partners grouped with their LO partnerships.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_realtor_partners( WP_REST_Request $request ) {
		global $wpdb;

		// Get all users with realtor_partner role
		$realtor_users = get_users( array( 'role' => 'realtor_partner' ) );

		$realtor_partners = array();
		foreach ( $realtor_users as $realtor ) {
			// Get all partnerships for this realtor with LO names
			$partnerships = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.*, u.display_name as lo_name, u.user_email as lo_email
					FROM {$wpdb->prefix}partnerships p
					LEFT JOIN {$wpdb->users} u ON p.loan_officer_id = u.ID
					WHERE p.agent_id = %d
					ORDER BY p.created_date DESC",
					$realtor->ID
				)
			);

			$realtor_partners[] = array(
				'realtor'      => array(
					'id'           => $realtor->ID,
					'display_name' => $realtor->display_name,
					'email'        => $realtor->user_email,
				),
				'partnerships' => $partnerships,
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $realtor_partners,
			),
			200
		);
	}

	/**
	 * Get all loan officers for dropdowns.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_loan_officers( WP_REST_Request $request ) {
		$loan_officers = get_users( array( 'role' => 'loan_officer' ) );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $loan_officers,
			),
			200
		);
	}

	/**
	 * Send partnership invitation email.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function send_invitation( WP_REST_Request $request ) {
		$partnership_id = $request->get_param( 'partnership_id' );
		$message        = sanitize_textarea_field( $request->get_param( 'message' ) );

		$partnership = Partnership::find( $partnership_id );

		if ( ! $partnership ) {
			return new WP_Error( 'partnership_not_found', 'Partnership not found', array( 'status' => 404 ) );
		}

		// Send invitation email
		$email_sent = $this->send_invitation_email( $partnership, $message );

		if ( ! $email_sent ) {
			return new WP_Error( 'email_failed', 'Failed to send invitation email', array( 'status' => 500 ) );
		}

		$partnership->invite_sent_date = current_time( 'mysql' );
		$partnership->updated_date     = current_time( 'mysql' );
		$partnership->save();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Invitation sent successfully',
			),
			200
		);
	}

	/**
	 * Send partnership invitation email.
	 *
	 * @param Partnership $partnership The partnership object.
	 * @param string      $message     Custom message from loan officer.
	 * @return bool True if email sent successfully, false otherwise.
	 */
	private function send_invitation_email( $partnership, $message = '' ) {
		// Get loan officer details
		$loan_officer = get_userdata( $partnership->loan_officer_id );
		if ( ! $loan_officer ) {
			error_log( '[LRH] Failed to send invitation: Loan officer not found (ID: ' . $partnership->loan_officer_id . ')' );
			return false;
		}

		// Try to get loan officer profile from frs-wp-users
		$lo_profile = null;
		if ( class_exists( 'FRSUsers\\Models\\Profile' ) ) {
			$lo_profile = \FRSUsers\Models\Profile::where( 'user_id', $partnership->loan_officer_id )->first();
		}

		// Build acceptance URL
		$accept_url = add_query_arg(
			array(
				'action' => 'accept_partnership',
				'token'  => $partnership->invite_token,
			),
			home_url( '/portal/' )
		);

		// Prepare email data
		$to          = $partnership->partner_email;
		$subject     = sprintf( '%s invited you to partner on 21st Century Lending Hub', $loan_officer->display_name );
		$lo_name     = $loan_officer->display_name;
		$lo_email    = $loan_officer->user_email;
		$lo_phone    = $lo_profile ? $lo_profile->phone_number : '';
		$partner_name = $partnership->partner_name ? $partnership->partner_name : 'there';
		$site_name   = get_bloginfo( 'name' );

		// Build HTML email
		$email_body = $this->get_invitation_email_template(
			array(
				'partner_name' => $partner_name,
				'lo_name'      => $lo_name,
				'lo_email'     => $lo_email,
				'lo_phone'     => $lo_phone,
				'message'      => $message,
				'accept_url'   => $accept_url,
				'site_name'    => $site_name,
			)
		);

		// Set email headers for HTML
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $site_name . ' <noreply@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>',
			'Reply-To: ' . $lo_name . ' <' . $lo_email . '>',
		);

		// Send email
		$sent = wp_mail( $to, $subject, $email_body, $headers );

		if ( ! $sent ) {
			error_log( '[LRH] Failed to send partnership invitation email to: ' . $to );
		} else {
			error_log( '[LRH] Partnership invitation email sent successfully to: ' . $to );
		}

		return $sent;
	}

	/**
	 * Get partnership invitation email template.
	 *
	 * @param array $args Template arguments.
	 * @return string HTML email template.
	 */
	private function get_invitation_email_template( $args ) {
		$defaults = array(
			'partner_name' => 'there',
			'lo_name'      => '',
			'lo_email'     => '',
			'lo_phone'     => '',
			'message'      => '',
			'accept_url'   => '',
			'site_name'    => get_bloginfo( 'name' ),
		);

		$args = wp_parse_args( $args, $defaults );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Partnership Invitation</title>
		</head>
		<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f4f4; padding: 20px;">
				<tr>
					<td align="center">
						<table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
							<!-- Header -->
							<tr>
								<td style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
									<h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">Partnership Invitation</h1>
								</td>
							</tr>

							<!-- Body -->
							<tr>
								<td style="padding: 40px 30px;">
									<p style="font-size: 18px; color: #1f2937; margin: 0 0 20px;">Hi <?php echo esc_html( $args['partner_name'] ); ?>,</p>

									<p style="font-size: 16px; color: #4b5563; line-height: 1.6; margin: 0 0 20px;">
										<strong><?php echo esc_html( $args['lo_name'] ); ?></strong> has invited you to partner with them on the <strong><?php echo esc_html( $args['site_name'] ); ?></strong>.
									</p>

									<?php if ( ! empty( $args['message'] ) ) : ?>
										<div style="background-color: #f3f4f6; border-left: 4px solid #3b82f6; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
											<p style="font-size: 14px; color: #1f2937; margin: 0; font-style: italic;">
												"<?php echo nl2br( esc_html( $args['message'] ) ); ?>"
											</p>
										</div>
									<?php endif; ?>

									<p style="font-size: 16px; color: #4b5563; line-height: 1.6; margin: 20px 0;">
										By accepting this invitation, you'll be able to:
									</p>

									<ul style="font-size: 15px; color: #4b5563; line-height: 1.8; margin: 0 0 30px; padding-left: 20px;">
										<li>Collaborate on leads and referrals</li>
										<li>Access shared resources and tools</li>
										<li>Track partnership performance</li>
										<li>Streamline communication</li>
									</ul>

									<!-- CTA Button -->
									<table cellpadding="0" cellspacing="0" border="0" width="100%">
										<tr>
											<td align="center" style="padding: 20px 0;">
												<a href="<?php echo esc_url( $args['accept_url'] ); ?>" style="display: inline-block; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 6px; font-size: 18px; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
													Accept Invitation
												</a>
											</td>
										</tr>
									</table>

									<p style="font-size: 14px; color: #6b7280; margin: 30px 0 0; line-height: 1.6;">
										If the button doesn't work, copy and paste this link into your browser:<br>
										<a href="<?php echo esc_url( $args['accept_url'] ); ?>" style="color: #3b82f6; word-break: break-all;"><?php echo esc_url( $args['accept_url'] ); ?></a>
									</p>
								</td>
							</tr>

							<!-- Footer -->
							<tr>
								<td style="background-color: #f9fafb; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
									<p style="font-size: 14px; color: #6b7280; margin: 0 0 10px;">
										<strong>Contact <?php echo esc_html( $args['lo_name'] ); ?></strong>
									</p>
									<p style="font-size: 13px; color: #9ca3af; margin: 0;">
										<?php if ( ! empty( $args['lo_email'] ) ) : ?>
											Email: <a href="mailto:<?php echo esc_attr( $args['lo_email'] ); ?>" style="color: #3b82f6; text-decoration: none;"><?php echo esc_html( $args['lo_email'] ); ?></a><br>
										<?php endif; ?>
										<?php if ( ! empty( $args['lo_phone'] ) ) : ?>
											Phone: <?php echo esc_html( $args['lo_phone'] ); ?><br>
										<?php endif; ?>
									</p>
									<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
									<p style="font-size: 12px; color: #9ca3af; margin: 0;">
										&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $args['site_name'] ); ?>. All rights reserved.
									</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Update notification preferences for a partnership.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error The response.
	 */
	public function update_notification_preferences( WP_REST_Request $request ) {
		$partnership_id = $request->get_param( 'id' );
		$partnership    = Partnership::find( $partnership_id );

		if ( ! $partnership ) {
			return new WP_Error( 'partnership_not_found', 'Partnership not found', array( 'status' => 404 ) );
		}

		// Verify user is part of this partnership
		$current_user_id = get_current_user_id();
		if ( $partnership->loan_officer_id != $current_user_id &&
			 $partnership->agent_id != $current_user_id &&
			 ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', 'Not authorized to update this partnership', array( 'status' => 403 ) );
		}

		// Get notification preferences from request
		$preferences = $request->get_json_params();

		// Sanitize notification preferences
		$sanitized_preferences = array();
		if ( isset( $preferences['emailOnNewLead'] ) ) {
			$sanitized_preferences['emailOnNewLead'] = (bool) $preferences['emailOnNewLead'];
		}
		if ( isset( $preferences['emailOnStatusChange'] ) ) {
			$sanitized_preferences['emailOnStatusChange'] = (bool) $preferences['emailOnStatusChange'];
		}
		if ( isset( $preferences['emailOnPartnerActivity'] ) ) {
			$sanitized_preferences['emailOnPartnerActivity'] = (bool) $preferences['emailOnPartnerActivity'];
		}
		if ( isset( $preferences['smsNotifications'] ) ) {
			$sanitized_preferences['smsNotifications'] = (bool) $preferences['smsNotifications'];
		}

		// Get existing custom_data and merge notification preferences
		$custom_data                           = $partnership->custom_data ?: array();
		$custom_data['notification_preferences'] = $sanitized_preferences;

		// Update partnership
		$partnership->custom_data  = $custom_data;
		$partnership->updated_date = current_time( 'mysql' );
		$partnership->save();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => 'Notification preferences updated successfully',
				'data'    => array(
					'partnershipId'           => $partnership->id,
					'notification_preferences' => $sanitized_preferences,
				),
			),
			200
		);
	}
}
