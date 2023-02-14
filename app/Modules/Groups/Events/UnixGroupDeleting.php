<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\UnixGroup;

class UnixGroupDeleting
{
	/**
	 * @var UnixGroup
	 */
	public $unixgroup;

	/**
	 * @var array<int,string>
	 */
	public $errors = array();

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
