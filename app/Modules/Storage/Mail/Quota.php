<?php

namespace App\Modules\Storage\Mail;

use App\Modules\Storage\Models\Directory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Quota extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The order instance.
	 *
	 * @var Order
	 */
	protected $directory;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Directory $directory)
	{
		$this->directory = $directory;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('storage::mail.quota')
					->subject('Storage Quota')
					->with([
						'directory' => $this->directory,
					]);
	}
}
