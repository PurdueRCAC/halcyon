<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeCluster extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The Queue
	 *
	 * @var Queue
	 */
	protected $user;

	/**
	 * The Queue
	 *
	 * @var Queue
	 */
	protected $activity;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $activity = array())
	{
		$this->user = $user;
		$this->activity = $activity;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.welcomemessage')
					->subject(trans('queues::mail.welcomemessage'))
					->with([
						'user' => $this->user,
						'activity' => $this->activity,
					]);
	}
}
