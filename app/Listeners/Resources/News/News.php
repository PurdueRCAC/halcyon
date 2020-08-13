<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Resources\News;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\News\Models\Type;

/**
 * Content listener for Resources
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
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		app('translator')->addNamespace('listener.resources.news', __DIR__ . '/lang');

		$event->addSection(
			route('site.news.type', ['name' => $event->getAsset()->name]),
			trans('listener.resources.news::news.outages')
		);
	}
}
