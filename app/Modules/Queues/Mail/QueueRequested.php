<?php

namespace App\Modules\Queues\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Queues\Models\UserRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Users\Models\User;

class QueueRequested extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user
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
		return $this->markdown('queues::mail.queuerequested')
					->subject(trans('queues::mail.queuerequested'))
					->with([
						'user' => $this->user,
						'requests' => $this->userrequests
					]);
	}
}
