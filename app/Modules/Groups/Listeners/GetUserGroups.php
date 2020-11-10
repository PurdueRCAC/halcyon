<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Listeners;

use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Groups\Models\Member;

/**
 * User listener for groups
 */
class GetUserGroups
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

		// Owner
		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->whereIsActive()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->ownerofgroups = $memberships;

		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsManager()
			->whereIsTrashed()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priorownerofgroups = $memberships;

		// Members
		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->whereIsActive()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->memberofgroups = $memberships;

		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsMember()
			->whereIsTrashed()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->priormemberofgroups = $memberships;

		// Viewers
		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->whereIsActive()
			->orderBy('datecreated', 'asc')
			->get();

		foreach ($memberships as $membership)
		{
			$membership->api = route('api.groups.read', ['id' => $membership->groupid]);
		}

		$user->viewerofgroups = $memberships;

		$memberships = Member::query()
			->withTrashed()
			->where('userid', '=', $user->id)
			->whereIsViewer()
			->whereIsTrashed()
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
