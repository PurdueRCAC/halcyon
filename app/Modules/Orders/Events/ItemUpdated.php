<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Models\Item;

class ItemUpdated
{
	/**
	 * @var Item
	 */
	public $item;

	/**
	 * Constructor
	 *
	 * @param  Item $item
	 * @return void
	 */
	public function __construct(Item $item)
	{
		$this->item = $item;
	}
}
