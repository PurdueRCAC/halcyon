<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Models\Order;

class OrderDeleted
{
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
}
