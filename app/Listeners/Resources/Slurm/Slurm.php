<?php
namespace App\Listeners\Resources\Slurm;

use App\Modules\Queues\Events\QueueReading;

/**
 * Slurm listener for Resources
 */
class Slurm
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(QueueReading::class, self::class . '@handleQueueReading');
	}

	/**
	 * Format queue allocations for Slurm
	 *
	 * @param   QueueReading  $event
	 * @return  void
	 */
	public function handleQueueReading(QueueReading $event)
	{
		$queue = $event->queue;

		if (!$queue->scheduler || !$queue->resource)
		{
			return;
		}

		if (!$queue->scheduler->batchsystm || strtolower($queue->scheduler->batchsystm->name) != 'slurm')
		{
			return;
		}

		// Convert Service Units (minutes) to seconds
		$queue->serviceunits = $queue->serviceunits * 60;

		$event->queue = $queue;
	}
}
