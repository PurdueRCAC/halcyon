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
			if (substr($s, 0, strlen('compute/')) == 'compute/')
			{
				$s = str_replace(['compute/', 'images/'], ['resources/', ''], $s);
			}
			if (substr($s, 0, strlen('storage/')) == 'storage/')
			{
				$s = str_replace(['storage/', 'images/'], ['resources/', ''], $s);
			}

			$f = explode('/', $s);

			for ($i = 0; $i < count($f); $i++)
			{
				$path = 'public/' . implode('/', $f);
				$path = str_replace('../', '', $path);

				if (is_file(storage_path('app/' . $path)) && !is_dir(storage_path('app/' . $path)))
				{
					return redirect('/files/' . implode('/', $f));//, 301); //Storage::download($path);
				}

				$first = array_shift($f);
			}
		}

		return $next($request);
	}
}
