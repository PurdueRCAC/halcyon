<?php

namespace App\Modules\Knowledge\Events;

class PageMetadata
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
		$this->page = $page;
	}
}
