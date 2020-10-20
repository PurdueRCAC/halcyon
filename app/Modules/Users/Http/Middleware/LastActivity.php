<?php

namespace App\Modules\Users\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class LastActivity
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
		if ($this->auth->check()
		 && $this->auth->user()->last_visit < Carbon::now()->subMinutes(5)->toDateTimeString())
		{
			$user = $this->auth->user();
			/*$user->update(['last_visit' => Carbon::now()->toDateTimeString()]);*/

			$user->getUserUsername()->update(['datelastseen' => Carbon::now()->toDateTimeString()]);
		}

		return $next($request);
	}
}
