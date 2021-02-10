<?php

namespace App\Modules\Courses\Mail;

use App\Modules\Courses\Models\Account;
use App\Modules\Users\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Removed extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The user the mail goes to
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The course
	 *
	 * @var Course
	 */
	protected $course;

	/**
	 * The list of users
	 *
	 * @var  array
	 */
	protected $accounts;

	//public $priority = 'high';

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, Account $course, $accounts)
	{
		$this->user = $user;
		$this->course = $course;
		$this->accounts = $accounts;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('courses::mail.removed')
					->subject('Class Account Removal')
					->with([
						'user' => $this->user,
						'class' => $this->course,
						'accounts' => $this->accounts,
						//'priority' => $this->priority,
					]);
	}
}
