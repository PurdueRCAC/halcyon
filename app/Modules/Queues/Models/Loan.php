<?php
namespace App\Modules\Queues\Models;

use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Queues\Events\QueueLoanUpdated;
use App\Modules\Queues\Events\QueueLoanDeleted;
use Carbon\Carbon;

/**
 * Model for a queue loan
 */
class Loan extends Size
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueloans';

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created' => QueueLoanCreated::class,
		'updated' => QueueLoanUpdated::class,
		'deleted' => QueueLoanDeleted::class,
	];

	/**
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to a lender queue
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
