<?php
namespace App\Modules\Queues\Models;

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
	 * Defines a relationship to a lender
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Queue::class, 'lenderqueueid');
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
