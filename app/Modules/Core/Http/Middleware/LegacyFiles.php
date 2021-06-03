<?php

namespace App\Modules\Core\Http\Middleware;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Closure;

class LegacyFiles
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
		$s = trim($request->server->get('REQUEST_URI'), '/');

		if (strstr($s, '.'))
		{
			$f = explode('/', $s);

			for ($i = 0; $i < count($f); $i++)
			{
				$first = array_shift($f);

				$path = 'public/' . implode('/', $f);

				if (file_exists(storage_path('app/' . $path)))
				{
					return redirect('/files/' . implode('/', $f), 301); //Storage::download($path);
				}
			}
		}

		return $next($request);
	}
}
