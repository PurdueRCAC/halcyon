<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Modules\Users\Models\User;

class ForgotPasswordController extends Controller
{
	/**
	 * Display the password reset link request view.
	 *
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return view('users::site.forgot-password');
	}

	/**
	 * Handle an incoming password reset link request.
	 *
	 * @param  Request  $request
	 * @return RedirectResponse
	 * @throws ValidationException
	 */
	public function store(Request $request)
	{
		$request->validate([
			'email' => ['required', 'email'],
		]);

		$user = User::findByEmail($request->input('email'));

		if (!$user)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['email' => trans('users::auth.account not found for email')]);
		}

		// We will send the password reset link to this user. Once we have attempted
		// to send the link, we will examine the response then see the message we
		// need to show to the user. Finally, we'll send out a proper response.
		if (Password::getRepository()->recentlyCreatedToken($user))
		{
			return back()->withInput($request->only('email'))
				->withErrors(['email' => trans(Password::RESET_THROTTLED)]);
		}

		$token = Password::getRepository()->create($user);

		// Once we have the reset token, we are ready to send the message out to this
		// user with a link to reset their password. We will then redirect back to
		// the current URI having nothing set in the session to indicate errors.
		$user->sendPasswordResetNotification($token);

		return back()->with('success', trans(Password::RESET_LINK_SENT));
	}
}
