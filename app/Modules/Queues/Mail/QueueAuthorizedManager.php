<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Queues\Models\Queue;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueAuthorizedManager extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of authorized users and queues
	 *
	 * @var array<int,array>
	 */
	protected $authorized;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array> $authorized
	 * @return void
	 */
	public function __construct(User $user, $authorized = array())
	{
		$this->user = $user;
		$this->authorized = $authorized;

		$this->mailTags[] = 'queue-authorized';
		$this->mailTags[] = 'queue-manager';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.queueauthorized.manager')
					->subject(trans('queues::mail.queueauthorized'))
					->with([
						'user' => $this->user,
						'authorized' => $this->authorized,
					]);
	}
}
