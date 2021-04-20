<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Session\Store;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Modules\Users\Models\User;

class IpWhitelistMiddleware
{
	/**
	 * @var Authentication
	 */
	private $auth;

	/**
	 * @var SessionManager
	 */
	private $session;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Redirector
	 */
	private $redirect;

	/**
	 * @var Application
	 */
	private $application;

	/**
	 * Constructor
	 *
	 * @param  Illuminate\Contracts\Auth\Factory $auth
	 * @param  Illuminate\Session\Store $session
	 * @param  Illuminate\Http\Request $request
	 * @param  Illuminate\Routing\Redirector $redirect
	 * @param  Illuminate\Foundation\Application $application
	 * @return mixed
	 */
	public function __construct(Auth $auth, Store $session, Request $request, Redirector $redirect, Application $application)
	{
		$this->auth = $auth;
		$this->session = $session;
		$this->request = $request;
		$this->redirect = $redirect;
		$this->application = $application;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle($request, \Closure $next)
	{
		if (!$this->auth->check())
		{
			if (!in_array($request->ip(), config('ws.whitelist', [])))
			{
				$this->application->abort(403, 'IP Restricted.');
			}

			$existUser = User::where('id', config('ws.user_id', 1))->first();

			if ($existUser)
			{
				\Auth::loginUsingId($existUser->id);
			}
		}

		return $next($request);
	}
}
