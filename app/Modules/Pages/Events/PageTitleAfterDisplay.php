<?php

namespace App\Modules\Pages\Events;

class PageTitleAfterDisplay
{
	/**
	 * The page
	 *
	 * @var object
	 */
	private $page;

	/**
	 * The content to render after the title
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Constructor
	 *
	 * @param  Page $page
	 * @return void
	 */
	public function __construct($page)
	{
		$this->page = $page;
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set the body
	 *
	 * @param string $title
	 * @return void
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * Get the original, unaltered title
	 *
	 * @return mixed
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getContent();
	}
}
