<?php

namespace App\Modules\ContactReports\Events;

use App\Modules\ContactReports\Models\Report;
use Carbon\Carbon;

class ReportFrom
{
	/**
	 * @var Report
	 */
	public $report;

	/**
	 * @var string
	 */
	public $from_type;

	/**
	 * @var int
	 */
	public $from_id;

	/**
	 * Constructor
	 *
	 * @param string $type
	 * @param int $id
	 * @return void
	 */
	public function __construct(string $type, int $id = 0)
	{
		if (strstr($type, ':'))
		{
			$bits = explode(':', $type);
			$type = $bits[0];
			$id = $bits[1];
		}
		$this->from_type = $type;
		$this->from_id = $id;
		$this->report = new Report;
		$this->report->datetimecontact = Carbon::now();
	}
}
