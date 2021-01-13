<?php

namespace App\Modules\Status\Events;

class StatusUpdated
{
	/**
	 * @var array
	 */
	public $status;

	/**
	 * Constructor
	 *
	 * @param  string status
	 * @return void
	 */
	public function __construct(string $status)
	{
		$this->status = $status;
	}
}
