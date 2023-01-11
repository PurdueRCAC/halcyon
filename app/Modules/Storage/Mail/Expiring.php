<?php

namespace App\Modules\Storage\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
