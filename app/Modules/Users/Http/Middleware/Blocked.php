<?php

namespace App\Modules\Users\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

class Blocked
{
	/**
	 * The authentication factory instance.
	 *
	 * @var Auth
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param   Auth  $auth
	 * @return  void
	 */
	public function __construct(Auth $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param   Request  $request
	 * @param   Closure  $next
	 * @return  mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->check()
		 && $this->auth->user()
		 && !$this->auth->user()->enabled)
		{
			abort(403);
		}

		return $next($request);
	}
}
