<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Queue;

class QueueUpdating
{
	/**
	 * @var Queue
	 */
	private $queue;

	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * @return User
	 */
	public function getQueue()
	{
		return $this->queue;
	}
}
