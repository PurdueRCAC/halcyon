<?php

namespace App\Modules\Menus\Events;

use App\Modules\Menus\Models\Type;

class TypeUpdating
{
	/**
	 * @var Type
	 */
	private $type;

	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	/**
	 * @return Item
	 */
	public function getType()
	{
		return $this->type;
	}
}
