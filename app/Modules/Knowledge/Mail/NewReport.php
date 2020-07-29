<?php

namespace App\Modules\Knowledge\Mail;

use App\Modules\Knowledge\Models\Report;
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
		return $this->markdown('knowledge::mail.newreport')
					->subject('Contact Report')
					->with([
						'report' => $this->report,
					]);
	}
}
