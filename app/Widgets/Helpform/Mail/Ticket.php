<?php

namespace App\Widgets\Helpform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Ticket extends Mailable
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
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(array $data, string $destination)
	{
		$this->data = $data;
		$this->destination = $destination;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('widget.helpform::mail.ticket')
					->from($this->data['email'], ($this->data['user'] ? $this->data['user']->name : $this->data['email']))
					->subject($this->data['subject'])
					->with([
						'data' => $this->data,
						'destination' => $this->destination,
					]);
	}
}
