<?php

namespace App\Modules\Orders\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Modules\Orders\Models\Order;

class OrderFulfilled implements ShouldBroadcast
{
	use SerializesModels;

	/**
	 * @var Order
	 */
	public $order;

	/**
	 * Constructor
	 *
	 * @param  Order $order
	 * @return void
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @inheritdoc
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('users.' . $this->order->userid);
	}
}
