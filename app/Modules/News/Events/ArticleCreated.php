<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Article;

class ArticleCreated
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
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getArticle()
	{
		return $this->article;
	}
}
