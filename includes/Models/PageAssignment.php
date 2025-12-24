<?php
/**
 * Class PageAssignment
 *
 * Represents the PageAssignment model for LendingResourceHub.
 *
 * @package LendingResourceHub\Models
 * @since 1.0.0
 */

namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

/**
 * Class PageAssignment
 *
 * Represents page assignments to users.
 *
 * @package LendingResourceHub\Models
 */
class PageAssignment extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'page_assignments';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'user_id',
		'template_page_id',
		'assigned_page_id',
		'page_type',
		'slug_pattern',
		'created_date',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = array(
		'user_id'          => 'integer',
		'template_page_id' => 'integer',
		'assigned_page_id' => 'integer',
		'created_date'     => 'datetime',
	);

	/**
	 * Get the user that owns the page assignment.
	 */
	public function user() {
		return $this->belongsTo( Users::class, 'user_id' );
	}

	/**
	 * Scope for biolink pages.
	 */
	public function scopeBiolink( $query ) {
		return $query->where( 'page_type', 'biolink' );
	}

	/**
	 * Scope for prequal pages.
	 */
	public function scopePrequal( $query ) {
		return $query->where( 'page_type', 'prequal' );
	}

	/**
	 * Scope for open house pages.
	 */
	public function scopeOpenHouse( $query ) {
		return $query->where( 'page_type', 'openhouse' );
	}
}
