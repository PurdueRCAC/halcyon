<?php

namespace App\Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\News\Models\Article;
use App\Modules\Users\Models\User;

class EventClaimed extends Notification
{
	use Queueable;

	/**
	 * The article being notified about
	 * 
	 * @var Article $event
	 */
	private $event;

	/**
	 * Constructor
	 * 
	 * @param   Article $event
	 * @return  void
	 */
	public function __construct(Article $event)
	{
		$this->event = $event;
	}

	/**
	 * What methods can this notificaiton be sent
	 * 
	 * @param   object  $notifiable
	 * @return  array
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
		$event = $this->event;
		$claim = $event->associations()->where('assoctype', '=', 'staff')->first();

		$msg = (new SlackMessage)
			->from(config('app.name'));

		if (!$claim)
		{
			$msg->warning();
		}
		else
		{
			$msg->success();
		}

		$msg
			->content($event->datetimenews->format('g:ia') . ' - ' . $event->datetimenewsend->format('g:ia T'))
			->attachment(function ($attachment) use ($event, $claim)
			{
				$user = null;

				if ($claim)
				{
					$user = User::find($claim->associd);
				}

				$attachment->block(function ($block) use ($event, $user)
				{
					$block
						->type('section')
						->fields([
							[
								'type' => 'plain_text',
								'text' => $event->headline,
							],
							[
								'type' => 'mrkdwn',
								'text' => ($user ? $user->name . ' (' . $user->username . ')' : '_unclaimed_'),
							],
						]);
				})->color($user ? '#36b787' : '#da9e40');
			});

		return $msg;
	}
}
