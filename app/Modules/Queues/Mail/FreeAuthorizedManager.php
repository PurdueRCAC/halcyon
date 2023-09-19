<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeAuthorizedManager extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * Group manager
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of queue users
	 *
	 * @var array<int,array<string,mixed>>
	 */
	protected $authorized;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array<string,mixed>> $authorized
	 * @return void
	 */
	public function __construct(User $user, $authorized = array())
	{
		$this->user = $user;
		$this->authorized = $authorized;

		$this->mailTags[] = 'queue-authorized';
		$this->mailTags[] = 'queue-free';
		$this->mailTags[] = 'queue-manager';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freeauthorized.manager')
					->subject(trans('queues::mail.freeauthorized'))
					->with([
						'user' => $this->user,
						'authorized' => $this->authorized
					]);
	}
}
