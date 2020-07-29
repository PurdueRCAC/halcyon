<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\Group;

class GroupReading
{
	/**
	 * @var Group
	 */
	public $group;

	/**
	 * Constructor
	 *
	 * @param Group $group
	 * @return void
	 */
	public function __construct(Group $group)
	{
		$this->group = $group;
	}
}
