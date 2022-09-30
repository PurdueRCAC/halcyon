<?php

namespace App\Modules\News\Listeners;

use App\Modules\News\Models\Type;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * News listener for menu routes
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
		$route = route('site.news.search');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('news::news.module name'),
			trans('news::news.search news'),
			'news::search',
			$route
		);

		$route = route('site.news.rss');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('news::news.module name'),
			trans('news::news.rss feeds'),
			'news::rss',
			$route
		);

		$options = Type::query()
			->where('name', 'NOT LIKE', 'coffee%')
			->where('parentid', '=', 0)
			->orderBy('ordering', 'asc')
			->orderBy('name', 'asc')
			->get();

		foreach ($options as $type)
		{
			$indent = $type->parentid ? str_repeat('|— ', 1) : '';

			$route = route('site.news.type', ['name' => $type->alias]);
			$route = str_replace(request()->root(), '', $route);

			$event->addRoute(
				trans('news::news.module name'),
				$type->name,
				'news::' . $type->id,
				$route,
				$indent,
			);

			$children = $type->children()
				->orderBy('ordering', 'asc')
				->orderBy('name', 'asc')
				->get();

			foreach ($children as $child)
			{
				$indent = $child->parentid ? str_repeat('|— ', 1) : '';

				$route = route('site.news.type', ['name' => $child->alias]);
				$route = str_replace(request()->root(), '', $route);

				$event->addRoute(
					trans('news::news.module name'),
					$child->name,
					'news::' . $child->id,
					$route,
					$indent,
				);
			}
		}
	}
}
