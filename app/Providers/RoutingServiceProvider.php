<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Routing\Router;
use Illuminate\Http\Request;

abstract class RoutingServiceProvider extends ServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 *
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();
	}

	/**
	 * @return string
	 */
	abstract protected function getSiteRoute();

	/**
	 * @return string
	 */
	abstract protected function getAdminRoute();

	/**
	 * @return string
	 */
	abstract protected function getApiRoute();

	/**
	 * [!] Legacy compatibility
	 * 
	 * @return string
	 */
	protected function getWsRoute()
	{
		return '';
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function (Router $router)
		{
			$this->loadApiRoutes($router);
		});

		$router->group(
			[
				'namespace'  => $this->namespace,
				'prefix'     => config('locale'),
				'middleware' => ['web'],
			],
			function (Router $router)
			{
				$this->loadAdminRoutes($router);
				$this->loadSiteRoutes($router);
			}
		);
	}

	/**
	 * @param Router $router
	 * @return void
	 */
	private function loadSiteRoutes(Router $router)
	{
		$frontend = $this->getSiteRoute();

		if ($frontend && file_exists($frontend))
		{
			$router->group(
				[
					'namespace'  => 'Site',
					'middleware' => config('site.middleware', []),
				],
				function (Router $router) use ($frontend)
				{
					require $frontend;
				}
			);
		}
	}

	/**
	 * @param Router $router
	 * @return void
	 */
	private function loadAdminRoutes(Router $router)
	{
		$backend = $this->getAdminRoute();

		if ($backend && file_exists($backend))
		{
			$router->group(
				[
					'namespace'  => 'Admin',
					'prefix'     => 'admin',
					'middleware' => config('admin.middleware', ['auth']),
				],
				function (Router $router) use ($backend)
				{
					require $backend;
				}
			);
		}
	}

	/**
	 * @param Router $router
	 * @return void
	 */
	private function loadApiRoutes(Router $router)
	{
		$api = $this->getApiRoute();

		if ($api && file_exists($api))
		{
			RateLimiter::for('api', function (Request $request)
			{
				$limit = config('api.rate_limit.anonymous', 360);
				$by = $request->ip();

				if ($request->user())
				{
					$limit = config('api.rate_limit.registered', 1000);
					$by = $request->user()->id;
				}

				return $limit
					? Limit::perMinute($limit)->by($by)
					: Limit::none();
			});

			$router->group(
				[
					'namespace'  => 'Api',
					'prefix'     => 'api',
					'middleware' => config('api.middleware', ['api']),
				],
				function (Router $router) use ($api)
				{
					require $api;
				}
			);
		}

		// [!] Legacy compatibility
		$ws = $this->getWsRoute();

		if ($ws && file_exists($ws))
		{
			// Disable rate limiting for internal legacy routes
			RateLimiter::for('ws', function (Request $request)
			{
				return Limit::none();
			});

			$router->group(
				[
					'namespace'  => 'Api',
					'prefix'     => 'ws',
					'middleware' => [\Illuminate\Routing\Middleware\SubstituteBindings::class], //'throttle:2000,1'
				],
				function (Router $router) use ($ws)
				{
					require $ws;
				}
			);
		}
	}
}
