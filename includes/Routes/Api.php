<?php
/**
 * LendingResourceHub Routes
 *
 * Defines and registers custom API routes for the LendingResourceHub using the Haruncpi\WpApi library.
 *
 * @package LendingResourceHub\Routes
 */

namespace LendingResourceHub\Routes;

use LendingResourceHub\Libs\API\Route;

Route::prefix(
	LRH_ROUTE_PREFIX,
	function ( Route $route ) {

		// ====== EXAMPLE ROUTES (Keep for reference) ======
		$route->post( '/accounts/create', '\LendingResourceHub\Controllers\Accounts\Actions@create' );
		$route->get( '/accounts/get', '\LendingResourceHub\Controllers\Accounts\Actions@get' );
		$route->post( '/accounts/delete', '\LendingResourceHub\Controllers\Accounts\Actions@delete' );
		$route->post( '/accounts/update', '\LendingResourceHub\Controllers\Accounts\Actions@update' );
		$route->get( '/posts/get', '\LendingResourceHub\Controllers\Posts\Actions@get_all_posts' );
		$route->get( '/posts/get/{id}', '\LendingResourceHub\Controllers\Posts\Actions@get_post' );

		// ====== USER ROUTES ======
		$route->get( '/users/me', '\LendingResourceHub\Controllers\Users\Actions@get_current_user' );
		$route->get( '/users/me/person-profile', '\LendingResourceHub\Controllers\Users\Actions@get_person_profile' );
		$route->get( '/users/{id}', '\LendingResourceHub\Controllers\Users\Actions@get_user_by_id' );
		$route->put( '/users/{id}/profile', '\LendingResourceHub\Controllers\Users\Actions@update_profile' );
		$route->get( '/profile', '\LendingResourceHub\Controllers\Users\Actions@get_profile' );
		$route->post( '/profile', '\LendingResourceHub\Controllers\Users\Actions@update_profile_post' );

		// ====== PARTNERSHIP ROUTES ======
		$route->get( '/partnerships', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnerships' );
		$route->post( '/partnerships', '\LendingResourceHub\Controllers\Partnerships\Actions@create_partnership' );
		$route->post( '/partnerships/assign', '\LendingResourceHub\Controllers\Partnerships\Actions@assign_partnership' );
		$route->post( '/partnerships/invite', '\LendingResourceHub\Controllers\Partnerships\Actions@send_invitation' );
		$route->get( '/partnerships/lo/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnerships_for_lo' );
		$route->get( '/partnerships/realtor/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partnership_for_realtor' );
		$route->get( '/partnerships/loan-officers', '\LendingResourceHub\Controllers\Partnerships\Actions@get_loan_officers' );
		$route->get( '/partners/lo/{id}', '\LendingResourceHub\Controllers\Partnerships\Actions@get_partners_for_lo' );
		$route->get( '/realtor-partners', '\LendingResourceHub\Controllers\Partnerships\Actions@get_realtor_partners' );
		$route->put( '/partnerships/{id}/notifications', '\LendingResourceHub\Controllers\Partnerships\Actions@update_notification_preferences' );

		// ====== LEAD ROUTES ======
		$route->get( '/leads', '\LendingResourceHub\Controllers\Leads\Actions@get_leads' );
		$route->post( '/leads', '\LendingResourceHub\Controllers\Leads\Actions@create_lead' );
		$route->get( '/leads/lo/{id}', '\LendingResourceHub\Controllers\Leads\Actions@get_leads_for_lo' );
		$route->put( '/leads/{id}/status', '\LendingResourceHub\Controllers\Leads\Actions@update_lead_status' );
		$route->delete( '/leads/{id}', '\LendingResourceHub\Controllers\Leads\Actions@delete_lead' );
		$route->post( '/leads/{id}/notes', '\LendingResourceHub\Controllers\Leads\Actions@add_lead_note' );

		// ====== DASHBOARD STATS ROUTES ======
		$route->get( '/dashboard/stats', '\LendingResourceHub\Controllers\Dashboard\Stats@get_stats' );
		$route->get( '/dashboard/stats/lo/{id}', '\LendingResourceHub\Controllers\Dashboard\Stats@get_lo_stats' );
		$route->get( '/dashboard/stats/realtor/{id}', '\LendingResourceHub\Controllers\Dashboard\Stats@get_realtor_stats' );

		// ====== LANDING PAGES ROUTES ======
		$route->get( '/landing-pages/lo/{id}', '\LendingResourceHub\Controllers\LandingPages\Actions@get_landing_pages_for_lo' );
		$route->get( '/landing-pages/realtor/{id}', '\LendingResourceHub\Controllers\LandingPages\Actions@get_landing_pages_for_realtor' );
		$route->get( '/landing-pages/templates', '\LendingResourceHub\Controllers\LandingPages\Actions@get_templates' );

		// Page generation endpoints
		$route->post( '/pages/generate/biolink', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_biolink', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/prequal', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_prequal', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/openhouse', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_openhouse', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/mortgage', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_mortgage', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/tools', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_tools', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/calculator', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_calculator', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );
		$route->post( '/pages/generate/valuation', '\LendingResourceHub\Controllers\LandingPages\Actions@generate_valuation', '\LendingResourceHub\Controllers\LandingPages\Actions@check_generation_permissions' );

		// ====== MARKETING ROUTES ======
		$route->get( '/marketing-materials', '\LendingResourceHub\Controllers\Marketing\Actions@get_marketing_materials' );

		// ====== SETTINGS ROUTES ======
		$route->get( '/settings', '\LendingResourceHub\Controllers\Settings\Actions@get_settings' );
		$route->post( '/settings', '\LendingResourceHub\Controllers\Settings\Actions@update_settings' );
		$route->get( '/settings/system-info', '\LendingResourceHub\Controllers\Settings\Actions@get_system_info' );

		// ====== ANNOUNCEMENTS ROUTES ======
		$route->get( '/announcements', '\LendingResourceHub\Controllers\Announcements\Actions@get_announcements' );
		$route->get( '/announcements/{id}', '\LendingResourceHub\Controllers\Announcements\Actions@get_announcement' );

		// ====== CUSTOM LINKS ROUTES ======
		$route->get( '/custom-links', '\LendingResourceHub\Controllers\CustomLinks\Actions@get_custom_links' );

		// ====== FORM SUBMISSION ROUTES ======
		$route->post( '/form-submit', '\LendingResourceHub\Controllers\Forms\Actions@handle_form_submission' );
		$route->post( '/partnership-webhook', '\LendingResourceHub\Controllers\Forms\Actions@handle_partnership_webhook' );

		// ====== CALCULATOR & MORTGAGE LEAD ROUTES ======
		$route->post( '/calculator-leads', '\LendingResourceHub\Controllers\Leads\Actions@create_calculator_lead' );
		$route->post( '/mortgage-lead', '\LendingResourceHub\Controllers\Leads\Actions@create_mortgage_lead' );

		// ====== RENTCAST API ROUTES ======
		$route->get( '/rentcast/valuation', '\LendingResourceHub\Controllers\Rentcast\Actions@get_valuation' );

		// ====== CALENDAR ROUTES ======
		$route->post( '/calendar/setup', '\LendingResourceHub\Controllers\Calendar\Actions@setup_calendar' );
		$route->get( '/calendar/setup-status', '\LendingResourceHub\Controllers\Calendar\Actions@get_setup_status' );
		$route->post( '/calendar/complete-setup', '\LendingResourceHub\Controllers\Calendar\Actions@complete_setup' );
		$route->get( '/calendar/users', '\LendingResourceHub\Controllers\Calendar\Actions@get_calendar_users' );
		$route->post( '/calendar/reset', '\LendingResourceHub\Controllers\Calendar\Actions@reset_calendar' );

		// ====== SYSTEM DIAGNOSTIC ROUTES ======
		$route->get( '/system/diagnostics', '\LendingResourceHub\Controllers\System\Actions@get_diagnostics' );

		// Allow hooks to add more custom API routes.
		do_action( 'lrh_api', $route );
	}
);
