<?php

namespace App\Modules\Storage\Listeners;

use App\Modules\Storage\Models\Directory;
use App\Modules\Messages\Events\MessageReading;
use App\Modules\Messages\Models\Type as MessageType;
use App\Modules\Storage\Events\DirectoryCreated;

/**
 * Messages listener
 */
class Messages
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(MessageReading::class, self::class . '@handleMessageReading');
		$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
	}

	/**
	 * Setup some directories for new resource members
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleMessageReading(MessageReading $event)
	{
		$message = $event->message;

		if ($type = $message->type)
		{
			if (!$type->classname || $type->classname == 'storagedir')
			{
				$item = Directory::query()->withTrashed()->where('id', '=', $message->targetobjectid)->first();

				if ($item)
				{
					$sr = $item->storageResource()->withTrashed()->first();
					$event->target = ($sr ? $sr->path . '/' : '') . $item->path;
				}
			}
		}
	}

	/**
	 * Add messages to the queue for new directories
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$row = $event->directory;

		//if ($row->resourceid == 64 && !$row->bytes && $row->parent)
		if (!$row->bytes && $row->parent)
		{
			// Submit mkdir
			$type = MessageType::query()
				->where('resourceid', '=', $row->resourceid)
				->where('name', 'like', 'mkdir %')
				->get()
				->first();

			if ($type)
			{
				$row->addMessageToQueue($type->id, $row->userid, 10);
			}
		}

		//if ($row->resourceid == 64 && $row->bytes)
		if ($row->bytes)
		{
			// Submit filset create/sync
			$type = MessageType::query()
				->where('resourceid', '=', $row->resourceid)
				->where('name', 'like', 'fileset %')
				->get()
				->first();

			if ($type)
			{
				$row->addMessageToQueue($type->id, $row->userid, 10);
			}
		}
	}
}
