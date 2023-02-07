<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Type;

class TypeCreated
{
	/**
	 * @var Type
	 */
	public $type;

	/**
	 * Constructor
	 *
	 * @param Type $type
	 * @return void
	 */
	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	/**
	 * Return the entity
	 *
	 * @return Type
	 */
	public function getType(): Type
	{
		return $this->type;
	}
}
