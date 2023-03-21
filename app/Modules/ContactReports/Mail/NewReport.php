<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
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
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

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
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!$this->headers)
		{
			$this->headers = new Headers(
				messageId: null, //messageId: 'custom-message-id@example.com',
				references: [], //references: ['previous-message@example.com'],
				text: [
					'X-Target-Object' => $this->report->id,
				],
			);
		}
		return $this->headers;
	}

	/**
	 * Get the message envelope.
	 *
	 * @return Envelope
	 */
	public function envelope(): Envelope
	{
		return new Envelope(
			tags: ['contactreport'],
			metadata: [
				'report_id' => $this->report->id,
			],
		);
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
					->subject(trans('contactreports::contactreports.contact report') . ($append ? ' - ' . $append : ''))
					->with([
						'report' => $this->report,
					]);
	}
}
