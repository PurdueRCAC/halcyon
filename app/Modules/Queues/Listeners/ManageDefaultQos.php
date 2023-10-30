<?php

namespace App\Modules\Queues\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\QueueDeleted;
use App\Modules\Queues\Events\QueueSizeCreated;
use App\Modules\Queues\Events\QueueSizeUpdated;
use App\Modules\Queues\Events\QueueSizeDeleted;
use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Queues\Events\QueueLoanUpdated;
use App\Modules\Queues\Events\QueueLoanDeleted;
use App\Modules\Queues\Models\Qos;
use App\Modules\Queues\Models\QueueQos;
use App\Modules\Queues\Models\Queue;

/**
 * Manage a default QoS for new Queues
 */
class ManageDefaultQos
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		// This is disabled because it will result in a QoS with zero values/limits.
		// Since queues with no allocations don't get set, it's pointless to create
		// a QoS for them. Instead, do it when an allocation is actually assigned.
		//$events->listen(QueueCreated::class, self::class . '@handleQueueCreated');

		$events->listen(QueueDeleted::class, self::class . '@handleQueueDeleted');

		$events->listen(QueueSizeCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeDeleted::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanDeleted::class, self::class . '@handleQueueAllocation');
	}

	/**
	 * Check if this listener should handle this Queue
	 *
	 * @param  Queue|null $queue
	 * @return bool
	 */
	private function canProcessQueue($queue): bool
	{
		if (!$queue)
		{
			return false;
		}

		if (!$queue->scheduler
		 || !$queue->scheduler->resource
		 || !$queue->scheduler->resource->rolename)
		{
			return false;
		}

		$facet = $queue->scheduler->resource->getFacet('slurmapi');

		if (!$facet || !$facet->value || strtolower($facet->value) == 'no')
		{
			return false;
		}

		return true;
	}

	/**
	 * Create a default Qos
	 *
	 * @param   QueueCreated  $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event): void
	{
		$queue = $event->queue;

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		if ($queue->isSystem())
		{
			return;
		}

		$this->setDefaultQos($queue);
	}

	/**
	 * Delete default Qos
	 *
	 * @param   QueueDeleted  $event
	 * @return  void
	 */
	public function handleQueueDeleted(QueueDeleted $event): void
	{
		$queue = $event->queue;

		$qos = Qos::query()
			->where('name', '=', $queue->defaultQosName)
			->where('scheduler_id', '=', $queue->schedulerid)
			->first();

		if ($qos)
		{
			$qos->delete();
		}
	}

	/**
	 * Update QoS with new allocation information
	 *
	 * @param   QueueSizeCreated|QueueSizeUpdated|QueueSizeDeleted|QueueLoanCreated|QueueLoanUpdated|QueueLoanDeleted  $event
	 * @return  void
	 */
	public function handleQueueAllocation($event): void
	{
		$queue = null;

		if ($event instanceof QueueSizeCreated
		 || $event instanceof QueueSizeUpdated
		 || $event instanceof QueueSizeDeleted)
		{
			$queue = $event->size->queue;
		}
		elseif ($event instanceof QueueLoanCreated
		 || $event instanceof QueueLoanUpdated
		 || $event instanceof QueueLoanDeleted)
		{
			$queue = $event->loan->queue;
		}

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		if ($queue->isSystem())
		{
			return;
		}

		$this->setDefaultQos($queue);
	}

	/**
	 * Create and/or update the default QoS
	 *
	 * @param   Queue  $queue
	 * @return  void
	 */
	private function setDefaultQos(Queue $queue): void
	{
		$name = $queue->defaultQosName;

		// Check for an existing QoS
		$qos = Qos::query()
			->withTrashed()
			->where('name', '=', $name)
			->where('scheduler_id', '=', $queue->schedulerid)
			->first();

		if ($qos)
		{
			if ($qos->trashed())
			{
				$qos->restore();
			}
		}
		else
		{
			$qos = new Qos;
			$qos->name = $name;
			$qos->description = 'Default QoS for account ' . $qos->name;
			$qos->scheduler_id = $queue->schedulerid;
		}

		$unit = 'cores';
		$resource = $queue->resource;
		if ($facet = $resource->getFacet('allocation_unit'))
		{
			$unit = $facet->value;
		}

		$nodecores = $queue->subresource->nodecores;

		$l = 'cpu=' . $queue->totalcores;

		if ($unit == 'gpus' && $queue->subresource->nodegpus)
		{
			$nodes = round($queue->totalcores / $nodecores, 1);

			$l .= ',gres/gpu=' . ($queue->serviceunits ? $queue->serviceunits : round($nodes * $queue->subresource->nodegpus));

			$qos->min_tres_pj = 'gres/gpu=1';
		}
		elseif ($unit == 'sus')
		{
		}

		$qos->grp_tres = $l;
		$qos->flags = 'DenyOnLimit';

		if ($queue->maxjobsqueued)
		{
			$qos->grp_submit_jobs = $queue->maxjobsqueued;
		}
		if ($queue->maxjobsrunuser)
		{
			$qos->max_jobs_per_user = $queue->maxjobsrunuser;
		}
		if ($queue->maxjobsqueueduser)
		{
			//$qos->max_submit_jobs_per_user = $queue->maxjobsqueueduser;
			$qos->grp_jobs = $queue->maxjobsqueueduser;
		}
		if ($queue->walltime)
		{
			$qos->max_wall_duration_per_job = ($queue->walltime / 60);
		}
		if ($queue->priority)
		{
			$qos->priority = $queue->priority;
		}

		$qos->save();

		// Attach the QoS to the queue
		$queueqos = QueueQos::query()
			->where('qosid', '=', $qos->id)
			->where('queueid', '=', $queue->id)
			->first();

		if (!$queueqos)
		{
			$queueqos = new QueueQos;
			$queueqos->qosid = $qos->id;
			$queueqos->queueid = $queue->id;
			$queueqos->save();
		}
	}
}
