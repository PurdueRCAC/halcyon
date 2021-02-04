<?php

namespace App\Modules\ContactReports\Events;

use App\Modules\ContactReports\Models\Type;

class TypeCreated
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
