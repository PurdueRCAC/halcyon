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
	 * The order instance.
	 *
	 * @var Order
	 */
	protected $comment;

	/**
	 * Create a new message instance.
	 *
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
					->subject('Contact Report Comment')
					->with([
						'comment' => $this->comment,
					]);
	}
}
