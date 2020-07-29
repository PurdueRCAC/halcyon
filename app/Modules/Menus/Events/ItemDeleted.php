<?php

namespace App\Modules\Menus\Events;

use App\Modules\Menus\Models\Item;

class ItemDeleted
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
	public function __construct($item)
	{
		$this->item = $item;
	}
}
