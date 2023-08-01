<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use App\Halcyon\Access\Role;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;


class RegisterController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @return RedirectResponse|View
	 */
	public function index()
	{
		if (Auth::check())
		{
			return redirect($this->authenticatedRoute())
				->withSuccess(trans('users::messages.already registered'));
		}

		return view('users::site.register');
	}

	/**
	 * Store a newly created resource in storage.
	 * 
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	public function store(Request $request): RedirectResponse
	{
		$rules = [
			'name' => ['required', 'string', 'max:255'],
			'username' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255'], //, 'unique:users'],
			'password' => ['required', 'confirmed', Rules\Password::defaults()],
		];

		if (config('module.users.terms'))
		{
			$rules['terms'] = ['required', 'int', Rule::in([1])];
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$user = User::findByUsername($request->input('username'));

		if ($user && $user->id)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['username' => 'Username is already taken']);
		}

		$user = User::findByEmail($request->input('email'));

		if ($user && $user->id)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['email' => 'Email is already taken']);
		}

		$user = new User;
		$user->name = $request->input('name');
		$user->api_token = Str::random(60);
		$user->password = Hash::make($request->input('password'));

		$user->setDefaultRole();

		if ($user->save())
		{
			$userusername = new UserUsername;
			$userusername->userid = $user->id;
			$userusername->username = $request->input('username');
			$userusername->email = $request->input('email');
			$userusername->save();
		}

		event(new Registered($user));

		Auth::loginUsingId($user->id);

		return redirect(route(config('module.users.redirect_route_after_login', 'home')));
	}

	/**
	 * Is the provided url internal to the site?
	 *
	 * @param   string  $str
	 * @return  bool
	 **/
	protected function isInternal(string $str): bool
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
			if (substr($url, -6) != 'login'
			 && substr($url, -8) != 'register'
			 && $this->isInternal($url))
			{
				$route = $url;
			}
		}

		return $route;
	}
}
