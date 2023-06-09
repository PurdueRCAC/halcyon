<?php

namespace App\Modules\Storage\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
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
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(string $type, User $user, Notification $notification, Usage $latest)
	{
		$this->type = $type;
		$this->user = $user;
		$this->notification = $notification;
		$this->latest = $latest;
	}

	/**
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!$this->headers)
		{
			$this->headers = new Headers(
				messageId: null,
				references: [],
				text: [
					'X-Target-Object' => $this->notification->id,
					'X-Target-User' => $this->user->id,
				],
			);
		}
		return $this->headers;
	}

	/**
	 * Get the message envelope.
	 *
	 * @return Envelope
	 */
	public function envelope(): Envelope
	{
		return new Envelope(
			tags: ['storage', 'storage-quota'],
			metadata: [
				'user_id' => $this->user->id,
				'notification_id' => $this->notification->id,
			],
		);
	}

	/**
	 * Build the message.
	 *
	 * @return self
	 */
	public function build()
	{
		return $this->markdown('storage::mail.quota.' . $this->type)
					->subject(trans('storage::storage.mailquota.' . $this->type))
					->with([
						'user' => $this->user,
						'latest' => $this->latest,
						'notification' => $this->notification,
					]);
	}
}
