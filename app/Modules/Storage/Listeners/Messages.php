<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Storage\Listeners;

use App\Modules\Storage\Models\Directory;
use App\Modules\Messages\Events\MessageReading;

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
			if ($type->classname == 'storagedir')
			{
				$item = Directory::find($message->targetobjectid);

				if ($item)
				{
					$event->target = $item->storageResource->path . '/' . $item->path;
				}
			}
		}
	}
}
