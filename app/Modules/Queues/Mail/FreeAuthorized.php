<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeAuthorized extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of queue memberships
	 *
	 * @var array<int,\App\Modules\Queues\Models\User>
	 */
	protected $queueusers;

	/**
	 * List of resources the user has been authorized for
	 *
	 * @var array<int,\App\Modules\Resources\Models\Asset>
	 */
	protected $roles;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,\App\Modules\Queues\Models\User> $queueusers
	 * @param array<int,\App\Modules\Resources\Models\Asset> $roles
	 * @return void
	 */
	public function __construct(User $user, $queueusers, $roles)
	{
		$this->user = $user;
		$this->queueusers = $queueusers;
		$this->roles = $roles;

		$this->mailTags[] = 'queue-authorized';
		$this->mailTags[] = 'queue-free';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freeauthorized.user')
					->subject(trans('queues::mail.freeauthorized'))
					->with([
						'user' => $this->user,
						'queueusers' => $this->queueusers,
						'roles' => $this->roles,
					]);
	}
}
