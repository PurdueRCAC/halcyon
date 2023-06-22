<?php

namespace App\Modules\Media\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use App\Modules\Media\Events\DirectoryCreated;
use App\Modules\Media\Events\DirectoryUpdated;
use App\Modules\Media\Events\DirectoryDeleted;
use App\Modules\Media\Helpers\MediaHelper;

/**
 * Update cache when a directory si created or deleted
 */
class CacheTree
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(DirectoryCreated::class, self::class . '@handle');
		$events->listen(DirectoryUpdated::class, self::class . '@handle');
		$events->listen(DirectoryDeleted::class, self::class . '@handle');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   DirectoryCreated|DirectoryUpdated|DirectoryDeleted $event
	 * @return  void
	 */
	public function handle($event): void
	{
		if (!($event instanceof DirectoryCreated)
		 && !($event instanceof DirectoryUpdated)
		 && !($event instanceof DirectoryDeleted))
		{
			return;
		}

		$base = storage_path('app/public');

		$folders = MediaHelper::getTree($base);

		Cache::forever('media_tree', $folders);
	}
}
