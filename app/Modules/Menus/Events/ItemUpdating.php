<?php

namespace App\Modules\Menus\Events;

use App\Modules\Menus\Models\Item;

class ItemUpdating
{
	/**
	 * @var Item
	 */
	private $item;

	public function __construct(Item $item)
	{
		$this->item = $item;
	}

	/**
	 * @return Item
	 */
	public function getItem()
	{
		return $this->item;
	}
}
