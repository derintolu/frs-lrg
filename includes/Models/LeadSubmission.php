<?php
/**
 * Class LeadSubmission
 *
 * Represents the LeadSubmission model for LendingResourceHub.
 *
 * @package LendingResourceHub\Models
 * @since 1.0.0
 */

namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

/**
 * Class LeadSubmission
 *
 * Represents lead submissions from various forms.
 *
 * @package LendingResourceHub\Models
 */
class LeadSubmission extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'lead_submissions';

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
		'partnership_id',
		'loan_officer_id',
		'agent_id',
		'lead_source',
		'first_name',
		'last_name',
		'email',
		'phone',
		'loan_amount',
		'property_value',
		'property_address',
		'lead_data',
		'form_data',
		'status',
		'created_date',
		'updated_date',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @var array
	 */
	protected $casts = array(
		'partnership_id'  => 'integer',
		'loan_officer_id' => 'integer',
		'agent_id'        => 'integer',
		'loan_amount'     => 'decimal:2',
		'property_value'  => 'decimal:2',
		'created_date'    => 'datetime',
		'updated_date'    => 'datetime',
	);

	/**
	 * Get the partnership that owns the lead.
	 */
	public function partnership() {
		return $this->belongsTo( Partnership::class, 'partnership_id' );
	}

	/**
	 * Get the loan officer user.
	 */
	public function loanOfficer() {
		return $this->belongsTo( Users::class, 'loan_officer_id' );
	}

	/**
	 * Get the agent user.
	 */
	public function agent() {
		return $this->belongsTo( Users::class, 'agent_id' );
	}

	/**
	 * Decode JSON lead_data field.
	 */
	public function getLeadDataAttribute( $value ) {
		return $value ? json_decode( $value, true ) : null;
	}

	/**
	 * Encode JSON lead_data field.
	 */
	public function setLeadDataAttribute( $value ) {
		$this->attributes['lead_data'] = is_array( $value ) ? wp_json_encode( $value ) : $value;
	}

	/**
	 * Decode JSON form_data field.
	 */
	public function getFormDataAttribute( $value ) {
		return $value ? json_decode( $value, true ) : null;
	}

	/**
	 * Encode JSON form_data field.
	 */
	public function setFormDataAttribute( $value ) {
		$this->attributes['form_data'] = is_array( $value ) ? wp_json_encode( $value ) : $value;
	}

	/**
	 * Scope for new leads.
	 */
	public function scopeNew( $query ) {
		return $query->where( 'status', 'new' );
	}
}
