<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Article;

class ArticleUpdating
{
	/**
	 * @var Article
	 */
	public $article;

	/**
	 * Constructor
	 *
	 * @param Article $article
	 * @return void
	 */
	public function __construct(Article $article)
	{
		$this->article = $article;
	}

	/**
	 * @return Article
	 */
	public function getArticle()
	{
		return $this->article;
	}
}
