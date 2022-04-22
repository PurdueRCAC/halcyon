<?php

namespace App\Modules\Users\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Modules\Users\Models\User;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Events\UserDisplay;
use App\Modules\Users\Events\UserLookup;
use App\Modules\Users\Events\UserDeleted;


class UsersController extends Controller
{
	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function profile(Request $request)
	{
		$user = auth()->user();

		if (auth()->user()->can('manage users'))
		{
			if ($id = $request->input('u'))
			{
				if (is_numeric($id))
				{
					$user = User::findOrFail($id);
				}
				else
				{
					$user = User::findByUsername($id);

					if ((!$user || !$user->id) && config('module.users.create_on_search'))
					{
						event($event = new UserLookup(['username' => $id]));

						if (count($event->results))
						{
							$user = User::createFromUsername($id);
						}
					}
				}

				if (!$user || !$user->id)
				{
					abort(404);
				}
			}
		}

		event($event = new UserBeforeDisplay($user));
		$user = $event->getUser();

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			);

		event($event = new UserDisplay($user, $request->segment(2)));
		$sections = collect($event->getSections());
		$parts = collect($event->getParts());

		return view('users::site.profile', [
			'user' => $user,
			'sections' => $sections,
			'parts' => $parts,
		]);
	}

	/**
	 * Show the specified resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function request(Request $request)
	{
		$user = auth()->user();

		event($event = new UserBeforeDisplay($user));
		$user = $event->getUser();

		app('pathway')
			->append(
				$user->name,
				route('site.users.account')
			)
			->append(
				trans('users::users.request access'),
				route('site.users.account.request')
			);

		event($event = new UserDisplay($user, $request->segment(2)));
		$sections = collect($event->getSections());

		return view('users::site.request', [
			'user' => $user,
			'sections' => $sections,
		]);
	}

	/**
	 * Show confirmation form for deleting user
	 * 
	 * @return Response
	 */
	public function delete(Request $request)
	{
		if (!config('module.users.allow_self_deletion'))
		{
			abort(403);
		}

		$user = auth()->user();

		$rules = [
			'confirmdelete' => 'required|string|max:128',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		if ($user->name != $request->input('confirmdelete'))
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors(['confirmdelete' => 'Confirmation does not match']);
		}

		foreach ($user->usernames as $username)
		{
			if (!$username->delete())
			{
				abort(500, trans('global.messages.delete failed', ['id' => $user->id]));
			}
		}

		event(new UserDeleted($user));

		Auth::logout();

		return redirect(url('/'));
	}
}
