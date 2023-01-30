<?php

namespace App\Modules\Knowledge\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Route collecter for events
 */
class RouteCollector
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events)
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
		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$options = $lists = Page::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.*', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id AS assoc_id', $a . '.path AS assoc_path')
			->where($a . '.state', '=', 1)
			->orderBy($a . '.lft', 'asc')
			->get();

		foreach ($options as $page)
		{
			$indent = str_repeat('|— ', $page->level);

			$event->addRoute(
				trans('knowledge::knowledge.module name'),
				$page->title,
				'knowledge::' . $page->assoc_id,
				'/knowledge/' . $page->assoc_path,
				$indent,
			);
		}
	}
}
