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
		// Check if the user is logged in
		if (!$this->auth->check() && route('login') != $request->url())
		{
			if ($request->ajax())
			{
				return response('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
			}

			// Store the current uri in the session
			$this->session->put('url.intended', $this->request->url());

			// Redirect to the login page
			//return $this->redirect->route('login');
			if (app()->has('cas'))
			{
				$cas = app('cas');
				$segs = $request->segments();
				$seg = end($segs);
				if ($seg != 'login'
				 && $seg != 'logout')
				{
					$cas->setFixedServiceURL($request->url());
				}

				if (!$cas->checkAuthentication())
				{
					return $cas->authenticate();
				}
				else
				{
					$user = \App\Modules\Users\Models\User::findByUsername($cas->user());

					$newUsertype = config('module.users.new_usertype');

					if (!$newUsertype)
					{
						$newUsertype = \App\Halcyon\Access\Role::findByTitle('Registered')->id;
					}

					if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
					{
						$user = new \App\Modules\Users\Models\User;
						$user->name = $cas->getAttribute('fullname');
						$user->api_token = \Illuminate\Support\Str::random(60);

						if ($newUsertype)
						{
							$user->newroles = array($newUsertype);
						}

						if ($user->save())
						{
							$userusername = new \App\Modules\Users\Models\UserUsername;
							$userusername->userid = $user->id;
							$userusername->username = $cas->user();
							$userusername->save();
						}
					}

					if ($user && $user->id)
					{
						if (!count($user->roles) && $newUsertype)
						{
							$user->newroles = array($newUsertype);
							$user->save();
						}

						if (!$user->api_token)
						{
							$user->api_token = \Illuminate\Support\Str::random(60);
							$user->save();
						}

						\Illuminate\Support\Facades\Auth::loginUsingId($user->id);
					}
					else
					{
						return response('Unauthorized.', 401);
					}
					//return redirect(route('callback'));
				}
			}
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
