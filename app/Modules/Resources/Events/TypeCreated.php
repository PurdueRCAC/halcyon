<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Type;

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
	 * @param array $data
	 * @return void
	 */
	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getType()
	{
		return $this->type;
	}
}
