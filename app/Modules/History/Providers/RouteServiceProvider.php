<?php

namespace App\Modules\History\Providers;

//use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
//use Illuminate\Routing\Router;
//use Illuminate\Support\Facades\Route;
use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Modules\History\Http\Controllers';

	/**
	 * Define the routes for the application.
	 *
	 * @return null
	 */
	/*public function map()
	{
		Route::namespace($this->namespace)->group(function (Router $router)
		{
			// API routes
			$router->middleware('api')->prefix('api')->group(function (Router $router)
			{
				$router->middleware('auth:api')->group(function (Router $router)
				{
					$router->get('history', 'ApiController@index')->middleware('can:see-history');
					$router->delete('history', 'ApiController@destroy')->middleware('can:clear-history');
				});
			});
		});
	}*/

	/**
	 * @return string
	 */
	protected function getSiteRoute()
	{
		return '';
	}

	/**
	 * @return string
	 */
	protected function getAdminRoute()
	{
		return dirname(__DIR__) . '/Http/adminRoutes.php';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute()
	{
		return dirname(__DIR__) . '/Http/apiRoutes.php';
	}
}
