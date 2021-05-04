<?php

namespace App\Modules\Resources\Listeners;

use App\Modules\Resources\Events\AssetCreated;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;

/**
 * Subresource listener
 */
class Subresources
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssetCreated::class, self::class . '@handleAssetCreated', 1000);
	}

	/**
	 * Create a default subresource for new compute resources
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleAssetCreated(AssetCreated $event)
	{
		$asset = $event->asset;

		if ($asset->resourcetype != 1)
		{
			return;
		}

		$subresource = new Subresource;
		$subresource->name = $asset->name . '-Nonspecific';
		$subresource->cluster = '';
		$subresource->nodecores = 0;
		$subresource->save();

		$child = new Child;
		$child->resourceid = $asset->id;
		$child->subresourceid = $subresource->id;
		$child->save();
	}
}
