<?php

namespace App\Modules\Groups\Mail;

use App\Modules\Groups\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerRemoved extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The order instance.
	 *
	 * @var Order
	 */
	protected $member;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Member $member)
	{
		$this->member = $member;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('groups::mail.ownerremoved')
					->subject(trans('groups::mail.ownerremoved'))
					->with([
						'member' => $this->member,
					]);
	}
}
