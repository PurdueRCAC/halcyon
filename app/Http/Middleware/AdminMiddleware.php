<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Session\Store;
//use App\Modules\User\Contracts\Authentication;
use Illuminate\Contracts\Auth\Factory as Auth;

class AdminMiddleware
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
		/*if ($this->application->has('cas'))
		{
			$cas = $this->application['cas'];

			if ($cas->checkAuthentication())
			{
				// Store the user credentials in a Laravel managed session
				//session()->put('cas_user', $cas->user());
				if (!$this->auth->user())
				{
					$user = \App\Modules\Users\Models\User::where('username', '=', $cas->user())->first();

					if (!$user)
					{
						$user = new \App\Modules\Users\Models\User;
						$user->username = $cas->user();
						$user->name = $cas->user();
						$user->email = $cas->user() . '@purdue.edu';
						$user->save();
					}

					//if ($user)
					//{
						$this->auth->loginUsingId($user->id);
					//}
				}
			}
			else
			{
				if ($request->ajax() || $request->wantsJson())
				{
					return response('Unauthorized.', 401);
				}
				$cas->authenticate();
			}
		}*/

		// Check if the user is logged in
		if (!$this->auth->check() && route('admin.login') != $request->url())
		{
			if ($request->ajax())
			{
				return response('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
			}

			// Store the current uri in the session
			$this->session->put('url.intended', $this->request->url());

			// Redirect to the login page
			return $this->redirect->route('admin.login');
		}

		// Check if the user has access to the dashboard page
		/*if (! $this->auth->hasAccess('dashboard.index'))
		{
			// Show the insufficient permissions page
			return $this->application->abort(Response::HTTP_FORBIDDEN);
		}*/

		return $next($request);
	}
}
