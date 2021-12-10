<?php

namespace App\Modules\Courses\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Composed extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user the mail goes to
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The message subject
	 *
	 * @var Course
	 */
	public $subject;

	/**
	 * The message body
	 *
	 * @var  array
	 */
	protected $body;

	/**
	 * Create a new message instance.
	 *
	 * @param  User    $user
	 * @param  string  $subject
	 * @param  string  $body
	 * @return void
	 */
	public function __construct(User $user, string $subject, string $body)
	{
		$this->user = $user;
		$this->subject = $subject;
		$this->body = $body;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('courses::mail.composed')
					->subject($this->subject)
					->with([
						'user' => $this->user,
						'body' => $this->body,
					]);
	}
}
