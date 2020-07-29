<?php

namespace App\Modules\Orders\Mail;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Canceled extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The order
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('contactorders::mail.neworder')
					->subject('Contact Order')
					->with([
						'order' => $this->order,
					]);
	}
}
