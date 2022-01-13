<?php

namespace App\Modules\News\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Association;
//use App\Modules\Users\Models\User;

class Cancelled extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The article
	 *
	 * @var Article
	 */
	protected $article;

	/**
	 * User association
	 *
	 * @var Association
	 */
	protected $association;

	/**
	 * Create a new message instance.
	 *
	 * @param  Article  $article
	 * @param  Association $association
	 * @return void
	 */
	public function __construct(Association $association)
	{
		$this->article = $association->article;
		$this->association = $association;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('news::mail.cancelled')
					->subject(trans('news::news.cancelled event registration'))
					->with([
						'article' => $this->article,
						'association' => $this->association,
					]);
	}
}
