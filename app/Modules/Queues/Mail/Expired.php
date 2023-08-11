<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Expired extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of expired accounts
	 *
	 * @var array<int,\App\Modules\Queues\Models\User>
	 */
	protected $people;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,\App\Modules\Queues\Models\User> $people
	 * @return void
	 */
	public function __construct(User $user, $people = array())
	{
		$this->user = $user;
		$this->people = $people;

		$this->mailTags[] = 'queue-expired';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.expired')
					->subject(trans('queues::mail.expired'))
					->with([
						'user' => $this->user,
						'people' => $this->people
					]);
	}
}
