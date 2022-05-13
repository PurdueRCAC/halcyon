<?php

namespace App\Listeners\Queues\Anvil;

use App\Modules\Queues\Events\WelcomeClusterBuild;
use App\Modules\News\Events\ArticleMailing;

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
		$events->listen(ArticleMailing::class, self::class . '@handleArticleMailing');
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

	/**
	 * Change mail templates if the article is for Anvil
	 *
	 * @param   ArticleMailing   $event
	 * @return  void
	 */
	public function handleArticleMailing(ArticleMailing $event)
	{
		if (count($event->article->resources) != 1)
		{
			return;
		}

		$found = false;
		foreach ($event->article->resources as $res)
		{
			if ($res->resource->listname == 'anvil')
			{
				$found = true;
				break;
			}
		}

		if (!$found)
		{
			return;
		}

		$event->layout = 'xsede';
	}
}
