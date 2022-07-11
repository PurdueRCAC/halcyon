<?php

namespace App\Modules\News\Events;

class ArticleMetadata
{
	/**
	 * The page
	 *
	 * @var object
	 */
	public $page;

	/**
	 * Constructor
	 *
	 * @param  object $page
	 * @return void
	 */
	public function __construct($page)
	{
		$page->title = $page->title ?: $page->headline;
		$page->content = $page->toHtml();

		$this->page = $page;
	}
}
