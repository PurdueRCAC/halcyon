<?php
namespace App\Listeners\Resources\Queues;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Events\AssetCreated;
use App\Modules\Resources\Events\SubresourceCreated;
use App\Modules\Resources\Events\SubresourceDeleted;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\Walltime;
use App\Modules\Queues\Models\Scheduler;

/**
 * Queue listener for resources
 */
class Queues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssetCreated::class, self::class . '@handleAssetCreated');
		$events->listen(SubresourceCreated::class, self::class . '@handleSubresourceCreated');
		$events->listen(SubresourceDeleted::class, self::class . '@handleSubresourceDeleted');
	}

	/**
	 * Create a default scheduler for a new compute asset
	 *
	 * @param   AssetCreated  $event
	 * @return  void
	 */
	public function handleAssetCreated(AssetCreated $event): void
	{
		if ($event->asset->resourcetype != 1)
		{
			return;
		}

		$child = $event->asset->children->first();

		$scheduler = new Scheduler;
		$scheduler->hostname = $event->asset->rolename . '-adm.' . str_replace('www', '', request()->getHost());
		if ($child)
		{
			$scheduler->queuesubresourceid = $child->subresourceid;
		}
		$scheduler->batchsystem = $event->asset->batchsystem;
		$scheduler->schedulerpolicyid = 1;
		$scheduler->defaultmaxwalltime = 1209600;
		$scheduler->save();
	}

	/**
	 * Set up a default Queue when a Subresource is created
	 *
	 * @param   SubresourceCreated  $event
	 * @return  void
	 */
	public function handleSubresourceCreated(SubresourceCreated $event): void
	{
		$subresource = $event->subresource;

		if (!$subresource->cluster)
		{
			return;
		}

		$queue = new Queue;

		$queue->name          = config()->get('module.queues.prefix', 'system-') . $subresource->cluster;
		$queue->cluster       = $subresource->cluster;
		$queue->groupid       = '-1';
		$queue->subresourceid = $subresource->id;
		$queue->queuetype     = 1;

		$walltime = 0;

		$scheduler = Scheduler::query()
			->where('queuesubresourceid', '=', $subresource->id)
			->first();

		if ($scheduler)
		{
			$queue->schedulerid = $scheduler->id;
			$queue->schedulerpolicyid = $scheduler->schedulerpolicyid;

			$walltime = $scheduler->defaultmaxwalltime;
		}

		$queue->maxjobsqueued     = config()->get('module.queues.maxjobsqueued', 12000);
		$queue->maxjobsqueueduser = config()->get('module.queues.maxjobsqueueduser', 5000);
		$queue->nodecoresmin = $subresource->nodecores;
		$queue->nodecoresmax = $subresource->nodecores;
		$queue->nodememmin   = $subresource->nodemem;
		$queue->nodememmax   = $subresource->nodemem;

		if ($queue->save())
		{
			$wtime = new Walltime;
			$wtime->queueid = $queue->id;
			$wtime->walltime = $walltime;
			$wtime->datetimestart = $queue->datetimecreated;
			$wtime->save();
		}
	}

	/**
	 * Mark schedulers & queues when a subresource is deleted
	 *
	 * @param   SubresourceDeleted  $event
	 * @return  void
	 */
	public function handleSubresourceDeleted(SubresourceDeleted $event): void
	{
		Scheduler::query()
			->where('queuesubresourceid', '=', $event->subresource->id)
			->delete();

		Queue::query()
			->where('subresourceid', '=', $event->subresource->id)
			->delete();
	}
}
