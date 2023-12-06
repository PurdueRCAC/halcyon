<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Modules\Users\Events\Authenticators;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;

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

		$authenticators = array();

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

		return view('users::admin.auth.login', [
			'return' => $return,
			'authenticators' => $authenticators
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
			return redirect()
				->back()
				->withInput()
				->withError(trans('users::auth.authentication failed'));
		}

		return redirect($this->authenticatedRoute());
	}

	/**
	 * Logout
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function logout(Request $request): RedirectResponse
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
	protected function isBase64($str): bool
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
	protected function isInternal($str): bool
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
		return route(config('module.users.redirect_route_after_login', 'home'));
	}
}
