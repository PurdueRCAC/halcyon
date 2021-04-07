<?php
namespace App\Listeners\Groups\Orders;

use App\Modules\Groups\Events\GroupDisplay;

/**
 * Storage listener for group events
 */
class Orders
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
	 * Load Orders data when displaying a Group
	 *
	 * @param   GroupDisplay  $event
	 * @return  void
	 */
	public function handleGroupDisplay(GroupDisplay $event)
	{
		$group = $event->getGroup();
		$user = auth()->user();

		if (!$user || !($user->can('edit groups') || ($user->can('edit.own groups') && $group->isManager($user))))
		{
			return;
		}

		$content = null;
		$client = app('isAdmin') ? 'admin' : 'site';

		if ($event->getActive() == 'orders' || $client == 'admin')
		{
			$content = view('orders::' . $client . '.orders.group', [
				'group' => $group
			]);
		}

		$event->addSection(
			'orders',
			trans('orders::orders.orders'),
			($event->getActive() == 'orders'),
			$content
		);
	}
}
