<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRemovedManager extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The User
	 *
	 * @var array
	 */
	protected $removals;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $removals = array())
	{
		$this->user = $user;
		$this->removals = $removals;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freeremoved.manager')
					->subject(trans('queues::mail.freeremoved'))
					->with([
						'user' => $this->user,
						'removals' => $this->removals,
					]);
	}
}
