<?php

namespace App\Halcyon\Cas\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Halcyon\Cas\CasManager;

class RedirectCASAuthenticated
{
	/**
	 * @var Guard
	 */
	protected $auth;

	/**
	 * @var CasManager
	 */
	protected $cas;

	/**
	 * @param Guard $auth
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
		$this->cas = app('cas');
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->cas->checkAuthentication())
		{
			$config = config('cas.cas_redirect_path', []);

			$route = isset($config['cas_redirect_path']) ? (string)$config['cas_redirect_path'] : '';
			$route = $route ?: route('callback');

			return redirect($route);
		}

		return $next($request);
	}
}
