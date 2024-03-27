<?php
namespace App\Modules\Pages\Events;

use App\Modules\Pages\Models\Page;

class PageTitleAfterDisplay
{
	/**
	 * The page
	 *
	 * @var Page
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
	public function __construct(Page $page)
	{
		$this->page = $page;
		$this->content = '';
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Set the body
	 *
	 * @param string $content
	 * @return void
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	/**
	 * Get the page model
	 *
	 * @return Page
	 */
	public function getPage(): Page
	{
		return $this->page;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getContent();
	}
}
