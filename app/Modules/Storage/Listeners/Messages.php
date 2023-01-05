<?php

namespace App\Modules\Storage\Listeners;

use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
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
	 * @param  \Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(MessageReading::class, self::class . '@handleMessageReading');
		//$events->listen(DirectoryCreated::class, self::class . '@handleDirectoryCreated');
	}

	/**
	 * Gather some information about the target directory
	 *
	 * @param   MessageReading  $event
	 * @return  void
	 */
	public function handleMessageReading(MessageReading $event)
	{
		$message = $event->message;

		if ($type = $message->type)
		{
			if (!$type->classname || $type->classname == 'storagedir')
			{
				$item = Directory::query()
					->withTrashed()
					->where('id', '=', $message->targetobjectid)
					->first();

				if ($item)
				{
					$sr = $item->storageResource;
					if (!$sr)
					{
						$sr = StorageResource::query()
							->where('parentresourceid', '=', $item->resourceid)
							->first();
					}
					$event->target = ($sr ? $sr->path . '/' : '') . $item->path;
				}
			}
		}
	}

	/**
	 * Add messages to the queue for new directories
	 *
	 * @param   DirectoryCreated  $event
	 * @return  void
	 */
	public function handleDirectoryCreated(DirectoryCreated $event)
	{
		$row = $event->directory;

		if (!$row->bytes && $row->parent)
		{
			// Submit mkdir
			/*$type = MessageType::query()
				->where('resourceid', '=', $row->resourceid)
				->where('name', 'like', 'mkdir %')
				->get()
				->first();*/

			$typeid = $row->storageResource->createtypeid;

			if ($typeid)
			{
				$row->addMessageToQueue($typeid, $row->userid, 10);
			}
		}

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
