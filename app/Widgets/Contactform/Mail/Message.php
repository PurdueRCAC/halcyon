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
	 * @param array<string,mixed> $data
	 * @param string $dest_email
	 * @param string $dest_name
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
					->from($this->data['email'], $this->data['name'])
					->with([
						'name'  => $this->data['name'],
						'email' => $this->data['email'],
						'body'  => $this->data['body'],
					]);
	}
}
