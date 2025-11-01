<?php
/**
 * Dashboard Stats Controller
 *
 * Handles dashboard statistics API endpoints.
 *
 * @package LendingResourceHub\Controllers\Dashboard
 * @since 1.0.0
 */

namespace LendingResourceHub\Controllers\Dashboard;

use LendingResourceHub\Models\Partnership;
use LendingResourceHub\Models\LeadSubmission;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Stats
 *
 * Handles dashboard statistics.
 *
 * @package LendingResourceHub\Controllers\Dashboard
 */
class Stats {

	/**
	 * Get dashboard stats for loan officer.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_lo_stats( WP_REST_Request $request ) {
		$lo_id = $request->get_param( 'id' );

		// Active partnerships
		$active_partnerships = Partnership::where( 'loan_officer_id', $lo_id )
			->where( 'status', 'active' )
			->count();

		// Pending invitations
		$pending_invitations = Partnership::where( 'loan_officer_id', $lo_id )
			->where( 'status', 'pending' )
			->count();

		// Total leads
		$total_leads = LeadSubmission::where( 'loan_officer_id', $lo_id )->count();

		// New leads this month
		$new_leads_this_month = LeadSubmission::where( 'loan_officer_id', $lo_id )
			->where( 'created_date', '>=', date( 'Y-m-01' ) )
			->count();

		// Co-branded pages (partnerships with active status)
		$cobranded_pages = $active_partnerships;

		// Conversion rate - calculate percentage of closed leads
		$closed_leads    = LeadSubmission::where( 'loan_officer_id', $lo_id )
			->where( 'status', 'closed' )
			->count();
		$conversion_rate = $total_leads > 0 ? round( ( $closed_leads / $total_leads ) * 100, 2 ) : 0;

		// Top performing pages - partnerships ranked by lead count
		$top_performing_pages = $this->get_top_performing_partnerships( $lo_id, 'loan_officer' );

		return new WP_REST_Response(
			array(
				'success'             => true,
				'activePartnerships'  => $active_partnerships,
				'pendingInvitations'  => $pending_invitations,
				'totalLeads'          => $total_leads,
				'conversionRate'      => $conversion_rate,
				'coBrandedPages'      => $cobranded_pages,
				'newLeadsThisMonth'   => $new_leads_this_month,
				'topPerformingPages'  => $top_performing_pages,
			),
			200
		);
	}

	/**
	 * Get dashboard stats for realtor.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_realtor_stats( WP_REST_Request $request ) {
		$realtor_id = $request->get_param( 'id' );

		// Active partnerships
		$active_partnerships = Partnership::where( 'agent_id', $realtor_id )
			->where( 'status', 'active' )
			->count();

		// Pending invitations
		$pending_invitations = Partnership::where( 'agent_id', $realtor_id )
			->where( 'status', 'pending' )
			->count();

		// Total leads
		$total_leads = LeadSubmission::where( 'agent_id', $realtor_id )->count();

		// New leads this month
		$new_leads_this_month = LeadSubmission::where( 'agent_id', $realtor_id )
			->where( 'created_date', '>=', date( 'Y-m-01' ) )
			->count();

		// Co-branded pages
		$cobranded_pages = $active_partnerships;

		// Conversion rate - calculate percentage of closed leads
		$closed_leads    = LeadSubmission::where( 'agent_id', $realtor_id )
			->where( 'status', 'closed' )
			->count();
		$conversion_rate = $total_leads > 0 ? round( ( $closed_leads / $total_leads ) * 100, 2 ) : 0;

		// Top performing pages - partnerships ranked by lead count
		$top_performing_pages = $this->get_top_performing_partnerships( $realtor_id, 'realtor' );

		return new WP_REST_Response(
			array(
				'success'             => true,
				'activePartnerships'  => $active_partnerships,
				'pendingInvitations'  => $pending_invitations,
				'totalLeads'          => $total_leads,
				'conversionRate'      => $conversion_rate,
				'coBrandedPages'      => $cobranded_pages,
				'newLeadsThisMonth'   => $new_leads_this_month,
				'topPerformingPages'  => $top_performing_pages,
			),
			200
		);
	}

	/**
	 * Get top performing partnerships ranked by lead count.
	 *
	 * @param int    $user_id The user ID.
	 * @param string $user_type Type of user (loan_officer or realtor).
	 * @return array Top performing partnerships.
	 */
	private function get_top_performing_partnerships( $user_id, $user_type = 'loan_officer' ) {
		global $wpdb;

		$id_field = $user_type === 'loan_officer' ? 'loan_officer_id' : 'agent_id';

		// Get partnerships with lead counts
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					p.id as partnership_id,
					p.partner_name,
					p.partner_email,
					p.status,
					COUNT(l.id) as lead_count,
					SUM(CASE WHEN l.status = 'closed' THEN 1 ELSE 0 END) as closed_count
				FROM {$wpdb->prefix}partnerships p
				LEFT JOIN {$wpdb->prefix}lead_submissions l ON p.id = l.partnership_id
				WHERE p.{$id_field} = %d AND p.status = 'active'
				GROUP BY p.id
				ORDER BY lead_count DESC, closed_count DESC
				LIMIT 5",
				$user_id
			)
		);

		// Format results
		$top_pages = array();
		foreach ( $results as $row ) {
			$conversion_rate = $row->lead_count > 0
				? round( ( $row->closed_count / $row->lead_count ) * 100, 2 )
				: 0;

			$top_pages[] = array(
				'partnershipId'  => (int) $row->partnership_id,
				'partnerName'    => $row->partner_name,
				'partnerEmail'   => $row->partner_email,
				'leadCount'      => (int) $row->lead_count,
				'closedCount'    => (int) $row->closed_count,
				'conversionRate' => $conversion_rate,
			);
		}

		return $top_pages;
	}

	/**
	 * Get global dashboard stats (for admins).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response The response.
	 */
	public function get_stats( WP_REST_Request $request ) {
		global $wpdb;

		// Stats
		$active_partnerships = Partnership::where( 'status', 'active' )->count();
		$pending_invitations = Partnership::where( 'status', 'pending' )->count();
		$total_leads         = LeadSubmission::count();
		$seven_days_ago      = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
		$recent_leads        = LeadSubmission::where( 'created_date', '>=', $seven_days_ago )->count();

		// Recent partnerships with loan officer names
		$recent_partnerships = $wpdb->get_results(
			"SELECT p.*, u.display_name as lo_name
			FROM {$wpdb->prefix}partnerships p
			LEFT JOIN {$wpdb->users} u ON p.loan_officer_id = u.ID
			ORDER BY p.created_date DESC
			LIMIT 5"
		);

		// Recent leads with names
		$recent_leads_data = $wpdb->get_results(
			"SELECT l.*,
				u1.display_name as lo_name,
				u2.display_name as agent_name
			FROM {$wpdb->prefix}lead_submissions l
			LEFT JOIN {$wpdb->users} u1 ON l.loan_officer_id = u1.ID
			LEFT JOIN {$wpdb->users} u2 ON l.agent_id = u2.ID
			ORDER BY l.created_date DESC
			LIMIT 5"
		);

		// User counts
		$loan_officers_count = count( get_users( array( 'role' => 'loan_officer' ) ) );
		$realtors_count      = count( get_users( array( 'role' => 'realtor_partner' ) ) );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'stats'          => array(
						'activePartnerships' => $active_partnerships,
						'pendingInvitations' => $pending_invitations,
						'totalLeads'         => $total_leads,
						'recentLeads'        => $recent_leads,
					),
					'recentActivity' => array(
						'partnerships' => $recent_partnerships,
						'leads'        => $recent_leads_data,
					),
					'userCounts'     => array(
						'loanOfficers' => $loan_officers_count,
						'realtors'     => $realtors_count,
					),
				),
			),
			200
		);
	}
}
