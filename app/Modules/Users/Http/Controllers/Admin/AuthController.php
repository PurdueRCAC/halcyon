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
			return response(trans('users::auth.authentication failed'), 401);
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
	 * Get route to redirect to after being authenticated
	 * 
	 * @return string
	 */
	private function authenticatedRoute()
	{
		return route(config('module.users.redirect_route_after_login', 'home'));
	}
}
