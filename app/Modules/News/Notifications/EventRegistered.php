<?php

namespace App\Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\News\Models\Article;

class EventRegistered extends Notification
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

		return (new SlackMessage)
			->content($event->datetimenews->format('g:ia') . ' - ' . $event->datetimenewsend->format('g:ia T'))
			->attachment(function ($attachment) use ($notifiable, $event)
			{
				$assoc = $event->associations->first();
				$user = $assoc->associated;

				event($e = new UserBeforeDisplay($user));
				$user = $e->getUser();

				$memberships = $user->groups()
					->where('groupid', '>', 0)
					->whereIsManager()
					->get();

				$ids = array();
				$allqueues = array();
				foreach ($memberships as $membership)
				{
					$group = $membership->group;

					$queues = $group->queues;

					foreach ($queues as $queue)
					{
						$ids[] = $queue->id;

						if (!$queue || $queue->trashed())
						{
							continue;
						}

						if (!$queue->scheduler || $queue->scheduler->trashed())
						{
							continue;
						}

						$queue->status = 'member';

						$allqueues[] = $queue;
					}
				}

				$queues = $user->queues()
					->whereNotIn('queueid', $ids)
					->get();

				foreach ($queues as $qu)
				{
					if ($qu->trashed())
					{
						continue;
					}

					$queue = $qu->queue;

					if (!$queue || $queue->trashed())
					{
						continue;
					}

					if (!$queue->scheduler || $queue->scheduler->trashed())
					{
						continue;
					}

					$group = $queue->group;

					if (!$group || !$group->id)
					{
						continue;
					}

					if ($qu->isPending())
					{
						$queue->status = 'pending';
					}
					else
					{
						$queue->status = 'member';
					}

					$allqueues[] = $queue;
				}

				$groups = array();
				foreach ($allqueues as $queue)
				{
					$groups[] = $group->name . ' - ' . $queue->name . ($queue->resource ? ' (' . $queue->resource->name . ')' : '');
				}

				$attachment
					->title($event->headline, route('site.news.show', ['id' => $event->id]))
					->author($user->name . ' (' . $user->username . ')', route('site.users.account', ['u' => $user->id]))
					->content($assoc->comment)
					->fields([
						'Groups' => (count($groups) ? implode("\n", $groups) : '-'),
						'Department' => ($user->department ? $user->department : '-'),
					]);

				if ($event->url)
				{
					$attachment->action($event->location, $event->url, 'primary');
				}
			});
	}
}
