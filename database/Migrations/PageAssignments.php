<?php
/**
 * Database migration for page_assignments table.
 *
 * @package LendingResourceHub
 * @subpackage Database
 * @since 1.0.0
 */

namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use Prappo\WpEloquent\Database\Capsule\Manager as Capsule;
use Prappo\WpEloquent\Database\Schema\Blueprint;
use Prappo\WpEloquent\Support\Facades\Schema;

/**
 * Class PageAssignments
 *
 * Represents the migration for creating the 'page_assignments' table.
 *
 * @package LendingResourceHub\Database\Migrations
 */
class PageAssignments implements Migration {

	/**
	 * Table name for the migration.
	 *
	 * @var string
	 */
	private static $table = 'page_assignments';

	/**
	 * Run the migrations.
	 */
	public static function up() {
		if ( Capsule::schema()->hasTable( self::$table ) ) {
			return;
		}
		Capsule::schema()->create(
			self::$table,
			function ( Blueprint $table ) {
				$table->id();
				$table->unsignedBigInteger( 'user_id' );
				$table->unsignedBigInteger( 'template_page_id' );
				$table->unsignedBigInteger( 'assigned_page_id' );
				$table->string( 'page_type', 50 )->nullable();
				$table->string( 'slug_pattern' )->nullable();
				$table->dateTime( 'created_date' )->nullable();

				// Indexes for performance
				$table->index( 'user_id' );
				$table->index( 'template_page_id' );
				$table->index( 'assigned_page_id' );
				$table->index( 'page_type' );
			}
		);
	}

	/**
	 * Reverse the migrations.
	 */
	public static function down() {
		Schema::dropIfExists( self::$table );
	}
}
