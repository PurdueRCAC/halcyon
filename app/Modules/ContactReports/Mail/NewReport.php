<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewReport extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The report
	 *
	 * @var Report
	 */
	protected $report;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Report $report)
	{
		$this->report = $report;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('contactreports::mail.newreport')
					->subject('Contact Report - ' . ($this->report->group ? $this->report->group->name . ', ' : '') . $this->report->usersAsString())
					->with([
						'report' => $this->report,
					]);
	}
}
