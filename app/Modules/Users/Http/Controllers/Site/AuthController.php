<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Halcyon\Access\Role;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;
use App\Modules\Users\Events\UserRegistered;

class AuthController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return Response
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

		event($event = new Login($request));

		if (Auth::check())
		{
			return redirect($this->authenticatedRoute());
		}

		return view('users::site.login', [
			'return' => $return
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function authenticate(Request $request)
	{
		event($event = new Authenticate($request));

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
	 * @return Response
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

					$newUsertype = config('module.users.new_usertype');

					if (!$newUsertype)
					{
						$newUsertype = Role::findByTitle('Registered')->id;
					}

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

						if ($newUsertype)
						{
							$user->newroles = array($newUsertype);
						}

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

						if (!count($user->roles) && $newUsertype)
						{
							$user->newroles = array($newUsertype);
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
	 * @return Response
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
			 && $this->isInternal($url))
			{
				$route = $url;
			}
		}

		return $route;
	}
}
