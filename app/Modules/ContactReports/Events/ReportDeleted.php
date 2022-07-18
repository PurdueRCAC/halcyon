<?php

namespace App\Modules\ContactReports\Events;

use App\Modules\ContactReports\Models\Report;

class ReportDeleted
{
	/**
	 * @var Report
	 */
	public $report;

	/**
	 * Constructor
	 *
	 * @param  Report $report
	 * @return void
	 */
	public function __construct(Report $report)
	{
		$this->report = $report;
	}
}
