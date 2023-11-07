<?php

namespace App\Modules\News\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Modules\News\Models\Association;
use App\Modules\News\Mail\Cancelled;

class ArticleCancelled extends Notification
{
	use Queueable;

	/**
	 * The article association being notified about
	 * 
	 * @var Association $association
	 */
	private $association;

	/**
	 * Constructor
	 * 
	 * @param   Association $association
	 * @return  void
	 */
	public function __construct(Association $association)
	{
		$this->association = $association;
	}

	/**
	 * What methods can this notificaiton be sent
	 * 
	 * @param   object  $notifiable
	 * @return  array<int,string>
	 */
	public function via($notifiable)
	{
		return ['database', 'mail'];
	}

	/**
	 * Generate a message formatted for database
	 * 
	 * @param   object  $notifiable
	 * @return  array<string,string>
	 */
	public function toArray($notifiable)
	{
		$article = $this->association->article;
		$title = $article->headline . ' @ ' . $article->formatDate($article->datetimenews, $article->datetimenewsend);

		return [
			'title' => $article->type->name,
			'data' => '<a href="' . route('site.news.show', ['id' => $this->association->id]) . '">Registration to ' . $title . ' cancelled.</a>'
		];
	}

	/**
	 * Generate a message formatted for mail
	 * 
	 * @param   object  $notifiable
	 * @return  object
	 */
	public function toMail($notifiable)
	{
		$message = new Cancelled($this->association);
		$message->headers()->text([
			'X-Command' => 'notification:articlecancelled',
			'X-Target-User' => $notifiable->id,
			'X-Target-Object' => $this->association->id,
		]);

		return $message;
	}
}
