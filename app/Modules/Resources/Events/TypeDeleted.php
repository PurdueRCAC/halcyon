<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Type;

class TypeDeleted
{
	/**
	 * @var Type
	 */
	public $type;

	/**
	 * Constructor
	 *
	 * @param  Type $type
	 * @return void
	 */
	public function __construct(Type $type)
	{
		$this->type = $type;
	}
}
