<?php

namespace App\Modules\Orders\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Groups\Events\GroupReading;
use App\Modules\Orders\Models\Order;

/**
 * Group listener
 */
class GroupOrders
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events)
	{
		$events->listen(GroupReading::class, self::class . '@handleGroupReading');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   GroupReading  $event
	 * @return  void
	 */
	public function handleGroupReading(GroupReading $event)
	{
		$event->group->orders = Order::query()
			->where('groupid', '=', $event->group->id)
			->get();
	}
}
