<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Queues\Models\UserRequest;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\User;

class QueueRequested extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The user request
	 *
	 * @var array<int,array<int,QueueUser>>
	 */
	protected $userrequests;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array<int,QueueUser>> $userrequests
	 * @return void
	 */
	public function __construct(User $user, $userrequests)
	{
		$this->user = $user;
		$this->userrequests = $userrequests;

		$this->mailTags[] = 'queue-requested';
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
