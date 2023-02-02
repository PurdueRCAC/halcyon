<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Groups\Models\Member;

/**
 * Group listener to get compile info for a user
 */
class GetUserGroups
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param   Dispatcher  $events
	 * @return  void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
	}

	/**
	 * Display user profile info
	 *
	 * @param   UserBeforeDisplay  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event): void
	{
		$user = $event->getUser();

		// Owner
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->ownerofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priorownerofgroups = $memberships;

		// Members
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->memberofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priormemberofgroups = $memberships;

		// Viewers
		$memberships = Member::query()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->viewerofgroups = $memberships;

		$memberships = Member::query()
			->onlyTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priorviewerofgroups = $memberships;

		$event->setUser($user);
	}
}
