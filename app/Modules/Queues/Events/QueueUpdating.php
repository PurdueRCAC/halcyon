<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Queue;

class QueueUpdating
{
	/**
	 * @var Queue
	 */
	public $queue;

	/**
	 * Constructor
	 *
	 * @param  Queue $queue
	 * @return void
	 */
	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}
}
