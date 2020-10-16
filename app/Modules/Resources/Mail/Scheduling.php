<?php

namespace App\Modules\Resources\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Scheduling extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The action performed (started|stopped)
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * List of started resources
	 *
	 * @var array
	 */
	protected $started;

	/**
	 * List of stopped resources
	 *
	 * @var array
	 */
	protected $stopped;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($action, $started = array(), $stopped = array())
	{
		$this->action  = $action;
		$this->started = $started;
		$this->stopped = $stopped;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('resources::mail.scheduling.' . $this->action)
					->subject(trans('resources::mail.scheduling.' . $this->action))
					->with([
						'started' => $this->started,
						'stopped' => $this->stopped,
					]);
	}
}
