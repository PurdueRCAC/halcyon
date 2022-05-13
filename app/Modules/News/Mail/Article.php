<?php

namespace App\Modules\News\Mail;

use App\Modules\News\Models\Article as Art;
use App\Modules\News\Events\ArticleMailing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Article extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The article
	 *
	 * @var Art
	 */
	protected $article;

	/**
	 * From name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Create a new message instance.
	 *
	 * @param  Art  $article
	 * @param  string $name
	 * @return void
	 */
	public function __construct(Art $article, $name = null)
	{
		$this->article = $article;
		$this->name = $name;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		if ($this->name)
		{
			$this->from(config('mail.from.address'), $this->name);
		}

		event($e = new ArticleMailing($this->article, $this->name));
		$article = $e->article;

		return $this->markdown('news::mail.article')
					->subject($article->headline)
					->with([
						'article' => $article,
						'layout' => $e->layout ? $e->layout : 'message',
					]);
	}
}
