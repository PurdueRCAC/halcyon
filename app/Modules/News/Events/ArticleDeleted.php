<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Article;

class ArticleDeleted
{
	/**
	 * @var Article
	 */
	public $article;

	/**
	 * Constructor
	 *
	 * @param  Article $article
	 * @return void
	 */
	public function __construct($article)
	{
		$this->article = $article;
	}
}
