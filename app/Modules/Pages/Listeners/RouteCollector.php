<?php

namespace App\Modules\Pages\Listeners;

use App\Modules\Pages\Models\Page;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Menu listener for sessions
 */
class RouteCollector
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(CollectingRoutes::class, self::class . '@handleCollectingRoutes');
	}

	/**
	 * Add module-specific routes
	 *
	 * @param   CollectingRoutes $event
	 * @return  void
	 */
	public function handleCollectingRoutes(CollectingRoutes $event)
	{
		$options = Page::query()
			//->select(['id AS value', 'title AS text', 'level'])
			//->where('level', '>', 0)
			->where('state', '=', 1)
			->orderBy('path', 'asc')
			->get();

		foreach ($options as $page)
		{
			$indent = str_repeat('|— ', $page->level);

			$event->addRoute(
				'00_' . trans('pages::pages.module name'),
				$page->title,
				'pages::' . $page->id,
				$page->path,
				$indent,
			);
		}
	}
}
