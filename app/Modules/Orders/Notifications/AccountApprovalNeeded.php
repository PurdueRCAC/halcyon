<?php

namespace App\Modules\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Modules\Orders\Models\Account;

class AccountApprovalNeeded extends Notification
{
	use Queueable;

	/**
	 * The user request
	 * 
	 * @var Account $account
	 */
	private $account;

	/**
	 * Constructor
	 * 
	 * @param   UserRequest $userrequest
	 * @return  void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
	}

	/**
	 * What methods can this notificaiton be sent
	 * 
	 * @param   object  $notifiable
	 * @return  array<int,string>
	 */
	public function via($notifiable)
	{
		return ['database'];
	}

	/**
	 * Generate a message formatted for database
	 * 
	 * @param   object  $notifiable
	 * @return  array<string,string>
	 */
	public function toArray($notifiable)
	{
		$title = trans('orders::orders.orders');

		$account = $this->account->purchasewbse;
		$account = $account ?: $this->account->purchaseio;

		$content = '<a href="' . route('site.orders.read', ['id' => $this->account->orderid]) . '">';
		$content .= trans('orders::orders.purchase account waiting approval', ['order' => $this->account->orderid, 'account' => $account]);
		$content .= '</a>';

		return [
			'title' => $title,
			'data' => $content
		];
	}
}
