<?php

namespace App\Modules\Resources\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Modules\Resources\Models\Asset;

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
	 * @var array<int,Asset>
	 */
	protected $started;

	/**
	 * List of stopped resources
	 *
	 * @var array<int,Asset>
	 */
	protected $stopped;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  string $action
	 * @param  array<int,Asset> $started
	 * @param  array<int,Asset> $stopped
	 * @return void
	 */
	public function __construct($action, $started = array(), $stopped = array())
	{
		$this->action  = $action;
		$this->started = $started;
		$this->stopped = $stopped;
	}

	/**
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!($this->headers instanceof Headers))
		{
			$this->headers = new Headers(
				messageId: null,
				references: [],
				text: [],
			);
		}
		return $this->headers;
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
