<?php

namespace App\Modules\Orders\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Orders listener for menu routes
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
		$route = route('site.orders.index');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('orders::orders.module name'),
			trans('orders::orders.orders'),
			'orders::orders',
			$route
		);

		$route = route('site.orders.cart');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('orders::orders.module name'),
			trans('orders::orders.cart'),
			'orders::cart',
			$route
		);

		$route = route('site.orders.products');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('orders::orders.module name'),
			trans('orders::orders.products'),
			'orders::products',
			$route
		);
	}
}
