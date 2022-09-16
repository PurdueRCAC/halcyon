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
					'prefix'     => 'admin', //config('admin-prefix'),
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
				return $request->user()
					? Limit::perMinute(420)->by($request->user()->id)
					: Limit::perMinute(120)->by($request->ip());
			});

			$router->group(
				[
					'namespace'  => 'Api',
					'prefix'     => 'api',
					'middleware' => ['api'],
					//'middleware' => config('api.middleware', ['auth:api']),
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
			RateLimiter::for('ws', function (Request $request)
			{
				return Limit::none(); //perMinute(1000)->by(optional($request->user())->id ?: $request->ip());
			});

			$router->group(
				[
					'namespace'  => 'Api',
					'prefix'     => 'ws',
					'middleware' => ['throttle:2000,1', \Illuminate\Routing\Middleware\SubstituteBindings::class],
				],
				function (Router $router) use ($ws)
				{
					require $ws;
				}
			);
		}
	}
}
