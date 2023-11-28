<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRemoved extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * List of queue memberships that where removed
	 *
	 * @var array<int,\App\Modules\Queues\Models\User>
	 */
	protected $removedqueues;

	/**
	 * List of queue memberships that are kept
	 *
	 * @var array<int,\App\Modules\Queues\Models\User>
	 */
	protected $keptqueues;

	/**
	 * List of resources that were removed
	 *
	 * @var array<int,\App\Modules\Resources\Models\Asset>
	 */
	protected $removedroles;

	/**
	 * Constructor
	 *
	 * @param User $user
	 * @param array<int,\App\Modules\Queues\Models\User>|\Illuminate\Database\Eloquent\Collection<int,\App\Modules\Queues\Models\User> $removedqueues
	 * @param array<int,\App\Modules\Queues\Models\User>|\Illuminate\Database\Eloquent\Collection<int,\App\Modules\Queues\Models\User> $keptqueues
	 * @param array<int,\App\Modules\Resources\Models\Asset>|\Illuminate\Database\Eloquent\Collection<int,\App\Modules\Resources\Models\Asset> $removedroles
	 * @return void
	 */
	public function __construct(User $user, $removedqueues = array(), $keptqueues = array(), $removedroles = array())
	{
		$this->user = $user;
		$this->removedqueues = $removedqueues;
		$this->keptqueues = $keptqueues;
		$this->removedroles = $removedroles;

		$this->mailTags[] = 'queue-removed';
		$this->mailTags[] = 'queue-free';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freeremoved.user')
					->subject(trans('queues::mail.freeremoved'))
					->with([
						'user' => $this->user,
						'removedqueues' => $this->removedqueues,
						'keptqueues' => $this->keptqueues,
						'removedroles' => $this->removedroles,
					]);
	}
}
