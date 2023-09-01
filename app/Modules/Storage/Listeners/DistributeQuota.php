<?php

namespace App\Modules\Storage\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Events\LoanCreated;
use App\Modules\Storage\Events\LoanUpdated;
use App\Modules\Storage\Events\LoanDeleted;
use App\Modules\Storage\Events\PurchaseCreated;
use App\Modules\Storage\Events\PurchaseUpdated;
use App\Modules\Storage\Events\PurchaseDeleted;
use App\Modules\Messages\Models\Type as MessageType;

/**
 * Recalculate directory quotas
 */
class DistributeQuota
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(LoanCreated::class, self::class . '@handleQuotaChange');
		$events->listen(LoanUpdated::class, self::class . '@handleQuotaChange');
		$events->listen(LoanDeleted::class, self::class . '@handleQuotaChange');

		$events->listen(PurchaseCreated::class, self::class . '@handleQuotaChange');
		$events->listen(PurchaseCreated::class, self::class . '@handleQuotaChange');
		$events->listen(PurchaseCreated::class, self::class . '@handleQuotaChange');
	}

	/**
	 * Display user profile info
	 *
	 * @param   LoanCreated|LoanUpdated|LoanDeleted|PurchaseCreated|PurchaseUpdated|PurchaseDeleted  $event
	 * @return  void
	 */
	public function handleQuotaChange($event): void
	{
		$allocation = null;

		if ($event instanceof LoanCreated
		 || $event instanceof LoanUpdated
		 || $event instanceof LoanDeleted)
		{
			$allocation = $event->loan;
		}
		if ($event instanceof PurchaseCreated
		 || $event instanceof PurchaseUpdated
		 || $event instanceof PurchaseDeleted)
		{
			$allocation = $event->purchase;
		}

		if (!$allocation)
		{
			return;
		}

		/*if ($allocation->groupid <= 0)
		{
			return;
		}*/

		$dir = Directory::query()
			->where('resourceid', '=', $allocation->resourceid)
			->where('groupid', '=', $allocation->groupid)
			->where('parentstoragedirid', '=', 0)
			->first();

		if (!$dir)
		{
			return;
		}

		if (!$dir->storageResource)
		{
			return;
		}

		// Get the current byte total
		$items = $dir->resourceTotal;

		if (!empty($items))
		{
			$last = end($items);
			$dir->bytes = $last['bytes'];
		}

		// Update the database entry as needed
		if ($dir->bytes == $dir->getOriginal('bytes'))
		{
			// No change
			return;
		}

		$dir->saveQuietly();

		// Get the appropriate message for the message queue
		// to update the quota and submit it
		$typeid = $dir->storageResource->createtypeid;

		if (!$typeid)
		{
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'fileset %')
				->first();

			if ($type)
			{
				$typeid = $type->id;
			}
		}

		if (!$typeid)
		{
			return;
		}

		$dir->addMessageToQueue($typeid);
	}
}
