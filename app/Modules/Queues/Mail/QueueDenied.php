<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueDenied extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The user
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
	 * @param array<int,\App\Modules\Queues\Models\User> $queueusers
	 * @return void
	 */
	public function __construct(User $user, $queueusers = array())
	{
		$this->user = $user;
		$this->queueusers = $queueusers;

		$this->mailTags[] = 'queue-denied';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.queuedenied.user')
					->subject(trans('queues::mail.queuedenied'))
					->with([
						'user' => $this->user,
						'queueusers' => $this->queueusers
					]);
	}
}
