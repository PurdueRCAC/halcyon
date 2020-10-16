<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\Type;

class TypeCreating
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
