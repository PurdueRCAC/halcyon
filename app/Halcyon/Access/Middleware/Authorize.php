<?php

namespace App\Halcyon\Access\Middleware;

use Closure;
use App\Halcyon\Access\Gate;

class Authorize
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string  $ability
	 * @return mixed
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	public function handle($request, Closure $next, $ability)
	{
		Gate::authorize(auth()->id(), $ability);

		return $next($request);
	}
}
