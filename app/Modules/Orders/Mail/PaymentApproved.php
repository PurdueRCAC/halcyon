<?php

namespace App\Modules\Orders\Mail;

use App\Modules\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Users\Models\User;

class PaymentApproved extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The order instance.
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * The user instance.
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Order $order, User $user)
	{
		$this->order = $order;
		$this->user = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('orders::mail.paymentapproved')
					->subject(config('app.name') . '- Order #' . $this->order->id . ' Payment Approved')
					->with([
						'order' => $this->order,
						'user' => $this->user,
					]);
	}
}
