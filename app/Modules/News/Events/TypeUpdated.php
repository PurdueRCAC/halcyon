<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Type;

class TypeUpdated
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
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getType()
	{
		return $this->type;
	}
}
