<?php

namespace App\Modules\Listeners\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Schema;
use App\Modules\Listeners\Models\Listener;

class RegisterListeners
{
	/**
	 * The authentication factory instance.
	 *
	 * @var \Illuminate\Contracts\Auth\Factory
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param   \Illuminate\Contracts\Auth\Factory  $auth
	 * @return  void
	 */
	public function __construct(Auth $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @param   \Closure  $next
	 * @return  mixed
	 */
	public function handle($request, Closure $next)
	{
		if (Schema::hasTable('extensions'))
		{
			$query = Listener::where('enabled', 1)
				->where('type', '=', 'listener');

			if ($user = auth()->user())
			{
				$query->whereIn('access', $user->getAuthorisedViewLevels());
			}

			$listeners = $query
				->orderBy('ordering', 'asc')
				->get();

			foreach ($listeners as $listener)
			{
				$this->subscribeListener($listener);
			}
		}

		return $next($request);
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   object  $listener
	 * @return  void
	 */
	protected function subscribeListener($listener)
	{
		//try
		//{
			//$path = '/Listeners/' . Str::studly($listener->folder) . '/' . Str::studly($listener->element);

			if (!$listener->path)
			{
				return;
			}

			//$cls = 'App' . str_replace('/', '\\', $path) . '\\' . Str::studly($listener->element);
			//$cls = 'App' . '\\Listeners\\' . Str::studly($listener->folder) . '\\' . Str::studly($listener->element) . '\\' . Str::studly($listener->element);
			$cls = $listener->className;

			$r = new \ReflectionClass($cls);

			foreach ($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
			{
				$name = $method->getName();

				if ($name == 'subscribe')
				{
					app('events')->subscribe(new $cls);
				}
				elseif (substr(strtolower($name), 0, 6) == 'handle')
				{
					$event = lcfirst(substr($name, 6));

					app('events')->listen($event, $cls . '@' . $name);
				}

				app('config')->set('listeners.' . $listener->folder . '.' . $listener->element, $listener->params->all());
			}
		//}
		//catch (\Exception $e)
		//{
			// Listener not found
		//}
	}
}
