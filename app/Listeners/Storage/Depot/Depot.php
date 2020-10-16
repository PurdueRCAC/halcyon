<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Storage\Depot;

use App\Modules\Storage\Events\DirectoryCreated;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Messages\Models\Type as MessageType;

/**
 * Depot listener for Storage
 */
class Depot
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
		$events->listen(DirectoryUpdated::class, self::class . '@handleDirectoryUpdated');
		$events->listen(DirectoryDeleted::class, self::class . '@handleDirectoryDeleted');
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$dir = $event->directory;

		// Create bonus thing if we're Depot, and root directory
		if ($dir->resourceid != 64)
		{
			return;
		}

		$this->addMessages($dir);

		if (!$dir->bytes || $dir->parentstoragedirid)
		{
			return;
		}

		$dir->resourceid = 48;
		$dir->storageresourceid = 1;
		$dir->parentstoragedirid = 0;

		// Get "-data" directory
		$unixgroups = UnixGroup::query()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%1')
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->first();

		if (!$unixgroups)
		{
			//$this->addError(__METHOD__ . '(): Failed to retrieve `unixgroups` for groupid ' . $dir->groupid);
			return;
		}

		$dir->unixgroupid = $unixgroups->id;
		$dir->owneruserid = 0;
		$dir->bytes       = 0;
		$dir->files       = 0;
		$dir->groupread   = 1;
		$dir->groupwrite  = 1;
		$dir->otherread   = 0;
		$dir->otherwrite  = 0;
		$dir->id          = null;
		$dir->save();

		$dir->resourceid = 93;
		$dir->storageresourceid = 21;
		$dir->parentstoragedirid = 0;

		// Get "base" directory
		$unixgroups = UnixGroup::query()
			->where('groupid', '=', $dir->groupid)
			->where('shortname', 'like', '%0')
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->first();

		if (!$unixgroups)
		{
			//$this->addError(__METHOD__ . '(): Failed to retrieve `unixgroups` for groupid ' . $dir->groupid);
			return;
		}

		$dir->unixgroup   = $unixgroups->id;
		$dir->owneruserid = 0;
		$dir->bytes       = 0;
		$dir->files       = 0;
		$dir->path        = '[L1FR] ' . $dir->path;
		$dir->name        = '[L1FR] ' . $dir->name;
		$dir->id          = null;
		$dir->save();
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleDirectoryUpdated(DirectoryUpdated $event)
	{
		$dir = $event->directory;

		if ($dir->resourceid != 64)
		{
			return;
		}

		$this->addMessages($dir);
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object   $dir
	 * @return  void
	 */
	private function addMessages($dir)
	{
		if (!$dir->bytes && $dir->parent)
		{
			// Submit mkdir
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'mkdir %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}

		if ($dir->bytes)
		{
			// Submit filset create/sync
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'fileset %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}
	}

	/**
	 * Setup default notifications for new directory
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleDirectoryDeleted(DirectoryDeleted $event)
	{
		$dir = $event->directory;

		if ($dir->resourceid != 64)
		{
			return;
		}

		if (!$dir->bytes && $dir->parent)
		{
			// Submit mkdir
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'rmdir %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}

		if ($dir->bytes)
		{
			// Submit filset create/sync
			$type = MessageType::query()
				->where('resourceid', '=', $dir->resourceid)
				->where('name', 'like', 'fileset %')
				->get()
				->first();

			if ($type)
			{
				$dir->addMessageToQueue($type->id, auth()->user()->id);
			}
		}
	}
}
