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
			/*$user->last_visit = Carbon::now()->toDateTimeString();
			$user->timestamps = false;
			$user->save();*/
			$user->usernames()
				->where(function($where)
				{
					$where->whereNull('dateremoved')
						->orWhere('dateremoved', '=', '0000-00-00 00:00:00');
				})
				->orderBy('datecreated', 'asc')
				->first()
				->update(['datelastseen' => Carbon::now()->toDateTimeString()]);
		}

		return $next($request);
	}
}
