<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UnixGroup;

class UnixGroupCreated
{
	/**
	 * @var UnixGroup
	 */
	public $unixgroup;

	/**
	 * Constructor
	 *
	 * @param UnixGroup $unixgroup
	 * @return void
	 */
	public function __construct(UnixGroup $unixgroup)
	{
		$this->unixgroup = $unixgroup;
	}
}
