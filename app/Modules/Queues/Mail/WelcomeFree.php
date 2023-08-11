<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeFree extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The Queue
	 *
	 * @var Queue
	 */
	protected $user;

	/**
	 * List of activity
	 *
	 * @var array<int,\stdClass>
	 */
	protected $activity;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,\stdClass> $activity
	 * @return void
	 */
	public function __construct(User $user, $activity = array())
	{
		$this->user = $user;
		$this->activity = $activity;

		$this->mailTags[] = 'queue-welcome-free';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.welcome.free')
					->subject(trans('queues::mail.welcome.free'))
					->with([
						'user' => $this->user,
						'activity' => $this->activity,
					]);
	}
}
