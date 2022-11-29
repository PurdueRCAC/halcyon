<?php

namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for a queue/qos mapping
 */
class QueueQos extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queueqoses';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Defines a relationship to a qos
	 *
	 * @return  object
	 */
	public function qos()
	{
		return $this->belongsTo(Qos::class, 'qosid')->withTrashed();
	}

	/**
	 * Defines a relationship to a queue
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid')->withTrashed();
	}
}
