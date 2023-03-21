<?php

namespace App\Modules\Storage\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Group;

class Expiring extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The group
	 *
	 * @var Group
	 */
	protected $group;

	/**
	 * List of directories with expiring allocations
	 *
	 * @var array
	 */
	protected $directories;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  array $directories
	 * @param  User  $user
	 * @return void
	 */
	public function __construct($directories, User $user, Group $group)
	{
		$this->directories = $directories;
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
			tags: ['storage', 'storage-expiring'],
			metadata: [
				'group_id' => $this->group->id,
				'user_id' => $this->user->id,
			],
		);
	}

	/**
	 * Build the message.
	 *
	 * @return Expiring
	 */
	public function build()
	{
		return $this->markdown('storage::mail.expiring')
					->subject(trans('storage::storage.mailexpiring'))
					->with([
						'user' => $this->user,
						'directories' => $this->directories,
						'group' => $this->group,
					]);
	}
}
