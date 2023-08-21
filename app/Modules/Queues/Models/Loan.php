<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Queues\Events\QueueLoanUpdated;
use App\Modules\Queues\Events\QueueLoanDeleted;
use Carbon\Carbon;

/**
 * Model for a queue loan
 *
 * @property int    $id
 * @property int    $queueid
 * @property Carbon|null $datetimestart
 * @property Carbon|null $datetimestop
 * @property int    $nodecount
 * @property int    $corecount
 * @property int    $lenderqueueid
 * @property string $comment
 * @property float  $serviceunits
 *
 * @property string $api
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
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => QueueLoanCreated::class,
		'updated' => QueueLoanUpdated::class,
		'deleted' => QueueLoanDeleted::class,
	];

	/**
	 * Defines a relationship to a lender
	 *
	 * Note: Thsi is an override from the Size class just to
	 * ensure it returns the correct object.
	 *
	 * @return  BelongsTo
	 */
	public function seller(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to a lender queue
	 *
	 * @return  BelongsTo
	 */
	public function lender(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Defines a relationship to a source queue
	 *
	 * @return  BelongsTo
	 */
	public function source(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
	}

	/**
	 * Get type
	 *
	 * @return  int
	 */
	public function getTypeAttribute(): int
	{
		return 1;
	}
}
