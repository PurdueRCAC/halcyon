<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model for a queue loan
 */
class Loan extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueloans';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimestart',
		'datetimestop',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStarted()
	{
		// No start time means start immediately
		if (!$this->datetimestart
		 || $this->datetimestart == '0000-00-00 00:00:00'
		 || $this->datetimestart == '-0001-11-30 00:00:00')
		{
			return true;
		}
		return ($this->datetimestart >= Carbon::now());
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnded()
	{
		return ($this->datetimestop
			&& $this->datetimestop != '0000-00-00 00:00:00'
			&& $this->datetimestop != '-0001-11-30 00:00:00'
			&& $this->datetimestop < Carbon::now());
	}

	/**
	 * Defines a relationship to a queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function lender()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function source()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Get type
	 *
	 * @return  integer
	 */
	public function getTypeAttribute()
	{
		return 1;
	}
}
