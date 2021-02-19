<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Entities\CartItem;

class CartAdded
{
	/**
	 * @var CartItem
	 */
	public $item;

	/**
	 * Constructor
	 *
	 * @param CartItem $item
	 * @return void
	 */
	public function __construct(CartItem $item)
	{
		$this->item = $item;
	}
}
