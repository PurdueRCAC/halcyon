<?php

namespace App\Modules\Groups\Mail;

use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerRemoved extends Mailable
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
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('groups::mail.ownerremoved.user')
					->subject(trans('groups::groups.mail.ownerremoved'))
					->with([
						'user' => $this->user,
						'group' => $this->group
					]);
	}
}
