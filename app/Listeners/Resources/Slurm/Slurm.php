<?php
namespace App\Listeners\Resources\Slurm;

use App\Modules\Queues\Events\QueueReading;

/**
 * Knowledge base listener for Resources
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
	 * Unpublish linked pages when a resource is trashed
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

		$queue->serviceunits = $queue->totalserviceunits * 60;

		$event->queue = $queue;
	}
}
