<?php

namespace App\Widgets\Earlyuserform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Confirmation extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Submitted data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Destination email
	 *
	 * @var string
	 */
	protected $destination;

	/**
	 * Resource name
	 *
	 * @var string
	 */
	protected $resource;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(array $data, string $destination, string $resource)
	{
		$this->data = $data;
		$this->destination = $destination;
		$this->resource = $resource;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('widget.earlyuserform::mail.confirmation')
					->subject(config('app.name') . '- Early User Application Confirmation')
					->with([
						'data' => $this->data,
						'destination' => $this->destination,
						'resource' => $this->resource,
					]);
	}
}
