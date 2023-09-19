<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Console\ScheduleCommand;
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
	 * @var bool
	 */
	public $verbose;

	/**
	 * Constructor
	 *
	 * @param  Asset $resource
	 * @param  ScheduleCommand $command
	 * @param  bool $verbose
	 * @return void
	 */
	public function __construct(Asset $resource, ScheduleCommand $command, bool $verbose = false)
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
	public function isVerbose(): bool
	{
		return $this->verbose;
	}
}
