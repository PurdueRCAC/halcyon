<?php

namespace App\Modules\Groups\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\MemberCreated;
use App\Modules\Groups\Notifications\UserRequestSubmitted;

/**
 * Notify managers of new user request
 */
class NotifyManagersOfUserRequest
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(MemberCreated::class, self::class . '@handleMemberCreated');
	}

	/**
	 * Notify group managers
	 *
	 * @param   MemberCreated $event
	 * @return  void
	 */
	public function handleMemberCreated(MemberCreated $event): void
	{
		$member = $event->member;

		if (!$member || !$member->isPending() || !$member->group || !$member->request)
		{
			return;
		}

		foreach ($member->group->managers as $manager)
		{
			if (!$manager->user || substr($manager->user->username, 0, 2) == 'x-')
			{
				continue;
			}

			$manager->user->notify(new UserRequestSubmitted($member->request));
		}
	}
}
