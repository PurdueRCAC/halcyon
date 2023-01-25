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
	 * @param  Report $report
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
		$append = ($this->report->groupid && $this->report->group ? $this->report->group->name . ', ' : '') . $this->report->usersAsString();

		return $this->markdown('contactreports::mail.newreport')
					->subject('Contact Report' . ($append ? ' - ' . $append : ''))
					->with([
						'report' => $this->report,
					]);
	}
}
