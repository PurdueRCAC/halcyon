<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueAuthorized extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The User
	 *
	 * @var array
	 */
	protected $queueusers;

	/**
	 * The User
	 *
	 * @var array
	 */
	protected $roles;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $queueusers, $roles)
	{
		$this->user = $user;
		$this->queueusers = $queueusers;
		$this->roles = $roles;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.queueauthorized.user')
					->subject(trans('queues::mail.queueauthorized'))
					->with([
						'user' => $this->user,
						'queueusers' => $this->queueusers,
						'roles' => $this->roles,
					]);
	}
}
