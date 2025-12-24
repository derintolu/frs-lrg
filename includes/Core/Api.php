<?php

namespace LendingResourceHub\Core;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\API\Config;

/**
 * Class API
 *
 * Initializes and configures the API for the LendingResourceHub.
 *
 * @package LendingResourceHub\Core
 */
class API {

	use Base;

	/**
	 * Initializes the API for the LendingResourceHub.
	 *
	 * @return void
	 */
	public function init() {
		Config::set_route_file( LRH_DIR . '/includes/Routes/Api.php' )
			->set_namespace( 'LendingResourceHub\Api' )
			->init();
	}
}
