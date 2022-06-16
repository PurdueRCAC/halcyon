<?php

namespace App\Widgets\Contactform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Message extends Mailable
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
	protected $dest_email;

	/**
	 * Resource name
	 *
	 * @var string
	 */
	protected $dest_name;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(array $data, string $dest_email, string $dest_name)
	{
		$this->data = $data;
		$this->dest_email = $dest_email;
		$this->dest_name = $dest_name;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('widget.contactform::mail.message')
					->subject($this->data['subject'])
					->from($this->data['name'], $this->data['email'])
					->with([
						'name'  => $this->data['name'],
						'email' => $this->data['email'],
						'body'  => $this->data['body'],
					]);
	}
}
