<?php

namespace App\Modules\Groups\Events;

class UnixGroupFetch
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array<int,mixed>
	 */
	public $results;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @return void
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
		$this->results = array();
	}
}
