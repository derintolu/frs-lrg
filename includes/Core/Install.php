<?php

namespace LendingResourceHub\Core;

use LendingResourceHub\Database\Migrations\Accounts;
use LendingResourceHub\Database\Migrations\Partnerships;
use LendingResourceHub\Database\Migrations\LeadSubmissions;
use LendingResourceHub\Database\Migrations\PageAssignments;
use LendingResourceHub\Database\Migrations\MigrateOldData;
use LendingResourceHub\Database\Seeders\Accounts as SeedersAccounts;
use LendingResourceHub\Traits\Base;

/**
 * This class is responsible for the functionality
 * which is required to set up after activating the plugin
 */
class Install {


	use Base;

	/**
	 * Initialize the class
	 *
	 * @return void
	 */
	public function init() {

		$this->install_pages();
		$this->install_tables();
		$this->insert_data();

		// Register post types before flushing to ensure rules are generated
		PostTypes::get_instance()->register_post_types();

		// Flush rewrite rules to register our custom URLs
		flush_rewrite_rules();
	}

	/**
	 * Install the pages
	 *
	 * @return void
	 */
	private function install_pages() {
		lrh_install_page(
			Template::FRONTEND_TEMPLATE_NAME,
			Template::FRONTEND_TEMPLATE_SLUG,
			Template::FRONTEND_TEMPLATE
		);
	}

	/**
	 * Install the tables
	 *
	 * @return void
	 */
	private function install_tables() {
		// Original example table
		Accounts::up();

		// Portal tables
		Partnerships::up();
		LeadSubmissions::up();
		PageAssignments::up();

		// Migrate data from old plugin
		MigrateOldData::up();
	}

	/**
	 * Insert data to the tables
	 *
	 * @return void
	 */
	private function insert_data() {
		// Insert data to the tables.
		SeedersAccounts::run();
	}
}
