<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeDenied extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The Queue
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freedenied.user')
					->subject(trans('queues::mail.freedenied.user'))
					->with([
						'queue' => $this->queue,
					]);
	}
}
