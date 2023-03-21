<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewComment extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The comment instance.
	 *
	 * @var Comment
	 */
	protected $comment;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  Comment $comment
	 * @return void
	 */
	public function __construct(Comment $comment)
	{
		$this->comment = $comment;
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
				messageId: null,
				references: [],
				text: [
					'X-Target-Object' => $this->comment->id,
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
			tags: ['contactreport', 'contactreport-comment'],
			metadata: [
				'comment_id' => $this->comment->id,
				'report_id' => $this->comment->contactreportid,
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
		return $this->markdown('contactreports::mail.newcomment')
					->subject(trans('contactreports::contactreports.contact report') . ': ' . trans('contactreports::contactreports.comment') . ' - ' . ($this->comment->report->groupid && $this->comment->report->group ? $this->comment->report->group->name . ', ' : '') . $this->comment->report->usersAsString())
					->with([
						'comment' => $this->comment,
					]);
	}
}
