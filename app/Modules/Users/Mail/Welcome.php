<?php
namespace App\Modules\Users\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  User  $user
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!($this->headers instanceof Headers))
		{
			$this->headers = new Headers(
				messageId: null,
				references: [],
				text: [
					'X-Target-User' => $this->user->id,
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
			tags: ['user', 'user-welcome'],
			metadata: [
				'user_id' => $this->user->id,
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
		return $this->markdown('users::mail.welcome')
					->subject(trans('users::users.welcome'))
					->with([
						'user' => $this->user,
					]);
	}
}
