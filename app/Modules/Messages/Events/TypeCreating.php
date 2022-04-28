<?php

namespace App\Modules\Messages\Events;

use App\Modules\Messages\Models\Type;

class TypeCreating
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
}
