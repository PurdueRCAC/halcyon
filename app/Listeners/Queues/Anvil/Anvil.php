<?php

namespace App\Listeners\Queues\Anvil;

use App\Modules\Queues\Events\WelcomeClusterBuild;

/**
 * Anvil listener
 */
class Anvil
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(WelcomeClusterBuild::class, self::class . '@handleWelcomeClusterBuild');
	}

	/**
	 * Add the given resource as a role to each manager of the group that owns the newly created queue
	 *
	 * @param   WelcomeClusterBuild   $event
	 * @return  void
	 */
	public function handleWelcomeClusterBuild(WelcomeClusterBuild $event)
	{
		if (substr($event->user->username, 0, 2) != 'x-')
		{
			return;
		}

		$activity = $event->activity;

		$found = false;
		foreach ($activity as $resourceid => $data)
		{
			if ($data->resource->rolename == 'anvil')
			{
				$found = true;
				break;
			}
		}

		if (!$found)
		{
			return;
		}

		app('view')->addNamespace(
			'listener.queues.anvil',
			__DIR__ . '/views'
		);

		$event->path = 'listener.queues.anvil::welcome';
	}
}
