<?php

namespace App\Modules\Core\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;

class ApiDocs
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $redirectToRoute
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle($request, Closure $next, $redirectToRoute = null)
	{
		if (($request->is('api') || $request->is('api/*')) && stristr($request->headers->get('accept'), 'text/html') !== false)
		{
			//return App::make('App\Modules\Core\Http\Controllers\Site\DocsController')->index($request);

			$route = $request->route();

			$routeAction = array_merge($route->getAction(), [
				'uses'       => '\App\Modules\Core\Http\Controllers\Site\DocsController@index',
				'controller' => '\App\Modules\Core\Http\Controllers\Site\DocsController@index',
			]);
			$route->setAction($routeAction);
			$route->controller = false;
		}

		return $next($request);
	}
}

