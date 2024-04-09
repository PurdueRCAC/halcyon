<?php

namespace App\Modules\Media\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use App\Modules\Media\Contracts\DirectoryEvent;
use App\Modules\Media\Events\DirectoryCreated;
use App\Modules\Media\Events\DirectoryUpdated;
use App\Modules\Media\Events\DirectoryDeleted;
use App\Modules\Media\Helpers\MediaHelper;

/**
 * Update cache when a directory is created or deleted
 */
class CacheTree
{
	/**
	 * Register the listeners for the subscriber.
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(DirectoryCreated::class, self::class . '@handle');
		$events->listen(DirectoryUpdated::class, self::class . '@handle');
		$events->listen(DirectoryDeleted::class, self::class . '@handle');
	}

	/**
	 * Rebuild the cached directory tree
	 */
	public function handle(DirectoryEvent $event): void
	{
		$base = storage_path('app/public');

		$folders = MediaHelper::getTree($base);

		Cache::forever('media_tree', $folders);
	}
}
