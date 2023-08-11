<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueDeniedManager extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Denied users
	 *
	 * @var array<int,array>
	 */
	protected $denials;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array> $denials
	 * @return void
	 */
	public function __construct(User $user, $denials = array())
	{
		$this->user = $user;
		$this->denials = $denials;

		$this->mailTags[] = 'queue-denied';
		$this->mailTags[] = 'queue-manager';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.queuedenied.manager')
					->subject(trans('queues::mail.queuedenied'))
					->with([
						'user' => $this->user,
						'denials' => $this->denials,
					]);
	}
}
