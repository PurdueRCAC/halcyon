<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Type;
use App\Modules\ContactReports\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class Followup extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The report type
	 *
	 * @var Type
	 */
	protected $type;

	/**
	 * The report user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  Type $type
	 * @param  User $user
	 * @return void
	 */
	public function __construct(Type $type, User $user)
	{
		$this->type = $type;
		$this->user = $user;
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
					'X-Target-Object' => $this->type->id,
				],
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
		return $this->markdown('contactreports::mail.followup')
					->subject(trans('contactreports::contactreports.survey'))
					->with([
						'type' => $this->type,
						'user' => $this->user,
					]);
	}
}
