<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Models\UserRequest;
use App\Modules\Queues\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRequested extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user request
	 *
	 * @var UserRequest
	 */
	protected $userrequests;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($userrequests)
	{
		$this->userrequests = $userrequests;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freerequested')
					->subject(trans('queues::mail.freerequested'))
					->with([
						'requests' => $this->userrequests
					]);
	}
}
