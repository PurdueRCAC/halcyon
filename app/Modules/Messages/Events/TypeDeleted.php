<?php

namespace App\Modules\Messages\Events;

use App\Modules\Messages\Models\Type;

class TypeDeleted
{
	/**
	 * @var Type
	 */
	public $type;

	/**
	 * Constructor
	 *
	 * @param  Message $message
	 * @return void
	 */
	public function __construct(Type $type)
	{
		$this->type = $type;
	}
}
