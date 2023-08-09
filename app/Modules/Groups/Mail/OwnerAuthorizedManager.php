<?php

namespace App\Modules\Groups\Mail;

use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OwnerAuthorizedManager extends Mailable
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
	 * List of people authorized
	 *
	 * @var array<int,\App\Modules\Groups\Models\Member>
	 */
	protected $people;

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
	 * @param  array<int,\App\Modules\Groups\Models\Member> $people
	 * @return void
	 */
	public function __construct(User $user, Group $group, $people = array())
	{
		$this->user = $user;
		$this->group = $group;
		$this->people = $people;
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
			tags: ['group', 'group-manager'],
			metadata: [
				'group_id' => $this->group->id,
			],
		);
	}

	/**
	 * Build the message.
	 *
	 * @return self
	 */
	public function build()
	{
		return $this->markdown('groups::mail.ownerauthorized.manager')
					->subject(trans('groups::groups.mail.ownerauthorized'))
					->with([
						'user' => $this->user,
						'group' => $this->group,
						'people' => $this->people
					]);
	}
}
