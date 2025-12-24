<?php
/**
 * Database migration for lead_submissions table.
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
 * Class LeadSubmissions
 *
 * Represents the migration for creating the 'lead_submissions' table.
 *
 * @package LendingResourceHub\Database\Migrations
 */
class LeadSubmissions implements Migration {

	/**
	 * Table name for the migration.
	 *
	 * @var string
	 */
	private static $table = 'lead_submissions';

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
				$table->unsignedInteger( 'partnership_id' )->nullable();
				$table->unsignedBigInteger( 'loan_officer_id' )->nullable();
				$table->unsignedBigInteger( 'agent_id' )->nullable();
				$table->string( 'lead_source' )->nullable();
				$table->string( 'first_name' )->nullable();
				$table->string( 'last_name' )->nullable();
				$table->string( 'email' )->nullable();
				$table->string( 'phone', 20 )->nullable();
				$table->decimal( 'loan_amount', 12, 2 )->nullable();
				$table->decimal( 'property_value', 12, 2 )->nullable();
				$table->text( 'property_address' )->nullable();
				$table->text( 'message' )->nullable();
				$table->longText( 'lead_data' )->nullable();
				$table->longText( 'form_data' )->nullable();
				$table->longText( 'notes' )->nullable();
				$table->string( 'status', 20 )->default( 'new' );
				$table->dateTime( 'created_date' )->nullable();
				$table->dateTime( 'updated_date' )->nullable();

				// Indexes for performance
				$table->index( 'partnership_id' );
				$table->index( 'loan_officer_id' );
				$table->index( 'agent_id' );
				$table->index( 'lead_source' );
				$table->index( 'email' );
				$table->index( 'created_date' );
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
