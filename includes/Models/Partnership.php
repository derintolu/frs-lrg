<?php
/**
 * Class Partnership
 *
 * Represents the Partnership model for LendingResourceHub.
 *
 * @package LendingResourceHub\Models
 * @since 1.0.0
 */

namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

/**
 * Class Partnership
 *
 * Represents partnerships between loan officers and realtor partners.
 *
 * @package LendingResourceHub\Models
 */
class Partnership extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'partnerships';

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
		'loan_officer_id',
		'agent_id',
		'partner_post_id',
		'partner_email',
		'partner_name',
		'status',
		'invite_token',
		'invite_sent_date',
		'accepted_date',
		'custom_data',
		'created_date',
		'updated_date',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = array(
		'loan_officer_id' => 'integer',
		'agent_id'        => 'integer',
		'partner_post_id' => 'integer',
		'invite_sent_date' => 'datetime',
		'accepted_date'   => 'datetime',
		'created_date'    => 'datetime',
		'updated_date'    => 'datetime',
	);

	/**
	 * Get the loan officer user.
	 */
	public function loanOfficer() {
		return $this->belongsTo( Users::class, 'loan_officer_id' );
	}

	/**
	 * Get the agent/realtor user.
	 */
	public function agent() {
		return $this->belongsTo( Users::class, 'agent_id' );
	}

	/**
	 * Get leads for this partnership.
	 */
	public function leads() {
		return $this->hasMany( LeadSubmission::class, 'partnership_id' );
	}

	/**
	 * Scope for active partnerships.
	 */
	public function scopeActive( $query ) {
		return $query->where( 'status', 'active' );
	}

	/**
	 * Scope for pending partnerships.
	 */
	public function scopePending( $query ) {
		return $query->where( 'status', 'pending' );
	}

	/**
	 * Decode JSON custom_data field.
	 */
	public function getCustomDataAttribute( $value ) {
		return $value ? json_decode( $value, true ) : array();
	}

	/**
	 * Encode JSON custom_data field.
	 */
	public function setCustomDataAttribute( $value ) {
		$this->attributes['custom_data'] = is_array( $value ) ? wp_json_encode( $value ) : $value;
	}
}
