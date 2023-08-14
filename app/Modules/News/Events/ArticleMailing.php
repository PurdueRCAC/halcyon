<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Article;

class ArticleMailing
{
	/**
	 * @var Article
	 */
	public $article;

	/**
	 * From name
	 *
	 * @var string|null
	 */
	public $name;

	/**
	 * Layout to use
	 *
	 * @var string
	 */
	public $layout;

	/**
	 * Constructor
	 *
	 * @param  Article $article
	 * @param  string|null $name
	 * @return void
	 */
	public function __construct($article, $name = null)
	{
		$this->article = $article;
		$this->name = $name;
	}
}
