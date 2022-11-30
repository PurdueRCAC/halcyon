<?php

namespace App\Modules\Queues\Events;

use App\Modules\Resources\Models\Asset;

class Schedule
{
	/**
	 * @var Asset
	 */
	public $resource;

	/**
	 * @var ScheduleCommand
	 */
	public $command;

	/**
	 * Constructor
	 *
	 * @param  Asset $resource
	 * @param  object $command
	 * @param  bool $verbose
	 * @return void
	 */
	public function __construct(Asset $resource, $command, $verbose = false)
	{
		$this->resource = $resource;
		$this->command = $command;
		$this->verbose = $verbose;
	}

	/**
	 * Is output verbose
	 *
	 * @return bool
	 */
	public function isVerbose()
	{
		return $this->verbose;
	}
}
