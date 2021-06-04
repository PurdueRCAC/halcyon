<?php
namespace App\Listeners\Groups\Storage;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * Storage listener for group events
 */
class Storage
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(GroupDisplay::class, self::class . '@handleGroupDisplay');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   GroupDisplay  $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$group = $event->getGroup();

		//$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->isManager(auth()->user()));
		$canManage = auth()->user()->can('manage groups') || ((auth()->user()->can('edit groups') || auth()->user()->can('edit.own groups')) && $group->isManager(auth()->user()));

		if (!$canManage)
		{
			return;
		}

		$content = null;
		$client = app('isAdmin') ? 'admin' : 'site';

		if ($event->getActive() == 'storage' || $client == 'admin')
		{
			$content = view('storage::' . $client . '.directories.group', [
				'group' => $group
			]);
		}

		$event->addSection(
			'storage',
			trans('storage::storage.storage'),
			($event->getActive() == 'storage'),
			$content
		);
	}
}
