<?php

namespace App\Modules\Storage\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Storage\Models\Notification;
use App\Modules\Storage\Models\Usage;
use App\Modules\Users\Models\User;

class Quota extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The email type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The Notification
	 *
	 * @var Order
	 */
	protected $notification;

	/**
	 * The Notification
	 *
	 * @var Order
	 */
	protected $latest;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($type, User $user, Notification $notification, Usage $latest)
	{
		$this->type = $type;
		$this->user = $user;
		$this->notification = $notification;
		$this->latest = $latest;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('storage::mail.quota.' . $this->type)
					->subject(trans('storage::mail.quota.' . $this->type))
					->with([
						'user' => $this->user,
						'latest' => $this->latest,
						'notification' => $this->notification,
					]);
	}
}
