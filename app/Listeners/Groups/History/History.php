<?php
namespace App\Listeners\Groups\History;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * History listener for group events
 */
class History
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
	 * Load history data when displaying a Group
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

		if ($event->getActive() == 'history' || $client == 'admin')
		{
			$content = view('groups::site.group.history', [
				'group' => $group
			]);
		}

		$event->addSection(
			'history',
			trans('groups::groups.history.title'),
			($event->getActive() == 'history'),
			$content
		);
	}
}
