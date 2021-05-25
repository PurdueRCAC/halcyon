<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeAuthorizedManager extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Group manager
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of queue users
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $authorized = array())
	{
		$this->user = $user;
		$this->authorized = $authorized;
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
