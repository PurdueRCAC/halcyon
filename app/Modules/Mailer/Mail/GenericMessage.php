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
	 * From email and name
	 *
	 * @var array<string,string>
	 */
	protected $frominfo;

	/**
	 * Create a new message instance.
	 *
	 * @param  Message $message
	 * @param  User $user
	 * @param  array<string,string> $from
	 * @return void
	 */
	public function __construct(Message $message, User $user, $from = array())
	{
		$this->message = $message;
		$this->user = $user;
		$this->frominfo = (array)$from;
	}

	/**
	 * Build the message.
	 *
	 * @return GenericMessage
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

		if (isset($this->frominfo['email']))
		{
			if (!isset($this->frominfo['name']))
			{
				$this->frominfo['name'] = $this->frominfo['email'];
			}

			$this->from($this->frominfo['email'], $this->frominfo['name']);
		}

		return $this->markdown('mailer::mail.message')
					->subject($this->message->subject)
					->with([
						'body' => $body,
						'alert' => $this->message->alert,
					]);
	}
}
