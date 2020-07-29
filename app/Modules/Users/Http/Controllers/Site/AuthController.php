<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
//use App\User;

class AuthController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function login(Request $request)
	{
		/*if (app()->has('cas'))
		{
			$cas = app('cas');

			if ($cas->checkAuthentication())
			{
				return $this->callback();
			}
			else
			{
				return $cas->authenticate();
			}
		}*/

		if (app()->has('cas'))
		{
			$cas = app('cas');

			if (!$cas->checkAuthentication())
			{
				return $cas->authenticate();
			}
		}

		$return = $request->input('return');

		return view('users::site.login', [
			'return' => $return
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * @return Response
	 */
	public function authenticate(Request $request)
	{
		//Auth::loginUsingId($user->id);

		$request->validate([
			'email' => 'required|email',
			'password' => 'required|min:3'
		]);

		$credentials = [
			'email'    => $request->input('email'),
			'password' => $request->input('password'),
		];

		$remember = (bool) $request->get('remember_me', false);

		//$error = $this->auth->login($credentials, $remember);

		//if ($error)
		if (!Auth::attempt($credentials, $remember))
		{
			return redirect()
				->back()
				->withInput()
				->withError('Authentication failed');
		}

		return redirect()
			->intended(route(config('users.redirect_route_after_login', 'home')));
			//->withSuccess(trans('users::messages.successfully logged in'));
	}

	public function callback(Request $request)
	{
		/*try
		{
			$linkdinUser = Socialite::driver('linkedin')->user();
			$existUser = User::where('email', $linkdinUser->email)->first();

			if ($existUser)
			{
				Auth::loginUsingId($existUser->id);
			}
			else
			{
				$user = new User;
				$user->name = $linkdinUser->name;
				$user->email = $linkdinUser->email;
				$user->linkedin_id = $linkdinUser->id;
				$user->password = md5(rand(1,10000));
				$user->save();

				Auth::loginUsingId($user->id);
			}

			return redirect()->to('/home');
		}
		catch (Exception $e)
		{
			return 'error';
		}*/

		if (app()->has('cas'))
		{
			$cas = app('cas');

			if ($cas->checkAuthentication())
			{
				// Store the user credentials in a Laravel managed session
				//session()->put('cas_user', $cas->user());
				if (!auth()->user())
				{
					$user = \App\Modules\Users\Models\User::where('username', '=', $cas->user())->first();

					if ($user)
					{
						Auth::loginUsingId($user->id);
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

		$route = route(config('users.redirect_route_after_login', 'home'));

		if ($url = session('url.intended'))
		{
			$route = $url;
		}

		return redirect()
			->intended($route);
	}

	/**
	 * Logout
	 * @return Response
	 */
	public function logout(Request $request)
	{
		Auth::logout();

		if (app()->has('cas'))
		{
			app('cas')->logout(route('home'), route('home'));
		}

		return redirect()->route('login');
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function register()
	{
		if (Auth::check())
		{
			return redirect()
				->intended(route(config('users.redirect_route_after_login', 'home')))
				->withSuccess(trans('users::messages.already registered'));
		}

		return view('users::site.register');
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function registering(Request $request)
	{
	}
}
