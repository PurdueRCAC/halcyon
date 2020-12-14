<?php
namespace App\Listeners\Resources\News;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\News\Models\Type;

/**
 * News listener for Resources
 */
class News
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		app('translator')->addNamespace('listener.resources.news', __DIR__ . '/lang');

		$event->addSection(
			route('site.news.type', ['name' => $event->getAsset()->name]),
			trans('listener.resources.news::news.outages')
		);
	}
}
