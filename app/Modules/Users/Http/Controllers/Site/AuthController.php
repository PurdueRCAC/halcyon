<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Halcyon\Access\Role;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Events\Authenticators;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;
use App\Modules\Users\Events\UserRegistered;

class AuthController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse|View
	 */
	public function login(Request $request)
	{
		$return = $request->input('return');

		if ($return && $this->isBase64($return))
		{
			$return = base64_decode($return);

			// Assume redirect URLs are internal
			if ($return && $this->isInternal($return))
			{
				session()->put('url.intended', $return);
			}
		}

		if (Auth::check())
		{
			return redirect($this->authenticatedRoute());
		}

		event($event = new Authenticators());

		// If we only have one authenticator or a specific authenitcator has
		// been given, go ahead and call the Login event
		if (count($event->authenticators) == 1 || $request->has('authenticator'))
		{
			$authenticators = array_keys($event->authenticators);
			$authenticator = $request->input('authenticator');
			if (!in_array($authenticator, $authenticators))
			{
				$authenticator = array_shift($authenticators);
			}

			session()->put('authenticator', 'cilogon');

			event(new Login($request, $authenticator));
		}

		return view('users::site.login', [
			'return' => $return,
			'authenticators' => $event->authenticators,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function authenticate(Request $request)
	{
		$authenticator = $request->input('authenticator');
		$authenticator = $authenticator ?: session()->get('authenticator');

		event($event = new Authenticators());
		$authenticators = array_keys($event->authenticators);

		if (!$authenticator || !in_array($authenticator, $authenticators))
		{
			$authenticator = array_shift($authenticators);
		}

		event($event = new Authenticate($request, $authenticator));

		if (!$event->authenticated || !Auth::check())
		{
			return response(trans('users::auth.authentication failed'), 401);

			/*return redirect()
				->back()
				->withInput()
				->withError(trans('users::auth.authentication failed'));*/
		}

		return redirect($this->authenticatedRoute());
			//->intended($this->authenticatedRoute());
	}

	/**
	 * Callback for third-party auth
	 * 
	 * @param  Request $request
	 * @return Response|RedirectResponse
	 */
	public function callback(Request $request)
	{
		if (app()->has('cas'))
		{
			$cas = app('cas');

			if ($cas->checkAuthentication())
			{
				// Store the user credentials in a Laravel managed session
				//session()->put('cas_user', $cas->user());
				if (!auth()->user())
				{
					$user = User::findByUsername($cas->user(), config('module.users.restore_on_login', 0));

					// Create accounts on login?
					if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
					{
						$user = new User;
						$user->name = $cas->getAttribute('fullname');
						$user->api_token = Str::random(60);

						$attrs = $cas->getAttributes();
						if (isset($attrs['puid']))
						{
							$user->puid = intval($attrs['puid']);
						}

						$user->setDefaultRole();

						if ($user->save())
						{
							$userusername = new UserUsername;
							$userusername->userid = $user->id;
							$userusername->username = $cas->user();
							if (isset($attrs['email']))
							{
								$userusername->email = $attrs['email'];
							}
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
							$user->api_token = Str::random(60);
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

						Auth::loginUsingId($user->id);
					}
					else
					{
						return response('Unauthorized.', 401);
					}
				}
			}
			else
			{
				if ($request->ajax() || $request->wantsJson())
				{
					return response('Unauthorized.', 401);
				}
				return $cas->authenticate();
			}
		}

		return redirect($this->authenticatedRoute());
	}

	/**
	 * Logout
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function logout(Request $request)
	{
		Auth::logout();

		return redirect()->route(config('module.users.redirect_route_after_logout', 'login'));
	}

	/**
	 * Is the provided string base64 encoded?
	 *
	 * @param   string  $str
	 * @return  bool
	 **/
	protected function isBase64($str)
	{
		if (preg_match('/[^A-Za-z0-9\+\/\=]/', $str))
		{
			return false;
		}

		return true;
	}

	/**
	 * Is the provided url internal to the site?
	 *
	 * @param   string  $str
	 * @return  bool
	 **/
	protected function isInternal($str)
	{
		return (stripos($str, request()->root()) !== false);
	}

	/**
	 * Get route to redirect to after being authenticated
	 * 
	 * @return string
	 */
	private function authenticatedRoute()
	{
		$route = route(config('module.users.redirect_route_after_login', 'home'));

		if ($url = session('url.intended'))
		{
			$route = $url;
		}
		elseif ($url = session()->previousUrl())
		{
			if (substr($url, -5) != 'login'
			 && substr($url, -8) != 'register'
			 && !strstr($url, '/callback')
			 && $this->isInternal($url))
			{
				$route = $url;
			}
		}

		return $route;
	}
}
