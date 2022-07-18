<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Type;

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
	public function getType()
	{
		return $this->type;
	}
}
