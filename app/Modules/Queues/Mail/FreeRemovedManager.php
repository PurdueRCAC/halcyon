<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRemovedManager extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of removals
	 *
	 * @var array<int,array<string,mixed>>
	 */
	protected $removals;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array<string,mixed>> $removals
	 * @return void
	 */
	public function __construct(User $user, $removals = array())
	{
		$this->user = $user;
		$this->removals = $removals;

		$this->mailTags[] = 'queue-removed';
		$this->mailTags[] = 'queue-free';
		$this->mailTags[] = 'queue-manager';
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
