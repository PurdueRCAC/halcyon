<?php

namespace App\Modules\Groups\Mail;

use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerRemovedManager extends Mailable
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
	 * @var array
	 */
	protected $people;

	/**
	 * Create a new message instance.
	 *
	 * @param  User $user
	 * @param  Group $group
	 * @param  array $people
	 * @return void
	 */
	public function __construct(User $user, Group $group, $people = array())
	{
		$this->user = $user;
		$this->group = $group;
		$this->people = $people;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('groups::mail.ownerremoved.manager')
					->subject(trans('groups::groups.mail.ownerremoved'))
					->with([
						'user' => $this->user,
						'group' => $this->group,
						'people' => $this->people
					]);
	}
}
