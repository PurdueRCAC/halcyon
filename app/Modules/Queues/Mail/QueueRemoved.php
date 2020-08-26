<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueRemoved extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The user
	 *
	 * @var array
	 */
	protected $removedqueues;

	/**
	 * The user
	 *
	 * @var array
	 */
	protected $keptqueues;

	/**
	 * The user
	 *
	 * @var array
	 */
	protected $removedroles;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $removedqueues = array(), $keptqueues = array(), $removedroles = array())
	{
		$this->user = $user;
		$this->removedqueues = $removedqueues;
		$this->keptqueues = $keptqueues;
		$this->removedroles = $removedroles;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.queueremoved.user')
					->subject(trans('queues::mail.queueremoved'))
					->with([
						'user' => $this->user,
						'removedqueues' => $this->removedqueues,
						'keptqueues' => $this->keptqueues,
						'removedroles' => $this->removedroles,
					]);
	}
}
