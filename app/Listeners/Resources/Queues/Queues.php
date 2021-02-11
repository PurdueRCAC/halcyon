<?php
namespace App\Listeners\Resources\Queues;

use App\Modules\Resources\Events\SubresourceCreated;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Walltime;

/**
 * Queue listener for resources
 */
class Queues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(SubresourceCreated::class, self::class . '@handleSubresourceCreated');
	}

	/**
	 * Set up a default Queue when a Subresource is created
	 *
	 * @param   SubresourceCreated  $event
	 * @return  void
	 */
	public function handleSubresourceCreated(SubresourceCreated $event)
	{
		$subresource = $event->getSubresource();

		if (!$subresource->cluster)
		{
			return;
		}

		$queue = new Queue;

		$queue->name          = config()->get('queues.prefix', 'rcac-') . $subresource->cluster;
		$queue->cluster       = $subresource->cluster;
		$queue->groupid       = '-1';
		$queue->subresourceid = $subresource->id;
		$queue->queuetype     = 1;

		$walltime = 0;

		if ($assoc = $subresource->association)
		{
			$resource = $assoc->resource;

			$queue->schedulerid = $resource->schedulerid;
			$queue->schedulerpolicyid = $resource->schedulerpolicy_id;
			$walltime = $resource->defaultmaxwalltime;
		}

		$queue->maxjobsqueued     = config()->get('queues.maxjobsqueued', 12000);
		$queue->maxjobsqueueduser = config()->get('queues.maxjobsqueueduser', 5000);
		$queue->nodecoresmin = $subresource->nodecores;
		$queue->nodecoresmax = $subresource->nodecores;
		$queue->nodememmin   = $subresource->nodemem;
		$queue->nodememmax   = $subresource->nodemem;

		if ($queue->save())
		{
			$wtime = new Walltime;
			$wtime->queueid = $queue->id;
			$wtime->walltime = $walltime;
			$wtime->save();
		}
	}
}
