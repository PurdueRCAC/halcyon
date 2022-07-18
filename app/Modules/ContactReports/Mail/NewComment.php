<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('contactreports::mail.newcomment')
					->subject('Contact Report Comment - ' . ($this->comment->report->groupid && $this->comment->report->group ? $this->comment->report->group->name . ', ' : '') . $this->comment->report->usersAsString())
					->with([
						'comment' => $this->comment,
					]);
	}
}
