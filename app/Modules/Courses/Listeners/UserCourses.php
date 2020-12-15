<?php

namespace App\Modules\Courses\Listeners;

use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Courses\Models\Account;
use Carbon\Carbon;

/**
 * User listener for courses
 */
class UserCourses
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
	}

	/**
	 * Display user profile info
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();

		$accounts = Account::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsActive()
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->orderBy('classname', 'asc')
			->get();

		foreach ($accounts as $account)
		{
			$account->api = route('api.courses.read', ['id' => $account->id]);
			$account->users = $account->members()
				->withTrashed()
				->whereIsActive()
				->get();
		}

		$user->classaccounts = $accounts;

		$event->setUser($user);
	}
}
