<?php

namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for a queue/qos mapping
 *
 * @property int    $id
 * @property int    $qosid
 * @property int    $queueid
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
	 * @return  BelongsTo
	 */
	public function qos(): BelongsTo
	{
		return $this->belongsTo(Qos::class, 'qosid')->withTrashed();
	}

	/**
	 * Defines a relationship to a queue
	 *
	 * @return  BelongsTo
	 */
	public function queue(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'queueid')->withTrashed();
	}
}
