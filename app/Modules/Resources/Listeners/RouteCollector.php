<?php

namespace App\Modules\Resources\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Models\Type;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Resources listener for menu routes
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
		$module = trans('resources::resources.resources');
		$types = Type::all();

		foreach ($types as $type)
		{
			$route = route('site.resources.type.' . $type->alias);
			$route = str_replace(request()->root(), '', $route);

			$event->addRoute(
				$module,
				$type->name,
				'resources::type.' . $type->alias,
				$route
			);

			$route = route('site.resources.' . $type->alias . '.retired');
			$route = str_replace(request()->root(), '', $route);

			$event->addRoute(
				$module,
				$type->name . ': ' . trans('resources::resources.retired'),
				'resources::type.' . $type->alias . '.retired',
				$route
			);

			$rows = $type->resources()
				->with('facets')
				->where('display', '>', 0)
				->where(function ($where)
				{
					$where->whereNotNull('listname')
						->where('listname', '!=', '');
				})
				->whereNotNull('description')
				->orderBy('display', 'desc')
				->get();

			foreach ($rows as $row)
			{
				$route = route('site.resources.' . $type->alias . '.show', ['name' => $row->listname]);
				$route = str_replace(request()->root(), '', $route);

				$event->addRoute(
					$module,
					$row->name,
					'resources::asset.' . $row->listname,
					$route
				);

				$section = null;
				event($e = new AssetDisplaying($row, $section));
				$sections = collect($e->getSections());

				foreach ($sections as $sec)
				{
					$route = $sec['route'];
					$route = str_replace(request()->root(), '', $route);

					$event->addRoute(
						$module,
						$sec['name'],
						'resources::asset.' . $row->listname . '.' . $sec['name'],
						$route
					);
				}
			}
		}
	}
}
