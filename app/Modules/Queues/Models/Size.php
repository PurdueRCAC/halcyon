<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
/*use App\Modules\Queues\Events\SizeCreating;
use App\Modules\Queues\Events\SizeCreated;
use App\Modules\Queues\Events\SizeUpdating;
use App\Modules\Queues\Events\SizeUpdated;
use App\Modules\Queues\Events\SizeDeleted;*/

/**
 * Model for a queue size
 */
class Size extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queuesizes';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimestart';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'queueid' => 'required|integer|min:1'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	/*protected $dispatchesEvents = [
		'creating' => SizeCreating::class,
		'created'  => SizeCreated::class,
		'updating' => SizeUpdating::class,
		'updated'  => SizeUpdated::class,
		'deleted'  => SizeDeleted::class,
	];*/

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStarted()
	{
		if (!$this->datetimestart || $this->datetimestart == '0000-00-00 00:00:00' && $this->datetimestart == '-0001-11-30 00:00:00')
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
		return ($this->datetimestop && $this->datetimestop != '0000-00-00 00:00:00' && $this->datetimestop != '-0001-11-30 00:00:00' && $this->datetimestop < Carbon::now());
	}

	/**
	 * Defines a relationship to queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to queue
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Queue::class, 'sellerqueueid');
	}

	/**
	 * Defines a relationship to seller
	 *
	 * @return  object
	 */
	public function source()
	{
		return $this->belongsTo(Queue::class, 'sellerqueueid');
	}

	/**
	 * Get type
	 *
	 * @return  integer
	 */
	public function getTypeAttribute()
	{
		return 0;
	}
}
