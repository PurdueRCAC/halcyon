<?php
namespace App\Modules\Users\Mail;

use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Create a new message instance.
	 *
	 * @param  User  $user
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('users::mail.welcome')
					->subject('Welcome!')
					->with([
						'user' => $this->user,
					]);
	}
}
