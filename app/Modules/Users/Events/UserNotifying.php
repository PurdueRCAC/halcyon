<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Entities\Notification;
use App\Modules\Users\Models\User;

class UserNotifying
{
	/**
	 * @var User
	 */
	public $user;

	/**
	 * @var array
	 */
	public $notifications;

	/**
	 * Constructor
	 *
	 * @param User $user
	 * @return void
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
		$this->notifications = collect([]);
	}

	/**
	 * Add an item to the list
	 *
	 * @param Notification $item
	 * @return void
	 */
	public function addNotification(Notification $item)
	{
		$this->notifications->push($item);
	}
}
