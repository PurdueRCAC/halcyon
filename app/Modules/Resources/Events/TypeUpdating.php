<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Type;

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
	 * @return Article
	 */
	public function getType()
	{
		return $this->type;
	}
}
