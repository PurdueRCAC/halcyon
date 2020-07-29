<?php

namespace App\Modules\News\Mail;

use App\Modules\News\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Article extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The article
	 *
	 * @var Article
	 */
	protected $article;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(Article $article)
	{
		$this->article = $article;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('news::mail.article')
					->subject($this->article->headline)
					->with([
						'article' => $this->article,
					]);
	}
}
