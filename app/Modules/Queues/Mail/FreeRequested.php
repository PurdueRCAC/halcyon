<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use App\Modules\Queues\Models\UserRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRequested extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The user request
	 *
	 * @var UserRequest
	 */
	protected $userrequests;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $userrequests)
	{
		$this->user = $user;
		$this->userrequests = $userrequests;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freerequested')
					->subject(trans('queues::mail.freerequested'))
					->with([
						'user' => $this->user,
						'requests' => $this->userrequests
					]);
	}
}
