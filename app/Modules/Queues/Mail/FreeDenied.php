<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeDenied extends Mailable
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
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,\App\Modules\Queues\Models\User>
	 * @return void
	 */
	public function __construct(User $user, $queueusers)
	{
		$this->user = $user;
		$this->queueusers = $queueusers;

		$this->mailTags[] = 'queue-denied';
		$this->mailTags[] = 'queue-free';
	}


	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freedenied.user')
					->subject(trans('queues::mail.freedenied.user'))
					->with([
						'user' => $this->user,
						'denials' => $this->queueusers,
					]);
	}
}
