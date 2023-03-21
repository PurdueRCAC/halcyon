<?php

namespace App\Modules\Groups\Mail;

use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OwnerAuthorized extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The User the mail is being sent to
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The Group the user is now a manager of
	 *
	 * @var Group
	 */
	protected $group;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  User $user
	 * @param  Group $group
	 * @return void
	 */
	public function __construct(User $user, Group $group)
	{
		$this->user = $user;
		$this->group = $group;
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
					'X-Target-Object' => $this->group->id,
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
			tags: ['group'],
			metadata: [
				'group_id' => $this->group->id,
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
		return $this->markdown('groups::mail.ownerauthorized.user')
					->subject(trans('groups::groups.mail.ownerauthorized'))
					->with([
						'user' => $this->user,
						'group' => $this->group
					]);
	}
}
