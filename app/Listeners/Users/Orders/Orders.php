<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\Orders;

use App\Modules\Users\Events\UserDisplay;
use App\Modules\Orders\Models\Order;

/**
 * User listener for Orders
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
		$events->listen(UserDisplay::class, self::class . '@handleUserDisplay');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserDisplay(UserDisplay $event)
	{
		$content = null;
		$user = $event->getUser();

		$r = ['section' => 'orders'];
		if (auth()->user()->id != $user->id)
		{
			$r['u'] = $user->id;
		}

		if ($event->getActive() == 'orders')
		{
			$content = view('orders::site.profile', [
				'user'   => $user,
				'orders' => $orders,
			]);
		}

		$event->addSection(
			route('site.users.account.section', $r),
			trans('orders::orders.my orders'),
			($event->getActive() == 'orders'),
			$content
		);
	}
}
