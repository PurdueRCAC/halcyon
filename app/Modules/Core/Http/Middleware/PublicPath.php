<?php

namespace App\Modules\Core\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;

class PublicPath
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
		//duplicate the request
		//$dupRequest = $request->duplicate();

		//get the language part
		$first = $request->segment(1);

		if ($first == 'public')
		{
			// Remove the part from the URI
			$newpath = substr($request->path(), strlen($first . '/'));

			//set the new URI
			$request->server->set('REQUEST_URI', $newpath);
		}

		return $next($request);
	}
}
