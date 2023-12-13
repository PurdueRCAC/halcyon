<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use Illuminate\Contracts\Auth\Factory as Auth;

class AdminMiddleware
{
	/**
	 * @var Auth
	 */
	private $auth;

	/**
	 * @var Store
	 */
	private $session;

	/**
	 * Constructor
	 *
	 * @param  Auth $auth
	 * @param  Store $session
	 * @return void
	 */
	public function __construct(Auth $auth, Store $session)
	{
		$this->auth = $auth;
		$this->session = $session;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  \Closure(Request): (Response|RedirectResponse)  $next
	 * @return Response|RedirectResponse
	 */
	public function handle(Request $request, \Closure $next)
	{
		// Check if the user is logged in
		if (!$this->auth->check() && route('login') != $request->url())
		{
			if ($request->ajax())
			{
				return response('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
			}

			// Store the current uri in the session
			$this->session->put('url.intended', $request->url());

			// Redirect to the login page
			//return redirect('login');
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
					$user = \App\Modules\Users\Models\User::findByUsername($cas->user(), config('module.users.restore_on_login', 0));

					if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
					{
						$user = new \App\Modules\Users\Models\User;
						$user->name = $cas->getAttribute('fullname');
						$user->api_token = \Illuminate\Support\Str::random(60);

						$attrs = $cas->getAttributes();
						if (isset($attrs['puid']))
						{
							$user->puid = intval($attrs['puid']);
						}

						$user->setDefaultRole();

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
						// Restore "trashed" accounts on login?
						if ($user->trashed())
						{
							if (config('module.users.restore_on_login', 0))
							{
								$user->getUserUsername()->restore();
							}
							else
							{
								return response('Unauthorized.', 401);
							}
						}

						if (!count($user->roles))
						{
							$user->setDefaultRole();
							$user->save();
						}

						if (!$user->api_token)
						{
							$user->api_token = \Illuminate\Support\Str::random(60);
							$user->save();
						}

						if (!$user->puid)
						{
							
							$attrs = $cas->getAttributes();
							if (isset($attrs['puid']))
							{
								$user->update(['puid' => intval($attrs['puid'])]);
							}
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
			return abort(Response::HTTP_FORBIDDEN);
		}*/

		return $next($request);
	}
}
