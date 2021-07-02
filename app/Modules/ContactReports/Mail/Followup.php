<?php

namespace App\Modules\ContactReports\Mail;

use App\Modules\ContactReports\Models\Type;
use App\Modules\ContactReports\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Type $type, User $user)
	{
		$this->type = $type;
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('contactreports::mail.followup')
					->subject('Survey')
					->with([
						'type' => $this->type,
						'user' => $this->user,
					]);
	}
}
