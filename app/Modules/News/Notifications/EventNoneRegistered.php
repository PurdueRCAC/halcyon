<?php

namespace App\Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class EventNoneRegistered extends Notification
{
	use Queueable;

	/**
	 * What methods can this notificaiton be sent
	 * 
	 * @param   object  $notifiable
	 * @return  array<int,string>
	 */
	public function via($notifiable)
	{
		return ['slack'];
	}

	/**
	 * Generate a message formatted for Slack
	 * 
	 * @param   object  $notifiable
	 * @return  SlackMessage
	 */
	public function toSlack($notifiable)
	{
		$msg = (new SlackMessage)
			->from(config('app.name'))
			->info()
			->content(trans('news::news.no registrations for today'));

		return $msg;
	}
}
