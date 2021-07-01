<?php
namespace App\Listeners\Users\Xsede;

use App\Modules\Users\Events\UserBeforeDisplay;

/**
 * User listener for XSEDE user attributes
 */
class Xsede
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
	 * @param   UserBeforeDisplay  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();

		foreach ($user->facets as $facet)
		{
			if ($facet->key == 'departmentNumber')
			{
				$user->department = $facet->value;
			}

			if ($facet->key == 'mail')
			{
				$user->email = $facet->value;
			}

			if ($facet->key == 'telephoneNumber')
			{
				$user->phone = $facet->value;
			}

			if ($facet->key == 'o')
			{
				$user->campus = $facet->value;
			}
		}

		$event->setUser($user);
	}
}
