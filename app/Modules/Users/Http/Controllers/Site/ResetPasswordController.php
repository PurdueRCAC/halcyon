<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Modules\Users\Models\User;


class ResetPasswordController extends Controller
{
	/**
	 * Display the password reset view.
	 *
	 * @param  Request  $request
	 * @return View
	 */
	public function index(Request $request): View
	{
		return view('users::site.reset-password', [
			'request' => $request
		]);
	}

	/**
	 * Handle an incoming new password request.
	 *
	 * @param  Request  $request
	 * @return RedirectResponse
	 * @throws ValidationException
	 */
	public function store(Request $request): RedirectResponse
	{
		$request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', 'confirmed', Rules\Password::defaults()],
		]);

		// Here we will attempt to reset the user's password. If it is successful we
		// will update the password on an actual user model and persist it to the
		// database. Otherwise we will parse the error and return the response.
		/*$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function ($user) use ($request)
			{
				$user->forceFill([
					'password' => Hash::make($request->input('password')),
					//'remember_token' => Str::random(60),
				])->save();

				event(new PasswordReset($user));
			}
		);*/

		$credentials = $request->only('email', 'password', 'password_confirmation', 'token');

		$user = User::findByEmail($credentials['email']);

		if (!$user)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['email' => trans('users::auth.account not found for email')]);
		}

		if (! Password::getRepository()->exists($user, $credentials['token']))
		{
			return back()->withInput($request->only('email'))
				->withErrors(['email' => trans(static::INVALID_TOKEN)]);
		}

		$user->forceFill([
			'password' => Hash::make($request->input('password')),
		])->save();

		event(new PasswordReset($user));

		Password::getRepository()->delete($user);

		// If the password was successfully reset, we will redirect the user back to
		// the application's home authenticated view.
		return redirect()->route('login')->with('status', trans(Password::PASSWORD_RESET));
	}
}
