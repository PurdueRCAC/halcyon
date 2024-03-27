<?php

namespace App\Modules\Pages\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Pages\Models\Page;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Menu listener for page routes
 */
class RouteCollector
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(CollectingRoutes::class, self::class . '@handleCollectingRoutes');
	}

	/**
	 * Add module-specific routes
	 *
	 * @param   CollectingRoutes $event
	 * @return  void
	 */
	public function handleCollectingRoutes(CollectingRoutes $event): void
	{
		$options = Page::query()
			->select('id', 'title', 'level', 'path')
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
