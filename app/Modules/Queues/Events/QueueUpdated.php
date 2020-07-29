<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Queue;

class QueueUpdated
{
	/**
	 * @var array
	 */
	public $data;

	/**
	 * @var Queue
	 */
	public $queue;

	/**
	 * Constructor
	 *
	 * @param Queue $queue
	 * @param array $data
	 * @return void
	 */
	public function __construct(Queue $queue, array $data)
	{
		$this->data = $data;
		$this->queue = $queue;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getQueue()
	{
		return $this->queue;
	}

	/**
	 * Return ALL data sent
	 *
	 * @return array
	 */
	public function getSubmissionData()
	{
		return $this->data;
	}
}
