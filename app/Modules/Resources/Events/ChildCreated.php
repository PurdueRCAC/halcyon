<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Child;

class ChildCreated
{
	/**
	 * @var Child
	 */
	public $child;

	/**
	 * Constructor
	 *
	 * @param Child $child
	 * @return void
	 */
	public function __construct(Child $child)
	{
		$this->child = $child;
	}
}
