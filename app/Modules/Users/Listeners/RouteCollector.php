<?php

namespace App\Modules\Users\Listeners;

use App\Modules\Menus\Events\CollectingRoutes;

/**
 * Users listener for menu routes
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
		$route = route('login');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('users::users.module name'),
			trans('users::auth.login'),
			'users::login',
			$route
		);

		$route = route('logout');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('users::users.module name'),
			trans('users::auth.logout'),
			'users::logout',
			$route
		);

		if (config('module.users.allow_registration', true))
		{
			$route = route('register');
			$route = str_replace(request()->root(), '', $route);

			$event->addRoute(
				trans('users::users.module name'),
				trans('users::auth.register'),
				'users::register',
				$route
			);
		}

		$route = route('site.users.account');
		$route = str_replace(request()->root(), '', $route);

		$event->addRoute(
			trans('users::users.module name'),
			trans('users::users.my account'),
			'users::account',
			$route
		);
	}
}
