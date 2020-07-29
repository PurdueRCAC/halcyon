<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Type;

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
