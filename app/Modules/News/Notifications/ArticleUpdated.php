<?php

namespace App\Modules\News\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\News\Models\Article;

class ArticleUpdated extends Notification
{
	use Queueable;

	/**
	 * The article being notified about
	 * 
	 * @var Article $article
	 */
	private $article;

	/**
	 * Constructor
	 * 
	 * @param   Article $article
	 * @return  void
	 */
	public function __construct(Article $article)
	{
		$this->article = $article;
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
		$article = $this->article;

		$update = $article->updates()->orderBy('datetimecreated', 'desc')->first();

		return (new SlackMessage)
			->success()
			->from(config('app.name'))
			->content('News article *updated* at ' . ($update ? $update->formattedDatetimecreated : $article->datetimeedited->format('l, F j, Y g:ia')) . '.')
			->attachment(function ($attachment) use ($notifiable, $article, $update)
			{
				$user = $article->creator;

				$content = $article->toHtml();

				if (count($article->updates))
				{
					$content = $update->toHtml();
				}

				$content = strip_tags($content);
				$content = html_entity_decode($content);
				$content = Str::limit($content, 150);

				$resources = $article->resourceList()->get();
				if (count($resources) > 0)
				{
					$resourceArray = array();
					foreach ($resources as $resource)
					{
						$resourceArray[] = $resource->name;
					}
				}

				$fields = [
					'Category' => $article->type->name,
					'Date/Time' => $article->formatDate($article->datetimenews, $article->datetimenewsend),
				];

				if (count($resourceArray))
				{
					$fields['Resources'] = implode(', ', $resourceArray);
				}

				if ($article->location)
				{
					$fields['Location'] = $article->location;
				}

				if ($article->url)
				{
					$fields['URL'] = $article->url;
				}

				$attachment
					->title($article->headline, route('site.news.show', ['id' => $article->id]))
					//->author($user->name . ' (' . $user->username . ')')
					->content($content)
					->fields($fields);
			});
	}
}
