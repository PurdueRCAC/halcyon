<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Entities\Cart;

class CartUpdated
{
	/**
	 * @var Cart
	 */
	public $cart;

	/**
	 * Constructor
	 *
	 * @param Cart $cart
	 * @return void
	 */
	public function __construct(Cart $cart)
	{
		$this->cart = $cart;
	}
}
