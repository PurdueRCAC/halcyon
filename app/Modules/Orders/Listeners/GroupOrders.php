<?php

namespace App\Modules\Orders\Listeners;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(GroupReading::class, self::class . '@handleGroupReading');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleGroupReading(GroupReading $event)
	{
		$event->group->orders = Order::query()
			->where('groupid', '=', $event->group->id)
			->get();
	}
}
