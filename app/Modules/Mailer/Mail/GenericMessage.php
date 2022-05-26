<?php
namespace App\Modules\Mailer\Mail;

use App\Modules\Users\Models\User;
use App\Modules\Mailer\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMessage extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The message
	 *
	 * @var Message
	 */
	protected $message;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Create a new message instance.
	 *
	 * @param  Message  $message
	 * @return void
	 */
	public function __construct(Message $message, User $user)
	{
		$this->message = $message;
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		$user = $this->user;

		$body = $this->message->body;
		$body = str_replace(
			[
				'{user.id}',
				'{user.name}',
				'{user.username}',
				'{user.email}',
				'{site.name}',
				'{site.url}'
			],
			[
				$user->id,
				$user->name,
				$user->username,
				$user->email,
				config('app.name'),
				url('/')
			],
			$body
		);

		return $this->markdown('mailer::mail.message')
					->subject($this->message->subject)
					->with([
						'body' => $body,
						'alert' => $this->message->alert,
					]);
	}
}
