<?php
/**
 * Admin Dashboard Page
 *
 * Traditional PHP admin page (not React).
 *
 * @package LendingResourceHub\Admin
 * @since 1.0.0
 */

namespace LendingResourceHub\Admin;

use LendingResourceHub\Models\Partnership;
use LendingResourceHub\Models\LeadSubmission;
use LendingResourceHub\Traits\Base;

/**
 * Class Dashboard
 *
 * Handles the main admin dashboard page.
 *
 * @package LendingResourceHub\Admin
 */
class Dashboard {

	use Base;

	/**
	 * Render the dashboard page.
	 *
	 * @return void
	 */
	public function render() {
		// Get summary statistics using Eloquent
		$total_partnerships   = Partnership::count();
		$active_partnerships  = Partnership::where( 'status', 'active' )->count();
		$pending_partnerships = Partnership::where( 'status', 'pending' )->count();
		$total_leads          = LeadSubmission::count();
		$recent_leads         = LeadSubmission::where( 'created_date', '>=', date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ) )->count();

		// Get user counts
		$loan_officers_count = count( get_users( array( 'role' => 'loan_officer' ) ) );
		$agents_count        = count( get_users( array( 'role' => 'realtor_partner' ) ) );

		// Load template
		include LRH_DIR . 'views/admin/dashboard.php';
	}
}
