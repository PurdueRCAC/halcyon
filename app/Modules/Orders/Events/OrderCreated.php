<?php

namespace App\Modules\Orders\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Modules\Orders\Models\Order;

class OrderCreated implements ShouldBroadcast
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
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('users.' . $this->order->userid);
	}
}
