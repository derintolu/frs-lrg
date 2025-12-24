<?php
/**
 * Database migration for partnerships table.
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
 * Class Partnerships
 *
 * Represents the migration for creating the 'partnerships' table.
 *
 * @package LendingResourceHub\Database\Migrations
 */
class Partnerships implements Migration {

	/**
	 * Table name for the migration.
	 *
	 * @var string
	 */
	private static $table = 'partnerships';

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
				$table->unsignedBigInteger( 'loan_officer_id' );
				$table->unsignedBigInteger( 'agent_id' )->nullable();
				$table->unsignedBigInteger( 'partner_post_id' )->nullable();
				$table->string( 'partner_email' );
				$table->string( 'partner_name' )->nullable();
				$table->string( 'status', 20 )->default( 'pending' );
				$table->string( 'invite_token', 64 )->nullable();
				$table->dateTime( 'invite_sent_date' )->nullable();
				$table->dateTime( 'accepted_date' )->nullable();
				$table->longText( 'custom_data' )->nullable();
				$table->dateTime( 'created_date' )->nullable();
				$table->dateTime( 'updated_date' )->nullable();

				// Indexes for performance
				$table->index( 'loan_officer_id' );
				$table->index( 'agent_id' );
				$table->index( 'partner_post_id' );
				$table->index( 'partner_email' );
				$table->index( 'status' );
				$table->index( 'invite_token' );
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
