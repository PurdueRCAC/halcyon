<?php

namespace App\Modules\Orders\Listeners;

use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Orders\Models\Order;

/**
 * User listener for queues
 */
class UserOrders
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
	 * @param   object  $event  UserBeforeDisplay
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		$user = $event->getUser();

		$orders = Order::query()
			->where(function($where) use ($user)
			{
				$where->where('userid', '=', $user->id)
					->orWhere('submitteruserid', '=', $user->id);
			})
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($orders as $order)
		{
			$order->api = route('api.orders.read', ['id' => $order->id]);
		}

		$user->orders = $orders;

		$event->setUser($user);
	}
}
