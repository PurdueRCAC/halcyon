<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

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

		if ($user && $user->id)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['email' => 'No account found for the given email']);
		}

		// We will send the password reset link to this user. Once we have attempted
		// to send the link, we will examine the response then see the message we
		// need to show to the user. Finally, we'll send out a proper response.
		$status = Password::sendResetLink(
			$request->only('email')
		);

		return $status == Password::RESET_LINK_SENT
					? back()->with('status', trans($status))
					: back()->withInput($request->only('email'))
							->withErrors(['email' => trans($status)]);
	}
}
